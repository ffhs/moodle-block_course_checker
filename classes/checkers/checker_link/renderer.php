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
namespace block_course_checker\checkers\checker_link;

defined('MOODLE_INTERNAL') || die();

use block_course_checker\global_plugin_renderer;
use block_course_checker\model\check_result_interface;

class renderer extends global_plugin_renderer {
    /**
     * @inheritdoc
     */
    public function render_for_block(check_result_interface $result): string {
        $details = $result->get_details();
        // We output the resource as an external link into the message column.
        foreach ($details as $key => &$data) {
            $details[$key]["message"] = sprintf("%s<br>%s", s($data["message"]),
                    \html_writer::link($data["resource"], '<i class="fa fa-external-link"></i>'));
            $details[$key]["message_safe"] = true;
        }
        $result->set_details($details);
        // We render it as any other checks.
        return parent::render_for_block($result);
    }
}