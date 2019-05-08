<?php
// This file is part of the Fraisa Moodle
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

require_once("../../config.php");

use block_course_checker\result_persister;

$courseid = required_param('id', PARAM_INT);

require_login($courseid, false);

$course = get_course($courseid);
$PAGE->set_url(new moodle_url('/blocks/course_checker/details.php', array('id' => $courseid)));
$context = context_course::instance($courseid);
$PAGE->set_context($context);
$PAGE->set_title(get_string("resultpagetitle", "block_course_checker", $course));
$PAGE->set_heading(get_string("resultpageheader", "block_course_checker", $course));
$PAGE->set_pagelayout('report');

if (!has_capability('block/course_checker:view_report', $context)) {
    print_error('resultpermissiondenied', 'block_course_checker');
}

// Load previous check results.
$record = result_persister::instance()->load_last_checks($COURSE->id);
if ($record) {
    $results = $record["result"];
} else {
    $results = [];
}

// Run the test directly.
if (\block_course_checker\plugin_manager::IMMEDIATE_RUN) {
    $results = \block_course_checker\plugin_manager::instance()->run_checks($COURSE);
}

// Render each check result with the dedicated render for this checker.
$manager = \block_course_checker\plugin_manager::instance();
$htmlresults = [];

foreach ($results as $checkername => $result) {

    // Ignore missing checker.
    if ($manager->get_checker($checkername) == null) {
        continue;
    }
    $htmlresults[] = [
            "name" => $checkername,
            "output" => $manager->get_renderer($checkername)->render_for_page($checkername, clone $result)
    ];
}

// Sort results by group.
$groupedresults = [];
$grouporder = $manager->get_group_order();
foreach ($htmlresults as $count => $result) {
    $group = $manager->get_group($result['name']);
    $groupnr = $grouporder[$group];
    $groupname = get_string($group, "block_course_checker");
    if (!array_key_exists($groupnr, $groupedresults)) {
        $groupedresults[$groupnr] = ['results' => [], "group" => $group, "groupname" => $groupname];
    }

    $groupedresults[$groupnr]['results'][] = $result;
}
ksort($groupedresults);
$groupedresults = array_values($groupedresults);

$groupedevents = [];


/** @var \block_course_checker\output\page_renderer $renderer */
$renderer = $PAGE->get_renderer("block_course_checker", "page");

echo $OUTPUT->header();
echo $renderer->renderer([
        "groupedresults" => $groupedresults,
        "back" => new \moodle_url("/course/view.php", ["id" => $courseid])]);
echo $OUTPUT->footer();