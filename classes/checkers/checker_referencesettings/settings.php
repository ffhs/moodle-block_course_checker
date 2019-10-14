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
 * Settings for checking the course settings compared to a reference course
 *
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @author     2019 Christoph Karlen, Fernfachhochschule Schweiz (FFHS) <christoph.karlen@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use block_course_checker\checkers\checker_referencesettings\checker;

/** @var admin_settingpage $setting */
$setting;

/** @var array of coursesettings_fields to check $choices */
$choices = [
    // General.
    'category' => get_string('category'),
    'visible' => get_string('visible'),
    'startdate' => get_string('startdate'),
    // Summary.
    'summary' => get_string('summary'),
    // Course Format.
    'format' => get_string('format'),
    // Appearance.
    'showgrades' => get_string('showgrades'),
    'newsitems' => get_string('newsitemsnumber'),
    'lang' => get_string('forcelanguage'),
    'showreports' => get_string('showreports'),
    // Files and uploads.
    'legacyfiles' => get_string('courselegacyfiles'),
    'maxbytes' => get_string('maximumupload'),
    // Completion Tracking.
    'enablecompletion' => get_string('enablecompletion', 'completion'),
];

// Referencesettings Checker Checklist settings.
$visiblename = get_string('checker_referencesettings_checklist', 'block_course_checker');
$description = new lang_string('checker_referencesettings_checklist_help', 'block_course_checker');
$checklist = new admin_setting_configmulticheckbox(checker::REFERENCE_COURSE_SETTINGS,
    $visiblename, $description, checker::REFERENCE_COURSE_SETTINGS_DEFAULT, $choices);
$setting->add($checklist);
