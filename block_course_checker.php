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
 * This file contains the course_checker modules block.
 *
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 */
defined('MOODLE_INTERNAL') || die();

class block_course_checker extends block_base {
    /**
     * @inheritdoc
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_course_checker');
        $this->content_type = BLOCK_TYPE_TEXT;
    }

    /**
     * @return bool
     */
    public function instance_create()
    {
        if (has_capability('block/course_checker:addinstance', $this->context)) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function get_content() {
        global $COURSE;
        if (has_capability('moodle/course:update', $this->context)) {
            if ($this->content !== null) {
                return $this->content;
            }
            $this->content = new \stdClass();

            // This is a test output.
            $this->content->text = 'Work in progress ' . $this->title;
            $this->content->text .= '<br><pre>' . $this->run_checks($COURSE) . '</pre>';
            $this->content->footer = date("Y");

            return $this->content;
        }
        return null;
    }

    /**
     * @return bool
     */
    public function has_config() {
        return false;
    }

    /**
     * Run checks and var dump the results.
     * @param $COURSE
     * @return false|string
     */
    protected function run_checks($COURSE) {
        // This is a test to output each checker results.
        $manager = \block_course_checker\plugin_manager::instance();
        ob_start();
        $results = $manager->run_checks($COURSE);
        var_dump($results);
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    /**
     * @return array
     */
    public function applicable_formats() {
        return ['course-view' => true];
    }
}