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
 * Course list block settings
 *
 * @package    block_course_list
 * @copyright  2007 Petr Skoda
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_course_checker\admin\admin_setting_courseid_selector;
use block_course_checker\plugin_manager;

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $name = get_string("admin_referencecourseid", "block_course_checker");
    $settings->add(new admin_setting_courseid_selector('block_course_checker/referencecourseid', $name, '', null));

    foreach (plugin_manager::instance()->get_checkers_setting_files() as $checkername => $settingfile) {
        $checkernamedisplay = get_string($checkername, 'block_course_checker');
        $checkernamedisplay = get_string('settings_checker_header', 'block_course_checker', $checkernamedisplay);

        // We provide a fake settingpage so each checker can use it to add his own settings.
        $setting = new admin_settingpage("block_course_checker/" . $checkername . "_page", $checkernamedisplay);

        // Include the checker's setting file so it can only alter "$setting".
        $setting = call_user_func(function() use ($setting, $settingfile, $checkername) {
            require($settingfile);
            return $setting;
        });

        // We add the settings only if the plugin itself did not set the value to null. (This is a Moodle beaviour).
        if ($setting === null) {
            continue;
        }

        // Loop trough each setting that the plugin added and move them to the global settings.
        foreach ($setting->settings as $checkersetting) {
            // Add a friendly header.
            $heading = new admin_setting_heading("block_course_checker/" . $checkername . "_heading", $checkernamedisplay, '');
            $settings->add($heading);
            $settings->add($checkersetting);
        }
    }
}


