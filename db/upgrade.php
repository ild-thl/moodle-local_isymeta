<?php

function xmldb_local_ildmeta_upgrade($oldversion)
{
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
        upgrade_mod_savepoint(true, 2022060220, 'local_ildmeta');
    }


    if ($oldversion < 2022060514) {

        // Define ildmeta_settings table to be added.
        $settings_table = new xmldb_table('ildmeta_settings');
        $settings_table->add_field('id',            XMLDB_TYPE_INTEGER, '10',   null, XMLDB_NOTNULL, XMLDB_SEQUENCE,    null);
        $settings_table->add_field('coursetype',    XMLDB_TYPE_CHAR,    "512",  null, XMLDB_NOTNULL, null,              "[]");
        $settings_table->add_field('courseformat',  XMLDB_TYPE_CHAR,    "512",  null, XMLDB_NOTNULL, null,              "[]");
        $settings_table->add_field('audience',      XMLDB_TYPE_CHAR,    "512",  null, XMLDB_NOTNULL, null,              "[]");

        // Adding keys to table ildmeta_settings.
        $settings_table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally add ildmeta_settings table.
        if (!$dbman->table_exists($settings_table)) {
            $dbman->create_table($settings_table);
        }

        // Define ildmeta_spdx_licenses table to be added.
        $license_table = new xmldb_table('ildmeta_spdx_licenses');
        $license_table->add_field('id',             XMLDB_TYPE_INTEGER, '10',   null, XMLDB_NOTNULL, XMLDB_SEQUENCE,    null);
        $license_table->add_field('moodle_license', XMLDB_TYPE_INTEGER, '10',   null, XMLDB_NOTNULL, null,              null);
        $license_table->add_field('spdx_shortname', XMLDB_TYPE_CHAR,    "64",   null, XMLDB_NOTNULL, null,              null);
        $license_table->add_field('spdx_fullname',  XMLDB_TYPE_CHAR,    "256",  null, XMLDB_NOTNULL, null,              null);
        $license_table->add_field('spdx_url',       XMLDB_TYPE_CHAR,    "512",  null, XMLDB_NOTNULL, null,              null);

        // Adding keys to table ildmeta_settings.
        $license_table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $license_table->add_key('moodle_license', XMLDB_KEY_FOREIGN, array('moodle_license'), 'license', array('id'));

        // Conditionally launch add field exporttobird.
        if (!$dbman->table_exists($license_table)) {
            $dbman->create_table($license_table);
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
        upgrade_mod_savepoint(true, 2022060518, 'local_ildmeta');
    }

    return true;
}
