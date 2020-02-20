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
 * Checking the group submission settings on
 * assignments for a course.
 *
 * @package    block_course_checker
 * @copyright  2019 FFHS <christoph.karlen@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker\checkers\checker_data;

defined('MOODLE_INTERNAL') || die();

use block_course_checker\check_result;
use block_course_checker\model\check_plugin_interface;
use block_course_checker\model\check_result_interface;
use block_course_checker\model\checker_config_trait;

class checker implements check_plugin_interface {
    use checker_config_trait;
    // Module name for databases in Moodle.
    const MOD_TYPE_DB = 'data';

    
    /**
     * Runs the check data activities of a course
     *
     * @param \stdClass $course The course itself.
     * @return check_result_interface The check result.
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function run($course) {
        global $DB;
        // Initialize check result array.
        $checkresult = new check_result();
        // Get all assignment activities for the course.
        $modinfo = get_fast_modinfo($course);
        foreach ($modinfo->cms as $cm) {
            // Skip activities that are not assignments.
            if ($cm->modname != self::MOD_TYPE_DB) {
                continue;
            }
            // Skip activities that are not visible.
            if (!$cm->uservisible or !$cm->has_view()) {
                continue;
            }
    
            $countfields = $DB->count_records('data_fields', array('dataid' => $cm->instance));
            $target = $this->get_target($cm);
            $link = $this->get_link_to_modedit_page($cm);

            if($countfields == 0){
                $message = get_string('data_nofieldsdefined', 'block_course_checker');
                $checkresult->add_detail([
                        "successful" => false,
                        "message" => $message,
                        "target" => $target,
                        "link" => $link
                ])->set_successful(false);
                continue;
            }
    
            $message = get_string('data_fieldsdefined', 'block_course_checker');
            $checkresult->add_detail([
                    "successful" => true,
                    "message" => $message,
                    "target" => $target,
                    "link" => $link,
            ]);
        }
        // Return the check results.
        return $checkresult;
    }
    
    /**
     * @param \cm_info $cm
     * @return string
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    private function get_link_to_modedit_page(\cm_info $cm) {
        $url = new \moodle_url('/mod/data/view.php', [
                "id" => $cm->id,
                "sesskey" => sesskey()
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
     * Get the group defined for this check.
     * This is used to display checks from the same group together.
     *
     * @return string
     */
    public static function get_group() {
        return 'group_activities';
    }
}