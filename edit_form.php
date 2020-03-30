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

use block_course_checker\plugin_manager;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/locallib.php');

/**
 * Form for editing HTML block instances.
 *
 * @package     block_course_checker
 * @copyright   2018 Christoph Karlen, Fernfachhochschule Schweiz (FFHS) <christoph.karlen@ffhs.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_course_checker_edit_form extends block_edit_form {
    /**
     * @param object $mform
     * @throws coding_exception
     */
    protected function specific_definition($mform) {
        // Get checker plugins.
        $manager = plugin_manager::instance();
        foreach ($manager->get_checkers_plugins() as $checkername => $plugin) {
            $truecheckername = get_string($checkername, 'block_course_checker');
            $classname = $checkername . '_edit_form';
            
            // Include the checker's edit form file.
            $mform = call_user_func(function() use ($mform, $checkername, $manager, $classname, $truecheckername) {
                $settingfile = $manager->get_checker_edit_form_file($checkername);
                if (null == $settingfile) {
                    return $mform;
                }
                require($settingfile);
                
                if (!class_exists($classname)) {
                    return $mform;
                }
                
                // Load the checkers specific definition.
                $mform->addElement('header', $checkername. '_header', $truecheckername);
                return $classname::specific_definition($mform);
            });
        }
    }
}
