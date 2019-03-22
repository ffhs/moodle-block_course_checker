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

    // Module name for assignments in Moodle.
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

        // Initialize check result array.
        $checkresult = new check_result();
        // Get all assignment activities for the course.
        $modinfo = get_fast_modinfo($course);

        foreach ($modinfo->cms as $cm) {

            // Skip activities that are not assignments.
            if ($cm->modname != self::MOD_TYPE_ASSIGN) {
                continue;
            }

            // Skip activities that are not visible.
            if (!$cm->uservisible or !$cm->has_view()) {
                continue;
            }

            // FIXME Sometime links are not serialized ?
            $link = $cm->url ? $cm->url->out_as_local_url() : null;
            // Get the assignment record from the assignment table.
            // The instance of the course_modules table is used as a foreign key to the assign table.
            $assign = $DB->get_record('assign',
                    ['course' => $course->id, 'id' => $cm->instance]);

            // Get the settings from the assign table: these are the settings used for group submission.
            $groupmode = $assign->teamsubmission;
            $groupingid = $assign->teamsubmissiongroupingid;

            $targetcontext = (object) ["name" => strip_tags($cm->name)];
            $target = get_string("groups_activity", "block_course_checker", $targetcontext);
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
     * @inheritdoc
     */
    public static function get_group() {
        return "course";
    }
}