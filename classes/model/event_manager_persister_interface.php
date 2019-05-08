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

defined('MOODLE_INTERNAL') || die();

interface event_manager_persister_interface {

    public function set_last_activity_event(int $courseid, string $action, int $userid,
                                            int $instanceid, string $modulename, string $name, int $timestamp
    );

    /**
     * @param int $courseid
     * @param \DateTime $timestamp
     * @return array
     */
    public function list_events_updated(int $courseid, \DateTime $timestamp): array;
}