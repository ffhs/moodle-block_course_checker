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
        return $this->render_generic($result, false);
    }


    /**
     * Output a check_result for inside the page
     *
     * @param check_result_interface $result
     * @return string
     */
    public function render_for_page(check_result_interface $result): string {
        return $this->render_generic($result, true);
    }

    /**
     * Build up the check results table
     *
     * @param check_result_interface $result
     * @param bool $showdetails
     * @return string
     */
    protected function render_generic(check_result_interface $result, $showdetails = true) :string {
        $render = '';
        $resultdetail = $result->get_details();
        $globallink = $result->get_link();

        $render .= $result->is_successful() ?
            \html_writer::tag('h4', 'Success', ['class' => 'text-success']) :
            \html_writer::tag('h4', 'Failure', ['class' => 'text-warning']);

        $render .= \html_writer::start_tag('div', ['class' => 'table-responsive']);
        $render .= \html_writer::start_tag('table', ['class' => 'table']);
        $render .= \html_writer::start_tag('thead');
        $render .= \html_writer::start_tag('tr');

        $tableheaders = ['result', 'message', 'link'];
        $tableheaders = array_map(function($el) {
            return get_string($el, "block_course_checker");
        }, $tableheaders);
        foreach ($tableheaders as $tableheader) {
            $render .= \html_writer::tag('th', $tableheader, ['class' => 'col w-25']);
        }
        $render .= \html_writer::end_tag('thead');
        $render .= \html_writer::end_tag('tr');
        $render .= \html_writer::start_tag('tbody');

        $icons = [
            'success' => \html_writer::tag('i', null, ['class' => 'fas fa-check-circle text-success']),
            'failure' => \html_writer::tag('i', null, ['class' => 'fas fa-times text-danger']),
            'link' => \html_writer::tag('i', null, ['class' => 'fas fa-link text-muted'])
        ];
        foreach ($resultdetail as $index => $detail) {
            $humanresult = $detail['successful'] ? $icons['success'] : $icons['failure'];
            $render .= \html_writer::start_tag('tr');
            $render .= \html_writer::tag('td', $humanresult);

            if (!array_key_exists("message_safe", $detail) || !$detail["message_safe"]) {
                $message = s($detail['message']);
            } else {
                $message = $detail['message'];
            }
            $render .= \html_writer::tag('td', $message);

            if ($detail['link'] != null) {
                $render .= \html_writer::tag('td', \html_writer::link($detail['link'], $icons['link']));
            }
            $render .= \html_writer::end_tag('tr');
        }
        $render .= \html_writer::end_tag('tbody');
        $render .= \html_writer::end_tag('table');
        $render .= \html_writer::end_tag('div');
        if ($globallink != null) {
            $render .= \html_writer::start_div('mt-1');
            $render .= \html_writer::label(get_string('resolutionlink', 'block_course_checker'),
                null, true, ['class' => 'mr-1']
            );
            $render .= \html_writer::link($globallink, $globallink, ['class' => 'font-weight-bold']);
            $render .= \html_writer::end_div();
        }
        return $render;
    }
}