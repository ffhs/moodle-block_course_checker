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
 * Manage activities events.
 *
 * Goal is to track activities modifications into DB so we can display the last edited activities.
 *
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker;

defined('MOODLE_INTERNAL') || die();

use block_course_checker\model\event_persister_interface;

class event_persister implements event_persister_interface {
    const TABLENAME = "block_course_checker_events";

    /**
     * A singleton instance of this class.
     *
     * @var \block_course_checker\event_persister
     */
    private static $instance;

    /**
     * Event wrapper, see db/events.php.
     *
     * @todo load instance name from db for instanceid
     *
     * Deleted has no name
     * @see course/lib.php #1208
     *
     * @param $event
     */
    public static function course_module_event_trigger($event) {
        if (isset($event->other['name']) and $event->other['name'] !== null) {
            $name = $event->other['name'];
        } else {
            $name = $event->other['instanceid'];
        }
        self::instance()->set_last_activity_event(
                $event->courseid,
                $event->action,
                $event->userid,
                $event->other['instanceid'],
                $event->other['modulename'],
                $name,
                $event->timecreated
        );
    }

    /**
     * Force singleton
     */
    protected function __construct() {

    }

    /**
     * Don't allow to clone singleton
     */
    protected function __clone() {

    }

    /**
     * Factory method for this class .
     *
     * @return \block_course_checker\event_persister the singleton instance
     */
    public static function instance() {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function set_last_activity_event(int $courseid, string $action, int $userid, int $instanceid,
            string $modulename, string $name, int $timestamp = null) {
        global $DB;
        $data = [
                'action' => $action,
                'user_id' => $userid,
                'modulename' => $modulename,
                'name' => $name,
                'timestamp' => $timestamp
        ];

        // Get the previous event if any and update it.
        $sql = "course_id = ? AND instance_id = ? AND " . $DB->sql_compare_text("modulename") . " = ?";
        $record = $DB->get_record_select(self::TABLENAME, $sql, [$courseid, $instanceid, $modulename]);
        $isnew = !$record;
        if ($isnew) {
            $record = new \stdClass();
            $record->course_id = $courseid;
            $record->instance_id = $instanceid;
            $record->modulename = $modulename;
        }
        if ($timestamp === null) {
            $record->timestamp = date('U');
        }
        foreach ($data as $key => $value) {
            $record->{$key} = $value;
        }

        if ($isnew) {
            $DB->insert_record(self::TABLENAME, $record);
        } else {
            $DB->update_record(self::TABLENAME, $record);
        }

        // Set the human check date for the last activity change.
        result_persister::instance()->set_last_activity_edition($record->course_id, $record->timestamp);
    }

    /**
     * Get the list of all the updated/created event since the specified date.
     *
     * @param int $courseid
     * @param \DateTime $timestamp
     * @return event_result[]
     * @throws \dml_exception
     */
    public function list_events_updated(int $courseid, \DateTime $timestamp): array {
        global $DB;
        // Get all the event for this course (skip the deleted one).
        $select = 'course_id = ? AND action != ? AND timestamp >= ?';
        $params = array($courseid, "deleted", $timestamp->format("U"));
        $result = [];
        foreach ($DB->get_records_select(self::TABLENAME, $select, $params, 'timestamp DESC') as $record) {
            $result[] = new event_result($courseid, $record);
        }
        return $result;
    }
}