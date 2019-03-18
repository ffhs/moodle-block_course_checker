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
     * @param check_result_interface[] $checks
     * @return void
     */
    public function save_checks($courseid, $checks, array $data = []) {
        global $DB;
        foreach ($checks as $pluginname => $result) {
            $this->assert_checks($checks);
        }

        $playload = [];
        foreach ($checks as $pluginname => $result) {
            $playload[$pluginname] = [
                    "successful" => $result->is_successful(),
                    "details" => $result->get_details(),
                    "link" => $result->get_link()
            ];
        }

        $record = new \stdclass();
        $record->course_id = is_object($courseid) ? $courseid->id : $courseid;
        $record->date = date("U");
        $record->result = json_encode($playload);
        foreach ($data as $key => $value) {
            $record->${$key} = $value;
        }

        return $DB->insert_record("block_course_checker", $record);
    }

    /**
     * @param int $courseid
     * @return array[]
     */
    public function load_last_checks(int $courseid): array {
        global $DB;
        $record = $DB->get_record("block_course_checker", ["course_id" => $courseid]);
        if (!$record) {
            return null;
        }

        $record->result = json_decode($record->result);
        $response = [];
        foreach ($record->result as $pluginname => $payload) {
            $result = new check_result();
            $result->set_details($payload["details"])
                    ->set_link($payload["link"])
                    ->set_successful($payload["link"]);
            $response[$pluginname] = $result;
        }
        $record->result = $response;
        return (array)$response;
    }

    private function assert_checks($check) {
        if (!$check instanceof check_result_interface) {
            throw new \RuntimeException("Object must be an instance of " . check_result_interface::class);
        }
    }
}