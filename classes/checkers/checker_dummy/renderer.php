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
        if ($result->is_successful()) {
            $render .= '<h4 class="text-success">Success</h4>';
            $render .= '<div class="table-responsive">';
            $render .= '<table class="table">';
            $render .= '<thead><tr>';
            $render .= '<th scope="col">Result</th><th scope="col">Message</th><th scope="col">Link</th>';
            $render .= '</thead></tr>';
            $render .= '<tbody>';
            foreach ($resultdetail as $index => $detail) {
                $render .= '<tr>';
                $render .= '<td>' . $detail['successful'] . '</td>';
                $render .= '<td>' . $detail['message'] . '</td>';
                $render .= '<td>' . $detail['link'] . '</td>';
                $render .= '</tr>';
            }
            $render .= '</tbody>';
            $render .= '</table>';
            $render .= '</div>';
        } else {
            $render .= '<h4 class="text-warning">Failure</h4>';
        }

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