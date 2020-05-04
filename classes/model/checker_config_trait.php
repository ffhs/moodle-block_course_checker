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
 *
 * Used to simplify the reading of config values
 *
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker\model;

use dml_exception;

defined('MOODLE_INTERNAL') || die();

trait checker_config_trait {
    /**
     * Read a setting based on plugin name.
     *
     * @param $name
     * @param null $defaultvalue
     * @return mixed|null
     * @throws dml_exception
     */
    protected function get_config($name, $defaultvalue = null) {
        list($plugin, $name) = explode("/", $name);
        $value = get_config($plugin, $name);
        if ($value) {
            return $value;
        }
        return $defaultvalue;
    }
}