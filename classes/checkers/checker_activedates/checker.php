<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Checking if all dates are disabled. In reference courses no dates should be enabled.
 *
 * @package    block_course_checker
 * @copyright  2020 FFHS <christoph.karlen@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker\checkers\checker_activedates;

defined('MOODLE_INTERNAL') || die();

use block_course_checker\check_result;
use block_course_checker\model\check_plugin_interface;
use block_course_checker\model\check_result_interface;
use block_course_checker\model\checker_config_trait;
use block_course_checker\model\mod_type_interface;

class checker implements check_plugin_interface, mod_type_interface {
    use checker_config_trait;
    
    private $checkresult;
    
    /**
     * @var array
     */
    private $modtypstocheck = [
            self::MOD_TYPE_ASSIGN => [
                    'allowsubmissionsfromdate' => false,
                    'duedate' => false,
                    'cutoffdate' => false,
                    'gradingduedate' => false
            ],
            self::MOD_TYPE_CHOICE => [
                    'timeopen' => 'choiceopen',
                    'timeclose' => 'choiceclose'
            ],
            self::MOD_TYPE_CHOICEGROUP => [
                    'timeopen' => 'choicegroupopen',
                    'timeclose' => 'choicegroupclose'
            ],
            self::MOD_TYPE_FEEDBACK => [
                    'timeopen' => 'feedbackopen',
                    'timeclose' => 'feedbackclose'
            ],
            self::MOD_TYPE_QUESTIONNAIRE => [
                    'opendate' => false,
                    'closedate' => false
            ],
            self::MOD_TYPE_QUIZ => [
                    'timeopen' => 'quizopen',
                    'timeclose' => 'quizclose'
            ],
            self::MOD_TYPE_LESSON => [
                    'available' => false,
                    'deadline' => false
            ],
            self::MOD_TYPE_DATA => [
                    'timeavailablefrom' => 'availablefromdate',
                    'timeavailableto' => 'availabletodate',
                    'timeviewfrom' => 'viewfromdate',
                    'timeviewto' => 'viewtodate'
            ],
            self::MOD_TYPE_FORUM => [
                    'duedate' => false,
                    'cutoffdate' => false
            ],
            self::MOD_TYPE_SCORM => [
                    'timeopen' => false,
                    'timeclose' => false
            ],
            self::MOD_TYPE_WORKSHOP => [
                    'submissionstart' => false,
                    'submissionend' => false,
                    'assessmentstart' => false,
                    'assessmentend' => false
            ]
    ];
    
    /**
     * @param \stdClass $course
     * @return check_result|check_result_interface
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function run($course) {
        // Initialize check result array.
        $this->checkresult = new check_result();
        
        // Get all assignment activities for the course.
        $modinfo = get_fast_modinfo($course);
        foreach ($modinfo->cms as $cm) {
            // Skip activities that are not visible.
            if (!$cm->uservisible or !$cm->has_view()) {
                continue;
            }
            
            // Search for problems in the "Activity completion" section.
            if ($cm->completionexpected !== 0) {
                $message = get_string('activedates_noactivedates', 'block_course_checker');
                $this->checkresult->add_detail([
                        "successful" => false,
                        "message" => $message,
                        "target" => $this->get_target($cm),
                        "link" => $this->get_link_to_modedit_page($cm)
                ])->set_successful(false);
            }
            
            // Search for custom date fields in different activities.
            foreach ($this->modtypstocheck as $modtypekey => $fields) {
                $this->check_mod_date_fields(
                        $cm,
                        $modtypekey,
                        $fields
                );
            }
        }
        // Return the check results.
        return $this->checkresult;
    }
    
    /**
     * @param $cm
     * @param $modtype
     * @param $fields
     * @param bool $table
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    private function check_mod_date_fields($cm, $modtype, $fields, $table = false) {
        global $DB;
        $adateissetin = [];
        
        // We only want to test some modules.
        if ($cm->modname != $modtype) {
            return;
        }
        
        // Usually base table names of a module corresponds to the modname.
        if (!$table) {
            $table = $modtype;
        }
        
        $coursemodule = $DB->get_record($table, array('id' => $cm->instance), implode(',', array_keys($fields)));
        foreach ($fields as $field => $languagekey) {
            if ($coursemodule->$field != 0) {
                $adateissetin[] = self::get_field_translation($field, $languagekey, $modtype);
            }
        }
        
        if (!empty($adateissetin)) {
            $message = get_string(
                    "activedates_noactivedatesinactivity",
                    "block_course_checker",
                    array(
                            'modtype' => $modtype,
                            'adateissetin' => implode(', ', $adateissetin)
                    )
            );
            $this->checkresult->add_detail([
                    "successful" => false,
                    "message" => $message,
                    "target" => $this->get_target($cm),
                    "link" => $this->get_link_to_modedit_page($cm)
            ])->set_successful(false);
        }
    }
    
    /**
     * @param \cm_info $cm
     * @return string
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    private function get_link_to_modedit_page(\cm_info $cm) {
        $url = new \moodle_url('/course/mod.php', [
                "sesskey" => sesskey(),
                "update" => $cm->id,
                "sr" => 0
        ]);
        $link = $url->out_as_local_url(false);
        return $link;
    }
    
    /**
     * @param \cm_info $cm
     * @return string
     * @throws \coding_exception
     */
    private function get_target(\cm_info $cm) {
        $targetcontext = (object) ["name" => strip_tags($cm->name)];
        $target = get_string("groups_activity", "block_course_checker", $targetcontext);
        return $target;
    }

    /**
     * Get the customfield string from Moodle core.
     * Most times the lang string identifier and the db field name are the same.
     *
     * @param $field
     * @param $languagekey
     * @param $modtype
     * @return string
     * @throws \coding_exception
     */
    private static function get_field_translation($field, $languagekey, $modtype) {
        if ($languagekey) {
            return get_string($languagekey, $modtype);
        }
        return get_string($field,  $modtype);
    }

    /**
     * Get the group defined for this check.
     * This is used to display checks from the same group together.
     *
     * @return string
     */
    public static function get_group() {
        return 'group_activities';
    }
}