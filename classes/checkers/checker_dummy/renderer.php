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

namespace block_course_checker\checkers\checker_dummy;

defined('MOODLE_INTERNAL') || die();

use block_course_checker\model\check_result_interface;

class renderer extends \block_course_checker\abstract_plugin_renderer {

    /**
     * Output a check_result for inside the block
     *
     * @param check_result_interface $result
     * @return string
     */
    public function render_for_block(check_result_interface $result): string {
        $render = '';
        $resultdetail = $result->get_details();
        $link = $result->get_link();

        $tableheaders = ['Result', 'Message', 'Link'];

        $render .= $result->is_successful() ? \html_writer::label('Success', null, true, ['class' => 'text-success']):
            \html_writer::label('Failure', null, true, ['class' => 'text-warning']);

        $render .= \html_writer::start_tag('div', ['class' => 'table-responsive']);
        $render .= \html_writer::start_tag('table', ['class' => 'table']);
        $render .= \html_writer::start_tag('thead');
        $render .= \html_writer::start_tag('tr');
        foreach ($tableheaders as $tableheader) {
            $render .= \html_writer::tag('th', $tableheader, ['class' => 'col']);
        }
        $render .= \html_writer::end_tag('thead');
        $render .= \html_writer::end_tag('tr');
        $render .= \html_writer::start_tag('tbody');
        foreach ($resultdetail as $index => $detail) {
            $render .= \html_writer::start_tag('tr');
            $render .= \html_writer::tag('td', $detail['successful']);
            $render .= \html_writer::tag('td', $detail['message']);
            $render .= \html_writer::tag('td', $detail['link']);
            $render .= \html_writer::end_tag('tr');
        }
        $render .= \html_writer::end_tag('tbody');
        $render .= \html_writer::end_tag('table');
        $render .= \html_writer::end_tag('div');
        return $render;
    }

    /**
     * Output a check_result for inside the result page
     *
     * @param check_result_interface $result
     * @return string
     */
    public function render_for_page(check_result_interface $result): string {
        return "This is the output for a page";
    }
}