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
 * Fetch an url and return true if the code is between 200 and 400.
 *
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker\checkers\checker_link;

use block_course_checker\model\checker_config_trait;

defined('MOODLE_INTERNAL') || die();

/**
 * Class fetch_url
 *
 * @package block_course_checker\checkers\checker_link
 */
class fetch_url {
    const TIMEOUT_SETTING = 'block_course_checker/checker_link_timeout';
    const CONNECT_TIMEOUT_SETTING = 'block_course_checker/checker_link_connect_timeout';
    const TIMEOUT_DEFAULT = 13;
    const CONNECT_TIMEOUT_DEFAULT = 5;
    const WHITELIST_SETTING = 'block_course_checker/checker_link_whitelist';
    const WHITELIST_HEADING = 'block_course_checker/checker_link_whitelist_heading';
    const WHITELIST_DEFAULT = 'www.w3.org';
    
    use checker_config_trait;
    
    /** @var int $connecttimeout from checker settings */
    protected $connecttimeout;
    
    /** @var int $connecttimeout from checker settings */
    protected $timeout;
    
    /** @var array list of ignored domains  */
    protected $ignoredomains;
    
    /** @var string $message */
    public $message;
    
    /** @var bool $ignoreddomain */
    public $ignoreddomain;
    
    /** @var bool $successful */
    public $successful;
    
    /**
     * Initialize checker by setting it up with the configuration
     */
    public function init() {
        // Load settings.
        $this->connecttimeout = (int) $this->get_config(self::CONNECT_TIMEOUT_SETTING, self::CONNECT_TIMEOUT_DEFAULT);
        $this->timeout = (int) $this->get_config(self::TIMEOUT_SETTING, self::TIMEOUT_DEFAULT);
        $domainwhitelist = (string) $this->get_config(self::WHITELIST_SETTING, self::WHITELIST_DEFAULT);
        $this->ignoredomains = array_filter(array_map('trim', explode("\n", $domainwhitelist)));
    }
    
    /**
     * fetch_url constructor.
     *
     * @param array $ignoredomains
     */
    public function __construct($ignoredomains = []) {
        $this->ignoredomains = $ignoredomains;
    }
    
    public function fetch($url) {
        $parseurl = parse_url($url);
        if ($parseurl["host"] == null) {
            $this->message = get_string("checker_link_error_undefined", "block_course_checker");
            $this->ignoreddomain = false;
            $this->successful = false;
            return $this;
        }
        // Skip whitelisted domains.
        if ($this->is_ignored_host($parseurl["host"])) {
            $context = $parseurl + ["url" => $url];
            $this->message = get_string("checker_link_error_skipped", "block_course_checker", $context);
            $this->ignoreddomain = true;
            $this->successful = true;
            return $this;
        }
    
        $this->ignoreddomain = false;
    
        // Use curl to checks the urls.
        $curl = new \curl();
        $curl->head($url, [
                "CURLOPT_CONNECTTIMEOUT" => $this->connecttimeout,
                "CURLOPT_TIMEOUT" => $this->timeout,
                "CURLOPT_FOLLOWLOCATION" => 1,
                "CURLOPT_MAXREDIRS" => 3
        ]);
    
        $infos = $curl->get_info();
        $code = (int) $infos["http_code"];
        if ($code === 0) {
            // Code 0: timeout or other curl error.
            $context = $parseurl + ["url" => $url, "curl_errno" => $curl->get_errno(), "curl_error" => $curl->error];
            $this->message = get_string("checker_link_error_curl", "block_course_checker", $context);
            $this->successful = false;
            return $this;
        }
    
        $context = $parseurl + ["url" => $url, "http_code" => $code];
        if ($code >= 200 && $code < 400) {
            $this->message = get_string("checker_link_ok", "block_course_checker", $context);
            $this->successful = true;
            return $this;
        }
        // Code != 0 means it's a http error.
        $this->message = get_string("checker_link_error_code", "block_course_checker", $context);
        $this->successful = false;
        return $this;
    }
    
    /**
     * Tells if an url should be skipped.
     *
     * @param string $host
     * @return boolean
     */
    protected function is_ignored_host(string $host) {
        return in_array($host, $this->ignoredomains);
    }
}