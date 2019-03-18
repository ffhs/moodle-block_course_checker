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
    public function instance_create() {
        return has_capability('block/course_checker:addinstance', $this->context);
    }

    /**
     * @return string
     */
    public function get_content() {
        global $COURSE, $PAGE;
        if (!has_capability('moodle/course:update', $this->context)) {
            return null;
        }

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new \stdClass();

        // TODO Remove ob_start.
        ob_start();

        // Run the checks with an output buffer.
        $checks = $this->run_checks($COURSE);

        // TODO Remove this useless if when debug is over.
        if (false) {
            $output = ob_get_contents();
            if (!empty($output) && debugging()) {
                $this->content->text .= $output;
            }
            ob_end_clean();
        }

        // Render the checks results.
        $this->content->text = $this->render_checks($checks);

        $rundate = date('d.m.Y - H:i');

        /** @var \block_course_checker\output\block_renderer_footer $footerrenderer */
        $footerrenderer = $PAGE->get_renderer('block_course_checker', "footer");
        $this->content->footer = $footerrenderer->renderer([
            'automaticcheck' => $rundate,
            'humancheck' => $rundate, // TODO: Change me after DB saving
            'automaticcheckstring' => get_string('automaticcheck', 'block_course_checker'),
            'humancheckstring' => get_string('humancheck', 'block_course_checker')
        ]);

        return $this->content;
    }

    /**
     * @return bool
     */
    public function has_config() {
        return false;
    }

    /**
     * Run checks and var dump the results.
     *
     * @param $COURSE
     * @return \block_course_checker\check_result[]
     */
    protected function run_checks($COURSE) {
        // This is a test to output each checker results.
        $manager = \block_course_checker\plugin_manager::instance();
        return $manager->run_checks($COURSE);
    }

    /**
     * @param $results
     * @return mixed
     */
    protected function render_checks($results) {
        global $PAGE;

        // Render each check result with the dedicated render for this plugin.
        $manager = \block_course_checker\plugin_manager::instance();
        $htmlresults = [];
        foreach ($results as $pluginname => $result) {
            $htmlresults[] = [
                    "name" => $pluginname,
                    "result" => $manager->get_renderer($pluginname)->render_for_block($result)
            ];
        }

        /** @var \block_course_checker\output\block_renderer $renderer */
        $renderer = $PAGE->get_renderer("block_course_checker", "block");
        return $renderer->renderer([
                "results" => $htmlresults
        ]);
    }

    /**
     * @return array
     */
    public function applicable_formats() {
        return ['course-view' => true];
    }
}