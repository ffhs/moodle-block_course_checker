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
    protected $lasterror = "";

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
            // Exclude activities which are not visible or have no link (=label).
            if (!$cm->uservisible or !$cm->has_view()) {
                continue;
            }
            switch ($cm->modname) {
                case "url":
                    $message = get_string("checker_link_activity", "block_course_checker",
                            (object) ["modname" => get_string("pluginname", $cm->modname), "name" => $cm->name]);

                    $record = $this->get_mod_url_link_record($cm, $course->id);
                    // Check the link itself.
                    $this->check_urls_with_resolution_url([$record->externalurl], $cm->url, $message);
                    // Check the link intro text.
                    $this->check_urls_with_resolution_url($this->get_urls_from_text($record->intro), $cm->url, $message);

                    break;
                default:
                    break; // FFHSCC-38 Add new type of activities here !
            }
        }
        return $this->result;
    }

    /**
     * Check all urls for a single resolution_url
     *
     * @param string[] $urls
     * @param string|null $resolutionlink
     * @param string|null $message
     */
    protected function check_urls_with_resolution_url(array $urls, string $resolutionlink = null, $message = null) {
        foreach ($urls as $i => $url) {
            $successful = $this->check_url($url);
            $message = empty($this->lasterror) ? $message : $this->lasterror;
            $this->result->set_successful($this->result->is_successful() & $successful);
            $this->result->add_detail([
                    "successful" => $successful,
                    "message" => $message,
                    "link" => $resolutionlink,
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
        if ($code >= 200 && $code < 400) {
            $this->lasterror = null;
            return true;
        }
        $this->lasterror = $code;
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
     * Get the record for a mod_url. Note that they are loaded by course_id in a single query.
     *
     * @param \cm_info $cm Mod info
     * @param int $courseid the course id
     * @return mixed|null the record or null
     */
    private function get_mod_url_link_record(\cm_info $cm, int $courseid) {
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
            foreach ($DB->get_records_select("url", "course = " . (int) $courseid) as $record) {
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
        return "course";
    }
}