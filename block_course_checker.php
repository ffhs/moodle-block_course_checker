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
 * @author     2019 Adrian Perez, Fernfachhochschule Schweiz (FFHS) <adrian.perez@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_course_checker\plugin_manager;
use block_course_checker\result_group;
use block_course_checker\result_persister;
use block_course_checker\task_helper;

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
        global $COURSE;
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
        if (plugin_manager::IMMEDIATE_RUN) {
            $checks = plugin_manager::instance()->run_checks($COURSE, $loadedchecks);
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
        $footerrenderer = $this->page->get_renderer('block_course_checker', "footer");

        /** @var \block_course_checker\output\block_renderer $blockrenderer */
        $blockrenderer = $this->page->get_renderer('block_course_checker', "block");

        $this->content->footer = $footerrenderer->renderer([
                'automaticcheck' => $rundate,
                'humancheck' => $human,
                'humanreason' => $humancomment,
                "details" => new \moodle_url("/blocks/course_checker/details.php", ["id" => $COURSE->id]),
                "runbtn" => $this->render_run_task_button($COURSE->id),
                "humancheckbtn" => $blockrenderer->renderer_human_check_form($COURSE->id, $humancomment),
                "runscheduled" => task_helper::instance()->is_task_scheduled($COURSE->id),
                "arecheckersenabled" => plugin_manager::instance()->are_checkers_enabled($COURSE->id),
                "showdetailsbutton" => $showdetailsbutton,
                'lastactivityedition' => $lastactivityedition
        ]);

        return $this->content;
    }

    /**
     * Returns true or false, depending on whether this block has any content to display
     * and whether the user has permission to view the block
     *
     * @return boolean
     * @throws coding_exception
     */
    public function is_empty() {
        if (!has_capability('block/course_checker:view', $this->context)) {
            return true;
        }

        $this->get_content();
        return(empty($this->content->text) && empty($this->content->footer));
    }

    /**
     * @return bool
     */
    public function has_config() {
        return true;
    }

    /**
     * Render the checks results.
     *
     * @param array $results
     * @param int $courseid
     * @return mixed
     * @throws coding_exception
     * @throws moodle_exception
     */
    protected function render_block(array $results) {
        // Render each check result with the dedicated render for this checker.
        $manager = plugin_manager::instance();
        $htmlresults = [];
        foreach ($results as $checkername => $result) {

            // Ignore missing checker.
            if ($manager->get_checker($checkername) == null) {
                continue;
            }
            $htmlresults[] = [
                    "checkername" => $checkername,
                    "name" => get_string($checkername, "block_course_checker"),
                    "output" => $manager->get_renderer($checkername)->render_for_block($checkername, clone $result)
            ];
        }

        // Sort results by group.
        $groupedresults = [];
        $grouporder = $manager->get_group_order();
        foreach ($htmlresults as $result) {
            $group = $manager->get_group($result['checkername']);
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
        $renderer = $this->page->get_renderer("block_course_checker", "block");
        return $renderer->renderer([
                "groupedresults" => $groupedresults,
                "hasresults" => !empty($htmlresults),
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
}