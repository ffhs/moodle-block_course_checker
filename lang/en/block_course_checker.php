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
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @author     2019 Adrian Perez, Fernfachhochschule Schweiz (FFHS) <adrian.perez@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'Course checker';
$string['privacy:metadata'] = 'The course checker block only contains anonymous data.';
$string['course_checker:addinstance'] = 'Add a new course checker block';
$string['course_checker:view'] = 'View the course checker block';
$string['course_checker:view_report'] = 'View the check result page';
$string['course_checker:view_notification'] = 'View the course checker notifications';
$string['messageprovider:checker_completed'] = 'Course check is completed';

// String specific for the checker settings.
$string['settings_general'] = '<p>If the checker is disabled (after save changes) there will be shown below a new setting to hide and show each checker results.</p>';
$string['settings_referencecourseid'] = 'Reference course id';
$string['settings_rolesallowedmanual'] = 'Roles for manual check';
$string['settings_rolesallowedmanual_description'] = 'Define the global roles which are allowed to use the manual check form.';
$string['settings_checker_header'] = '{$a} settings';
$string['settings_checker_toggle'] = '{$a} enabled';
$string['settings_checker_hide'] = '{$a} hidden';
$string['settings_checker_dependency'] = '<div class="alert alert-warning">Checker dependency failed, check if plugin <a href="/admin/modules.php" target="_blank">{$a}</a> installed and enabled.</div>';

// String for checker block and results page.
$string['noresults'] = 'This course has never been checked automatically';
$string['nogroupresults'] = 'Nothing found to check on. Everything is fine!';
$string['backtocourse'] = 'Back to course';
$string['resultpagegoto'] = 'View detailed results';
$string['resultpageheader'] = 'View detailed results';
$string['resultpagetitle'] = 'View detailed results for course {$a->name}';
$string['automaticcheck'] = 'Last automatic check';
$string['lastactivityedition'] = 'Last activity change';
$string['automaticcheckempty'] = 'The checks have never been launched for this course';
$string['humancheckempty'] = 'This course has never been manually checked';
$string['humancheck'] = 'Last manual check:';
$string['humancheck_comment_placeholder'] = 'Note';
$string['humancheck_reason'] = 'Reason:';
$string['humancheck_title'] = 'Set manual check date:';
$string['humancheck_update'] = 'Update human review';
$string['invalidtoken'] = 'Your token is invalid';
$string['runcheckbtn'] = 'Check this course';
$string['runcheckbtn_already'] = 'This course is already scheduled to be checked automatically.';
$string['runcheckbtn_nocheckers'] = 'There are no checkers enabled.';
$string['result'] = 'Result';
$string['resultpermissiondenied'] = 'You are not allowed to access this page';
$string['message'] = 'Message';
$string['link'] = 'Link';
$string['check_successful'] = 'Success';
$string['check_failed'] = 'Failure';
$string['resolutionlink'] = 'Resolution: ';
$string['checker_col_block_header'] = 'Check';
$string['result_col_block_header'] = 'Result';
$string['rerun_col_block_header'] = 'Re-run';
$string['rerun_disabled_col_block_header'] = 'This check is already scheduled to re-run';
$string['result_col_page_header'] = 'Result';
$string['link_col_page_header'] = 'Link to resolve';
$string['message_col_page_header'] = 'Message';
$string['checker_last_run'] = 'Last run {$a}';
$string['checker_last_run_global'] = 'Unknown date for this checker. The global course check was on {$a}';
$string['result_last_activity_header'] = 'Last modified activities';
$string['result_last_activity_header_date'] = 'Last modified activities since {$a}';
$string['result_last_activity_empty'] = 'No modified activities since {$a}';
$string['result_checker_disabled'] = 'This checker is disabled by the administrator.';

// Name of each group that can be assigned to checkers.
$string['group_course_settings'] = 'Course settings';
$string['group_links'] = 'Link validator';
$string['group_activities'] = 'Activity settings';

// Name and title of each checker.
$string['checker_groups'] = 'Group submission check';
$string['checker_groups_display'] = 'Group submission for assignments';
$string['checker_link'] = 'Links check';
$string['checker_link_display'] = 'Links in course summary and URL activities';
$string['checker_attendance'] = 'Attendance sessions check';
$string['checker_attendance_display'] = 'Attendance sessions';
$string['checker_data'] = 'Data activity check';
$string['checker_data_display'] = 'Data activity with fields';
$string['checker_subheadings'] = 'Label subheadings check';
$string['checker_subheadings_display'] = 'Label subheadings';
$string['checker_referencesettings'] = 'Reference settings check';
$string['checker_referencesettings_display'] = 'Settings compared to reference course';
$string['checker_activedates'] = 'Active dates check';
$string['checker_activedates_display'] = 'Active dates in activity configurations';

// String specific for the link checker.
$string['checker_link_activity'] = 'Activity: {$a->name}  ({$a->modname})';
$string['checker_link_book_chapter'] = 'Book Chapter: {$a->title}';
$string['checker_link_wiki_page'] = 'Wiki Page: {$a->title}';
$string['checker_link_summary'] = 'Course summary';
$string['checker_link_error_curl'] =
        'cURL Error {$a->curl_errno} {$a->curl_error} on {$a->url}'; // You can get any curl info or pare_url field in $a.
$string['checker_link_error_code'] =
        'HTTP Error {$a->http_code} on {$a->url}'; // You can get any curl info or pare_url field in $a.
$string['checker_link_ok'] =
        '{$a->url} is valid (Code {$a->http_code})'; // You can get any curl info or pare_url field in $a.
$string['checker_link_error_skipped'] = 'The domain {$a->host} is whitelisted for {$a->url}';
$string['checker_link_error_undefined'] = 'A undefined error with the link occurred';
$string['checker_link_setting_timeout'] = 'cURL timeout';
$string['checker_link_setting_connect_timeout'] = 'cURL connection timeout';
$string['checker_link_setting_useragent'] = 'User Agent';
$string['checker_link_setting_useragent_help'] = 'User Agent';

$string['checker_link_setting_whitelist'] = 'Link checker whitelist';
$string['checker_link_setting_whitelist_help'] = 'Please add one url per line. Example: "www.google.com". Note that www.w3.org must be present.';

// String specific for the group checker.
$string['groups_deactivated'] = 'Group submission setting is deactivated';
$string['groups_idmissing'] = 'Group submission is active, but no grouping is set';
$string['groups_missing'] = 'Grouping has not been set up correctly';
$string['groups_lessthantwogroups'] = 'Less than 2 groups have been set up for the active grouping';
$string['groups_success'] = 'Group submission setting is well defined';
$string['groups_activity'] = 'Activity "{$a->name}"';

// String specific for the activedates checker.
$string['activedates_noactivedates'] = 'There shouldn\'t be enabled dates in the "activity completion" section.';
$string['activedates_noactivedatesinactivity'] = 'There shouldn\'t be enabled dates in the {$modtype} activity, look for {$adateissetin}';

// String specific for the attendance checker.
$string['attendance_missingplugin'] = 'Skip this testcase because mod_attendance is not installed';
$string['attendance_missingattendanceactivity'] = 'Check attendance failed - no attendance activity in this course';
$string['attendance_onlyoneattendenceactivityallowed'] = 'Check attendance failed - only one attendance activity is allowed';
$string['attendance_sessionsnotemty'] = 'Check attendance failed - it\'s not allowed to have any attendance sessions';
$string['attendance_success'] = 'The attendance activity is configured correctly';

// String specific for the data checker.
$string['data_nofieldsdefined'] = 'No fields are defined for this database activity';
$string['data_fieldsdefined'] = 'Fields are defined for this database activity';

// String specific for the subheadings checker.
$string['subheadings_wrongfirsthtmltag'] = 'The first html-tag is not a {$a->htmltag}';
$string['subheadings_iconmissing'] = 'The icon is missing in the first html-tag';
$string['subheadings_generalerror'] = 'There was a problem executing this check';
$string['subheadings_success'] = 'This label has a nice subheading and icon';
$string['subheadings_labelignored'] = 'This label is ignored due to whitelist in plugin configuration.';

$string['checker_subheadings_setting_whitelist'] = 'Subheading checker strings whitelist';
$string['checker_subheadings_setting_whitelist_help'] = 'Please add one string per line. Example: "Liebe(r) Modulentwickler".';

// String specific for the reference course settings checker.
$string['checker_referencesettings_comparison'] = ' (Reference course: "{$a->settingvaluereference}" | Current course: "{$a->settingvaluecurrent}")';
$string['checker_referencesettings_settingismissing'] = 'The "{$a->setting}" is not a coursesetting';
$string['checker_referencesettings_failing'] = 'The setting "{$a->setting}" is not correct';
$string['checker_referencesettings_success'] = 'The setting "{$a->setting}" is correct';
$string['checker_referencesettings_checklist'] = 'Reference course checker settings checklist';
$string['checker_referencesettings_checklist_help'] = 'Please select one or multiple settings to check with the reference course.';

// String specific for the reference course settings checker filters.
$string['checker_referencefilter_comparison'] = ' (Reference course: "{$a->filtervaluereference}" | Current course: "{$a->filtervaluecurrent}")';
$string['checker_referencefilter_failing'] = 'The filter "{$a->filterkey}" is not correct';
$string['checker_referencefilter_success'] = 'All filters are correctly set in current course';
$string['checker_referencefilter_enabled'] = 'Reference settings filter check enabled';
$string['checker_referencefilter_enabled_help'] = 'Please enable this to compare all course filter with the reference course.';
$string['checker_referencefilter_filternotsetincurrentcourse'] = 'The filter "{$a->filterkey}" is missing in the current course.';

// String for messageprovider.
$string['messageprovider_allchecks_subject'] = 'Checks completed on course {$a->coursename}';
$string['messageprovider_allchecks_completed'] = 'The checks are completed.';
$string['messageprovider_singlechecks_subject'] = 'Check {$a->checkername} completed on course {$a->coursename}';
$string['messageprovider_singlechecks_completed'] = 'The check {$a->checkername} is completed.';
$string['messageprovider_result_plain'] = 'You can see the result at {$a->url}.';
$string['messageprovider_result_html'] = 'You can see the result on {$a->urlhtml}';
$string['messageprovider_result_html_label'] = 'the dedicated result page';

// Admin component. Please add specific checker settings under the checker section.
$string['admin_restrictedint_min'] = 'Minimum value is {$a}';
$string['admin_restrictedint_max'] = 'Maximum value is {$a}';
$string['admin_domain_name_notvalid'] = 'Domainname not valid: {$a}. Please add only one domain name per line';
$string['admin_domain_name_default_missing'] = 'Domainname missing: {$a}';
$string['admin_domain_list_notvalid'] = 'The list is not a valid list of domains';