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
 * This file only schedule a task and redirect back to course.
 *
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 */
require_once(__DIR__ . "/../../config.php");

// We must be logged-in, but no permission check is made on this side, as discussed with the client.
require_login();

$PAGE->set_context(context_system::instance());
$courseid = required_param('courseid', PARAM_INT);
$token = required_param('token', PARAM_TEXT);
$date = required_param_array('human_review', PARAM_RAW);
$comment = required_param('human_comment', PARAM_TEXT);

if (empty($CFG->disablelogintoken) || false == (bool) $CFG->disablelogintoken) {
    if ($token != \core\session\manager::get_login_token()) {
        print_error("invalidtoken", 'block_course_checker');
    }
}

// Load the course, so whe know it exist before updating human review.
$course = get_course($courseid);

$date = \Datetime::createFromFormat("Y-m-d", $date['year'] . '-' . $date['month'] . '-' . $date['day']);
$resultpersister = \block_course_checker\result_persister::instance()->save_human_review($course->id, $date, $comment);

$url = new \moodle_url("/course/view.php", ["id" => $courseid]);
redirect($url);