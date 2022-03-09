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
 * This type of field should be used for config settings which contains domains.
 *
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker\admin;

use admin_setting_configtextarea;
use block_course_checker\checkers\checker_links\config;

class admin_setting_domainwhitelist extends admin_setting_configtextarea {

    /**
     * @inheritDoc
     */
    public function validate($data) {
        $domains = array_filter(array_map('trim', explode("\n", $data)));
        if (!in_array(config::WHITELIST_DEFAULT, $domains)) {
            return get_string('admin_domain_name_default_missing', 'block_course_checker', config::WHITELIST_DEFAULT);
        }
        foreach ($domains as $domainname) {
            if (!is_valid_domain_name($domainname)) {
                return get_string('admin_domain_name_notvalid', 'block_course_checker', $domainname);
            };
        }
        return true;
    }
}
