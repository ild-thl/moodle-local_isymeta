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
 * Upgrade script for local_ildmeta.
 *
 * @package     local_ildmeta
 * @copyright   2022 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrades the database according to the current version.
 *
 * @param int $oldversion
 * @return boolean
 */
function xmldb_local_ildmeta_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2022060220) {

        // Define field dcattributes to be added to ildmeta.
        $table = new xmldb_table('ildmeta');
        $exporttobird = new xmldb_field('exporttobird', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 0, 'certificateofachievement');
        $coursetype = new xmldb_field('coursetype', XMLDB_TYPE_CHAR, '128', null, null, null, null, 'exporttobird');
        $courseformat = new xmldb_field('courseformat', XMLDB_TYPE_CHAR, '128', null, null, null, null, 'coursetype');
        $selfpaced = new xmldb_field('selfpaced', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 0, 'courseformat');
        $audience = new xmldb_field('audience', XMLDB_TYPE_CHAR, '128', null, null, null, null, 'selfpaced');
        $courseprerequisites = new xmldb_field('courseformat', XMLDB_TYPE_TEXT, '120', null, null, null, null, 'audience');

        // Conditionally launch add field exporttobird.
        if (!$dbman->field_exists($table, $exporttobird)) {
            $dbman->add_field($table, $exporttobird);
        }
        // Conditionally launch add field coursetype.
        if (!$dbman->field_exists($table, $coursetype)) {
            $dbman->add_field($table, $coursetype);
        }
        // Conditionally launch add field courseformat.
        if (!$dbman->field_exists($table, $courseformat)) {
            $dbman->add_field($table, $courseformat);
        }
        // Conditionally launch add field selfpaced.
        if (!$dbman->field_exists($table, $selfpaced)) {
            $dbman->add_field($table, $selfpaced);
        }
        // Conditionally launch add field audience.
        if (!$dbman->field_exists($table, $audience)) {
            $dbman->add_field($table, $audience);
        }
        // Conditionally launch add field courseprerequisites.
        if (!$dbman->field_exists($table, $courseprerequisites)) {
            $dbman->add_field($table, $courseprerequisites);
        }

        // Ildmeta savepoint reached.
        upgrade_plugin_savepoint(true, 2022070419, 'local', 'ildmeta');
    }

    if ($oldversion < 2022060514) {

        // Define ildmeta_settings table to be added.
        $settingstable = new xmldb_table('ildmeta_settings');
        $settingstable->add_field('id',            XMLDB_TYPE_INTEGER, '10',   null, XMLDB_NOTNULL, XMLDB_SEQUENCE,    null);
        $settingstable->add_field('coursetype',    XMLDB_TYPE_CHAR,    "512",  null, XMLDB_NOTNULL, null,              "[]");
        $settingstable->add_field('courseformat',  XMLDB_TYPE_CHAR,    "512",  null, XMLDB_NOTNULL, null,              "[]");
        $settingstable->add_field('audience',      XMLDB_TYPE_CHAR,    "512",  null, XMLDB_NOTNULL, null,              "[]");

        // Adding keys to table ildmeta_settings.
        $settingstable->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally add ildmeta_settings table.
        if (!$dbman->table_exists($settingstable)) {
            $dbman->create_table($settingstable);
        }

        // Define ildmeta_spdx_licenses table to be added.
        $licensetable = new xmldb_table('ildmeta_spdx_licenses');
        $licensetable->add_field('id',             XMLDB_TYPE_INTEGER, '10',   null, XMLDB_NOTNULL, XMLDB_SEQUENCE,    null);
        $licensetable->add_field('moodle_license', XMLDB_TYPE_INTEGER, '10',   null, XMLDB_NOTNULL, null,              null);
        $licensetable->add_field('spdx_shortname', XMLDB_TYPE_CHAR,    "64",   null, XMLDB_NOTNULL, null,              null);
        $licensetable->add_field('spdx_fullname',  XMLDB_TYPE_CHAR,    "256",  null, XMLDB_NOTNULL, null,              null);
        $licensetable->add_field('spdx_url',       XMLDB_TYPE_CHAR,    "512",  null, XMLDB_NOTNULL, null,              null);

        // Adding keys to table ildmeta_settings.
        $licensetable->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $licensetable->add_key('moodle_license', XMLDB_KEY_FOREIGN, array('moodle_license'), 'license', array('id'));

        // Conditionally launch add field exporttobird.
        if (!$dbman->table_exists($licensetable)) {
            $dbman->create_table($licensetable);
        }

        // Ildmeta savepoint reached.
        upgrade_mod_savepoint(true, 2022060514, 'local_ildmeta');
    }

    if ($oldversion < 2022060518) {
        // Define field dcattributes to be added to ildmeta.
        $table = new xmldb_table('ildmeta');
        $videolicense = new xmldb_field('videolicense', XMLDB_TYPE_CHAR, '256', null, null, null, null, 'videocode');

        // Conditionally launch add field videolicense.
        if (!$dbman->field_exists($table, $videolicense)) {
            $dbman->add_field($table, $videolicense);
        }

        // Ildmeta savepoint reached.
        upgrade_plugin_savepoint(true, 2022070419, 'local', 'ildmeta');
    }

    if ($oldversion < 2022070118) {
        // Rename university column in ildmeta table.
        $ildmetatable = new xmldb_table('ildmeta');
        $university = new xmldb_field('university', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'coursetitle');
        $subjectarea = new xmldb_field('subjectarea', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'university');
        $coursetype = new xmldb_field('coursetype', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'exporttobird');
        $courseformat = new xmldb_field('courseformat', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'coursetype');
        $audience = new xmldb_field('audience', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'selfpaced');

        // Conditionally rename field university to provider.
        if ($dbman->field_exists($ildmetatable, $university)) {
            $dbman->rename_field($ildmetatable,  $university,  "provider");
        }

        // Conditionally update field constraints.
        if ($dbman->field_exists($ildmetatable, $university)) {
            $dbman->change_field_precision($ildmetatable,  $university);
        }
        if ($dbman->field_exists($ildmetatable, $subjectarea)) {
            $dbman->change_field_precision($ildmetatable,  $subjectarea);
        }
        if ($dbman->field_exists($ildmetatable, $coursetype)) {
            $dbman->change_field_type($ildmetatable,  $coursetype);
            $dbman->change_field_precision($ildmetatable,  $coursetype);
        }
        if ($dbman->field_exists($ildmetatable, $courseformat)) {
            $dbman->change_field_type($ildmetatable,  $courseformat);
            $dbman->change_field_precision($ildmetatable,  $courseformat);
        }
        if ($dbman->field_exists($ildmetatable, $audience)) {
            $dbman->change_field_type($ildmetatable,  $audience);
            $dbman->change_field_precision($ildmetatable,  $audience);
        }

        // Delete ildmeta_settings table.
        $settingstable = new xmldb_table('ildmeta_settings');

        // Conditionally delete dd ildmeta_settings table.
        if ($dbman->table_exists($settingstable)) {
            $dbman->drop_table($settingstable);
        }

        // Define ildmeta_vocabulary table to be added.
        $vocabularytable = new xmldb_table('ildmeta_vocabulary');
        $vocabularytable->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $vocabularytable->add_field('title', XMLDB_TYPE_CHAR, "64", null, XMLDB_NOTNULL, null, null);
        $vocabularytable->add_field('terms', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);

        // Adding keys to table ildmeta_vocabulary.
        $vocabularytable->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally add ildmeta_vocabulary table.
        if (!$dbman->table_exists($vocabularytable)) {
            $dbman->create_table($vocabularytable);
        }

        // Fill database tables with default values.
        include("install.php");
        xmldb_local_ildmeta_install();

        // Ildmeta savepoint reached.
        upgrade_plugin_savepoint(true, 2022070419, 'local', 'ildmeta');
    }

    return true;
}
