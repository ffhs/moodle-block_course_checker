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
use block_course_checker\result_group;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');

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
            $humancomment = $loadedchecks['manual_reason'];
            $lastactivityedition = $loadedchecks['last_activity_edition'];
        } else {
            $rundate = null;
            $human = null;
            $humancomment = null;
            $lastactivityedition = null;
            $checks = [];
        }

        // Run the test directly.
        if (\block_course_checker\plugin_manager::IMMEDIATE_RUN) {
            $checks = \block_course_checker\plugin_manager::instance()->run_checks($COURSE);
        }

        // Render the checks results.
        $this->content->text = "";
        $this->content->text .= $this->render_block($checks);

        if ($loadedchecks != [] && (\has_capability('block/course_checker:view_report',
                context_course::instance($COURSE->id)))) {
            $showdetailsbutton = true;
        } else {
            $showdetailsbutton = false;
        }

        /** @var \block_course_checker\output\footer_renderer $footerrenderer */
        $footerrenderer = $PAGE->get_renderer('block_course_checker', "footer");
        $this->content->footer = $footerrenderer->renderer([
                'automaticcheck' => $rundate,
                'humancheck' => $human,
                'humanreason' => $humancomment,
                "details" => new \moodle_url("/blocks/course_checker/details.php", ["id" => $COURSE->id]),
                "runbtn" => $this->render_run_task_button($COURSE->id),
                "humancheckbtn" => $this->render_human_check_form($COURSE->id),
                "runscheduled" => $this->is_task_scheduled($COURSE->id),
                "showdetailsbutton" => $showdetailsbutton,
                'lastactivityedition' => $lastactivityedition
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
     * @param array $results
     * @return mixed
     * @throws coding_exception
     * @throws moodle_exception
     */
    protected function render_block(array $results) {
        global $PAGE, $COURSE;

        // Render each check result with the dedicated render for this checker.
        $manager = \block_course_checker\plugin_manager::instance();
        $htmlresults = [];
        foreach ($results as $pluginname => $result) {

            // Ignore missing checker.
            if ($manager->get_checker($pluginname) == null) {
                continue;
            }
            $htmlresults[] = [
                    "pluginname" => $pluginname,
                    "name" => get_string($pluginname, "block_course_checker"),
                    "output" => $manager->get_renderer($pluginname)->render_for_block($pluginname, clone $result)
            ];
        }

        // Sort results by group.
        $groupedresults = [];
        $grouporder = $manager->get_group_order();
        foreach ($htmlresults as $count => $result) {
            $group = $manager->get_group($result['pluginname']);
            $groupnr = $grouporder[$group];
            $groupname = get_string($group, "block_course_checker");
            if (!array_key_exists($groupnr, $groupedresults)) {
                $groupedresults[$groupnr] = ['results' => [], "group" => $group, "groupname" => $groupname];
            }
            $groupedresults[$groupnr]['results'][] = $result;
        }
        ksort($groupedresults);
        $groupedresults = array_values($groupedresults);

        /** @var \block_course_checker\output\block_renderer $renderer */
        $renderer = $PAGE->get_renderer("block_course_checker", "block");
        return $renderer->renderer([
            "groupedresults" => $groupedresults,
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
                "class" => "btn btn-primary btn-block"
        ]);
        $content .= html_writer::end_tag("form");

        return $content;
    }

    /**
     * Shows the form to update the human date review.
     *
     * @param int $courseid
     * @return string
     */
    private function render_human_check_form(int $courseid) {
        global $CFG;
        require_once($CFG->libdir . '/formslib.php');

        $url = $CFG->wwwroot . '/blocks/course_checker/update_human_date.php';
        $content = "";

        $content .= html_writer::div('', 'separator') . html_writer::end_div();
        $content .= html_writer::label(get_string('humancheck_title', 'block_course_checker'), null, false);
        $content .= html_writer::start_tag('form',
            ['method' => 'post', 'action' => new \moodle_url($url, ['courseid' => $courseid ])]
        );

        if (empty($CFG->disablelogintoken) || false == (bool) $CFG->disablelogintoken) {
            $content .= html_writer::tag("input", '',
                ["type" => "hidden", "name" => "token", "value" => \core\session\manager::get_login_token()]);
        }

        $dateform = new date_picker_input();
        $html = $dateform->tohtmlwriter();
        $html = str_replace('</form>', '', $html); // Removed form due to date_picker_input generate a <form> itself.
        $properhtml = str_replace('col-md-3', '', $html); // Same but with col-md-3.
        $content .= html_writer::div($properhtml, 'm-a-0');
        $content .= html_writer::start_div('pb-3');
        $content .= html_writer::tag('textarea', '', [
            'name' => 'human_comment',
            'placeholder' => get_string('human_comment', 'block_course_checker'),
            'class' => 'form-control'
        ]);
        $content .= html_writer::end_div();
        $content .= html_writer::tag('input', '', [
            'type' => 'submit',
            'placeholder' => get_string('update', 'block_course_checker'),
            'class' => 'btn btn-primary btn-block'
        ]);
        $content .= html_writer::end_tag('form');

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

class date_picker_input extends moodleform
{
    protected function definition() {
        $mform = $this->_form;
        $mform->addElement('date_selector', 'human_review', '', ['stopyear' => date('Y')]);
    }

    /**
     * @return string
     */
    public function tohtmlwriter() {
        return $this->_form->toHtml();
    }

}