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
 * This type of field should be used for config settings which contains a numeric value
 * which which is within a minimum and maximum number range.
 *
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker\admin;

class admin_setting_restrictedint extends \admin_setting_configtext {
    /**
     * @var int|null
     */
    protected $maximum = null;
    /**
     * @var int|null
     */
    protected $minimum = null;

    /**
     * @var bool Tells if the field can be empty.
     */
    protected $required = true;

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
            return get_string("invalidadminsettingname", 'error', $this->visiblename);
        }

        if ($this->maximum !== null) {
            if ($data > $this->maximum) {
                return get_string('admin_restrictedint_max', 'block_course_checker', $this->maximum);
            }
        }

        if ($this->minimum !== null) {
            if ($data < $this->minimum) {
                return get_string('admin_restrictedint_min', 'block_course_checker', $this->minimum);
            }
        }

        return parent::validate($data);
    }

    /**
     * @param int|null $maximum
     * @return $this
     */
    public function set_maximum(int $maximum = null) {
        $this->maximum = $maximum;

        return $this;
    }

    /**
     * @param int|null $minimum
     * @return $this
     */
    public function set_minimum($minimum) {
        $this->minimum = $minimum;

        return $this;
    }
}
