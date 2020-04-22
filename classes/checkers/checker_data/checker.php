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
 * @copyright  2020 FFHS <christoph.karlen@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker\checkers\checker_data;

defined('MOODLE_INTERNAL') || die();

use block_course_checker\check_result;
use block_course_checker\model\check_plugin_interface;
use block_course_checker\model\check_result_interface;
use block_course_checker\model\checker_config_trait;
use block_course_checker\model\mod_type_interface;
use block_course_checker\resolution_link_helper;

class checker implements check_plugin_interface, mod_type_interface {
    use checker_config_trait;

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
        // Get all database activities in the course.
        $modinfo = get_fast_modinfo($course);
        $cms = $modinfo->get_instances_of(self::MOD_TYPE_DATA);
        foreach ($cms as $cm) {
            // Skip activities that are not visible.
            if (!$cm->uservisible or !$cm->has_view()) {
                continue;
            }

            $countfields = $DB->count_records('data_fields', array('dataid' => $cm->instance));
            $target = $this->get_target($cm);
            $link = resolution_link_helper::get_link_to_modedit_or_view_page($cm->modname, $cm->id, false);

            if ($countfields == 0) {
                $message = get_string('nofieldindatabase', self::MOD_TYPE_DATA);
                $checkresult->add_detail([
                        "successful" => false,
                        "message" => $message,
                        "target" => $target,
                        "link" => $link
                ])->set_successful(false);
                continue;
            }

            $message = get_string('data_success', 'block_course_checker');
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

    /**
     * Get the defaultsetting to use in the global settings.
     *
     * @return bool
     */
    public static function is_checker_enabled_by_default() {
        return true;
    }
}