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
 * Checking if the completion_progress block is present in a course.
 *
 * @package    block_course_checker
 * @copyright  2019 Adrian Perez, Fernfachhochschule Schweiz (FFHS) <adrian.perez@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker\checkers\checker_completionprogress;

defined('MOODLE_INTERNAL') || die();

use block_course_checker\check_result;
use block_course_checker\model\check_plugin_interface;
use block_course_checker\model\check_result_interface;
use block_course_checker\model\checker_config_trait;

class checker implements check_plugin_interface {
    use checker_config_trait;
    // Block name for assignments in Moodle.
    const BLOCK_TYPE_COMPLETIONPROGRESS = 'completion_progress';

    /**
     * Initialize checker by setting it up with the configuration
     */
    public function init() {
        // Load settings.
        return;
    }

    /**
     * Runs the check if completion progress block is add and visible in course.
     *
     * @param \stdClass $course The course itself.
     * @return check_result_interface The check result.
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function run($course) {
        global $DB;

        // Get active setting checks from configuration.
        $this->init();

        // Initialize check result array.
        $this->result = new check_result();

        // Loading blocks and instances for the region.
        $context = $DB->get_record('context', ['instanceid' => $course->id, 'contextlevel' => CONTEXT_COURSE]);
        $blockexists = $DB->get_record('block_instances', [
                'blockname' => self::BLOCK_TYPE_COMPLETIONPROGRESS,
                'parentcontextid' => $context->id
        ]);

        if (isset($blockexists->id)) {
            // The block exists and all checks have passed -> check okay.
            $message = get_string('checker_completionprogress_success', 'block_course_checker');
            $this->result->add_detail([
                    "successful" => true,
                    "message" => $message,
                    "target" => '',
                    "link" => ''
            ])->set_successful(true);
        } else {
            $message = get_string('checker_completionprogress_blockmissing', 'block_course_checker');
            $this->result->add_detail([
                    "successful" => false,
                    "message" => $message,
                    "target" => '',
                    "link" => ''
            ])->set_successful(false);
        }

        return $this->result;
    }
    /**
     * Get the group defined for this check.
     * This is used to display checks from the same group together.
     *
     * @return string
     */
    public static function get_group() {
        return 'group_activities'; // TODO: Add grouping for blocks.
    }
}