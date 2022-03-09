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
 * Admin setting that allows a user to pick blocks for something.
 *
 * @package    block_course_checker
 * @copyright  2020 Adrian Perez, Fernfachhochschule Schweiz (FFHS) <adrian.perez@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker\admin;

class admin_setting_pickblocks extends \admin_setting_configmulticheckbox {
    /** @var array Array of modules */
    private $blocks;

    /**
     * @param string $name Name of config variable
     * @param string $visiblename Display name
     * @param string $description Description
     * @param array $blocks Array of blocks ($value => $label) that will be enabled by default
     */
    public function __construct($name, $visiblename, $description, $blocks) {
        parent::__construct($name, $visiblename, $description, null, null);
        $this->blocks = $blocks;
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

        $blocks = \core_plugin_manager::instance()->get_enabled_plugins('block');

        foreach ($blocks as $block) {
            $blocks[$block] = get_string('pluginname', 'block_' . $block);
        }

        if ($blocks) {
            $this->choices = $blocks;
            return true;
        }

        return false;
    }
}
