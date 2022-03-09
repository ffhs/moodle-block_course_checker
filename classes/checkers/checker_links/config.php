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
 *
 * @package    block_course_checker
 * @author     2020 Christoph Karlen, Fernfachhochschule Schweiz (FFHS) <christoph.karlen@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker\checkers\checker_links;

use block_course_checker\model\checker_config_trait;
use coding_exception;
use context_course;
use dml_exception;
use stdClass;

/**
 * Class config
 *
 * @package block_course_checker\checkers\checker_links
 */
class config {
    const TIMEOUT_SETTING = 'block_course_checker/checker_links_timeout';
    const TIMEOUT_DEFAULT = 13;
    const CONNECT_TIMEOUT_SETTING = 'block_course_checker/checker_links_connect_timeout';
    const CONNECT_TIMEOUT_DEFAULT = 5;
    const WHITELIST_SETTING = 'block_course_checker/checker_links_whitelist';
    const WHITELIST_HEADING = 'block_course_checker/checker_links_whitelist_heading';
    const WHITELIST_DEFAULT = 'www.w3.org';
    const USERAGENT_SETTING = 'block_course_checker/checker_links_useragent';
    const USERAGENT_DEFAULT = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) ' .
    'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.132 Safari/537.36';

    use checker_config_trait;

    /** @var int $connecttimeout from checker settings */
    public $connecttimeout;

    /** @var int $connecttimeout from checker settings */
    public $timeout;

    /** @var array list of ignored domains */
    public $ignoredomains;

    /** @var string user agent */
    public $useragent;

    /** @var stdClass $course */
    public $course;

    /**
     * config constructor.
     *
     * @param $course
     * @throws coding_exception
     * @throws dml_exception
     */
    public function __construct($course) {
        $this->course = $course;
        $this->init();
    }

    /**
     * Initialize checker by setting it up with the configuration.
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    public function init() {
        global $PAGE, $COURSE, $DB;

        // Load settings.
        $this->connecttimeout = (int) $this->get_config(self::CONNECT_TIMEOUT_SETTING, self::CONNECT_TIMEOUT_DEFAULT);
        $this->timeout = (int) $this->get_config(self::TIMEOUT_SETTING, self::TIMEOUT_DEFAULT);
        $this->useragent = (string) $this->get_config(self::USERAGENT_SETTING, self::USERAGENT_DEFAULT);
        $domainwhitelist = (string) $this->get_config(self::WHITELIST_SETTING, self::WHITELIST_DEFAULT);
        $this->ignoredomains = array_filter(array_map('trim', explode("\n", $domainwhitelist)));

        // Load edit form data.
        $PAGE->set_course($this->course);
        $coursecontext = context_course::instance($COURSE->id);
        $blockrecords =
                $DB->get_records('block_instances',
                        array('blockname' => 'course_checker', 'parentcontextid' => $coursecontext->id));
        foreach ($blockrecords as $b) {
            $blockinstance = block_instance('course_checker', $b);
            if (isset($blockinstance->config->link_whitelist) && $blockinstance->config->link_whitelist) {
                $ignoreddomains = array_filter(array_map('trim', explode("\n", $blockinstance->config->link_whitelist)));
                $this->ignoredomains = array_merge($ignoreddomains, $this->ignoredomains);
            }
            break;
        }
    }
}
