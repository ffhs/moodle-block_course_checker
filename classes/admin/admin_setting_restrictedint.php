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

defined('MOODLE_INTERNAL') || die();

class admin_setting_restrictedint extends \admin_setting_configtext_int_only {
    /**
     * @var int|null
     */
    protected $maximum = null;
    /**
     * @var int|null
     */
    protected $minimum = null;

    /**
     * @inheritDoc
     */
    public function validate($data) {
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