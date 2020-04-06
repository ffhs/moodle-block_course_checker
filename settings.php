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
 * Course checker block settings
 *
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @author     2019 Adrian Perez, Fernfachhochschule Schweiz (FFHS) <adrian.perez@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_course_checker\checkers\checker_attendance\checker;
use block_course_checker\admin\admin_setting_courseid_selector;
use block_course_checker\output\block_renderer;
use block_course_checker\plugin_manager;

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading('block_course_checker/description', null,
            get_string('settings_general', 'block_course_checker')));

    // Define reference course id.
    $visiblename = get_string("settings_referencecourseid", "block_course_checker");
    $settings->add(new admin_setting_courseid_selector('block_course_checker/referencecourseid', $visiblename, '', SITEID));

    // Define the global roles which are allowed to use the manual check form.
    $visiblename = get_string('settings_rolesallowedmanual', 'block_course_checker', null, true);
    $description = get_string('settings_rolesallowedmanual_description', 'block_course_checker', null, true);
    $settings->add(new admin_setting_pickroles(
            block_renderer::ROLESALLOWEDMANUAL_SETTING,
            $visiblename, $description,
            block_renderer::ROLESALLOWEDMANUAL_DEFAULT
    ));

    // Get checker plugins settings.
    $manager = plugin_manager::instance();
    foreach ($manager->get_checkers_plugins() as $checkername => $plugin) {
        $truecheckername = get_string($checkername, 'block_course_checker');
        $checkernamedisplay = get_string($checkername, 'block_course_checker');
        $checkernamedisplay = get_string('settings_checker_header', 'block_course_checker', $checkernamedisplay);

        // We provide a fake settingpage so each checker can use it to add his own settings.
        $setting = new admin_settingpage("block_course_checker/" . $checkername . "_page", $checkernamedisplay);

        // Include the checker's setting file so it can only alter "$setting".
        $setting = call_user_func(function() use ($setting, $plugin, $checkername, $manager) {
            $settingfile = $manager->get_checker_setting_file($checkername);
            if (null != $settingfile) {
                require($settingfile);
                return $setting;
            }
        });

        // Add a friendly header.
        $heading = new admin_setting_heading("block_course_checker/" . $checkername . "_heading", $checkernamedisplay, '');
        $settings->add($heading);

        // Check if checker has a dependency to another plugin.
        $dependency = $manager->get_checker_dependency_info($checkername);
        if (!$dependency['status']) {
            $param = checker::get_modulename_constant($dependency['name']);
            if (!$param) {
                $param = 'requirements';
            } else {
                $param = $dependency['type'] . '_' . $dependency['name'];
            }
            $settings->add(new admin_setting_description('block_course_checker/' . $checkername . '_info', '',
                    get_string('settings_checker_dependency', 'block_course_checker', $param)));
        } else {
            $visiblename = get_string('settings_checker_toggle', 'block_course_checker', $truecheckername);
            $settings->add(new admin_setting_configcheckbox("block_course_checker/" . $checkername . '_status', $visiblename, null,
                    true));
            if (!$manager->get_checker_status($checkername)) {
                $visiblename = get_string('settings_checker_hide', 'block_course_checker', $truecheckername);
                $settings->add(new admin_setting_configcheckbox("block_course_checker/" . $checkername . '_hidden', $visiblename,
                        null, false));
            }
        }

        // We add the settings only if the plugin itself did not set the value to null. (This is a Moodle behaviour).
        if ($setting === null) {
            continue;
        }
        // Loop trough each setting that the plugin added and move them to the global settings.
        foreach ($setting->settings as $checkersetting) {
            $settings->add($checkersetting);
        }
    }
}


