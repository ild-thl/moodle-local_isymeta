<?php

function xmldb_local_isymeta_upgrade($oldversion) {
     global $DB;
     $dbman = $DB->get_manager();

     if($oldversion < 2021101500) {
        $table = new xmldb_table('isymeta');
        $field = new add_field('meta1', XMLDB_TYPE_CHAR, '120', null, XMLDB_NOTNULL, null, null);

        upgrade_plugin_savepoint(true, 2021101500, 'local', 'isymeta');
     }

    if($oldversion < 2021020404) {
        $table = new xmldb_table('isymeta_sponsors');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('value', XMLDB_TYPE_TEXT, null, null, null, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $meta_table = new xmldb_table('isymeta');
        $fieldy = new xmldb_field('detailssponsor', XMLDB_TYPE_CHAR, '120', null, XMLDB_NOTNULL, null, null);
        $fieldz = new xmldb_field('detailsmoresponsor', XMLDB_TYPE_CHAR, '120', null, XMLDB_NOTNULL, null, null);

        if (!$dbman->field_exists($meta_table, $fieldy)) {
            $dbman->add_field($meta_table, $fieldy);
        }

        if (!$dbman->field_exists($meta_table, $fieldz)) {
            $dbman->add_field($meta_table, $fieldz);
        }

        upgrade_plugin_savepoint(true, 2021020404, 'local', 'isymeta');
    }

    if ($oldversion < 2020120901) {
        $table = new xmldb_table('isymeta');
        $field = new xmldb_field('meta4', XMLDB_TYPE_CHAR, '120', null, XMLDB_NOTNULL, null, null);

        $dbman->change_field_type($table, $field, $continue=true, $feedback=true);

        upgrade_plugin_savepoint(true, 2020120909, 'local', 'isymeta');
    }

    return true;
}