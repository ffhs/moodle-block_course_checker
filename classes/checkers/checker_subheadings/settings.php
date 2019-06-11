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

defined('MOODLE_INTERNAL') || die();

use block_course_checker\checkers\checker_subheadings\checker;

/** @var admin_settingpage $setting */
$setting;

// Subheadings Checker Whitelist setting.
$visiblename = get_string('checker_subheadings_setting_whitelist', 'block_course_checker');
$description = new lang_string('checker_subheadings_setting_whitelist_help', 'block_course_checker');
$domainwhitelist = new admin_setting_configtextarea(checker::WHITELIST_SETTING,
    $visiblename, $description, checker::WHITELIST_DEFAULT, PARAM_RAW, 600);
$setting->add($domainwhitelist);
