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
namespace block_course_checker;

use core\task\adhoc_task;

defined('MOODLE_INTERNAL') || die();

/**
 * Class run_checker_task
 * See https://docs.moodle.org/dev/Task_API
 *
 * @package block_course_checker
 */
class run_checker_task extends adhoc_task {

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        $data = $this->get_custom_data();

        if (!isset($data->course_id)) {
            throw new \RuntimeException("The task should contains custom_data with the course_id");
        }

        // Use the get_course function instead of using get_record('course', ...).
        // See https://docs.moodle.org/dev/Data_manipulation_API#get_course.
        $course = get_course($data->course_id);

        // For a single checker.
        if (isset($data->checker)) {
            // We reload all the check from database.
            $record = result_persister::instance()->load_last_checks($course->id);
            if ($record) {
                $checksresults = $record["result"];
            } else {
                $checksresults = [];
            }

            // We run the check.
            $singleresult = plugin_manager::instance()->run_single_check($course, $data->checker);

            // We merge the check result with the one stored into the database.
            $checksresults = array_merge($checksresults, $singleresult);

            $data = [];
        } else {
            // For all checkers.
            $checksresults = plugin_manager::instance()->run_checks($course);
            $data = [
                "timestamp" => date("U")
            ];
        }

        result_persister::instance()->save_checks($course->id, $checksresults, $data);
        task_helper::instance()->clear_is_scheduled_cache();
    }
}