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

defined('MOODLE_INTERNAL') || die();

/**
 * Class global_plugin_renderer
 *
 * @package block_course_checker
 */
class global_plugin_renderer extends \plugin_renderer_base {
    // Output the details as var dump.
    const DEBUG = false;

    /**
     * Output a check_result for inside the block
     *
     * @param string $pluginname
     * @param check_result_interface $result
     * @return string
     * @throws \coding_exception
     */
    public function render_for_block(string $pluginname, check_result_interface $result): string {
        $resulticon = $result->is_successful() ? $this->get_success_icon() : $this->get_failed_icon();
        $name = get_string($pluginname, "block_course_checker");
        $name = \html_writer::tag("p", $name, ["class" => "m-a-1"]);
        $output = \html_writer::tag('div', $resulticon . $name,
                ["class" => "d-flex", "style" => "justify-content: flex-start; align-items: center;"]); // TODO remove style.
        $output .= $this->debug($result);
        return $output;
    }

    /**
     * @return string
     */
    private function get_success_icon() {
        return \html_writer::tag('i', null, ['class' => 'fa fa-check-circle text-success']);
    }

    /**
     * @return string
     */
    private function get_failed_icon() {
        return \html_writer::tag('i', null, ['class' => 'fa fa-times text-danger']);
    }

    /**
     * Output a check_result for inside the page
     *
     * @param string $pluginname
     * @param check_result_interface $result
     * @return string
     * @throws \coding_exception
     */
    public function render_for_page(string $pluginname, check_result_interface $result): string {
        $name = get_string($pluginname, "block_course_checker") . ": ";
        $resultdetail = $result->get_details();
        $globallink = $result->get_link();

        $success = get_string('check_successful', "block_course_checker");
        $failure = get_string('check_failed', "block_course_checker");
        $output = $result->is_successful() ?
                \html_writer::tag('h4', $name . $success, ['class' => 'text-success']) :
                \html_writer::tag('h4', $name . $failure, ['class' => 'text-warning']);

        $output .= \html_writer::start_tag('div', ['class' => 'table-responsive']);

        $output .= $this->debug($result);

        $output .= \html_writer::start_tag('table', ['class' => 'table']);
        $output .= \html_writer::start_tag('thead');
        $output .= \html_writer::start_tag('tr');

        $tableheaders = ['result', 'message', 'link'];
        $tableheaders = array_map(function($el) {
            return get_string($el, "block_course_checker");
        }, $tableheaders);
        foreach ($tableheaders as $tableheader) {
            $output .= \html_writer::tag('th', $tableheader, ['class' => 'col w-25']);
        }
        $output .= \html_writer::end_tag('thead');
        $output .= \html_writer::end_tag('tr');
        $output .= \html_writer::start_tag('tbody');

        foreach ($resultdetail as $index => $detail) {
            $resulticon = $detail['successful'] ? $this->get_success_icon() : $this->get_failed_icon();
            $output .= \html_writer::start_tag('tr');
            $output .= \html_writer::tag('td', $resulticon);

            if (!array_key_exists("message_safe", $detail) || !$detail["message_safe"]) {
                $message = s($detail['message']);
            } else {
                $message = $detail['message'];
            }
            $output .= \html_writer::tag('td', $message);

            if ($detail['link'] != null) {
                $output .= \html_writer::tag('td', \html_writer::link($detail['link'], $this->get_link_icon()));
            }
            $output .= \html_writer::end_tag('tr');
        }
        $output .= \html_writer::end_tag('tbody');
        $output .= \html_writer::end_tag('table');
        $output .= \html_writer::end_tag('div');
        if ($globallink != null) {
            $output .= \html_writer::start_div('mt-1');
            $output .= \html_writer::label(get_string('resolutionlink', 'block_course_checker'),
                    null, true, ['class' => 'mr-1']
            );
            $output .= \html_writer::link($globallink, $globallink, ['class' => 'font-weight-bold']);
            $output .= \html_writer::end_div();
        }
        return $output;
    }

    /**
     * @return string
     */
    private function get_link_icon() {
        return \html_writer::tag('i', null, ['class' => 'fa fa-link text-muted']);
    }

    /**
     * For debug purpose.
     *
     * @param check_result_interface $result
     * @return check_result_interface|false|string
     */
    private function debug(check_result_interface $result) {
        if (! self::DEBUG) {
            return '';
        }
        ob_start();
        var_dump($result->get_details());
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }
}