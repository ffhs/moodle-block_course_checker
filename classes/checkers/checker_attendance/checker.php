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
 * Checking the attendance settings on
 * assignments for a course.
 *
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @author     2019 Christoph Karlen, Fernfachhochschule Schweiz (FFHS) <christoph.karlen@ffhs.ch>
 * @author     2019 Adrian Perez, Fernfachhochschule Schweiz (FFHS) <adrian.perez@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker\checkers\checker_attendance;

defined('MOODLE_INTERNAL') || die();

use block_course_checker\check_result;
use block_course_checker\model\check_plugin_interface;
use block_course_checker\model\check_result_interface;
use block_course_checker\model\mod_type_interface;

class checker implements check_plugin_interface, mod_type_interface{
    /** @var check_result */
    protected $result = null;

    /**
     * Runs the check on attendance activities of a course
     *
     * @todo investigate if we skip activities that are not visible and if we should add uservisible
     *
     * @param \stdClass $course The course itself.
     * @return check_result_interface The check result.
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function run($course) {
        // Initialize check result array.
        $this->result = new check_result();
        // List of all attendance activities in the course.
        $attendances = [];
        // Get all attendance activities for the course.
        $modinfo = get_fast_modinfo($course);
        $cms = $modinfo->get_instances_of( self::MOD_TYPE_ATTENDANCE);
        foreach ($cms as $cm) {
            // Skip activities that are not visible.
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

    /**
     * Get the defaultsetting to use in the global settings.
     *
     * @return bool
     */
    public static function get_defaultsetting() {
        return true;
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

    /*
     * Get constant of checker to use as parameter.
     */
    public static function get_modulename_constant($pluginname) {
        return constant('self::MOD_TYPE_'. strtoupper($pluginname));
    }
}
