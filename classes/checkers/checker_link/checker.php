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

namespace block_course_checker\checkers\checker_link;

defined('MOODLE_INTERNAL') || die();

use block_course_checker\check_result;
use block_course_checker\model\check_plugin_interface;
use block_course_checker\model\check_result_interface;
use block_course_checker\model\mod_type_interface;

class checker implements check_plugin_interface, mod_type_interface {
    /** @var check_result */
    protected $checkresult = null;
    
    /** @var array list of modules which can be linked directly to the module config page  */
    protected $directmodnames = [
            self::MOD_TYPE_RESOURCE,
            self::MOD_TYPE_LABEL
    ];
    
    /**
     * Runs the check for all links of a course
     *
     * @param \stdClass $course The course itself.
     * @return check_result_interface The check result.
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function run($course) {
        $this->checkresult = new check_result();
        $this->check_course_summary($course);
        $modules = $this->get_unique_modnames($course);
    
        // You will got strait to the edition page for theses mods.
        foreach ($modules as $modname) {
            $instances = get_all_instances_in_courses($modname, [$course->id => $course]);
            foreach ($instances as $mod) {
                $target = $this->get_target($modname,$mod);
                $url = $this->get_link_to_modedit_or_view_page($modname, $mod);
                
                // For url, we have to check the externalurl too.
                if ($modname === self::MOD_TYPE_URL) {
                    $this->check_urls_with_resolution_url([$mod->externalurl], $url, $target);
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
                    $this->check_urls_with_resolution_url($this->get_urls_from_text($mod->name), $url, $target);
                }
                if (property_exists($mod, "intro")) { // Into is the description.
                    $this->check_urls_with_resolution_url($this->get_urls_from_text($mod->intro), $url, $target);
                }
                if (property_exists($mod, "content")) {
                    $this->check_urls_with_resolution_url($this->get_urls_from_text($mod->content), $url, $target);
                }
            }
        }

        return $this->checkresult;
    }
    
    /**
     * Check all urls for a single resolution_url
     *
     * @param array $urls
     * @param string|null $resolutionlink
     * @param null $target
     */
    protected function check_urls_with_resolution_url(array $urls, string $resolutionlink = null, $target = null) {
        foreach ($urls as $i => $url) {
            $urlcheckresult = (new fetch_url())->fetch($url);
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
     * Get the group defined for this check.
     * This is used to display checks from the same group together.
     *
     * @return string
     */
    public static function get_group() {
        return 'group_links';
    }
    
    /**
     * @param $modname
     * @param $mod
     * @return \moodle_url|string
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    private function get_link_to_modedit_or_view_page($modname,$mod) {
        // We open the edition page instead of the mod/view itself.
        if (in_array($modname, $this->directmodnames)) {
            $url = new \moodle_url('/course/modedit.php', [
                    'return' => 0,
                    "update" => $mod->coursemodule,
                    "sr" => 0,
                    "sesskey" => sesskey()
            ]);
            return $url->out_as_local_url(false); // FIXME: Url double decoded ?
        }
        return new \moodle_url('/mod/' . $modname . '/view.php', ['id' => $mod->coursemodule]);
    }
    
    /**
     * @param $modname
     * @param $mod
     * @return string
     * @throws \coding_exception
     */
    private function get_target($modname, $mod) {
        return get_string("checker_link_activity", "block_course_checker",
                (object) ["modname" => get_string("pluginname", $modname), "name" => strip_tags($mod->name)]);
    }
    
    /**
     * @param $course
     * @return array
     * @throws \moodle_exception
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
     * @param $course
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    protected function check_course_summary($course) {
        $courseurl = new \moodle_url("/course/view.php", ["id" => $course->id]);
        $this->check_urls_with_resolution_url($this->get_urls_from_text($course->summary), $courseurl,
                get_string("checker_link_summary", "block_course_checker"));
    }
    
    /**
     * @param $mod
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    protected function check_book_chapters($mod) {
        global $DB;
        $chapters = $DB->get_records('book_chapters', array('bookid' => $mod->id), '','id,title,content');
        foreach ($chapters as $chapter) {
            $target = get_string('checker_link_book_chapter','block_course_checker', (object) ["title" => $chapter->title]);
            $url = new \moodle_url('/mod/wiki/edit.php', ['pageid' => $chapter->id]);
            $this->check_urls_with_resolution_url($this->get_urls_from_text($chapter->content), $url, $target);
        }
    }
    
    /**
     * @param $mod
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    protected function check_wiki_pages($mod) {
        global $DB;
        
        $pages = $DB->get_records('wiki_pages', array('subwikiid' => $mod->id), '', 'id,title,cachedcontent');
        foreach ($pages as $page) {
            $target = get_string('checker_link_wiki_page','block_course_checker', (object) ["title" => $page->title]);
            $url = new \moodle_url('/mod/wiki/edit.php', ['pageid' => $page->id]);
            $this->check_urls_with_resolution_url($this->get_urls_from_text($page->cachedcontent), $url, $target);
        }
    }
}