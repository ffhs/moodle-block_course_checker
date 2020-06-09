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
 * Fetch useradta and return records if entries exists
 *
 * @package    block_course_checker
 * @copyright  2020 Adrian Perez, Fernfachhochschule Schweiz (FFHS) <adrian.perez@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker\checkers\checker_userdata;

use block_course_checker\model\mod_type_interface;

defined('MOODLE_INTERNAL') || die();

/**
 * Class fetch_userdata
 *
 * @package block_course_checker\checkers\checker_userdata
 */
class fetch_userdata implements mod_type_interface {
    /**
     * @param \cm_info $cm
     * @return array
     * @throws \dml_exception
     */
    public function check_for_userdata_in_module(\cm_info $cm) {
        global $CFG, $DB;

        $records = [];
        switch ($cm->modname) {
            case self::MOD_TYPE_DATA:
                require_once($CFG->dirroot . '/mod/data/locallib.php');
                $data = $DB->get_record('data', array('id' => $cm->instance), '*', MUST_EXIST);
                $currentgroup = groups_get_activity_group($cm, true);
                list($records) = data_search_entries($data, $cm, $cm->context, 'list', $currentgroup);
                break;
            case self::MOD_TYPE_GLOSSARY:
                require_once($CFG->dirroot . '/mod/glossary/lib.php');
                $glossary = $DB->get_record('glossary', array('id' => $cm->instance), '*', MUST_EXIST);
                $options = ['includenotapproved' => true];
                list($records) = glossary_get_entries_by_search($glossary, $cm->context, '', 1, 'CONCEPT', 'ASC', 0,
                        999, $options);
                break;
            case self::MOD_TYPE_WIKI:
                require_once($CFG->dirroot . '/mod/wiki/locallib.php');
                $records = [];
                $subwikis = wiki_get_subwikis($cm->instance);
                foreach ($subwikis as $subwiki) {
                    $subwikirecords = wiki_get_page_list($subwiki->id);
                    $records = array_merge($records, $subwikirecords);
                }
                break;
            case self::MOD_TYPE_FORUM:
                require_once($CFG->dirroot . '/mod/forum/lib.php');
                $records = forum_get_discussions($cm, '', false, -1, -1, true, -1, 0, FORUM_POSTS_ALL_USER_GROUPS, 0);
                break;
        }

        return $records;
    }
}