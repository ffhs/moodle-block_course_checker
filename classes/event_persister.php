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
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker;

defined('MOODLE_INTERNAL') || die();

use block_course_checker\model\event_manager_persister_interface;

class event_persister implements event_manager_persister_interface {
    const TABLENAME = "block_course_checker_events";

    /**
     * A singleton instance of this class.
     *
     * @var \block_course_checker\event_persister
     */
    private static $instance;

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

    /**
     * @param int $courseid
     * @param int $instanceid
     * @param array $data
     * @return bool if the save passed.
     */
    public function save_event($courseid, $instanceid, array $data = []) {
        global $DB;

        $record = $DB->get_record(self::TABLENAME,
                ['course_id' => $courseid, 'instance_id' => $instanceid]
        );
        $isnew = !$record;
        if ($isnew) {
            $record = new \stdClass();
            $record->course_id = $courseid;
            $record->instance_id = $instanceid;
        }
        if (!array_key_exists('timestamp', $data)) {
            $record->timestamp = date('U');
        }
        foreach ($data as $key => $value) {
            $record->{$key} = $value;
        }

        if ($isnew) {
            try {
                $DB->insert_record(self::TABLENAME, $record);
                return true;
            } catch (\Exception $e) {
                debugging($e->getMessage());
            }
        } else {
            try {
                $DB->update_record(self::TABLENAME, $record);
                return true;
            } catch (\Exception $e) {
                debugging($e->getMessage());
            }
        }

        return false;
    }

    public function set_last_activity_event(int $courseid, $action, $userid, $instanceid, $modulename, int $timestamp) {
        $data = ['action' => $action, 'user_id' => $userid, 'modulename' => $modulename, 'timestamp' => $timestamp];
        return $this->save_event($courseid, $instanceid, $data);
    }

    /**
     * @param string $modulename
     * @param $instanceid
     * @return mixed record with event_result_interface[] inside result key
     */
    public function load_last_event($modulename, $instanceid): array {
        global $DB;
        $record = $DB->get_record(self::TABLENAME, ['modulename' => $modulename, 'instance_id' => $instanceid]);

        if (!$record) {
            return [];
        }

        return $record;
    }
}