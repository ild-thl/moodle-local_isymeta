<?php

function xmldb_local_ildmeta_upgrade($oldversion) {
     global $DB;
     $dbman = $DB->get_manager();

    if($oldversion < 2021020404) {
        $table = new xmldb_table('ildmeta_sponsors');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('value', XMLDB_TYPE_TEXT, null, null, null, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $meta_table = new xmldb_table('ildmeta');
        // $fieldx = new xmldb_field('sponsor', XMLDB_TYPE_CHAR, '120', null, XMLDB_NOTNULL, null, null);
        $fieldy = new xmldb_field('detailssponsor', XMLDB_TYPE_CHAR, '120', null, XMLDB_NOTNULL, null, null);
        $fieldz = new xmldb_field('detailsmoresponsor', XMLDB_TYPE_CHAR, '120', null, XMLDB_NOTNULL, null, null);

        if (!$dbman->field_exists($meta_table, $fieldx)) {
            $dbman->add_field($meta_table, $fieldx);
        }

        if (!$dbman->field_exists($meta_table, $fieldy)) {
            $dbman->add_field($meta_table, $fieldy);
        }

        if (!$dbman->field_exists($meta_table, $fieldz)) {
            $dbman->add_field($meta_table, $fieldz);
        }

        upgrade_plugin_savepoint(true, 2021020404, 'local', 'ildmeta');
    }

    if ($oldversion < 2020120901) {
        $table = new xmldb_table('ildmeta');
        $field = new xmldb_field('processingtime', XMLDB_TYPE_CHAR, '120', null, XMLDB_NOTNULL, null, null);

        $dbman->change_field_type($table, $field, $continue=true, $feedback=true);

        upgrade_plugin_savepoint(true, 2020120909, 'local', 'ildmeta');
    }
    //     upgrade_plugin_savepoint(true, 2020062301, 'local', 'ildmeta');


    // //if ($oldversion < 2018091800) {
    // 	$table = new xmldb_table('ildmeta');

    //     $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    //     $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    //     $table->add_field('videocode', XMLDB_TYPE_CHAR, '120', null, XMLDB_NOTNULL, null, null);
    //     $table->add_field('overviewimage', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    //     $table->add_field('detailimage', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    //     $table->add_field('coursetitle', XMLDB_TYPE_CHAR, '120', null, XMLDB_NOTNULL, null, null);
    //     $table->add_field('university', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
    //     $table->add_field('noindexcourse', XMLDB_TYPE_CHAR, '120', null, XMLDB_NOTNULL, null, null);
    //     $table->add_field('subjectarea', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
    //     $table->add_field('lecturer', XMLDB_TYPE_CHAR, '120', null, XMLDB_NOTNULL, null, null);
    //     $table->add_field('courselanguage', XMLDB_TYPE_CHAR, '120', null, XMLDB_NOTNULL, null, null);
    //     $table->add_field('processingtime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    //     $table->add_field('starttime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    //     $table->add_field('teasertext', XMLDB_TYPE_TEXT, '120', null, XMLDB_NOTNULL, null, null);
    //     $table->add_field('targetgroup', XMLDB_TYPE_TEXT, '120', null, XMLDB_NOTNULL, null, null);
    //     $table->add_field('learninggoals', XMLDB_TYPE_TEXT, '120', null, XMLDB_NOTNULL, null, null);
    //     $table->add_field('structure', XMLDB_TYPE_TEXT, '120', null, XMLDB_NOTNULL, null, null);
    //     $table->add_field('detailslecturer', XMLDB_TYPE_TEXT, '120', null, XMLDB_NOTNULL, null, null);
    //     $table->add_field('detailsmorelecturer', XMLDB_TYPE_TEXT, '120', null, XMLDB_NOTNULL, null, null);
    //     $table->add_field('license', XMLDB_TYPE_CHAR, '120', null, XMLDB_NOTNULL, null, null);
    //     $table->add_field('tags', XMLDB_TYPE_CHAR, '120', null, XMLDB_NOTNULL, null, null);
    //     $table->add_field('certificateofachievement', XMLDB_TYPE_TEXT, '120', null, XMLDB_NOTNULL, null, null);

    //     $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

    //     if (!$dbman->table_exists($table)) {
    //         $dbman->create_table($table);
    //     }
    // //}

    // // if ($oldversion < 2020061906) {

    //     // Define table ildmeta_additional to be created.
    //     $table = new xmldb_table('ildmeta_additional');

    //     // Adding fields to table ildmeta_additional.
    //     $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    //     $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    //     $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
    //     $table->add_field('value', XMLDB_TYPE_TEXT, null, null, null, null, null);

    //     // Adding keys to table ildmeta_additional.
    //     $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

    //     // Conditionally launch create table for ildmeta_additional.
    //     if (!$dbman->table_exists($table)) {
    //         $dbman->create_table($table);
    //     }

    //     // Ildmeta savepoint reached.
    //     upgrade_plugin_savepoint(true, 2020062301, 'local', 'ildmeta');
    // //  }


    return true;
}








