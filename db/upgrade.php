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
 * @return bool
 */
function xmldb_block_course_checker_upgrade($oldversion) {
    global $DB, $CFG;
    $file = $CFG->dirroot . '/blocks/course_checker/db/install.xml';

    if (!$oldversion) {
        return true;
    }

    // Install the database from a file.
    if (intval($oldversion) < 2019031504) {
        $tableexists = $DB->get_manager()->table_exists('block_course_checker');
        if (!$tableexists) {
            $DB->get_manager()->install_from_xmldb_file($file);
        }
    }

    // Migration to add the field "last_activity_edition".
    if (intval($oldversion) === 2019031507) { // TODO: Fix version.
        $xmldbfile = new xmldb_file($file);
        if (!$xmldbfile->fileExists()) {
            throw new ddl_exception('ddlxmlfileerror', null, 'File does not exist');
        }

        // Add the last_modification field (by loading the definition from the install.xml file).
        $xmldbfile->loadXMLStructure();
        $table = $xmldbfile->getStructure()->getTable("block_course_checker");
        $field = $table->getField("last_activity_edition");
        $DB->get_manager()->add_field($table, $field);
    }

    // Load the new table events.
    if ($oldversion < 2019050702) {
        $xmldbfile = new xmldb_file($file);
        if (!$xmldbfile->fileExists()) {
            throw new ddl_exception('ddlxmlfileerror', null, 'File does not exist');
        }
        $tableexists = $DB->get_manager()->table_exists('block_course_checker_events');

        if (!$tableexists) {
            $xmldbfile->loadXMLStructure();
            $tablestructure = $xmldbfile->getStructure()->getTable('block_course_checker_events');
            $DB->get_manager()->create_table($tablestructure);
        }
    }

    // Recreate block_course_checker_events's indexes.
    if ($oldversion < 2019050704) {
        $xmldbfile = new xmldb_file($file);
        $xmldbfile->loadXMLStructure();
        $tablestructure = $xmldbfile->getStructure()->getTable('block_course_checker_events');

        $index = new xmldb_index('course_id', XMLDB_INDEX_UNIQUE, array('course_id'));
        if ($DB->get_manager()->index_exists($tablestructure, $index)) {
            $DB->get_manager()->drop_index($tablestructure, $index);
        }

        $tablestructure = $xmldbfile->getStructure()->getTable('block_course_checker_events');
        foreach ($tablestructure->getIndexes() as $index) {
            if (! $DB->get_manager()->index_exists($tablestructure, $index)) {
                $DB->get_manager()->add_index($tablestructure, $index);
            }
        }
    }
    return true;
}