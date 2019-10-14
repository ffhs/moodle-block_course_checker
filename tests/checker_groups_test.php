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
 * Unit tests for groups checker.
 *
 * @package     block_course_checker
 * @copyright   2018 Christoph Karlen, Fernfachhochschule Schweiz (FFHS) <christoph.karlen@ffhs.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use block_course_checker\checkers\checker_groups;
use block_course_checker\model\check_result_interface;

/**
 * Class block_course_checker_groups_testcase
 */
class block_course_checker_groups_testcase extends \advanced_testcase {
    /** @var \stdClass $user */
    protected $user;
    /** @var block_course_checker\checkers\checker_groups\checker */
    protected $groupschecker;
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
        $this->groupschecker = new checker_groups\checker();
        // Get new data generator helper.
        $this->generator = $this->getDataGenerator();
        // Create a new course.
        $this->course = $this->generator->create_course();
    }
    /**
     * @test
     */
    public function test_when_the_group_mode_is_deactivated() {
        $this->init();
        $this->create_new_assignment_activity(
                $this->course,
                [
                        'teamsubmission' => 0,
                        'teamsubmissiongroupingid' => 0,
                ]
        );
        $this->run_groupschecker(
                function($detail) {
                    $this->assertTrue($detail['successful']);
                });
    }
    /**
     * @test
     */
    public function test_when_the_grouping_id_is_not_set_but_the_group_mode_is_activated() {
        $this->init();
        $this->create_new_assignment_activity(
                $this->course,
                [
                        'teamsubmission' => 1,
                        'teamsubmissiongroupingid' => 0,
                ]
        );
        $this->run_groupschecker(
                function($detail) {
                    $this->assertFalse($detail['successful']);
                });
    }
    /**
     * @test
     */
    public function test_when_the_grouping_does_not_exist() {
        $this->init();
        $this->create_new_assignment_activity(
                $this->course,
                [
                        'teamsubmission' => 1,
                        'teamsubmissiongroupingid' => 1,
                ]
        );
        $this->run_groupschecker(
                function($detail) {
                    $this->assertFalse($detail['successful']);
                });
    }
    /**
     * @test
     */
    public function test_when_the_grouping_has_less_than_two_groups() {
        $this->init();
        $grouping = $this->create_a_new_grouping_in_course();
        $this->create_new_groups_in_grouping($grouping, 1);
        $this->create_new_assignment_activity(
                $this->course,
                [
                        'teamsubmission' => 1,
                        'teamsubmissiongroupingid' => $grouping->id,
                ]
        );
        $this->run_groupschecker(
                function($detail) {
                    $this->assertFalse($detail['successful']);
                });
    }
    /**
     * @test
     */
    public function test_when_the_grouping_has_more_than_one_group() {
        $this->init();
        $grouping = $this->create_a_new_grouping_in_course();
        $this->create_new_groups_in_grouping($grouping, 2);
        $this->create_new_assignment_activity(
                $this->course,
                [
                        'teamsubmission' => 1,
                        'teamsubmissiongroupingid' => $grouping->id
                ]
        );

        $this->run_groupschecker(
                function($detail) {
                    $this->assertTrue($detail['successful']);
                });
    }
    /**
     * @param null $course
     * @param array $record
     * @param array $options
     * @return stdClass
     * @throws coding_exception
     */
    protected function create_new_assignment_activity($course = null, $record = [], $options = []) {
        if ($course === null) {
            $record['course'] = $this->course->id;
        } else {
            $record['course'] = $course->id;
        }
        if (!isset($options['visible'])) {
            $options['visible'] = 1;
        }
        /** @var mod_assign_generator $plugingenerator */
        $plugingenerator = $this->generator->get_plugin_generator('mod_assign');
        return $plugingenerator->create_instance($record);
    }
    /**
     *
     */
    protected function create_a_new_grouping_in_course() {
        $record = [];
        $record['courseid'] = $this->course->id;
        return $this->generator->create_grouping($record);
    }
    /**
     * @param $grouping
     * @param int $count
     * @return array
     * @throws coding_exception
     */
    protected function create_new_groups_in_grouping($grouping, int $count = 1) {
        $record = [];
        $record['courseid'] = $this->course->id;
        $groups = [];
        for ($i = 0; $i < $count; $i++) {
            $group = $this->generator->create_group($record);
            $record['groupingid'] = $grouping->id;
            $record['groupid'] = $group->id;
            $this->generator->create_grouping_group($record);
            $groups[] = $group;
        }
        return $groups;
    }

    /**
     * @param $assertion
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    protected function run_groupschecker($assertion): void {
        /** @var check_result_interface $result */
        $result = $this->groupschecker->run($this->course);
        $details = $result->get_details();
        foreach ($details as $detail) {
            $assertion($detail);
        }
        $this->assertCount(1, $details);
    }
}