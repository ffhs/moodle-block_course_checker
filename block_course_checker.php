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

use block_course_checker\result_persister;
use block_course_checker\run_checker_task;

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

        // Run the checks with an output buffer.
        $loadedchecks = result_persister::instance()->load_last_checks($COURSE->id);
        if ($loadedchecks != []) {
            $checks = $loadedchecks["result"];
            $rundate = $loadedchecks['timestamp'];
            $human = $loadedchecks['manual_date'];
        } else {
            $rundate = null;
            $human = null;
        }

        // Render the checks results.
        $this->content->text = "";
        $this->content->text .= $this->render_checks($checks);
        $this->content->text .= $this->render_run_task_button((int) $COURSE->id);

        /** @var \block_course_checker\output\block_renderer_footer $footerrenderer */
        $footerrenderer = $PAGE->get_renderer('block_course_checker', "footer");
        $this->content->footer = $footerrenderer->renderer([
                'automaticcheck' => $rundate,
                'humancheck' => $human,
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
     * Render the checks results
     *
     * @param $results
     * @return mixed
     */
    protected function render_checks($results) {
        global $PAGE;

        // Render each check result with the dedicated render for this checker.
        $manager = \block_course_checker\plugin_manager::instance();
        $htmlresults = [];
        foreach ($results as $pluginname => $result) {

            // Ignore missing checker.
            if ($manager->get_checker($pluginname) == null) {
                continue;
            }
            $htmlresults[] = [
                    "name" => $pluginname,
                    "result" => $manager->get_renderer($pluginname)->render_for_page(clone $result)
            ];
        }

        // Sort results by group.
        $groupedresults = [];
        foreach ($htmlresults as $count => $result) {
            $group = $manager->get_group($result['name']);
            if (!array_key_exists($group, $groupedresults)) {
                $groupedresults[$group] = ['results' => [], "group" => $group];
            }

            $groupedresults[$group]['results'][] = $result;
        }

        $groupedresults = array_values($groupedresults);

        /** @var \block_course_checker\output\block_renderer $renderer */
        $renderer = $PAGE->get_renderer("block_course_checker", "block");
        return $renderer->renderer([
                "groupedresults" => $groupedresults
        ]);
    }

    /**
     * @return array
     */
    public function applicable_formats() {
        return ['course-view' => true];
    }

    /**
     * Show the button to run a task, execpt if it's already scheduled.
     *
     * @param int $courseid
     * @return string
     */
    private function render_run_task_button(int $courseid) {

        if ($this->is_task_scheduled($courseid)) {
            return get_string("runcheckbtn_already", "block_course_checker");
        }

        global $CFG;
        $url = $CFG->wwwroot . '/blocks/course_checker/schedule_checker.php';
        $content = "";
        $content .= html_writer::start_tag('form',
                array('method' => "post", 'action' => new \moodle_url($url, ["courseid" => $courseid])));

        if (empty($CFG->disablelogintoken) || false == (bool) $CFG->disablelogintoken) {
            $content .= html_writer::tag("input", '',
                    ["type" => "hidden", "name" => "token", "value" => \core\session\manager::get_login_token()]);
        }
        $content .= html_writer::tag("input", '', [
                "type" => "submit",
                "value" => get_string("runcheckbtn", "block_course_checker"),
                "class" => "btn btn-primary"
        ]);
        $content .= html_writer::end_tag("form");

        return $content;
    }

    /**
     * Tells if a check for the specific course is already scheduled
     *
     * @param int $courseid
     * @return bool
     * @throws dml_exception
     */
    private function is_task_scheduled(int $courseid) {
        global $DB;

        $params = ["\\" . run_checker_task::class, json_encode(["course_id" => $courseid])];
        $sql = 'classname = ? AND ' .
                $DB->sql_compare_text('customdata', \core_text::strlen($params[1]) + 1) . ' = ?';
        return $DB->record_exists_select('task_adhoc', $sql, $params);
    }
}