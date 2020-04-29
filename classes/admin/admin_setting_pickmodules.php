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
 * Admin setting that allows a user to pick modules for something.
 *
 * @package    block_course_checker
 * @copyright  2020 Adrian Perez, Fernfachhochschule Schweiz (FFHS) <adrian.perez@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker\admin;

use block_course_checker\checker_helper;

defined('MOODLE_INTERNAL') || die();

class admin_setting_pickmodules extends \admin_setting_configmulticheckbox {
    /** @var array Array of modules */
    private $modules;

    /**
     * @param string $name Name of config variable
     * @param string $visiblename Display name
     * @param string $description Description
     * @param array $modules Array of modules ($value => $label) that will be enabled by default
     */
    public function __construct($name, $visiblename, $description, $modules) {
        parent::__construct($name, $visiblename, $description, null, null);
        $this->modules = $modules;
    }

    /**
     * Load modules as choices.
     *
     * @return bool
     * @throws \coding_exception
     */
    public function load_choices() {
        if (during_initial_install()) {
            return false;
        }

        if (is_array($this->choices)) {
            return true;
        }

        $plugins = \core_plugin_manager::instance()->get_enabled_plugins('mod');

        // Get only plugins that supports user data reset, used in checker_userdata.
        if ($this->name == 'userdata_modules') {
            $plugins = checker_helper::get_userdata_supported_mods($plugins);
        }

        foreach ($plugins as $plugin) {
            $modules[$plugin] = get_string('modulename', $plugin);
        }

        if ($modules) {
            $this->choices = $modules;
            return true;
        }

        return false;
    }
}