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
 * Settings for checking active dates of activities inside the course
 *
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use block_course_checker\admin\admin_setting_pickmodules;

/** @var admin_settingpage $setting */
$setting;

$visiblename = get_string('activedates_setting_coursesregex', 'block_course_checker');
$description = get_string('activedates_setting_coursesregex_help', 'block_course_checker');
$coursesregex = new admin_setting_configtext_with_advanced('block_course_checker/checker_activedates_coursesregex',
        $visiblename,
        $description,
        ['value' => '', 'adv' => false]);
$setting->add($coursesregex);

$visiblename = get_string('activedates_setting_modules', 'block_course_checker');
$description = get_string('activedates_setting_modules_help', 'block_course_checker');
$modules = new admin_setting_pickmodules('block_course_checker/activedates_modules', $visiblename, $description, []);
$setting->add($modules);
