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
namespace block_course_checker\checkers\checker_attendance;

defined('MOODLE_INTERNAL') || die();

use block_course_checker\check_result;
use block_course_checker\model\check_plugin_interface;
use block_course_checker\model\check_result_interface;

/**
 * Checking the attendance settings on
 * assignments for a course.
 *
 * @package block_course_checker
 */
class checker implements check_plugin_interface {
    /** @var check_result */
    protected $result = null;
    // Module name for attendance in Moodle.
    const MOD_TYPE_ATTENDANCE = 'attendance';
    /**
     * Runs the check
     *
     * @param \stdClass $course The course itself.
     * @return check_result_interface The check result.
     * @throws \coding_exception
     */
    public function run($course) {
        // Initialize check result array.
        $this->result = new check_result();
        // List of all attendance activities in the course.
        $attendances = [];
        // Get all attendance activities for the course.
        $modinfo = get_fast_modinfo($course);
        foreach ($modinfo->cms as $cm) {
            // Skip activities that are not attendance.
            if ($cm->modname != self::MOD_TYPE_ATTENDANCE) {
                continue;
            }
            // Skip activities that are not visible.
            // @todo investigate if uservisible is necessary.
            // if (!$cm->uservisible or !$cm->has_view()) {
            if (!$cm->has_view()) {
                continue;
            }
            $attendances[] = $cm;
        }
        // If there is no attendance activity in the course.
        if (empty($attendances)) {
            $message = get_string('attendance_missingattendanceactivity', 'block_course_checker');
            $this->result->add_detail([
                    "successful" => false,
                    "message" => $message,
                    "target" => '',
                    "link" => ''
            ])->set_successful(false);
            return $this->result;
        }
        // If there is more then one attendance activity.
        if (count($attendances) > 1) {
            $message = get_string('attendance_onlyoneattendenceactivityallowed', 'block_course_checker');
            $this->result->add_detail([
                    "successful" => false,
                    "message" => $message,
                    "target" => '',
                    "link" => ''
            ])->set_successful(false);
            return $this->result;
        }
        // Link to activity.
        $cm = $attendances[0];
        $link = $cm->url ? $cm->url->out_as_local_url() : null;
        $targetcontext = (object) ["name" => strip_tags($cm->name)];
        $target = get_string("groups_activity", "block_course_checker", $targetcontext);
        // If there are sessions in the attendance activity.
        if (count($this->get_attendance_sessions($course)) > 0) {
            $message = get_string('attendance_sessionsnotemty', 'block_course_checker');
            $this->result->add_detail([
                    "successful" => false,
                    "message" => $message,
                    "target" => $target,
                    "link" => $link
            ])->set_successful(false);
            return $this->result;
        }
        // When there are no problems.
        $message = get_string('attendance_success', 'block_course_checker');
        $this->result->add_detail([
                "successful" => true,
                "message" => $message,
                "target" => $target,
                "link" => $link
        ]);
        // Return the check results.
        return $this->result;
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
    protected function get_attendance_sessions(\stdClass $course) {
        global $DB;
        // Get all attendancesessions in a course.
        return $DB->get_records_sql("SELECT DISTINCT (ats.id), a.course, cm.course
                FROM {attendance_sessions} ats
                LEFT JOIN {attendance} a ON ats.attendanceid = a.id
                LEFT JOIN {course_modules} cm ON ats.attendanceid = cm.instance
                GROUP BY a.id,cm.course
                HAVING a.course = ? AND cm.course = ?",
                array($course->id, $course->id));
    }
}