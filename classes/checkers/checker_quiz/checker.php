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
 * Checking quizzes inside the course
 *
 * @package    block_course_checker
 * @copyright  2020 FFHS <christoph.karlen@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker\checkers\checker_quiz;

defined('MOODLE_INTERNAL') || die();

use block_course_checker\check_result;
use block_course_checker\model\check_plugin_interface;
use block_course_checker\model\check_result_interface;
use block_course_checker\model\mod_type_interface;
use block_course_checker\resolution_link_helper;

class checker implements check_plugin_interface, mod_type_interface {
    /** @var check_result */
    protected $checkresult = null;

    /**
     * Runs the check for all quizzes of a course.
     *
     * @param \stdClass $course
     * @return check_result|check_result_interface
     * @throws \moodle_exception
     */
    public function run($course) {
        // Initialize check result array.
        $this->checkresult = new check_result();
        // Get all quiz activities in the course.
        $modinfo = get_fast_modinfo($course);
        $instances = get_all_instances_in_courses(self::MOD_TYPE_QUIZ, [$course->id => $course]);
        foreach ($instances as $mod) {
            // Get cm_info object to use for target and resolution link.
            $cm = $modinfo->get_cm($mod->coursemodule);
            $target = resolution_link_helper::get_target($cm);
            $resolutionlink = resolution_link_helper::get_link_to_modedit_or_view_page($cm->modname, $cm->id);
            // For all quizzes we like to check if the "Maximum grade" and the "Total of marks" are the same numbers.
            $this->check_quiz_maximum_grade($mod, $resolutionlink, $target);
        }
        return $this->checkresult;
    }

    /**
     * @param $mod
     * @param string $link
     * @param null $target
     * @throws \coding_exception
     */
    protected function check_quiz_maximum_grade($mod, $link = '', $target = null) {
        if ($mod->grade != $mod->sumgrades) {
            $message = get_string(
                    'quiz_grade_sum_error',
                    'block_course_checker',
                    array(
                            'grade' => $mod->grade,
                            'sumgrades' => $mod->sumgrades
                    ));
            $this->checkresult->add_detail([
                    "successful" => false,
                    "message" => $message,
                    "target" => $target,
                    "link" => $link
            ])->set_successful(false);
        } else {
            $message = get_string(
                    'quiz_grade_sum_success',
                    'block_course_checker');
            $this->checkresult->add_detail([
                    "successful" => true,
                    "message" => $message,
                    "target" => $target,
                    "link" => $link
            ])->set_successful(true);
        }
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
     * Get the defaultsetting for this check.
     * This is used to set if the checker is enabled/disabled per default in the global settings.
     *
     * @return bool
     */
    public static function is_checker_enabled_by_default() {
        return false;
    }
}