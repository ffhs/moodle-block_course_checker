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

/**
 * Alter the message to display the url.
 *
 * @package block_course_checker\checkers\checker_link
 */
class renderer extends global_plugin_renderer {
    /**
     * @inheritdoc
     */
    public function render_for_block(string $pluginname, check_result_interface $result): string {
        $this->altermessage($result);
        return parent::render_for_block($pluginname, $result);
    }
    /**
     * @inheritdoc
     */
    public function render_for_page(string $pluginname, check_result_interface $result): string {
        $this->altermessage($result);
        return parent::render_for_page($pluginname, $result);
    }

    /**
     * Alter the message to provide a link to the url.
     *
     * @param check_result_interface $result
     */
    private function altermessage(check_result_interface $result) {
        $details = $result->get_details();
        // We output the resource as an external link into the message column.
        $attr = ["target" => "_blank"];
        foreach ($details as $key => &$data) {
            $details[$key]["message"] = sprintf("%s<br>%s", s($data["message"]),
                    \html_writer::link($data["resource"], '<i class="fa fa-external-link"></i>', $attr));
            $details[$key]["message_safe"] = true;
        }
        $result->set_details($details);
    }
}