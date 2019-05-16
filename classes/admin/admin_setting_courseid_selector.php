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
namespace block_course_checker\admin;

use admin_setting_configtext_int_only;

defined('MOODLE_INTERNAL') || die();

class admin_setting_courseid_selector extends admin_setting_configtext_int_only {
    /**
     * @var bool Tells if the field can be empty.
     */
    protected $required = true;

    /**
     * @inheritDoc
     */
    public function validate($data) {
        global $PAGE;

        $data = trim($data);

        // Don't force the plugin to be fully set up when installing. This is a Moodle behaviour.
        if ($PAGE->pagelayout === 'maintenance' && strlen($data) === 0) {
            return true;
        }

        // Allow empty value.
        if (!$this->required && empty($data)) {
            return true;
        }

        // Disallow empty value.
        if ($this->required && empty($data)) {
            return get_string('fieldrequired', 'error', $this->visiblename);
        }

        // Check that the value is an int.
        if (preg_match("/^[0-9]+$/", $data) !== 1) {
            return get_string("invalidcourseid", 'error');
        }

        // Load the course to be sure it exists.
        try {
            get_course($data, false);
            return true;
        } catch (\dml_exception $exception) {
            return get_string("cannotfindcourse", 'error');
        }
    }

    /**
     * @param bool $required
     */
    public function set_required(bool $required) {
        $this->required = $required;
    }

    /**
     * @return bool
     */
    public function is_required(): bool {
        return $this->required;
    }
}