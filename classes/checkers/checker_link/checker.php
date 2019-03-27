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
        $modules = [];
        foreach ($modinfo->cms as $cm) {
            $modules[] = $cm->modname;
        }
        // Be sure to check each type of activity ONLY once.
        $modules = array_unique($modules);

        // You will got strait to the edition page for theses mods.
        $directmodnames = ["resource", "label"];
        foreach ($modules as $modname) {
            $instances = get_all_instances_in_courses($modname, [$course->id => $course]);
            foreach ($instances as $mod) {
                $target = get_string("checker_link_activity", "block_course_checker",
                        (object) ["modname" => get_string("pluginname", $modname), "name" => strip_tags($mod->name)]);

                $url = new \moodle_url('/mod/' . $modname . '/view.php', ['id' => $mod->coursemodule]);

                // We open the edition page instead of the mod/view itself.
                if (in_array($modname, $directmodnames)) {
                    $url = new \moodle_url('/course/modedit.php', [
                            'return' => 0,
                            "update" => $mod->coursemodule,
                            "sr" => 0,
                            "sesskey" => sesskey()
                    ]);
                    $url = $url->out_as_local_url(false); // FIXME: Url double decoded ?
                }

                // For url, we have to check the externalurl too.
                if ($modname === "url") {
                    $this->check_urls_with_resolution_url([$mod->externalurl], $url, $target);
                }

                // Check modules properties.
                if (property_exists($mod, "name")) {
                    $this->check_urls_with_resolution_url($this->get_urls_from_text($mod->name), $url, $target);
                }
                if (property_exists($mod, "intro")) { // Into is the description.
                    $this->check_urls_with_resolution_url($this->get_urls_from_text($mod->intro), $url, $target);
                }
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
     * TODO: Whitelist our own domain.
     * @param string $url
     * @return bool
     */
    protected function check_url($url) {
        $curl = new \curl();
        $curl->head($url, [], [
                "CURLOPT_CONNECTTIMEOUT" => 5,
                "CURLOPT_TIMEOUT" => 13,
                'CURLOPT_FOLLOWLOCATION' => 0
        ]);
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
            $match = $match[0];
            $match = array_unique($match);
            return $match;
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
}