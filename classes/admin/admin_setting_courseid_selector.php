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
 * This type of field should be used for config settings which contains a courseid.
 *
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker\admin;

defined('MOODLE_INTERNAL') || die();

class admin_setting_courseid_selector extends admin_setting_restrictedint {

    /**
     * @inheritDoc
     */
    public function validate($data) {
        // Be sure the value is an int.
        $validate = parent::validate($data);
        if ($validate !== true) {
            return $validate;
        }

        // Load the course to be sure it exists.
        try {
            get_course($data, false);
            return true;
        } catch (\dml_exception $exception) {
            return get_string("cannotfindcourse", 'error');
        }
    }
}