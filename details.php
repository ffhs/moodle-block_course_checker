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
//require_once($CFG->dirroot.'/blocks/course_checker/locallib.php');
use block_course_checker\result_persister;

$courseid   = optional_param('id', 0, PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid));

require_login($courseid, false);
//$PAGE->set_url('/blocks/course_checker/courseindex1.php', );
$PAGE->set_url(new moodle_url('/blocks/course_checker/details.php', array('id' => $courseid)));
$PAGE->set_context(context_course::instance($courseid));
//$systemcontext = context_course::instance();
$PAGE->set_title('Course Checker Report Page');
$PAGE->set_heading('Course Checker Report Page');
$PAGE->set_pagelayout('report');

//$this->render_checks($checks);

$loadedchecks = result_persister::instance()->load_last_checks($COURSE->id);
$results = $loadedchecks["result"];

// Render each check result with the dedicated render for this checker.
$manager = \block_course_checker\plugin_manager::instance();
$htmlresults = [];


foreach ($results as $pluginname => $result) {

    // Ignore missing checker.
    if ($manager->get_checker($pluginname) == null) {
        continue;
    }
    $htmlresults[] = [
        "name" => $pluginname,
        "result" => $manager->get_renderer($pluginname)->render_for_page(clone $result)
    ];
}

foreach ($results as $pluginname => $result) {
    // Ignore missing checker.
    if ($manager->get_checker($pluginname) == null) {
        continue;
    }
    echo $pluginname;
    $renderable = $manager->get_renderer($pluginname)->render_for_page(clone $result);
    echo $renderable;

}


// Sort results by group.
$groupedresults = [];
foreach ($htmlresults as $count => $result) {
    $group = $manager->get_group($result['name']);
    if (!array_key_exists($group, $groupedresults)) {
        $groupedresults[$group] = ['results' => [], "group" => $group];
    }

    $groupedresults[$group]['results'][] = $result;
}

$groupedresults = array_values($groupedresults);

$renderer = $PAGE->get_renderer("block_course_checker", "page");
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($title));
$renderer->renderer([
    "groupedresults" => $groupedresults
]);

