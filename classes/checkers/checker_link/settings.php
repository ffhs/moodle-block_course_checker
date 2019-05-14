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

use block_course_checker\admin\admin_setting_restrictedint;
use block_course_checker\checkers\checker_link\checker;

/** @var admin_settingpage $setting */
$setting;

// CURL Timeout setting.
$visiblename = get_string('checker_link_setting_timeout', 'block_course_checker');
$timeout = new admin_setting_restrictedint(checker::CONNECT_TIMEOUT_SETTING, $visiblename, null,
        checker::CONNECT_TIMEOUT_DEFAULT);
$timeout->set_maximum(300)->set_minimum(0);
$setting->add($timeout);

// CURL Connect timeout setting.
$visiblename = get_string('checker_link_setting_connect_timeout', 'block_course_checker');
$timeout = new admin_setting_restrictedint(checker::TIMEOUT_SETTING,
        $visiblename, null, checker::TIMEOUT_DEFAULT);
$timeout->set_maximum(300)->set_minimum(0);
$setting->add($timeout);