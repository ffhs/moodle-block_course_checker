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
 * @author     2020 Christoph Karlen, Fernfachhochschule Schweiz (FFHS) <christoph.karlen@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker\checkers\checker_links;

use block_course_checker\model\checker_config_trait;
use curl;
use Exception;

/**
 * Class fetch_url
 *
 * @package block_course_checker\checkers\checker_links
 */
class fetch_url {
    use checker_config_trait;

    /** @var int $config */
    protected $config;

    /** @var string $message */
    public $message;

    /** @var bool $ignoreddomain */
    public $ignoreddomain;

    /** @var bool $successful */
    public $successful;

    /**
     * fetch_url constructor.
     *
     * @param config $config
     */
    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * @param $url
     * @return $this
     * @throws \coding_exception
     */
    public function fetch($url) {
        $parseurl = parse_url($url);
        if ($parseurl["host"] == null) {
            $this->message = get_string("checker_links_error_undefined", "block_course_checker");
            $this->ignoreddomain = false;
            $this->successful = false;
            return $this;
        }

        // Check URL against Moodle HTTP security settings.
        $curlhelper = new \core\files\curl_security_helper;
        if ($curlhelper->url_is_blocked($url)) {
            $this->message = get_string("checker_links_error_httpsecurity", "block_course_checker", $url);
            $this->ignoreddomain = true;
            $this->successful = true;
            return $this;
        }

        // Skip whitelisted domain.
        if ($this->is_ignored_domain($parseurl["host"])) {
            $context = $parseurl + ["url" => $url];
            $this->message = get_string("checker_links_error_skipped", "block_course_checker", $context);
            $this->ignoreddomain = true;
            $this->successful = true;
            return $this;
        }

        // Skip whitelisted URL.
        if ($this->is_ignored_url($url)) {
            $context = ["host" => $this->ignoreddomain, "url" => $url];
            $this->message = get_string("checker_links_error_skipped", "block_course_checker", $context);
            $this->ignoreddomain = true;
            $this->successful = true;
            return $this;
        }

        $this->ignoreddomain = false;

        // Use curl to checks the urls.
        // You can use "$settings['debug'] = true;" to debug the curl request.
        $curl = new curl();

        $httpheader = array();
        $httpheader[] = "Accept-Encoding: gzip, deflate, br";
        $httpheader[] = "Accept:*/*";

        $curl->head($url, [
                "CURLOPT_HTTPHEADER" => $httpheader,
                "CURLOPT_CONNECTTIMEOUT" => $this->config->connecttimeout,
                "CURLOPT_TIMEOUT" => $this->config->timeout,
                "CURLOPT_FOLLOWLOCATION" => 1,  // Follow redirects.
                "CURLOPT_MAXREDIRS" => 3,   // Maximal number of redirects 301, 302.
                "CURLOPT_USERAGENT" => $this->config->useragent, // Default Moodle USERAGENT causing problems.
                "CURLOPT_SSL_VERIFYHOST" => 0,
                "CURLOPT_SSL_VERIFYPEER" => 0,
                "CURLOPT_ENCODING" => "gzip",
                "CURLOPT_REFERER" => $url // Essentially this tells the server which page sent you there.
        ]);

        $infos = $curl->get_info();
        $code = (int) $infos["http_code"];
        if ($code === 0) {
            if ($this->file_get_content($url, $parseurl)) {
                return $this;
            }

            // Code 0: timeout or other curl error.
            $context = $parseurl + ["url" => $url, "curl_errno" => $curl->get_errno(), "curl_error" => $curl->error];
            $this->message = get_string("checker_links_error_curl", "block_course_checker", $context);
            $this->successful = false;
            return $this;
        }

        $context = $parseurl + ["url" => $url, "http_code" => $code];
        if ($code >= 200 && $code < 400) {
            $this->message = get_string("checker_links_ok", "block_course_checker", $context);
            $this->successful = true;
            return $this;
        }

        // If curl finds 404, we don't need to run file get content.
        if ($this->file_get_content($url, $parseurl) && $code != 404) {
            return $this;
        }

        // Code != 0 means it's a http error.
        $this->message = get_string("checker_links_error_code", "block_course_checker", $context);
        $this->successful = false;
        return $this;
    }

    /**
     * @param $url
     * @param $parseurl
     * @return bool
     */
    protected function file_get_content($url, $parseurl) {
        try {
            @file_get_contents($url);

            $httpresponse = null;
            if (!empty($http_response_header)) {
                $httpresponse = $this->parse_headers($http_response_header);
            }

            if (isset($httpresponse['reponse_code']) && (int) $httpresponse['reponse_code'] == 200) {
                $context = $parseurl + ["url" => $url, "http_code" => "200"];
                $this->message = get_string("checker_links_ok", "block_course_checker", $context) . " (file_get_contents)";
                $this->successful = true;
                return true;
            }
        } catch (Exception $exception) {
            return false;
        }
        return false;
    }

    /**
     * @param $headers
     * @return array
     */
    protected function parse_headers($headers) {
        $head = array();
        foreach ($headers as $v) {
            $t = explode(':', $v, 2);
            if (isset($t[1])) {
                $head[trim($t[0])] = trim($t[1]);
            } else {
                $head[] = $v;
                if (preg_match("#HTTP/[0-9\.]+\s+([0-9]+)#", $v, $out)) {
                    $head['reponse_code'] = intval($out[1]);
                }
            }
        }
        return $head;
    }

    /**
     * Tells if a domain should be skipped.
     *
     * @param string $domain
     * @return boolean
     */
    protected function is_ignored_domain(string $domain) {
        return in_array($domain, $this->config->ignoredomains);
    }

    /**
     * Tells if a URL should be skipped.
     *
     * @param string $url
     * @return bool
     */
    protected function is_ignored_url(string $url) {
        foreach ($this->config->ignoredomains as $ignoredomain) {
            if (strpos($url, $ignoredomain)) {
                $this->ignoreddomain = $ignoredomain;
                return true;
            }
        }
        return false;
    }
}
