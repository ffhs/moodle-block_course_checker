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
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker\checkers\checker_groups;

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
     * Runs the check on group assignment submissions for all assignments of a course
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
        // Get all assignment activities in the course.
        $modinfo = get_fast_modinfo($course);
        $cms = $modinfo->get_instances_of(self::MOD_TYPE_ASSIGN);
        foreach ($cms as $cm) {
            // Skip activities that are not visible.
            if (!$cm->uservisible or !$cm->has_view()) {
                continue;
            }
            $target = resolution_link_helper::get_target($cm, 'checker_groups');
            $link = resolution_link_helper::get_link_to_modedit_or_view_page($cm->modname, $cm->id);
            // Get the assignment record from the assignment table.
            // The instance of the course_modules table is used as a foreign key to the assign table.
            $assign = $DB->get_record('assign',
                    ['course' => $course->id, 'id' => $cm->instance]);
            // Get the settings from the assign table: these are the settings used for group submission.
            $groupmode = $assign->teamsubmission;
            $groupingid = $assign->teamsubmissiongroupingid;
            // Now the groups settings can be checked.
            // These are the settings of assignment group submission in the corresponding activity.
            // Case 1: the group mode is deactivated -> check okay.
            if ($groupmode == 0) {
                $message = get_string('groups_deactivated', 'block_course_checker');
                $checkresult->add_detail([
                        "successful" => true,
                        "message" => $message,
                        "target" => $target,
                        "link" => $link
                ]); // Note that set_successful should not be false here.
                continue;
            }

            // Case 2: the group mode is activated.
            // If the groupingid is not set -> check fails.
            if ($groupingid == 0) {
                $message = get_string('groups_idmissing', 'block_course_checker');
                $checkresult->add_detail([
                        "successful" => false,
                        "message" => $message,
                        "target" => $target,
                        "link" => $link
                ])->set_successful(false);
                continue;
            }
            // If the grouping does not exist -> check fails.
            $groupingexists = $DB->record_exists('groupings', array('id' => $groupingid));
            if (!$groupingexists) {
                $message = get_string('groups_missing', 'block_course_checker');
                $checkresult->add_detail([
                        "successful" => false,
                        "message" => $message,
                        "target" => $target,
                        "link" => $link
                ])->set_successful(false);
                continue;
            }
            // If the grouping has less then 2 groups -> check fails.
            $groupcount = $DB->count_records('groupings_groups', array('groupingid' => $groupingid));
            if ($groupcount < 2) {
                $message = get_string('groups_lessthantwogroups', 'block_course_checker');
                $checkresult->add_detail([
                        "successful" => false,
                        "message" => $message,
                        "target" => $target,
                        "link" => $link
                ])->set_successful(false);
                continue;
            }
            // The group submission is activated and all checks have passed -> check okay.
            $message = get_string('groups_success', 'block_course_checker');
            $checkresult->add_detail([
                    "successful" => true,
                    "message" => $message,
                    "target" => $target,
                    "link" => $link
            ]);
        }
        // Return the check results.
        return $checkresult;
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