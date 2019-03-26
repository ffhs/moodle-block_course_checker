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
namespace block_course_checker\checkers\checker_link;

defined('MOODLE_INTERNAL') || die();

use block_course_checker\check_result;
use block_course_checker\model\check_result_interface;

/**
 * Check link inside the course
 *
 * @package block_course_checker\checkers\checker_link
 */
class checker implements \block_course_checker\model\check_plugin_interface {
    /**
     * @var string|null Last CURL error after a call to check_url.
     */
    protected $lastmessage = "";

    /** @var check_result */
    protected $result = null;

    /**
     * @param \stdClass $course The course itself.
     * @return check_result_interface The check result.
     */
    public function run($course) {
        $this->result = new check_result();
        $courseurl = new \moodle_url("/course/view.php", ["id" => $course->id]);
        $this->check_urls_with_resolution_url($this->get_urls_from_text($course->summary), $courseurl,
                get_string("checker_link_summary", "block_course_checker"));

        $modinfo = get_fast_modinfo($course);
        foreach ($modinfo->cms as $cm) {
            // Exclude activities which are not visible.
            if (!$cm->uservisible) {
                continue;
            }
            switch ($cm->modname) {
                case "url":
                    $target = get_string("checker_link_activity", "block_course_checker",
                            (object) ["modname" => get_string("pluginname", $cm->modname), "name" => strip_tags($cm->name)]);

                    $record = $this->get_mod_url_link_record('url', $cm, $course->id);
                    // Check the link itself.
                    $this->check_urls_with_resolution_url([$record->externalurl], $cm->url, $target);
                    // Check the link intro text.
                    $this->check_urls_with_resolution_url($this->get_urls_from_text($record->intro), $cm->url, $target);

                    break;

                case "forum":
                    $target = get_string("checker_link_activity", "block_course_checker",
                        (object) ["modname" => get_string("pluginname", $cm->modname), "name" => strip_tags($cm->name)]);

                    $record = $this->get_mod_url_link_record('forum', $cm, $course->id);
                    // Check the link intro text.
                    $this->check_urls_with_resolution_url($this->get_urls_from_text($record->intro), $cm->forum, $target);

                    break;

                case "assign":
                    $target = get_string("checker_link_activity", "block_course_checker",
                        (object) ["modname" => get_string("pluginname", $cm->modname), "name" => strip_tags($cm->name)]);

                    $record = $this->get_mod_url_link_record('assign', $cm, $course->id);
                    // Check the assignment intro text.
                    $this->check_urls_with_resolution_url($this->get_urls_from_text($record->intro), $cm->assign, $target);

                    break;

                case "page":
                    $target = get_string("checker_link_activity", "block_course_checker",
                        (object) ["modname" => get_string("pluginname", $cm->modname), "name" => strip_tags($cm->name)]);

                    $record = $this->get_mod_url_link_record('page', $cm, $course->id);
                    // Check the page intro text.
                    $this->check_urls_with_resolution_url($this->get_urls_from_text($record->intro), $cm->page, $target);
                    // Check the page content.
                    $this->check_urls_with_resolution_url($this->get_urls_from_text($record->intro), $cm->page, $target);

                    break;
                // FFHSCC-38 Add new type of activities here !

                case "label":
                    $target = get_string("checker_link_activity", "block_course_checker",
                        (object)["modname" => get_string("pluginname", $cm->modname), "name" => strip_tags($cm->name)]);

                    $record = $this->get_mod_url_link_record('label', $cm, $course->id);
                    // Check the label intro text.
                    $this->check_urls_with_resolution_url($this->get_urls_from_text($record->intro), $cm->label, $target);

                    break;

                default:
                    break;
            }

        }
        return $this->result;
    }

    /**
     * Check all urls for a single resolution_url
     *
     * @param string[] $urls
     * @param string|null $resolutionlink
     * @param string|null $target
     */
    protected function check_urls_with_resolution_url(array $urls, string $resolutionlink = null, $target = null) {
        foreach ($urls as $i => $url) {
            $successful = $this->check_url($url);
            $this->result->set_successful($this->result->is_successful() & $successful);
            $this->result->add_detail([
                    "successful" => $successful,
                    "target" => $target,
                    "link" => $resolutionlink,
                    "message" => $this->lastmessage,
                    "resource" => $url, // The custom-renderer will display the resource correctly.
            ]);
        }
    }

    /**
     * Fetch an url and return true if the code is between 200 and 400.
     *
     * @param string $url
     * @return bool
     */
    protected function check_url($url) {
        $curl = new \curl();
        $curl->get($url, ["CURLOPT_CONNECTTIMEOUT" => 15, "CURLOPT_HEADER" => 1, "CURLOPT_VERBOSE" => 1]);
        $infos = $curl->get_info();
        $code = (int) $infos["http_code"];
        if ($code === 0) {
            // Code 0: timeout or other curl error.
            $context = parse_url($url) + ["url" => $url, "curl_errno" => $curl->get_errno(), "curl_error" => $curl->error];
            $this->lastmessage = get_string("checker_link_error_curl", "block_course_checker", $context);
            return false;
        }

        $context = parse_url($url) + ["url" => $url, "http_code" => $code];
        if ($code >= 200 && $code < 400) {
            $this->lastmessage = get_string("checker_link_ok", "block_course_checker", $context);
            return true;
        }
        // Code != 0 means it's a http error.
        $this->lastmessage = get_string("checker_link_error_code", "block_course_checker", $context);
        return false;
    }

    /**
     * Extarct url from a string
     *
     * @param string $text
     * @return string[] urls
     */
    protected function get_urls_from_text($text) {
        if (false !== preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $text, $match)) {
            return $match[0];
        }
        return [];
    }

    /**
     * Get the record for a course module instance table. Note that they are loaded by course_id in a single query.
     *
     * @param string instance table name
     * @param \cm_info $cm Mod info
     * @param int $courseid the course id
     * @return mixed|null the record or null
     * @throws \moodle_exception
     */
    private function get_mod_url_link_record($cminstancetablename, \cm_info $cm, $courseid) {
        global $DB;
        static $cache = null;
        static $cachecourseid = 0;
        // Reset cache.
        if ($cachecourseid !== $courseid) {
            $cachecourseid = $courseid;
            $cache = null;
        }
        // Load all the url for the same course for performance reasons.
        if ($cache === null) {
            foreach ($DB->get_records_select($cminstancetablename, "course = " . (int) $courseid) as $record) {
                $cache[(int) $record->id] = $record;
            };
        }
        // Fail if the mod_url is not saved.
        if (!array_key_exists((int) $cm->instance, $cache)) {
            return null;
        }

        // Return the mod_url record.
        return $cache[(int) $cm->instance];
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
}