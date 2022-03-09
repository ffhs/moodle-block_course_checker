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
 * This is a helper for the task scheduler
 *
 * @package    block_course_checker
 * @copyright  2020 FFHS <christoph.karlen@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker;

use block_course_checker\model\mod_type_interface;

/**
 * Class resolution_link_helper
 *
 * @package block_course_checker
 */
class resolution_link_helper implements mod_type_interface {

    /** @var array list of modules which can be linked directly to the module config page */
    const DIRECT_MOD_NAMES = [
            self::MOD_TYPE_ASSIGN,
            self::MOD_TYPE_BOOK,
            self::MOD_TYPE_CHOICE,
            self::MOD_TYPE_CHOICEGROUP,
            self::MOD_TYPE_DATA,
            self::MOD_TYPE_FEEDBACK,
            self::MOD_TYPE_FORUM,
            self::MOD_TYPE_LABEL,
            self::MOD_TYPE_LESSON,
            self::MOD_TYPE_PAGE,
            self::MOD_TYPE_QUESTIONNAIRE,
            self::MOD_TYPE_QUIZ,
            self::MOD_TYPE_RESOURCE,
            self::MOD_TYPE_URL,
            self::MOD_TYPE_WIKI
    ];

    /**
     * @param $modname
     * @param $coursemoduleid
     * @return \moodle_url|string
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public static function get_link_to_modedit_or_view_page($modname, $coursemoduleid, $gotoeditsettingspage = true) {
        // We open the edit settings page instead of the mod/view itself.
        if (in_array($modname, self::DIRECT_MOD_NAMES) && $gotoeditsettingspage) {
            $url = new \moodle_url('/course/mod.php', [
                    "update" => $coursemoduleid,
                    "sesskey" => sesskey(),
                    "sr" => 0
            ]);
        } else {
            $url = new \moodle_url('/mod/' . $modname . '/view.php', ['id' => $coursemoduleid]);
        }
        return $url->out(false);
    }

    /**
     * @param $course
     * @return string
     * @throws \moodle_exception
     */
    public static function get_link_to_course_view_page($courseid): string {
        return (new \moodle_url('/course/view.php', [
                'id' => $courseid
        ]))->out(false);
    }

    /**
     * @param $course
     * @return string
     * @throws \moodle_exception
     */
    public static function get_link_to_course_edit_page($course): string {
        return (new \moodle_url('/course/edit.php', [
                'id' => $course->id
        ]))->out(false);
    }

    /**
     * @param $coursecontext
     * @return string
     * @throws \moodle_exception
     */
    public static function get_link_to_course_filter_page($coursecontext): string {
        return (new \moodle_url('/filter/manage.php', [
                'contextid' => $coursecontext->id
        ]))->out(false);
    }

    /**
     * @param \cm_info $cm
     * @param string $checkername
     * @return string
     * @throws \coding_exception
     */
    public static function get_target(\cm_info $cm, string $checkername = ''): string {
        if ($checkername == 'checker_links') {
            $targetcontext = (object) ["modname" => get_string("pluginname", $cm->modname), "name" => strip_tags($cm->name)];
            $target = get_string("checker_links_activity", "block_course_checker", $targetcontext);
        } else {
            $targetcontext = (object) ["name" => strip_tags($cm->name)];
            $target = get_string("groups_activity", "block_course_checker", $targetcontext);
        }
        return $target;
    }
}
