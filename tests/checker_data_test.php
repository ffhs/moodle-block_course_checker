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
 * Unit tests for data checker.
 *
 * @package     block_course_checker
 * @copyright   2020 Christoph Karlen, Fernfachhochschule Schweiz (FFHS) <christoph.karlen@ffhs.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use block_course_checker\checkers\checker_data;
use block_course_checker\model\check_result_interface;
use block_course_checker\model\mod_type_interface;

/**
 * Class block_course_checker_data_testcase
 */
class block_course_checker_data_testcase extends \advanced_testcase implements mod_type_interface {
    /** @var \stdClass $user */
    protected $user;
    /** @var block_course_checker\checkers\checker_data\checker */
    protected $checker;
    /** @var testing_data_generator */
    protected $datagenerator;
    /** @var stdClass $course */
    protected $course;
    /** @var $instance */
    protected $instance;
    
    /**
     *
     */
    protected function init() {
        // Reset the database after test.
        $this->resetAfterTest(true);
        // Get the checker.
        $this->checker = new checker_data\checker();
        // Get new data generator helper.
        $this->datagenerator = $this->getDataGenerator()->get_plugin_generator($this->get_component());
        // Create a new course.
        $this->course = $this->getDataGenerator()->create_course();
        // Create instance.
        $this->instance = $this->datagenerator->create_instance(array('course' => $this->course->id));
    }
    
    /**
     * @test
     */
    public function test_field_in_data_activity() {
        $this->init();
        
        $record = new StdClass();
        $record->name = 'field-1';
        $record->type = 'text';
    
        $this->datagenerator->create_field($record, $this->instance);
        $this->assert_fields_in_data_activity();
    }
    
    /**
     * @test
     */
    public function test_no_fields_in_data_activity() {
        $this->init();
        $this->assert_fields_in_data_activity(false);
    }
    
    /**
     * @param bool $assert
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function assert_fields_in_data_activity($assert = true) {
        $this->run_checker(
                function($detail) use ($assert) {
                    if ($assert) {
                        $this->assertTrue($detail['successful']);
                    } else {
                        $this->assertFalse($detail['successful']);
                    }
                });
    }
    
    /**
     * @param $assertion
     * @throws coding_exception
     * @throws moodle_exception
     */
    protected function run_checker($assertion): void {
        /** @var check_result_interface $result */
        $result = $this->checker->run($this->course);
        $details = $result->get_details();
        foreach ($details as $detail) {
            $assertion($detail);
        }
    }
    
    /**
     * @return string
     */
    protected static function get_component(){
        return 'mod_' . self::MOD_TYPE_DATA;
    }
}