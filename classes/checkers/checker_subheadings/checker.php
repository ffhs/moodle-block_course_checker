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
 * Checking the labels subheadings and the leading icons inside the course
 *
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @author     2019 Christoph Karlen, Fernfachhochschule Schweiz (FFHS) <christoph.karlen@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker\checkers\checker_subheadings;

defined('MOODLE_INTERNAL') || die();

use block_course_checker\check_result;
use block_course_checker\model\check_plugin_interface;
use block_course_checker\model\check_result_interface;
use block_course_checker\model\checker_config_trait;
use block_course_checker\resolution_link_helper;

class checker implements check_plugin_interface {
    use checker_config_trait;
    /** @var check_result */
    protected $result = null;
    /** @var array list of ignored strings build from checker settings */
    protected $ignoredstrings;
    // Module name for labels in Moodle.
    const MOD_TYPE_LABEL = 'label';
    const FIRST_ITEM_HTML_TAG = 'h4';
    const WHITELIST_SETTING = 'block_course_checker/checker_subheadings_whitelist';
    const WHITELIST_HEADING = 'block_course_checker/checker_subheadings_whitelist_heading';
    const WHITELIST_DEFAULT = '';
    /**
     * Initialize checker by setting it up with the configuration
     */
    public function init() {
        // Load settings.
        $whitelist = (string) $this->get_config(self::WHITELIST_SETTING, self::WHITELIST_DEFAULT);
        $this->ignoredstrings = array_filter(array_map('trim', explode("\n", $whitelist)));
    }
    /**
     * Runs the check
     *
     * @param \stdClass $course The course itself.
     * @return check_result_interface The check result.
     * @throws \moodle_exception
     */
    public function run($course) {
        $this->init();
        // Initialize check result array.
        $this->result = new check_result();
        // Get all labels activities for the course.
        $modinfo = get_fast_modinfo($course);
        // Get a dom document for html operations.
        $dom = new \DOMDocument;
        foreach ($modinfo->cms as $cm) {
            // Skip activities that are not labels.
            if ($cm->modname != self::MOD_TYPE_LABEL) {
                continue;
            }
            // Skip activities that are not visible.
            if (!$cm->uservisible) {
                continue;
            }
            // Link to activity.
            $target = $this->get_target($cm);
            $link = resolution_link_helper::get_link_to_modedit_or_view_page($cm->modname, $cm->id);
            // Load the html content
            // - DOMDocument is not loading correctly if there are line breaks.
            $cmcontentwithoutnewlines = preg_replace("/[\r\n]/", '', $cm->content);
            $dom->loadHTML($cmcontentwithoutnewlines);
            $body = $dom->getElementsByTagName('body');
            if (!is_object($body)) {
                $this->add_general_error($target, $link);
                continue;
            }
            try {
                $elements = $body
                    ->item(0)->childNodes
                    ->item(0)->childNodes;
                $firstitem = $elements->item(0);
            } catch (\Exception $exception) {
                $this->add_general_error($target, $link);
                continue;
            }
            // Check if the text contains strings which are whitelisted.
            foreach ($this->ignoredstrings as $ignoredstring) {
                $pos = strpos($cmcontentwithoutnewlines, $ignoredstring);
                if ($pos !== false) {
                    $this->result->set_successful(true);
                    $this->result->add_detail([
                            "successful" => true,
                            "target" => $target,
                            "link" => $link,
                            "message" => get_string("subheadings_labelignored", "block_course_checker"),
                            "ignored" => true
                    ]);
                    continue 2;
                }
            }
            // Check if the first html element is set and has a correct header.
            if (!isset($firstitem->tagName) or $firstitem->tagName != self::FIRST_ITEM_HTML_TAG) {
                $message = get_string("subheadings_wrongfirsthtmltag", "block_course_checker",
                        (object) ["htmltag" => self::FIRST_ITEM_HTML_TAG]);
                $this->result->add_detail([
                        "successful" => false,
                        "message" => $message,
                        "target" => $target,
                        "link" => $link
                ])->set_successful(false);
                continue;
            }
            // Check if there is an icon in the first heading.
            $search = "(\[((?:icon\s)?fa-[a-z0-9 -]+)\])is";
            preg_match($search, $firstitem->textContent, $matches);
            if (empty($matches)) {
                $message = $message = get_string("subheadings_iconmissing", "block_course_checker");
                $this->result->add_detail([
                        "successful" => false,
                        "message" => $message,
                        "target" => $target,
                        "link" => $link
                ])->set_successful(false);
                continue;
            }
            // When there are no problems.
            $message = get_string('subheadings_success', 'block_course_checker');
            $this->result->add_detail([
                    "successful" => true,
                    "message" => $message,
                    "target" => $target,
                    "link" => $link
            ]);
        }
        // Return the check results.
        return $this->result;
    }
    /**
     * Get the group defined for this check.
     * This is used to display checks from the same group together.
     *
     * @return string
     */
    public static function get_group() {
        return 'group_activities';
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
     * @param \cm_info $cm
     * @return string
     * @throws \coding_exception
     */
    private function get_target(\cm_info $cm) {
        $targetcontext = (object) ["name" => strip_tags($cm->name)];
        $target = get_string("groups_activity", "block_course_checker", $targetcontext);
        return $target;
    }
    /**
     * @param $target
     * @param $link
     * @throws \coding_exception
     */
    private function add_general_error($target, $link) {
        $message = get_string("subheadings_generalerror", "block_course_checker");
        $this->result->add_detail([
                "successful" => false,
                "message" => $message,
                "target" => $target,
                "link" => $link
        ])->set_successful(false);
    }
}