<?php

function xmldb_local_isymeta_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    return true;
}