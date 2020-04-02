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
    const TIMEOUT_DEFAULT = 13;
    const CONNECT_TIMEOUT_SETTING = 'block_course_checker/checker_link_connect_timeout';
    const CONNECT_TIMEOUT_DEFAULT = 5;
    const WHITELIST_SETTING = 'block_course_checker/checker_link_whitelist';
    const WHITELIST_HEADING = 'block_course_checker/checker_link_whitelist_heading';
    const WHITELIST_DEFAULT = 'www.w3.org';
    const USERAGENT_SETTING = 'block_course_checker/checker_link_useragent';
    const USERAGENT_DEFAULT = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.132 Safari/537.36';
    
    use checker_config_trait;
    
    /** @var int $connecttimeout from checker settings */
    protected $connecttimeout;
    
    /** @var int $connecttimeout from checker settings */
    protected $timeout;
    
    /** @var array list of ignored domains  */
    protected $ignoredomains;
    
    /** @var string user agent  */
    protected $useragent;
    
    /** @var string $message */
    public $message;
    
    /** @var bool $ignoreddomain */
    public $ignoreddomain;
    
    /** @var bool $successful */
    public $successful;
    
    /**
     * Initialize checker by setting it up with the configuration
     * Todo access to blockdomainwhitelist config is not working;
     */
    public function init() {
        // Load settings.
        $this->connecttimeout = (int) $this->get_config(self::CONNECT_TIMEOUT_SETTING, self::CONNECT_TIMEOUT_DEFAULT);
        $this->timeout = (int) $this->get_config(self::TIMEOUT_SETTING, self::TIMEOUT_DEFAULT);
        $this->useragent = (string) $this->get_config(self::USERAGENT_SETTING, self::USERAGENT_DEFAULT);
        $domainwhitelist = (string) $this->get_config(self::WHITELIST_SETTING, self::WHITELIST_DEFAULT);
        //$blockdomainwhitelist = (string) $this->get_config('block_course_checker/config_link_whitelist');
        $this->ignoredomains = array_filter(array_map('trim', explode("\n", $domainwhitelist)));
    }
    
    /**
     * fetch_url constructor.
     *
     * @param array $ignoredomains
     */
    public function __construct($ignoredomains = []) {
        $this->ignoredomains = $ignoredomains;
        $this->init();
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
        //$settings['debug'] = true;
        $curl = new \curl();
        
        $httpheader=array();
        //$httpheader[]="Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        $httpheader[]="Accept-Encoding: gzip, deflate, br";
        //$header[]="Accept-Language: en-US,en;q=0.5";
        //$httpheader[]="Connection: keep-alive";
        $httpheader[]="Accept:*/*";
        
        $curl->head($url, [
                "CURLOPT_HTTPHEADER" => $httpheader,
                "CURLOPT_CONNECTTIMEOUT" => $this->connecttimeout,
                "CURLOPT_TIMEOUT" => $this->timeout,
                "CURLOPT_FOLLOWLOCATION" => 1,  // Follows redirects.
                "CURLOPT_MAXREDIRS" => 3,   // Maximal number of redirects 301,302?
                "CURLOPT_USERAGENT" => $this->useragent, // Default Moodle USERAGENT causing problems.
                "CURLOPT_SSL_VERIFYHOST" => 0,
                "CURLOPT_SSL_VERIFYPEER" => 0,
                "CURLOPT_ENCODING" => "gzip",
                //"CURLOPT_COOKIE" => "NID=67=pdjIQN5CUKVn0bRgAlqitBk7WHVivLsbLcr7QOWMn35Pq03N1WMy6kxYBPORtaQUPQrfMK4Yo0vVz8tH97ejX3q7P2lNuPjTOhwqaI2bXCgPGSDKkdFoiYIqXubR0cTJ48hIAaKQqiQi_lpoe6edhMglvOO9ynw; PREF=ID=52aa671013493765:U=0cfb5c96530d04e3:FF=0:LD=en:TM=1370266105:LM=1370341612:GM=1:S=Kcc6KUnZwWfy3cOl; OTZ=1800625_34_34__34_; S=talkgadget=38GaRzFbruDPtFjrghEtRw; SID=DQAAALoAAADHyIbtG3J_u2hwNi4N6UQWgXlwOAQL58VRB_0xQYbDiL2HA5zvefboor5YVmHc8Zt5lcA0LCd2Riv4WsW53ZbNCv8Qu_THhIvtRgdEZfgk26LrKmObye1wU62jESQoNdbapFAfEH_IGHSIA0ZKsZrHiWLGVpujKyUvHHGsZc_XZm4Z4tb2bbYWWYAv02mw2njnf4jiKP2QTxnlnKFK77UvWn4FFcahe-XTk8Jlqblu66AlkTGMZpU0BDlYMValdnU; HSID=A6VT_ZJ0ZSm8NTdFf; SSID=A9_PWUXbZLazoEskE; APISID=RSS_BK5QSEmzBxlS/ApSt2fMy1g36vrYvk; SAPISID=ZIMOP9lJ_E8SLdkL/A32W20hPpwgd5Kg1J",
                //"CURLOPT_AUTOREFERER" => 1,
                // "CURLOPT_HEADER" => 1
                "CURLOPT_REFERER"  => $url // Essentially this tells the server which page sent you there.
        ]);

        $infos = $curl->get_info();
        $code = (int) $infos["http_code"];
        if ($code === 0) {
            if($this->fileGetContent($url,$parseurl)){
                return $this;
            }
            
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
        
        if($this->fileGetContent($url,$parseurl) && $code != 404){  // If curl finds 404, we don't need to run file get content.
            return $this;
        }
        
        // Code != 0 means it's a http error.
        $this->message = get_string("checker_link_error_code", "block_course_checker", $context);
        $this->successful = false;
        return $this;
    }
    
    /**
     * @param $url
     * @param $parseurl
     * @return bool
     */
    protected function fileGetContent($url,$parseurl){
        try {
            @file_get_contents($url);
        
            $httpresponse = null;
            if(!empty($http_response_header)){
                $httpresponse = $this->parseHeaders($http_response_header);
            }
        
            if(isset($httpresponse['reponse_code']) && (int) $httpresponse['reponse_code'] == 200){
                $context = $parseurl + ["url" => $url, "http_code" => "200"];
                $this->message = get_string("checker_link_ok", "block_course_checker", $context)." (file_get_contents)";
                $this->successful = true;
                return true;
            }
        }catch (\Exception $exception) {
            return false;
        }
        return false;
    }
    
    /**
     * @param $headers
     * @return array
     */
    protected function parseHeaders( $headers )
    {
        $head = array();
        foreach( $headers as $k=>$v )
        {
            $t = explode( ':', $v, 2 );
            if( isset( $t[1] ) )
                $head[ trim($t[0]) ] = trim( $t[1] );
            else
            {
                $head[] = $v;
                if( preg_match( "#HTTP/[0-9\.]+\s+([0-9]+)#",$v, $out ) )
                    $head['reponse_code'] = intval($out[1]);
            }
        }
        return $head;
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