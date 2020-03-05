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
 * Renderer for the whole course checker block
 *
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker\output;

use block_course_checker\model\checker_config_trait;

defined('MOODLE_INTERNAL') || die();

class block_renderer extends \plugin_renderer_base {
    use checker_config_trait;
    
    const ROLESALLOWEDMANUAL_SETTING = 'block_course_checker/checker_rolesallowedmanual';
    const ROLESALLOWEDMANUAL_DEFAULT = array();
    
    /**
     * @param $context
     * @return bool|string
     * @throws \moodle_exception
     */
    public function renderer($context) {
        return $this->render_from_template("block_course_checker/full_block", $context);
    }

    /**
     * @param $context
     * @return string
     * @throws \moodle_exception
     */
    public function renderer_human_check_form(int $courseid, string $manualreason = null) {
        global $CFG;
        global $USER;
    
        $rolesallowedmanual = $this->get_config(self::ROLESALLOWEDMANUAL_SETTING, self::ROLESALLOWEDMANUAL_DEFAULT);
        if(!user_has_role_in_system($USER->id, $rolesallowedmanual)) {
            return "";
        }
        
        $humanplaceholder = get_string('humancheck_comment_placeholder', 'block_course_checker');
        $humanreasonpresent = !empty($manualreason);
        $data = [
                "action" => new \moodle_url('/blocks/course_checker/update_human_date.php', ['courseid' => $courseid]),
                "humanreasonpresent" => $humanreasonpresent,
                "humanplaceholder" => $humanplaceholder,
                "manualreason" => trim($manualreason),
        ];

        if (empty($CFG->disablelogintoken) || false == (bool) $CFG->disablelogintoken) {
            $data['token'] = \core\session\manager::get_login_token();
        }

        $dateform = new date_picker_input();
        $html = $dateform->tohtmlwriter();
        $html = str_replace('</form>', '', $html); // Removed form due to date_picker_input generate a <form> itself.
        $html = str_replace('col-md-3', '', $html);
        $html = str_replace('col-md-9', '', $html);
        $html = str_replace('form-group row', 'form-group', $html);
        $data["dateinputhtml"] = $html;

        return $this->render_from_template("block_course_checker/human_check_form", $data);
    }
}