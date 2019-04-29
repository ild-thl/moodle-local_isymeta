<?php

function xmldb_local_ildmeta_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    //if ($oldversion < 2018091800) {
    	$table = new xmldb_table('ildmeta');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('videocode', XMLDB_TYPE_CHAR, '120', null, XMLDB_NOTNULL, null, null);
        $table->add_field('overviewimage', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('detailimage', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('coursetitle', XMLDB_TYPE_CHAR, '120', null, XMLDB_NOTNULL, null, null);
        $table->add_field('university', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('noindexcourse', XMLDB_TYPE_CHAR, '120', null, XMLDB_NOTNULL, null, null);
        $table->add_field('subjectarea', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('lecturer', XMLDB_TYPE_CHAR, '120', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courselanguage', XMLDB_TYPE_CHAR, '120', null, XMLDB_NOTNULL, null, null);
        $table->add_field('processingtime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('starttime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('teasertext', XMLDB_TYPE_TEXT, '120', null, XMLDB_NOTNULL, null, null);
        $table->add_field('targetgroup', XMLDB_TYPE_TEXT, '120', null, XMLDB_NOTNULL, null, null);
        $table->add_field('learninggoals', XMLDB_TYPE_TEXT, '120', null, XMLDB_NOTNULL, null, null);
        $table->add_field('structure', XMLDB_TYPE_TEXT, '120', null, XMLDB_NOTNULL, null, null);
        $table->add_field('detailslecturer', XMLDB_TYPE_TEXT, '120', null, XMLDB_NOTNULL, null, null);
        $table->add_field('detailsmorelecturer', XMLDB_TYPE_TEXT, '120', null, XMLDB_NOTNULL, null, null);
        $table->add_field('license', XMLDB_TYPE_CHAR, '120', null, XMLDB_NOTNULL, null, null);
        $table->add_field('tags', XMLDB_TYPE_CHAR, '120', null, XMLDB_NOTNULL, null, null);
        $table->add_field('certificateofachievement', XMLDB_TYPE_TEXT, '120', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    //}
    return true;
}








