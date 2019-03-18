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

namespace block_course_checker\checkers;

defined('MOODLE_INTERNAL') || die();

use block_course_checker\model\check_result_interface;

class renderer extends \block_course_checker\abstract_plugin_renderer {

    public function render_block(check_result_interface $result): string {
        return "This is the output for a block";
    }

    public function render_pager(check_result_interface $result): string {
        return "This is the output for a page";
    }
}