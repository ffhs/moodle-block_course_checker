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
 * Unit tests for link checker.
 *
 * @package     block_course_checker
 * @copyright   2020 Christoph Karlen, Fernfachhochschule Schweiz (FFHS) <christoph.karlen@ffhs.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use block_course_checker\checkers\checker_links;
use block_course_checker\model\check_result_interface;
use block_course_checker\model\mod_type_interface;

/**
 * Class block_course_checker_links_testcase
 */
class block_course_checker_links_testcase extends \advanced_testcase implements mod_type_interface {
    /** @var \stdClass $user */
    protected $user;
    /** @var block_course_checker\checkers\checker_links\checker */
    protected $linkchecker;
    /** @var testing_data_generator */
    protected $generator;
    /** @var stdClass */
    protected $course;

    /**
     * @var array
     */
    private $urlactivitychecksfailing = [
            'https://httpstat.us/404', // Not Found.
            'https://httpstat.us/500', // Internal Server Error.
            'https://httpstat.us/503', // Service Unavailable.
            'https://httpstat.us/522', // Connection timed out.
            'https://httpstat.us/524', // A timeout occurred.
    ];

    /**
     * @var array
     */
    private $urlactivitycheckssuccess = [
            'https://httpstat.us/200', // OK.
            'https://httpstat.us/301', // Moved Permanently.
    ];

    /**
     * @test
     */
    public function test_failing_links_in_url_activity() {
        $this->assert_links_in_url_activity($this->urlactivitychecksfailing, false);
    }

    /**
     * @param $urlactivitycheckurls
     * @param bool $assert
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function assert_links_in_url_activity($urlactivitycheckurls, $assert = true) {
        $this->init();
        foreach ($urlactivitycheckurls as $urlactivitycheckurl) {
            $this->create_new_url_activity(
                    $this->course,
                    [
                            'externalurl' => $urlactivitycheckurl,
                    ]
            );
        }

        $this->run_linkchecker(
                function($detail) use ($assert) {
                    if ($assert) {
                        $this->assertTrue($detail['successful']);
                    } else {
                        $this->assertFalse($detail['successful']);
                    }
                });
    }

    /**
     *
     */
    protected function init() {
        // Reset the database after test.
        $this->resetAfterTest(true);
        // Get an link checker.
        $this->linkchecker = new checker_links\checker();
        // Get new data generator helper.
        $this->generator = $this->getDataGenerator();
        // Create a new course.
        $this->course = $this->generator->create_course();
    }

    /**
     * @param null $course
     * @param array $record
     * @param array $options
     * @return stdClass
     * @throws coding_exception
     */
    protected function create_new_url_activity($course = null, $record = [], $options = []) {
        if ($course === null) {
            $record['course'] = $this->course->id;
        } else {
            $record['course'] = $course->id;
        }
        if (!isset($options['visible'])) {
            $options['visible'] = 1;
        }
        /** @var mod_assign_generator $plugingenerator */
        $plugingenerator = $this->generator->get_plugin_generator('mod_' . self::MOD_TYPE_URL);
        return $plugingenerator->create_instance($record);
    }

    /**
     * @param $assertion
     * @throws coding_exception
     * @throws moodle_exception
     */
    protected function run_linkchecker($assertion): void {
        /** @var check_result_interface $result */
        $result = $this->linkchecker->run($this->course);
        $details = $result->get_details();
        foreach ($details as $detail) {
            $assertion($detail);
        }
    }

    /**
     * @test
     */
    public function test_success_links_in_url_activity() {
        $this->assert_links_in_url_activity($this->urlactivitycheckssuccess);
    }
}