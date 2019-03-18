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
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the badges block
 *
 * @param int $oldversion
 */
function xmldb_block_course_checker_upgrade($oldversion) {
    global $DB, $CFG;
    $file = $CFG->dirroot . '/blocks/course_checker/db/install.xml';

    if (!$oldversion) {
        return true;
    }

    if (intval($oldversion) < 2019031504) {
        $tableexists = $DB->get_manager()->table_exists('block_course_checker');
        if (!$tableexists) {
            $DB->get_manager()->install_from_xmldb_file($file);
        }
    }
    return true;
}