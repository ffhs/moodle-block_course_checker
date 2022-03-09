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

namespace block_course_checker\model;

/**
 * Goal is to persist/load tests results for all checks in a specific course.
 * TODO Improve this.
 *
 * @package block_course_checker\model
 */
interface check_manager_persister_interface {

    /**
     * @param int $courseid
     * @param check_result_interface[] $checkresults
     *
     * @param array $data
     * @return void
     */
    public function save_checks($courseid, $checkresults, array $data = []);

    /**
     * @param int $courseid
     * @return mixed record with check_result_interface[] inside result key
     */
    public function load_last_checks(int $courseid): array;

}
