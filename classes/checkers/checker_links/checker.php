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
 * Checking links inside the course
 *
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @copyright  2020 FFHS <christoph.karlen@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker\checkers\checker_links;

defined('MOODLE_INTERNAL') || die();

use block_course_checker\check_result;
use block_course_checker\resolution_link_helper;
use block_course_checker\model\check_plugin_interface;
use block_course_checker\model\check_result_interface;
use block_course_checker\model\mod_type_interface;
use coding_exception;
use moodle_exception;
use moodle_url;
use stdClass;

class checker implements check_plugin_interface, mod_type_interface {
    /** @var check_result $checkresult*/
    protected $checkresult = null;

    /** @var config $config */
    protected $config = null;

    /**
     * Get the group defined for this check.
     * This is used to display checks from the same group together.
     *
     * @return string
     */
    public static function get_group() {
        return 'group_links';
    }

    /**
     * Get the defaultsetting to use in the global settings.
     *
     * @return bool
     */
    public static function is_checker_enabled_by_default() {
        return true;
    }

    /**
     * Runs the check for all links of a course
     *
     * @param stdClass $course The course itself.
     * @return check_result_interface The check result.
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function run($course) {
        // Initialize check config.
        $this->config = new config($course);

        // Initialize check result array.
        $this->checkresult = new check_result();
        $this->check_course_summary($course);
        $modules = $this->get_unique_modnames($course);
        $modinfo = get_fast_modinfo($course);

        foreach ($modules as $modname) {
            // Get all activities for each modname in the course.
            $instances = get_all_instances_in_courses($modname, [$course->id => $course]);
            foreach ($instances as $mod) {
                // Get cm_info object to use for target and resolution link.
                $cm = $modinfo->get_cm($mod->coursemodule);
                $target = resolution_link_helper::get_target($cm, 'checker_links');
                $resolutionlink = resolution_link_helper::get_link_to_modedit_or_view_page($cm->modname, $cm->id);

                // For url, we have to check the externalurl too.
                if ($modname === self::MOD_TYPE_URL) {
                    $this->check_urls_with_resolution_url([$mod->externalurl], $resolutionlink, $target);
                }

                // For books, we have to check the chapters too.
                if ($modname === self::MOD_TYPE_BOOK) {
                    $this->check_book_chapters($mod);
                }

                // For wiki, we have to check the pages too.
                if ($modname === self::MOD_TYPE_WIKI) {
                    $this->check_wiki_pages($mod);
                }

                // Check modules properties.
                if (property_exists($mod, "name")) {
                    $this->check_urls_with_resolution_url($this->get_urls_from_text($mod->name), $resolutionlink, $target);
                }
                if (property_exists($mod, "intro")) { // Into is the description.
                    $this->check_urls_with_resolution_url($this->get_urls_from_text($mod->intro), $resolutionlink, $target);
                }
                if (property_exists($mod, "content")) {
                    $this->check_urls_with_resolution_url($this->get_urls_from_text($mod->content), $resolutionlink, $target);
                }
            }
        }

        return $this->checkresult;
    }

    /**
     * @param $course
     * @throws coding_exception
     * @throws moodle_exception
     */
    protected function check_course_summary($course) {
        $courseurl = new moodle_url("/course/view.php", ["id" => $course->id]);
        $this->check_urls_with_resolution_url($this->get_urls_from_text($course->summary), $courseurl,
                get_string("checker_links_summary", "block_course_checker"));
    }

    /**
     * Check all urls for a single resolution_url
     *
     * @param array $urls
     * @param string $resolutionlink
     * @param null $target
     * @throws coding_exception
     */
    protected function check_urls_with_resolution_url(array $urls, string $resolutionlink = null, $target = null) {
        $urlcheckresult = new fetch_url($this->config);
        foreach ($urls as $url) {
            $urlcheckresult->fetch($url);
            $this->checkresult->set_successful($this->checkresult->is_successful() & $urlcheckresult->successful);
            $this->checkresult->add_detail([
                    "successful" => $urlcheckresult->successful,
                    "target" => $target,
                    "link" => $resolutionlink,
                    "message" => $urlcheckresult->message,
                    "resource" => $url, // The custom-renderer will display the resource correctly.
                    "ignored" => $urlcheckresult->ignoreddomain
            ]);
        }
    }

    /**
     * Extract url from a string
     *
     * @param string $text
     * @return string[] urls
     */
    protected function get_urls_from_text($text) {
        // Be aware that XMLNS can be used.
        // Specially «math xmlns=¨http://www.w3.org/1998/Math/MathML¨».
        if (false !== preg_match_all('#\bhttps?:\/\/[^,\s()<>»¨]+(?:([\w\-]+)|([^,[:punct:],¨»\s]|\/))#', $text, $match)) {
            $match = $match[0];
            // If we have <a href="$url">$url</a> $url is not counted twice.
            return array_unique($match);
        }
        return [];
    }

    /**
     * @param $course
     * @return array
     * @throws moodle_exception
     */
    protected function get_unique_modnames($course) {
        $modinfo = get_fast_modinfo($course);
        $modules = [];
        foreach ($modinfo->cms as $cm) {
            $modules[] = $cm->modname;
        }
        // Be sure to check each type of activity ONLY once.
        $modules = array_unique($modules);
        return $modules;
    }

    /**
     * @param $mod
     * @throws \dml_exception
     * @throws moodle_exception
     */
    protected function check_book_chapters($mod) {
        global $DB;
        $chapters = $DB->get_records('book_chapters', array('bookid' => $mod->id), '', 'id,title,content');
        foreach ($chapters as $chapter) {
            $target = get_string('checker_links_book_chapter', 'block_course_checker', (object) ["title" => $chapter->title]);
            $resolutionlink = new moodle_url('/mod/book/edit.php', ['cmid' => $mod->coursemodule, 'id' => $chapter->id]);
            $url = $resolutionlink->out_as_local_url(false);
            $this->check_urls_with_resolution_url($this->get_urls_from_text($chapter->content), $url, $target);
        }
    }

    /**
     * @param $mod
     * @throws \dml_exception
     * @throws moodle_exception
     */
    protected function check_wiki_pages($mod) {
        global $DB;

        $pages = $DB->get_records('wiki_pages', array('subwikiid' => $mod->id), '', 'id,title,cachedcontent');
        foreach ($pages as $page) {
            $target = get_string('checker_links_wiki_page', 'block_course_checker', (object) ["title" => $page->title]);
            $resolutionlink = new moodle_url('/mod/wiki/edit.php', ['pageid' => $page->id]);
            $this->check_urls_with_resolution_url($this->get_urls_from_text($page->cachedcontent), $resolutionlink, $target);
        }
    }
}