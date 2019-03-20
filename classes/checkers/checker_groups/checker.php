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
namespace block_course_checker\checkers\checker_groups;

defined('MOODLE_INTERNAL') || die();

use block_course_checker\check_result;
use block_course_checker\model\check_result_interface;

/**
 * Checking the group submission settings on
 * assignments for a course.
 *
 * @package block_course_checker
 */
class checker implements \block_course_checker\model\check_plugin_interface {

    // Module name for assignments in moodle
    const MOD_TYPE_ASSIGN = 'assign';

    /**
     * Runs the check on group assignment submissions for all assignments
     * of a course
     *
     * @param \stdClass $course The course itself.
     * @return check_result_interface The check result.
     */
    public function run($course) {
        global $DB;

        // Initalize check result array.
        $check_result = new check_result();

        // Get the id of the module assign in moodle.
        $module_assign = $DB->get_record('modules',
            array('name' => self::MOD_TYPE_ASSIGN));
        $assignmentmodid = $module_assign->id;

        // Get all assignment activities for the course.
        $modinfo = get_fast_modinfo($course);

        foreach ($modinfo->cms as $cm) {

            // Skip activities that are not assignements.
            if ($cm->modname != self::MOD_TYPE_ASSIGN) {
                continue;
            }

            // Skip activities that are not visible.
            if (!$cm->uservisible or !$cm->has_view()) {
                continue;
            }

            // Get the assignment record from the assignment table.
            // The instance of the course_modules table is used as a foreign key to the assign table.
            $assign = $DB->get_record('assign',
                array('course'=> $course->id, 'id'=> $cm->instance));

            // Make the url to the activity.
            $assign_url = new \moodle_url('/mod/assign/view.php',
                array('id' => $cm->id));

            // Get the settings from the assign table: these are the settings used for group submission.
            $groupmode = $assign->teamsubmission;
            $groupingid = $assign->teamsubmissiongroupingid;

            // Now the groups settings can be checked.
            // These are the settings of assignment group submission in the corresponding activity.

            // Case 1: the group mode deactivated: okay.
            if ($groupmode == 0) {
                $check_result->add_detail([
                    "successful" => true,
                    "message" => get_string('group_deactivated', 'block_course_checker'),
                    "link" => $assign_url
                ]);
                continue;
            }

            // Case 2: the group mode is activated.

            // If the groupingid is not set -> check fails.
            if ($groupingid == 0) {
                $check_result->add_detail([
                    "successful" => false,
                    "message" => get_string('groupid_missing', 'block_course_checker'),
                    "link" => $assign_url
                ]);
                continue;
            }

            // If the grouping does not exist -> check fails.
            $groupingexists = $DB->record_exists('groupings', array('id' => $groupingid));
            if (!$groupingexists) {
                $check_result->add_detail([
                    "successful" => false,
                    "message" => get_string('groups_missing', 'block_course_checker'),
                    "link" => $assign_url
                ]);
                continue;
            }

            // If the grouping has less then 2 groups -> check fails.
            $groupcount = $DB->count_records('groupings_groups', array('groupingid' => $groupingid));
            if ($groupcount < 2) {
                $check_result->add_detail([
                    "successful" => false,
                    "message" => get_string('lessthentwogroups', 'block_course_checker'),
                    "link" => $assign_url
                ]);
                continue;
            }

            // The group submission is activated and all checks have passed -> check okay.
            $check_result->add_detail([
                "successful" => true,
                "message" => get_string('groupsdefined', 'block_course_checker'),
                "link" => $assign_url
            ]);
        }

        // Return the check results.
        return $check_result;
    }

}