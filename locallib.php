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
 * Local helper library for block plugin block_course_checker.
 *
 * @package     block_course_checker
 * @copyright   2019 Adrian Perez, Fernfachhochschule Schweiz (FFHS) <adrian.perez@ffhs.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . '/classes/plugin_manager.php');

/**
 * Helper function to check if dependency plugin is installed and enabled.
 *
 * @param $dependency
 * @return array
 */
function block_course_checker_get_dependency_info($checkername, $dependency) {
    $pluginman = core_plugin_manager::instance();
    $enbledplugins = $pluginman->get_enabled_plugins($dependency[$checkername]['type']);

    if (in_array($dependency[$checkername]['name'], $enbledplugins)) {
        $dependency[$checkername]['status'] = true;
    } else {
        $dependency[$checkername]['status'] = false;
    }

    return $dependency;
}