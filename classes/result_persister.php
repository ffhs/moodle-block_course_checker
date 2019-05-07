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

use block_course_checker\model\check_manager_persister_interface;
use block_course_checker\model\check_result_interface;

class result_persister implements check_manager_persister_interface {

    /**
     * A singleton instance of this class.
     *
     * @var \block_course_checker\result_persister
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
     * @return \block_course_checker\result_persister the singleton instance
     */
    public static function instance() {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @param $record \stdClass
     * @param array|check_result_interface[] $checkresults
     * @return \stdClass
     */
    private static function encode($record, $checkresults) {

        $payload = [];
        foreach ($checkresults as $pluginname => $result) {
            $payload[$pluginname] = [
                    "successful" => $result->is_successful(),
                    "details" => $result->get_details(),
                    "link" => $result->get_link()
            ];
        }
        $record->result = json_encode($payload);

        return $record;
    }

    /**
     * @param $record
     * @return array
     */
    private static function decode($record) {
        $response = [];
        if ($record->result !== null) {
            $result = json_decode($record->result, true);
            foreach ($result as $pluginname => $payload) {
                $result = new check_result();
                $result->set_details($payload["details"])->set_link($payload["link"])->set_successful($payload["successful"]);
                $response[$pluginname] = $result;
            }
        }
        $record->result = $response;

        return $record;
    }

    /**
     * @param $courseid
     * @param check_result_interface[]|false $checkresults False if we dont want to update the automatic check date and the results.
     * @param array $data
     * @return mixed record
     */
    public function save_checks($courseid, $checkresults, array $data = []) {
        global $DB;
        if (is_array($checkresults)) {
            foreach ($checkresults as $pluginname => $result) {
                $this->assert_checks($pluginname, $result);
            }
        }

        $record = $DB->get_record("block_course_checker", ["course_id" => $courseid]);
        $isnew = !$record;
        if ($isnew) {
            $record = new \stdClass();
            $record->course_id = $courseid;
            $record->result = null;
        }

        // Skip this if we don't have results.
        if ($checkresults !== false) {
            $record = self::encode($record, $checkresults);
        }
        foreach ($data as $key => $value) {
            $record->{$key} = $value;
        }

        if ($isnew) {
            $DB->insert_record("block_course_checker", $record);
        } else {
            $DB->update_record("block_course_checker", $record);
        }

        return self::decode($record);
    }

    /**
     * @param int $courseid
     * @param \DateTime $date
     * @param string $text
     * @return mixed|null
     */
    public function save_human_review(int $courseid, \DateTime $date, string $text = null) {

        $text = trim($text);
        if (empty($text)) {
            $text = null;
        }

        return $this->save_checks($courseid, false, [
                "manual_date" => $date->format("U"),
                "manual_reason" => $text,
        ]);
    }

    /**
     * @param int $courseid
     * @return array[]
     */
    public function load_last_checks(int $courseid): array {
        global $DB;
        $record = $DB->get_record("block_course_checker", ["course_id" => $courseid]);
        if (!$record) {
            return [];
        }
        $record = self::decode($record);
        return (array) $record;
    }

    /**
     * Check that the checkresult is an instance of check_result_interface
     *
     * @param string $pluginname
     * @param mixed $checkresult
     * @throws \RuntimeException
     */
    private function assert_checks(string $pluginname, $checkresult) {
        if (!$checkresult instanceof check_result_interface) {
            throw new \RuntimeException(sprintf("Result for %s must be an instance of %s, got %s", $pluginname,
                    check_result_interface::class, get_class($checkresult)));
        }
    }

    /**
     * @param int $courseid
     * @param int $timestamp
     * @return mixed record
     */
    public function set_last_activity_edition(int $courseid, int $timestamp) {
        return $this->save_checks((int) $courseid, false, ["last_activity_edition" => $timestamp]);
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
        global $DB;
        $data = ["course_id" => $courseid];
        if (!empty($checkername)) {
            $data["checker"] = $checkername;
        }
        $params = ["\\" . run_checker_task::class, json_encode($data)];
        $sql = 'classname = ? AND ' .
                $DB->sql_compare_text('customdata', \core_text::strlen($params[1]) + 1) . ' = ?';

        return $DB->record_exists_select('task_adhoc', $sql, $params);
    }
}