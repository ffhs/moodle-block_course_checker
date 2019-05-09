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
use block_course_checker\model\event_result_interface;

class event_persister implements event_manager_persister_interface {

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
     * @param event_result_interface[] $eventresults
     *
     * @param array $data
     * @return void
     */
    public function save_event($courseid, $eventresults, array $data = []) {
        // TODO: Implement save_event() method.
    }

    /**
     * @param int $courseid
     * @return mixed record with event_result_interface[] inside result key
     */
    public function load_last_event(int $courseid): array {
        // TODO: Implement load_last_event() method.
    }
}