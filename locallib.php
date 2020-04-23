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

/**
 * Checks if the user has any of the allowed global system roles.
 *
 * @param int $userid
 * @param string $systemrolesids A comma-separated whitelist of allowed system role ids.
 * @return bool
 * @throws dml_exception
 * @see https://github.com/moodleuulm/moodle-local_boostnavigation/blob/master/locallib.php
 */
function user_has_role_in_system($userid, $systemrolesids) {
    // Is the user an admin?
    if (is_siteadmin($userid)) {
        return true;
    }
    // Split system role shortnames by comma.
    $showforroles = explode(',', $systemrolesids);
    // Retrieve the assigned roles for the system context only once and remember for next calls of this function.
    static $rolesinsystemids;
    if ($rolesinsystemids == null) {
        // Get the assigned roles.
        $rolesinsystem = get_user_roles(context_system::instance(), $userid);
        $rolesinsystemids = [];
        foreach ($rolesinsystem as $role) {
            array_push($rolesinsystemids, $role->roleid);
        }
    }
    // Check if the user has at least one of the required roles.
    return count(array_intersect($rolesinsystemids, $showforroles)) > 0;
}

/**
 * Check if user has a allowed role in the course-context.
 *
 * @param $userid
 * @param $courseid
 * @param string $roles A comma-separated whitelist of allowed roles ids.
 * @return bool
 */
function user_has_given_role_in_course($userid, $courseid, $roles) {
    $hasrole = false;
    $context = \context_course::instance($courseid);

    $roles = explode(',', $roles);
    foreach ($roles as $role) {
        $hasrole = user_has_role_assignment($userid, $role, $context->id);
        if ($hasrole) {
            break;
        }
    }

    return $hasrole;
}

/**
 * Check one domain whether it is valid.
 * Taken from https://stackoverflow.com/a/4694816
 *
 * @param $domainname
 * @return bool
 */
function is_valid_domain_name($domainname) {
    return (1 === preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domainname) // Valid chars check.
            && 1 === preg_match("/^.{1,253}$/", $domainname) // Overall length check.
            && 1 === preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domainname)); // Length of each label.
}

