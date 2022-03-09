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
 * This is a helper for the task scheduler
 *
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker;

use core\message\message;
use core\task\manager;
use core_user;

class task_helper {

    /**
     * A singleton instance of this class.
     *
     * @var \block_course_checker\task_helper
     */
    private static $instance;
    /**
     * @var int|null
     */
    private $latestcourseid;
    /**
     * @var mixed
     */
    private $latestresult;

    /**
     * Force singleton
     */
    protected function __construct() {
    }

    /**
     * Don't allow to clone singleton.
     */
    protected function __clone() {
    }

    /**
     * This will clear the cache of scheduled tasks.
     *
     * @return $this
     */
    public function clear_is_scheduled_cache() {
        $this->latestresult = null;
        $this->latestcourseid = null;

        return $this;
    }

    /**
     * Factory method for this class .
     *
     * @return \block_course_checker\task_helper the singleton instance
     */
    public static function instance() {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Tells if a check is already scheduled. This is done by course id and optionaly by checkername.
     *
     * @param int $courseid
     * @param string|null $checkername
     * @return bool
     * @throws \dml_exception
     */
    public function is_task_scheduled(int $courseid, string $checkername = null) {
        $data = ["course_id" => $courseid];
        if (!empty($checkername)) {
            $data["checker"] = $checkername;
        }
        foreach ($this->get_scheduled_tasks($courseid) as $record) {
            // We get the task with exactly the same data.
            if ($record->customdata === json_encode($data)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add a adhoc task to run the checks for a specific course.
     *
     * @param int $courseid
     * @param string|null $checker The checker name, if null run all checks.
     */
    public function add_task(int $courseid, string $checker = null) {
        global $USER;
        // Generate the custom data.
        $data = ['course_id' => $courseid];
        // Allow checks to be run only for a specific checker.
        if (!empty($checker)) {
            $data["checker"] = $checker;
        }

        // The goal is to run the task asynchronously.
        $task = new run_checker_task();
        $task->set_blocking(false);
        $task->set_custom_data($data);
        $task->set_userid($USER ? $USER->id : null);
        // Queue the task.
        manager::queue_adhoc_task($task);
    }

    /**
     * Get all the run_checker_task for the specified course.
     * The result is cached in a local variable. You can call "clear_cache" to empty it.
     *
     * @param int $courseid
     * @return array
     * @throws \dml_exception
     */
    protected function get_scheduled_tasks(int $courseid) {
        global $DB;
        if ($this->latestcourseid !== null && $courseid === $this->latestcourseid) {
            return $this->latestresult;
        }

        $jsondata = json_encode(["course_id" => $courseid]);
        // We take the result with data like this: {course_id:xxx, ...} or {course_id:xxx}.
        $params = ["\\" . run_checker_task::class, rtrim($jsondata, "}") . ",%", $jsondata];
        $sql = sprintf('classname = ? AND (%s or %s)',
                $DB->sql_like('customdata', '?'),
                $DB->sql_compare_text('customdata', \core_text::strlen($params[1]) + 1) . ' = ?'
        );

        $this->latestresult = $DB->get_records_select('task_adhoc', $sql, $params);
        $this->latestcourseid = $courseid;
        return $this->latestresult;
    }

    /**
     * @param object $course
     * @param int|null $userid
     * @param string|null $checkername If the checker is null, it means that we run all the checks, for specific check the name is
     *         given.
     * @return null|false False on error, null otherwise.
     */
    public function notify_checkfinished($course, int $userid = null, string $checkername = null) {
        // Old tasks don't have any user id stored.
        if ($userid === null) {
            return;
        }

        $coursename = $course->fullname;
        $url = new \moodle_url('/blocks/course_checker/details.php', ['id' => $course->id]);

        // Strings for all courses checks.
        $urlhtmllabel = get_string("messageprovider_result_html_label", 'block_course_checker');
        $data = [
                "coursename" => $coursename,
                "url" => $url,
                "urlhtml" => sprintf('<a href="%s">%s</a>', $url, $urlhtmllabel)
        ];
        $subject = get_string('messageprovider_allchecks_subject', 'block_course_checker', $data);
        $completed = get_string('messageprovider_allchecks_completed', 'block_course_checker', $data);

        // Override some strings for a specific check.
        if ($checkername !== null) {
            $data += ["checkername" => get_string($checkername, 'block_course_checker')];
            $subject = get_string('messageprovider_singlechecks_subject', 'block_course_checker', $data);
            $completed = get_string('messageprovider_singlechecks_completed', 'block_course_checker', $data);

        }
        $resultplain = get_string('messageprovider_result_plain', 'block_course_checker', $data);
        $resulthtml = get_string('messageprovider_result_html', 'block_course_checker', $data);

        // Send the message notification. See https://docs.moodle.org/dev/Message_API.
        $message = new message();
        $message->component = 'block_course_checker';
        $message->name = 'checker_completed';
        $message->courseid = $course->id;
        $message->userto = $userid;
        $message->userfrom = core_user::get_noreply_user();
        $message->contexturl = $url;
        $message->contexturlname = get_string('messageprovider_result_html_label', 'block_course_checker');
        $message->subject = $subject;
        $message->fullmessage = $completed . " " . $resultplain;
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = \html_writer::tag("h2", s($subject)) . s($completed) . " " . $resulthtml;
        $message->smallmessage = $completed;

        return message_send($message);
    }
}
