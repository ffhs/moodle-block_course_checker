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
 * @package     block_course_checker
 * @copyright   2020 Christoph Karlen, Fernfachhochschule Schweiz (FFHS) <christoph.karlen@ffhs.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_course_checker\model\check_edit_form_interface;

defined('MOODLE_INTERNAL') || die();

class checker_links_edit_form implements check_edit_form_interface {

    /**
     * @var string $checkername
     */
    public $checkername;

    /**
     * @var string $truecheckername
     */
    public $truecheckername;

    /**
     * @param object $mform
     * @return object $mform
     * @throws coding_exception
     */
    public function specific_definition($mform) {
        // Whitelist for block-specific links.
        $mform->addElement('textarea', 'config_link_whitelist',
                get_string('checker_links_setting_whitelist', 'block_course_checker'));
        $mform->setType('config_link_whitelist', PARAM_TEXT);
        $mform->addHelpButton('config_link_whitelist', 'checker_links_setting_whitelist', 'block_course_checker');
        return $mform;
    }

    /**
     * @param $data
     * @param $files
     * @param $errors
     * @return mixed
     * @throws coding_exception
     */
    public function validation($data, $files, $errors) {
        if (!isset($data['config_link_whitelist'])) {
            return $errors;
        }

        if (trim($data['config_link_whitelist']) == "") {
            return $errors;
        }

        $domains = array_filter(array_map('trim', explode("\n", $data['config_link_whitelist'])));
        foreach ($domains as $domainname) {
            if (!is_valid_domain_name($domainname)) {
                $errors['config_link_whitelist'] = get_string('admin_domain_name_notvalid', 'block_course_checker', $domainname);
            };
        }

        return $errors;
    }
}