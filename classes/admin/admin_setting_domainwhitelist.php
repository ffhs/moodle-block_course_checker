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
namespace block_course_checker\admin;

use admin_setting_configtextarea;
use block_course_checker\checkers\checker_link\checker;

defined('MOODLE_INTERNAL') || die();

class admin_setting_domainwhitelist extends admin_setting_configtextarea {

    /**
     * check one domain whether it is valid
     */
    private function is_valid_domain_name($domainname) {
        return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domainname) // Valid chars check.
            && preg_match("/^.{1,253}$/", $domainname) // Overall length check.
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domainname)   ); // Length of each label.
    }

    /**
     * @inheritDoc
     */
    public function validate($data) {
        try {
            $domains = array_filter(array_map('trim', explode("\n", $data)));
            if (!in_array(checker::WHITELIST_DEFAULT, $domains)) {
                return get_string('admin_domain_name_default_missing', 'block_course_checker', checker::WHITELIST_DEFAULT);
            }
            foreach ($domains as $domainname) {
                if (!$this->is_valid_domain_name($domainname)) {
                    return get_string('admin_domain_name_notvalid', 'block_course_checker', $domainname);
                };
            }
            return true;
        } catch (\dml_exception $exception) {
            return get_string('admin_domain_list_notvalid', 'block_course_checker');
        }
    }

}