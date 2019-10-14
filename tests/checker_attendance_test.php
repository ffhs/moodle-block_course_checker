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
 * Unit tests for course checker attendance check.
 *
 * @package     block_course_checker
 * @copyright   2018 Christoph Karlen, Fernfachhochschule Schweiz (FFHS) <christoph.karlen@ffhs.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use block_course_checker\checkers\checker_attendance;
use block_course_checker\model\check_result_interface;

/**
 * Class block_course_checker_attendance_testcase
 */
class block_course_checker_attendance_testcase extends \advanced_testcase {
    /** @var \stdClass $user */
    protected $user;
    /** @var block_course_checker\checkers\checker_attendance\checker */
    protected $attendancechecker;
    /** @var testing_data_generator */
    protected $generator;
    /** @var stdClass */
    protected $course;
    /**
     *
     */
    protected function init() {
        // Reset the database after test.
        $this->resetAfterTest(true);
        // Get an attendance checker.
        $this->attendancechecker = new checker_attendance\checker();
        // Get new data generator helper.
        $this->generator = $this->getDataGenerator();
        // Create a new course.
        $this->course = $this->generator->create_course();
    }
    public function test_if_mod_attendance_is_installed() {
        $details = core_plugin_manager::instance()->get_plugin_info('mod_attendance');
        if ($details) {
            $this->assertEquals('attendance', $details->name);
        } else {
            $this->markTestSkipped(get_string('attendance_missingplugin', 'block_course_checker'));
        }
    }
    /**
     * @test
     * @depends test_if_mod_attendance_is_installed
     */
    public function test_if_there_is_no_attendance_activity_in_the_course() {
        $this->init();
        /** @var check_result_interface $result */
        $result = $this->attendancechecker->run($this->course);
        $details = $result->get_details();
        foreach ($details as $detail) {
            $this->assertFalse($detail['successful']);
            $this->assertEquals(get_string('attendance_missingattendanceactivity', 'block_course_checker'), $detail['message']);
        }
        $this->assertCount(1, $details);
    }
    /**
     * @test
     * @depends test_if_mod_attendance_is_installed
     */
    public function test_if_there_is_more_then_one_attendance_activity() {
        $this->init();
        // Create new attendance activities.
        $this->create_new_attendance_activity($this->course);
        $this->create_new_attendance_activity($this->course);
        /** @var check_result_interface $result */
        $result = $this->attendancechecker->run($this->course);
        $details = $result->get_details();
        foreach ($details as $detail) {
            $this->assertFalse($detail['successful']);
            $this->assertEquals(get_string(
                    'attendance_onlyoneattendenceactivityallowed',
                    'block_course_checker'),
                    $detail['message']);
            break;
        }
    }
    /**
     * @test
     * @depends test_if_mod_attendance_is_installed
     */
    public function test_if_there_are_sessions_in_the_attendance_activity() {
        $this->init();
        // Create new attendance activities.
        $attendance = $this->create_new_attendance_activity($this->course);
        // Create new attendance sessions.
        $this->create_new_attendance_session($attendance);
        /** @var check_result_interface $result */
        $result = $this->attendancechecker->run($this->course);
        $details = $result->get_details();
        foreach ($details as $detail) {
            $this->assertFalse($detail['successful']);
            $this->assertEquals(get_string('attendance_sessionsnotemty', 'block_course_checker'), $detail['message']);
        }
    }
    /**
     * @test
     * @depends test_if_mod_attendance_is_installed
     */
    public function test_if_there_is_a_attendance_activity_but_no_sessions() {
        $this->init();
        // Create new attendance activities.
        $this->create_new_attendance_activity($this->course);
        /** @var check_result_interface $result */
        $result = $this->attendancechecker->run($this->course);
        $details = $result->get_details();
        foreach ($details as $detail) {
            $this->assertTrue($detail['successful']);
            $this->assertEquals(get_string('attendance_success', 'block_course_checker'), $detail['message']);
        }
    }
    /**
     * @param $course
     * @param array $options
     * @return stdClass
     * @throws coding_exception
     */
    protected function create_new_attendance_activity($course, $options = null) {
        $record['course'] = $course->id;
        if (!isset($options['visible'])) {
            $options['visible'] = 1;
        }
        /** @var mod_attendance_generator $plugingenerator */
        $plugingenerator = $this->generator->get_plugin_generator('mod_attendance');
        if (!$plugingenerator) {
            $this->markTestSkipped('Skip...');
        }
        return $plugingenerator->create_instance($record, $options);
    }
    /**
     * @param $attendance
     * @throws dml_exception
     */
    protected function create_new_attendance_session($attendance) {
        global $DB;
        $session = new stdClass();
        $session->attendanceid = $attendance->id;
        $session->description = '-';
        $DB->insert_record('attendance_sessions', $session);
    }
}