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

use local_ildmeta\manager;

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
        $courseprerequisites = new xmldb_field('courseprerequisites', XMLDB_TYPE_TEXT, null, null, null, null, null, 'audience');

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
        $certificateofachievement = new xmldb_field('certificateofachievement', XMLDB_TYPE_TEXT, null, null, null, null, null, 'tags');

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
        if ($dbman->field_exists($ildmetatable, $certificateofachievement)) {
            $dbman->change_field_precision($ildmetatable,  $certificateofachievement);
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

    if ($oldversion < 2022070718) {
        $ildmetatable = new xmldb_table('ildmeta');

        // New Bird attributes.
        $availableuntil = new xmldb_field('availableuntil', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'courseprerequisites');
        $availablefrom = new xmldb_field('availablefrom', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'availableuntil');
        $abstract = new xmldb_field('abstract', XMLDB_TYPE_TEXT, null, null, null, null, null, 'availablefrom');
        $shortname = new xmldb_field('shortname', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'abstract');

        // Conditionally launch add field availableuntil.
        if (!$dbman->field_exists($ildmetatable, $availableuntil)) {
            $dbman->add_field($ildmetatable, $availableuntil);
        }
        // Conditionally launch add field availablefrom.
        if (!$dbman->field_exists($ildmetatable, $availablefrom)) {
            $dbman->add_field($ildmetatable, $availablefrom);
        }
        // Conditionally launch add field abstract.
        if (!$dbman->field_exists($ildmetatable, $abstract)) {
            $dbman->add_field($ildmetatable, $abstract);
        }
        // Conditionally launch add field shortname.
        if (!$dbman->field_exists($ildmetatable, $shortname)) {
            $dbman->add_field($ildmetatable, $shortname);
        }

        // Change filed constraint NOTNULL.
        $videocode = new xmldb_field('videocode', XMLDB_TYPE_CHAR, '120', null, null, null, null, 'noindexcourse');
        $videolicense = new xmldb_field('videolicense', XMLDB_TYPE_INTEGER, '10', null, null, null, 1, 'videocode');
        $overviewimage = new xmldb_field('overviewimage', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'videolicense');
        $detailimage = new xmldb_field('detailimage', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'overviewimage');
        $targetgroup = new xmldb_field('targetgroup', XMLDB_TYPE_TEXT, null, null, null, null, null, 'teasertext');
        $learninggoals = new xmldb_field('learninggoals', XMLDB_TYPE_TEXT, null, null, null, null, null, 'targetgroup');
        $structure = new xmldb_field('structure', XMLDB_TYPE_TEXT, null, null, null, null, null, 'learninggoals');
        $detailslecturer = new xmldb_field('detailslecturer', XMLDB_TYPE_TEXT, null, null, null, null, null, 'structure');
        $detailsmorelecturer = new xmldb_field('detailsmorelecturer', XMLDB_TYPE_TEXT, null, null, null, null, null, 'detailslecturer');
        $tags = new xmldb_field('tags', XMLDB_TYPE_CHAR, '120', null, null, null, null, 'detailsmorelecturer');
        $certificateofachievement = new xmldb_field('certificateofachievement', XMLDB_TYPE_TEXT, null, null, null, null, null, 'tags');

        // Conditionally update field constraints.
        if ($dbman->field_exists($ildmetatable, $videocode)) {
            $dbman->change_field_notnull($ildmetatable, $videocode);
        }
        if ($dbman->field_exists($ildmetatable, $videolicense)) {
            $dbman->change_field_notnull($ildmetatable, $videolicense);
        }
        if ($dbman->field_exists($ildmetatable, $overviewimage)) {
            $dbman->change_field_notnull($ildmetatable, $overviewimage);
        }
        if ($dbman->field_exists($ildmetatable, $detailimage)) {
            $dbman->change_field_notnull($ildmetatable, $detailimage);
        }
        if ($dbman->field_exists($ildmetatable, $targetgroup)) {
            $dbman->change_field_notnull($ildmetatable, $targetgroup);
        }
        if ($dbman->field_exists($ildmetatable, $learninggoals)) {
            $dbman->change_field_notnull($ildmetatable, $learninggoals);
        }
        if ($dbman->field_exists($ildmetatable, $structure)) {
            $dbman->change_field_notnull($ildmetatable, $structure);
        }
        if ($dbman->field_exists($ildmetatable, $detailslecturer)) {
            $dbman->change_field_notnull($ildmetatable, $detailslecturer);
        }
        if ($dbman->field_exists($ildmetatable, $detailsmorelecturer)) {
            $dbman->change_field_notnull($ildmetatable, $detailsmorelecturer);
        }
        if ($dbman->field_exists($ildmetatable, $tags)) {
            $dbman->change_field_notnull($ildmetatable, $tags);
        }
        if ($dbman->field_exists($ildmetatable, $certificateofachievement)) {
            $dbman->change_field_notnull($ildmetatable, $certificateofachievement);
        }

        // Define ildmeta_provider table to be added.
        $providertable = new xmldb_table('ildmeta_provider');
        $providertable->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $providertable->add_field('name_de', XMLDB_TYPE_CHAR, "128", null, XMLDB_NOTNULL, null, null);
        $providertable->add_field('name_en', XMLDB_TYPE_CHAR, "128", null, null, null, null);
        $providertable->add_field('url', XMLDB_TYPE_CHAR, "256", null, XMLDB_NOTNULL, null, null);

        // Adding keys to table ildmeta_vocabulary.
        $providertable->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally add ildmeta_vocabulary table.
        if (!$dbman->table_exists($providertable)) {
            $dbman->create_table($providertable);
        }

        // Ildmeta savepoint reached.
        upgrade_plugin_savepoint(true, 2022070718, 'local', 'ildmeta');
    }

    if ($oldversion < 2022110213) {
        $ildmetatable = new xmldb_table('ildmeta');

        // New Bird attributes.
        $birdsubjectarea = new xmldb_field('birdsubjectarea', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'exporttobird');

        // Conditionally launch add field availableuntil.
        if (!$dbman->field_exists($ildmetatable, $birdsubjectarea)) {
            $dbman->add_field($ildmetatable, $birdsubjectarea);

            if (!$DB->record_exists('ildmeta_vocabulary', array('title' => 'birdsubjectarea'))) {
                $birdsubjectarea = [
                    'title' => 'birdsubjectarea',
                    'terms' => json_encode([
                        ["de" => "Keine Angabe"],
                        ["de" => "Agrar- und Forstwissenschaften"],
                        ["de" => "Gesellschafts- und Sozialwissenschaften"],
                        ["de" => "Ingenieurwissenschaften"],
                        ["de" => "Kunst, Musik, Design"],
                        ["de" => "Lehramt"],
                        ["de" => "Mathematik, Naturwissenschaften"],
                        ["de" => "Medizin, Gesundheitswissenschaften"],
                        ["de" => "Sprach-, Kulturwissenschaften"],
                        ["de" => "Wirtschaftswissenschaften, Rechtswissenschaften"],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ];
                $DB->insert_record('ildmeta_vocabulary', $birdsubjectarea);
            }
        }

        // Ildmeta savepoint reached.
        upgrade_plugin_savepoint(true, 2022110213, 'local', 'ildmeta');
    }

    if ($oldversion < 2022111916) {
        $ildmetatable = new xmldb_table('ildmeta');

        // New Bird attributes.
        $languagelevels = new xmldb_field('languagelevels', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'courseformat');
        $languagesubject = new xmldb_field('languagesubject', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'languagelevels');

        // Conditionally launch add field availableuntil.
        if (!$dbman->field_exists($ildmetatable, $languagelevels)) {
            $dbman->add_field($ildmetatable, $languagelevels);

            if (!$DB->record_exists('ildmeta_vocabulary', array('title' => 'languagelevels'))) {
                $languagelevels = [
                    'title' => 'languagelevels',
                    'terms' => json_encode([
                        ["de" => "A1"],
                        ["de" => "A2"],
                        ["de" => "B1"],
                        ["de" => "B1.1"],
                        ["de" => "B1.2"],
                        ["de" => "B2"],
                        ["de" => "B2.1"],
                        ["de" => "B2.2"],
                        ["de" => "C1"],
                        ["de" => "C2"],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ];
                $DB->insert_record('ildmeta_vocabulary', $languagelevels);
            }
        }

        if (!$dbman->field_exists($ildmetatable, $languagesubject)) {
            $dbman->add_field($ildmetatable, $languagesubject);

            if (!$DB->record_exists('ildmeta_vocabulary', array('title' => 'languagesubject'))) {
                $languagesubject = [
                    'title' => 'languagesubject',
                    'terms' => json_encode([
                        ["de" => "allgemeinsprachlich"],
                        ["de" => "Prüfungsvorbereitung"],
                        ["de" => "Fachsprache"],
                        ["de" => "spezielle sprachliche Fertigkeiten"],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ];
                $DB->insert_record('ildmeta_vocabulary', $languagesubject);
            }
        }

        // Ildmeta savepoint reached.
        upgrade_plugin_savepoint(true, 2022111916, 'local', 'ildmeta');
    }

    if ($oldversion < 2022121216) {
        $ildmetatable = new xmldb_table('ildmeta');

        // Update coursetitle precision to be compatible with course fullname.
        $coursetitle = new xmldb_field('coursetitle', XMLDB_TYPE_CHAR, '254', null, XMLDB_NOTNULL, null, null, 'starttime');
        if ($dbman->field_exists($ildmetatable, $coursetitle)) {
            $dbman->change_field_precision($ildmetatable,  $coursetitle);
        }

        // Ildmeta savepoint reached.
        upgrade_plugin_savepoint(true, 2022121216, 'local', 'ildmeta');
    }

    if ($oldversion < 2023102713) {
        $ildmetatable = new xmldb_table('ildmeta');

        // Update birdsubjectarea default value to 0.
        $birdsubjectarea = new xmldb_field('birdsubjectarea', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'exporttobird');
        if ($dbman->field_exists($ildmetatable, $birdsubjectarea)) {
            $dbman->change_field_precision($ildmetatable,  $birdsubjectarea);
        }

        // Ildmeta savepoint reached.
        upgrade_plugin_savepoint(true, 2023102713, 'local', 'ildmeta');
    }

    if ($oldversion < 2023111619) {
        $ildmetatable = new xmldb_table('ildmeta');

        $field = new xmldb_field('uuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, 'id');

        // Conditionally launch add field uuid.
        if (!$dbman->field_exists($ildmetatable, $field)) {
            $dbman->add_field($ildmetatable, $field);
        }

        // Generate uuid for existing records, where uuid is null or empty.
        $records = $DB->get_records_sql('SELECT * FROM {ildmeta} WHERE uuid IS NULL OR uuid = ""');
        foreach ($records as $record) {
            do {
                $uuid = manager::guidv4();
            } while ($DB->record_exists('ildmeta', ['uuid' => $uuid]));

            $record->uuid = $uuid;
            $DB->update_record('ildmeta', $record);
        }

        // Define key uuid (unique) to be added to ildmeta.
        $key = new xmldb_key('uuid', XMLDB_KEY_UNIQUE, ['uuid']);

        // Launch add key uuid.
        $dbman->add_key($ildmetatable, $key);

        // Ildmeta savepoint reached.
        upgrade_plugin_savepoint(true, 2023111619, 'local', 'ildmeta');
    }

    if ($oldversion < 2024102509) {
        $ildmetatable = new xmldb_table('ildmeta');

        // new field <FIELD NAME="edulevel" TYPE="int" LENGTH="10" NOTNULL="false" SEQUENCE="false"/>
        $field = new xmldb_field('edulevel', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'shortname');

        // Conditionally launch add field edulevel.
        if (!$dbman->field_exists($ildmetatable, $field)) {
            $dbman->add_field($ildmetatable, $field);
        }

        // Add vocabulary for DigComp 2.2 educational levels.
        if (!$DB->record_exists('ildmeta_vocabulary', array('title' => 'edulevel_digcomp22'))) {
            $edulevel_digcomp22 = [
                'title' => 'edulevel_digcomp22',
                'terms' => json_encode([
                    [
                        "de" => "Grundlagen (Level 1)",
                        "en" => "Foundation (Level 1)",
                    ],
                    [
                        "de" => "Grundlagen (Level 2)",
                        "en" => "Foundation (Level 2)",
                    ],
                    [
                        "de" => "Aufbau (Level 3)",
                        "en" => "Intermediate (Level 3)",
                    ],
                    [
                        "de" => "Aufbau (Level 4)",
                        "en" => "Intermediate (Level 4)",
                    ],
                    [
                        "de" => "Fortgeschritten (Level 5)",
                        "en" => "Advanced (Level 5)",
                    ],
                    [
                        "de" => "Fortgeschritten (Level 6)",
                        "en" => "Advanced (Level 6)",
                    ],
                    [
                        "de" => "Hochspezialisiert (Level 7)",
                        "en" => "Highly specialised (Level 7)",
                    ],
                    [
                        "de" => "Hochspezialisiert (Level 8)",
                        "en" => "Highly specialised (Level 8)",
                    ]
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            ];

            $DB->insert_record('ildmeta_vocabulary', $edulevel_digcomp22);
        }

        // Ildmeta savepoint reached.
        upgrade_plugin_savepoint(true, 2024102509, 'local', 'ildmeta');
    }

    if ($oldversion < 2024110610) {
        $ildmetatable = new xmldb_table('ildmeta');

        // Add new fields to ildmeta table.
        $targetgroupheading = new xmldb_field('targetgroupheading', XMLDB_TYPE_CHAR, '120', null, null, null, null, 'edulevel');
        if (!$dbman->field_exists($ildmetatable, $targetgroupheading)) {
            $dbman->add_field($ildmetatable, $targetgroupheading);
        }

        $learninggoalsheading = new xmldb_field('learninggoalsheading', XMLDB_TYPE_CHAR, '120', null, null, null, null, 'targetgroupheading');
        if (!$dbman->field_exists($ildmetatable, $learninggoalsheading)) {
            $dbman->add_field($ildmetatable, $learninggoalsheading);
        }

        $structureheading = new xmldb_field('structureheading', XMLDB_TYPE_CHAR, '120', null, null, null, null, 'learninggoalsheading');
        if (!$dbman->field_exists($ildmetatable, $structureheading)) {
            $dbman->add_field($ildmetatable, $structureheading);
        }

        // Ildmeta savepoint reached.
        upgrade_plugin_savepoint(true, 2024110610, 'local', 'ildmeta');
    }

    return true;
}
