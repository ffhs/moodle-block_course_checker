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
 * This file keeps track of upgrades to the course checker block
 *
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
    if (intval($oldversion) === 2019031507) {
        $xmldbfile = new xmldb_file($file);
        if (!$xmldbfile->fileExists()) {
            throw new ddl_exception('ddlxmlfileerror', null, 'File does not exist');
        }

        // Add the last_modification field (by loading the definition from the install.xml file).
        $xmldbfile->loadXMLStructure();
        $table = $xmldbfile->getStructure()->getTable("block_course_checker");
        $field = $table->getField("last_activity_edition");
        $DB->get_manager()->add_field($table, $field);

        // Sitestats savepoint reached.
        upgrade_plugin_savepoint(true, 2019031507, 'block', 'course_checker');
    }

    // Load the new table.
    if ($oldversion < 2019050800) {
        // This will drop the table if already there (for developers).
        $table = new xmldb_table('block_course_checker_events');
        $tableexists = $DB->get_manager()->table_exists($table);
        if ($tableexists) {
            $DB->get_manager()->drop_table($table);
        }

        // Install the table.
        $DB->get_manager()->install_one_table_from_xmldb_file($file, $table->getName());

        // Sitestats savepoint reached.
        upgrade_plugin_savepoint(true, 2019050800, 'block', 'course_checker');
    }

    // Migration to change type of fields.
    if ($oldversion < 2019071002) {
        $dbman = $DB->get_manager();

        $table = new xmldb_table('block_course_checker_events');
        $fields = [
                new xmldb_field('action', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null),
                new xmldb_field('modulename', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null),
                new xmldb_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null)
        ];
        foreach ($fields as $field) {
            $dbman->change_field_type($table, $field);
        }

        // Sitestats savepoint reached.
        upgrade_plugin_savepoint(true, 2019071002, 'block', 'course_checker');
    }

    // Migration to change name of checker_link to checker_links.
    if ($oldversion >= 2020050500 && $oldversion < 2020050501) {
        global $DB;

        $query = "SELECT * FROM {config_plugins} WHERE plugin = 'block_course_checker' AND name LIKE 'checker_link%'";
        $records = $DB->get_records_sql($query);
        foreach ($records as $record) {
            $record->name = str_replace('checker_link', 'checker_links', $record->name);
            $DB->update_record('config_plugins', $record, true);
        }

        $query = "SELECT * FROM {block_course_checker} WHERE result LIKE '%checker_link%'";
        $records = $DB->get_records_sql($query);
        foreach ($records as $record) {
            $record->result = str_replace('checker_link', 'checker_links', $record->result);
            $DB->update_record('block_course_checker', $record, true);
        }

        // Sitestats savepoint reached.
        upgrade_plugin_savepoint(true, 2020050501, 'block', 'course_checker');
    }
    return true;
}