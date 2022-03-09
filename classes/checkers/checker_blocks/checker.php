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
 * Checking if blocks are present in a course.
 *
 * @package    block_course_checker
 * @copyright  2019 Adrian Perez, Fernfachhochschule Schweiz (FFHS) <adrian.perez@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker\checkers\checker_blocks;

use block_course_checker\check_result;
use block_course_checker\model\check_plugin_interface;
use block_course_checker\model\check_result_interface;
use block_course_checker\model\checker_config_trait;
use block_course_checker\resolution_link_helper;

class checker implements check_plugin_interface {
    use checker_config_trait;

    const REFERENCE_COURSE = 'block_course_checker/referencecourseid';
    const REFERENCE_COURSE_DEFAULT = 1;

    /** @var int $referencecourseid from checker settings */
    protected $referencecourseid;

    /** @var array $enabledblocks from checker settings */
    protected $enabledblocks;

    /**
     * Runs the check if blocks are add and visible in course.
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

        // Set contexts to check.
        $context = $DB->get_record('context', ['instanceid' => $course->id, 'contextlevel' => CONTEXT_COURSE]);
        $refcontext = $DB->get_record('context', ['instanceid' => $this->referencecourseid, 'contextlevel' => CONTEXT_COURSE]);

        // Loading blocks and instances in the region.
        foreach ($this->enabledblocks as $block) {
            $courseblock = $this->get_block_in_course_by_context($block, $context);
            $refblock = $this->get_block_in_course_by_context($block, $refcontext);

            $targetcontext = (object) ["name" => strip_tags($block)];
            $target = get_string("blocks_activity", "block_course_checker", $targetcontext);
            $resolutionlink = resolution_link_helper::get_link_to_course_view_page($course->id);

            // What are the differences? (if any).
            $comparison = $this->get_comparison_string($refblock, $courseblock);

            // Skip if no block is present in both contexts.
            if (!$courseblock && !$refblock) {
                continue;
            }

            // When there aren't two blocks and blockname is not equal (for whatever reason - should not).
            if ((!$courseblock || !$refblock) || ($courseblock->blockname != $refblock->blockname)) {
                $message = get_string('blocks_error', 'block_course_checker');
                $this->result->add_detail([
                        "successful" => false,
                        "message" => $message . ' ' . $comparison,
                        "target" => $target,
                        "link" => $resolutionlink
                ])->set_successful(false);
                continue;
            }

            $message = get_string('blocks_success', 'block_course_checker');
            $this->result->add_detail([
                    "successful" => true,
                    "message" => $message,
                    "target" => $target,
                    "link" => $resolutionlink
            ]);
        }

        return $this->result;
    }

    /**
     * Initialize checker by setting it up with the configuration
     *
     */
    public function init() {
        // Load settings.
        $this->referencecourseid = (int) $this->get_config(self::REFERENCE_COURSE, self::REFERENCE_COURSE_DEFAULT);
        $this->enabledblocks = explode(',', $this->get_config('block_course_checker/blocks'));
    }

    /**
     * Get block instances by blockname and course_context.
     *
     * @param $block
     * @param \stdClass $context
     * @return bool|false|mixed|\stdClass
     * @throws \dml_exception
     */
    private function get_block_in_course_by_context($block, \stdClass $context) {
        global $DB;

        $block = $DB->get_record('block_instances', [
                'blockname' => $block,
                'parentcontextid' => $context->id
        ]);
        return $block;
    }

    /**
     * @param $setting
     * @param \stdClass $referencecourse
     * @param \stdClass $currentcourse
     * @return string
     * @throws \coding_exception
     */
    private function get_comparison_string($refblock, $courseblock): string {
        return get_string(
                'blocks_comparison',
                'block_course_checker', [
                        'valuereference' => ($refblock !== false) ? '1' : '0',
                        'valuecurrent' => ($courseblock !== false) ? '1' : '0'
                ]);
    }

    /**
     * Get the group defined for this check.
     * This is used to display checks from the same group together.
     *
     * @return string
     */
    public static function get_group() {
        return 'group_blocks';
    }

    /**
     * Get the defaultsetting to use in the global settings.
     *
     * @return bool
     */
    public static function is_checker_enabled_by_default() {
        return false;
    }
}
