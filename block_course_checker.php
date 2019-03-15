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
     * @return string
     */
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = new \stdClass();
        $this->content->text = 'Work in progress ' . $this->title;
        $this->content->footer = date("Y");
        return $this->content;
    }

    /**
     * @return bool
     */
    public function has_config() {
        return false;
    }

    /**
     * @return array
     */
    public function applicable_formats() {
        return ['course-view' => true];
    }
}