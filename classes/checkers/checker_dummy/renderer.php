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
        ob_start();

        // TODO. Render a template or something nicer that a var_dump.
        var_dump($result);

        $result = ob_get_contents();
        ob_end_clean();
        return $result;
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