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
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker;

use block_course_checker\model\check_result_interface;
use core\session\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Class global_plugin_renderer
 *
 * @package block_course_checker
 */
class global_plugin_renderer extends \plugin_renderer_base {
    // Dump debug information in the page.
    const DEBUG = false;

    /**
     * Output a check_result for inside the block
     *
     * @param string $checkername
     * @param check_result_interface $result
     * @return string
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function render_for_block(string $checkername, check_result_interface $result): string {
        global $COURSE;

        $url = new \moodle_url('/blocks/course_checker/details.php', ['id' => $COURSE->id]);
        $url .= "#result-" . $checkername;

        $output = $this->render_from_template("block_course_checker/check_block", [
                'url' => $url,
                'successful' => $result->is_successful(),
                'checkername' => $checkername,
                'checkername_display' => get_string($checkername . '_display', 'block_course_checker'),
                'rerun_html' => $this->rerun($checkername, $COURSE->id)
        ]);
        $output .= $this->debug($result);
        return $output;
    }

    /**
     * @return string
     */
    private function get_success_icon() {
        return \html_writer::tag('i', null, ['class' => 'fa fa-check text-success']);
    }

    /**
     * @return string
     */
    private function get_failed_icon() {
        return \html_writer::tag('i', null, ['class' => 'fa fa-times text-danger']);
    }

    /**
     * @return string
     */
    private function get_link_icon() {
        return \html_writer::tag('i', null, ['class' => 'fa fa-link text-muted']);
    }

    private function get_external_link_icon() {
        return \html_writer::tag('i', null, ['class' => 'text-muted fa fa-external-link']);
    }

    /**
     * Output a check_result for inside the page
     *
     * @param string $checkername
     * @param check_result_interface $result
     * @return string
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function render_for_page(string $checkername, string $lastrundate, check_result_interface $result): string {

        // Format result details.
        $resultdetails = $result->get_details();
        foreach ($resultdetails as $index => $detail) {
            $resulticon = $detail['successful'] ? $this->get_success_icon() : $this->get_failed_icon();
            if (!array_key_exists("message_safe", $detail) || !$detail["message_safe"]) {
                $message = s($detail['message']);
            } else {
                $message = $detail['message'];
            }

            // Wrap the message with a target block.
            if (isset($detail['target'])) {
                $target = $detail['target'] ? \html_writer::div(s($detail['target'])) : '';
                $classname = $detail['successful'] ? "text-success" : "text-danger";
                $message = \html_writer::tag('span', $message, ["class" => $classname]);
                $message = \html_writer::tag('span', $target . $message);
            }

            // Display a resource url at the end of the message.
            if (isset($detail["resource"]) && $detail["resource"]) {
                $message .= ' - ';
                $message .= \html_writer::link($detail["resource"], $this->get_external_link_icon(),
                        ["target" => "_blank"]);
            }

            $link = isset($detail['link']) && $detail['link'] != null ?
                    \html_writer::link($detail['link'], $this->get_link_icon()) : null;
            $resultdetails[$index] = [
                    "classname" => trim("row " . ($index % 2 == 0 ? "odd" : "")),
                    "icon" => $resulticon,
                    "message" => $message,
                    "link" => $link
            ];
        }

        // Set timestsmp for the checker as either the last run date of the block.
        // Or the last run date of a checker.
        $checkertimestamp = $result->get_timestamp();
        if (!$checkertimestamp) {
            $checkertimestamp = $lastrundate;
        }

        $context = [
                "checkername" => $checkername,
                "checkername_display" => get_string($checkername . '_display', "block_course_checker"),
                "successful" => $result->is_successful(),
                "link" => $result->get_link(),
                "checkertimestamp" => $checkertimestamp,
                "resultdetails" => $resultdetails,
                "enabled" => plugin_manager::instance()->get_checker_status($checkername)
        ];

        $output = "";
        $output .= $this->debug($checkername);
        $output .= $this->debug($context);
        $output .= $this->render_from_template("block_course_checker/check_result", $context);
        return $output;

    }

    /**
     * For debug purpose.
     *
     * @param mixed
     * @return string
     */
    private function debug($result) {
        if (!self::DEBUG) {
            return '';
        }
        ob_start();
        var_dump($result);
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

    /**
     * @param string $checkername
     * @param int $courseid
     * @return bool|string
     * @throws \moodle_exception
     */
    protected function rerun(string $checkername, int $courseid) {
        global $CFG;

        // We can rerun a check if the check is not scheduled and the whole checks are not scheduled and is not deactivated.
        $canrerun = !task_helper::instance()->is_task_scheduled($courseid, $checkername);
        $canrerun &= !task_helper::instance()->is_task_scheduled($courseid);
        $isenabled = true;
        if (plugin_manager::instance()->get_checker_status($checkername) == false) {
            $canrerun = 0;
            $isenabled = false;
        }

        // Use a "CSRF" token.
        $token = null;
        if (empty($CFG->disablelogintoken) || false == (bool) $CFG->disablelogintoken) {
            $token = manager::get_login_token();
        }

        $action = new \moodle_url("/blocks/course_checker/schedule_checker.php");

        return $this->render_from_template("block_course_checker/check_block_rerun", [
                "action" => $action,
                "course_id" => $courseid,
                "checker" => $checkername,
                "token" => $token,
                "canrerun" => $canrerun,
                "isenabled" => $isenabled,
        ]);
    }
}