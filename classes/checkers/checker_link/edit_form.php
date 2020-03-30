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
 * Settings for checking links inside the course
 *
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class checker_link_edit_form {
    /**
     * @param object $mform
     * @return object
     * @throws coding_exception
     */
    public static function specific_definition($mform){
        // Whitelist for block-specific links.
        $mform->addElement('textarea', 'config_link_whitelist', get_string('checker_link_setting_whitelist', 'block_course_checker'));
        $mform->setType('config_link_whitelist', PARAM_TEXT);
        $mform->addHelpButton('config_link_whitelist', 'checker_link_setting_whitelist', 'block_course_checker');
        return $mform;
    }
}