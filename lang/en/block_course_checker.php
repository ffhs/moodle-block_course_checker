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
$string['course_checker:view_notification'] = 'View the course checker notifications';
$string['messageprovider:checker_completed'] = 'Course checker is completed';

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
$string['humancheck'] = 'Last manual check:';
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
$string['groups_deactivated'] = 'Group submission setting is deactivated';
$string['groups_idmissing'] = 'Group submission is active, but no grouping is set';
$string['groups_missing'] = 'Grouping has not been set up correctly';
$string['groups_lessthantwogroups'] = 'Less than 2 groups have been set up for the active grouping';
$string['groups_success'] = 'Group submission setting is well defined';
$string['groups_activity'] = 'Activity "{$a->name}"';
$string['checker_link_setting_timeout'] = 'CURL Timeout';
$string['checker_link_setting_connect_timeout'] = 'CURL Connection Timeout';
$string['checker_setting_toggle'] = 'Enable / Disable {$a}';
$string['checker_link_setting_whitelist'] = 'Link Checker Whitelist';
$string['checker_link_setting_whitelist_help'] = 'Please add one url per line. Example: "www.google.com". Note that www.w3.org must be present.';

// Name of each group that can be assigned to checkers.
$string['group_course_settings'] = 'Course Settings';
$string['group_links'] = 'Links';
// Name of each checker.
$string['checker_groups'] = 'Group Submission Check';
$string['checker_link'] = 'Links Check';
// Display title of each checker.
$string['checker_groups_display'] = 'Group Submission for Assignments';
$string['checker_link_display'] = 'Links in Course Summary and Url Activities';
// Checker last run.
$string['checker_last_run'] = 'Last run {$a}';

// Check Result Tables.
$string['result_col_block_header'] = 'Result';
$string['rerun_col_block_header'] = 'Re-run';
$string['rerun_disabled_col_block_header'] = 'This check is already scheduled to re-run';
$string['checker_col_block_header'] = 'Check';
$string['result_col_page_header'] = 'Result';
$string['message_col_page_header'] = 'Message';
$string['link_col_page_header'] = 'Link to Resolve';
$string['nogroupresults'] = 'Nothing found to check on. Everything is fine!';
$string['result_last_activity_header'] = 'Last modified activities';
$string['result_last_activity_header_date'] = 'Last modified activities since {$a}';
$string['result_last_activity_empty'] = 'No modified activities since {$a}';
$string['result_checker_disabled'] = 'This checker is disabled by the administrator.';

$string['humancheck_title'] = 'Set manual check date:';
$string['update'] = 'Update human review';
$string['human_comment'] = 'Set a comment to give on this update.';

$string['messageprovider_allchecks_subject'] = 'Checks completed on course {$a->coursename}';
$string['messageprovider_allchecks_completed'] = 'The checks are completed.';
$string['messageprovider_singlechecks_subject'] = 'Check {$a->checkername} completed on course {$a->coursename}';
$string['messageprovider_singlechecks_completed'] = 'The check {$a->checkername} is completed.';
$string['messageprovider_result_plain'] = 'You can see the result at {$a->url}.';
$string['messageprovider_result_html'] = 'You can see the result on {$a->urlhtml}';
$string['messageprovider_result_html_label'] = 'the dedicated result page';
$string['human_comment_placeholder'] = 'Note';
$string['humanreason'] = 'Reason:';
$string['settings_checker_header'] = 'Settings for the {$a}.';
$string['admin_referencecourseid'] = 'Reference course id';

// Admin component. Please add specific checker settings under the checker section.
$string['admin_restrictedint_min'] = 'Minimum value is {$a}';
$string['admin_restrictedint_max'] = 'Maximum value is {$a}';
$string['admin_domain_name_notvalid'] = 'Domainname not valid: {$a}. Please add only one domain name per line';
$string['admin_domain_name_default_missing'] = 'Domainname missing: {$a}';
$string['admin_domain_list_notvalid'] = 'The list is not a valid list of domains';