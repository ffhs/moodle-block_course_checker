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

    /**
     * Output a check_result for inside the block
     *
     * @param check_result_interface $result
     * @return string
     */
    public function render_for_block(check_result_interface $result) : string {
        $render = '';
        $resultdetail = $result->get_details();
        $globallink = $result->get_link();

        $render .= $result->is_successful() ?
            \html_writer::tag('h4','Success', ['class' => 'text-success']):
            \html_writer::tag('h4','Failure', ['class' => 'text-warning']);

        $render .= \html_writer::start_tag('div', ['class' => 'table-responsive']);
        $render .= \html_writer::start_tag('table', ['class' => 'table']);
        $render .= \html_writer::start_tag('thead');
        $render .= \html_writer::start_tag('tr');

        $tableheaders = ['result', 'message', 'link'];
        $tableheaders = array_map(function($el){ return get_string($el, "block_course_checker"); }, $tableheaders);
        foreach ($tableheaders as $tableheader) {
            $render .= \html_writer::tag('th', $tableheader, ['class' => 'col']);
        }
        $render .= \html_writer::end_tag('thead');
        $render .= \html_writer::end_tag('tr');
        $render .= \html_writer::start_tag('tbody');

        foreach ($resultdetail as $index => $detail) {
            $humanresult = $detail['successful'] ? '✅': '❌';
            $render .= \html_writer::start_tag('tr');
            $render .= \html_writer::tag('td', $humanresult);
            $render .= \html_writer::tag('td', $detail['message']);
            if ($detail['link'] != null) {
                $render .= \html_writer::tag('td', \html_writer::link($detail['link'], 'Resolve me'));
            }
            $render .= \html_writer::end_tag('tr');
        }
        $render .= \html_writer::end_tag('tbody');
        $render .= \html_writer::end_tag('table');
        $render .= \html_writer::end_tag('div');
        return $render;
    }

    /**
     * Output a check_result for inside the result page
     * TODO
     * @param check_result_interface $result
     * @return string
     */
    public function render_for_page(check_result_interface $result): string {
        return "This is the output for a page";
    }
}