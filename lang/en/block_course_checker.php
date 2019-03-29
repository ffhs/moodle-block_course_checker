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
 * Strings for component 'block_course_checker'.
 *
 * @package   block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 */
$string['course_checker.php:addinstance'] = 'Add a new course checker block';
$string['course_checker:view_report'] = 'View the check result page';
$string['pluginname'] = 'FFHS Course Checker';
$string['privacy:metadata'] = 'The Course Checker block only contains anonymous data.';
$string['course_checker:addinstance'] = 'Course checker create';
$string['noresults'] = 'This course has never been checked automatically';
$string['backtocourse'] = 'Back to course';
$string['resultpagegoto'] = 'View detailed results';
$string['resultpagetitle'] = 'detailed resutls';
$string['resultpageheader'] = 'View detailed results';
$string['resultpagetitle'] = 'View detailed results for course {$a->name}';
$string['automaticcheck'] = 'Last automatic check';
$string['lastactivityedition'] = 'Last activity change';
$string['automaticcheckempty'] = 'The checks have never been launched for this course';
$string['humancheck'] = 'Last manual check';
$string['humancheckempty'] = 'This course has never been manually checked';
$string['invalidtoken'] = 'Your token is invalid';
$string['runcheckbtn'] = 'Check this course';
$string['runcheckbtn_already'] = 'This course is already scheduled to be checked automatically';
$string['result'] = 'Result';
$string['resultpermissiondenied'] = 'You are not allowed to access this page';
$string['message'] = 'Message';
$string['link'] = 'Link';
$string['check_successful'] = 'Success';
$string['check_failed'] = 'Failure';
$string['resolutionlink'] = 'Resolution: ';

// String specific for the link checker.
$string['checker_link_activity'] = 'Activity: {$a->name}  ({$a->modname})';
$string['checker_link_summary'] = 'Course summary';
$string['checker_link_error_curl'] =
        'CURL Error {$a->curl_errno} {$a->curl_error} on {$a->url}'; // You can get any curl info or pare_url field in $a.
$string['checker_link_error_code'] =
        'HTTP Error {$a->http_code} on {$a->url}'; // You can get any curl info or pare_url field in $a.
$string['checker_link_ok'] =
        '{$a->url} is valid (Code {$a->http_code})'; // You can get any curl info or pare_url field in $a.
$string['checker_link_error_skipped'] = 'The domain {$a->host} is whitelisted for {$a->url}';
// String specific for the group checker.
$string['groups_deactivated'] = 'Grouping is deactivated';
$string['groups_idmissing'] = 'Grouping is missing';
$string['groups_missing'] = 'Grouping has not been set up';
$string['groups_lessthantwogroups'] = 'Less than 2 groups have been set up';
$string['groups_success'] = 'Grouping is well defined';
$string['groups_activity'] = 'Activity "{$a->name}"';

// Name of each group that can be assigned to checkers.
$string['group_course_settings'] = 'Course Settings';
$string['group_links'] = 'Links';
// Name of each checker.
$string['checker_groups'] = 'Group Submission Check';
$string['checker_link'] = 'Links Check';
// Display title of each checker.
$string['checker_groups_display'] = 'Group Submission for Assignments';
$string['checker_link_display'] = 'Links in Course Summary and Url Activities';

// Check Result Tables.
$string['result_col_block_header'] = 'Result';
$string['checker_col_block_header'] = 'Check';
$string['result_col_page_header'] = 'Result';
$string['message_col_page_header'] = 'Message';
$string['link_col_page_header'] = 'Link to Resolve';
$string['nogroupresults'] = 'Nothing found to check on. Everything is fine!';
