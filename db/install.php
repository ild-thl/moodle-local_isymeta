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
 * Post installation procedure for setting up defaults for ildmeta_vocabulary and ildmeta_spdx_licenses.
 *
 * @package     local_ildmeta
 * @copyright   2022 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function xmldb_local_ildmeta_install() {
    global $DB;
    $result = true;

    if (empty($DB->get_records('ildmeta_vocabulary'))) {
        $coursetypes = [
            'title' => 'coursetypes',
            'terms' => json_encode([
                ["de" => "Sprachkurs", "en" => "Language Course"],
                ["de" => "Fachkurs", "en" => "Specialised Course"],
                ["de" => "Propädeutik", "en" => "Propaedeutics"],
                ["de" => "Soft Skills", "en" => "Soft Skills"],
                ["de" => "Professional Skills", "en" => "Professional Skills"],
                ["de" => "Digital Skills", "en" => "Digital Skills"],
                ["de" => "Academic Skills", "en" => "Academic Skills"],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ];
        $DB->insert_record('ildmeta_vocabulary', $coursetypes);

        $courseformats = [
            'title' => 'courseformats',
            'terms' => json_encode([
                ["de" => "Präsenz", "en" => "Face To Face"],
                ["de" => "Online (Selbstlernkurs)", "en" => "Online Asynchronous"],
                ["de" => "Online mit festen Online-Gruppenterminen", "en" => "Online Synchronous"],
                ["de" => "Blended Learning mit festen Präsenz-Gruppenterminen", "en" => "Blended Learning"],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ];
        $DB->insert_record('ildmeta_vocabulary', $courseformats);

        $audience = [
            'title' => 'audience',
            'terms' => json_encode([
                ["de" => "Schüler*innen", "en" => "Pupils"],
                ["de" => "Studieninteressierte", "en" => "Prospective Students"],
                ["de" => "Studierende", "en" => "Students"],
                ["de" => "Promotionsinteresse", "en" => "Prospective Doctoral Candidates"],
                ["de" => "PASCH-Schüler*innen", "en" => "PASCH-Pupils"],
                ["de" => "Lehrende", "en" => "Teachers"],
                ["de" => "Eltern", "en" => "Parents"],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ];
        $DB->insert_record('ildmeta_vocabulary', $audience);

        $subjectarea = [
            'title' => 'subjectarea',
            'terms' => json_encode([
                ["de" => "Einstiegskurse", "en" => "Preparation Courses"],
                ["de" => "Geistes- und Kulturwissenschaften", "en" => "Humanities and Cultural Studies"],
                ["de" => "Gesundheitswissenschaften", "en" => "Health Care / Health Management"],
                ["de" => "Informatik", "en" => "Computer Science"],
                ["de" => "Ingenieurwissenschaften", "en" => "Engineering"],
                ["de" => "Lehramt", "en" => "Teacher Education"],
                ["de" => "Softskills", "en" => "Softskills"],
                ["de" => "Medizin", "en" => "Medicine / Medical Science"],
                ["de" => "Naturwissenschaften", "en" => "Natural Sciences"],
                ["de" => "Rechtswissenschaft", "en" => "Law"],
                ["de" => "Schlüsselqualifikationen", "en" => "Key Skills"],
                ["de" => "Soziale Arbeit", "en" => "Social Work"],
                ["de" => "Sozialwissenschaften", "en" => "Social Sciences"],
                ["de" => "Sprachen", "en" => "Languages"],
                ["de" => "Wirtschaftsinformatik", "en" => "Information Systems"],
                ["de" => "Wirtschaftswissenschaften", "en" => "Economic Sciences"],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ];
        $DB->insert_record('ildmeta_vocabulary', $subjectarea);

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

    if (empty($DB->get_records('ildmeta_spdx_licenses'))) {
        // Defaults.
        $default = array();
        // License not specified.
        $default["moodle_license"] = 1;
        $default["spdx_shortname"] = 'unknown';
        $default["spdx_fullname"] = 'Licence not specified';
        $default["spdx_url"] = "";
        $DB->insert_record('ildmeta_spdx_licenses', $default);
        // All rights reserved.
        $default["moodle_license"] = 2;
        $default["spdx_shortname"] = 'Proprietary';
        $default["spdx_fullname"] = 'All rights reserved';
        $default["spdx_url"] = "";
        $DB->insert_record('ildmeta_spdx_licenses', $default);
        // Public domain.
        $default["moodle_license"] = 3;
        $default["spdx_shortname"] = 'GPL-3.0-or-later';
        $default["spdx_fullname"] = 'GNU General Public License v3.0 or later';
        $default["spdx_url"] = "https://spdx.org/licenses/GPL-3.0-or-later.html";
        $DB->insert_record('ildmeta_spdx_licenses', $default);
        // All rights reserved.
        $default["moodle_license"] = 4;
        $default["spdx_shortname"] = 'CC-BY-3.0-DE';
        $default["spdx_fullname"] = 'Creative Commons Attribution 3.0 Germany';
        $default["spdx_url"] = "https://spdx.org/licenses/CC-BY-3.0-DE.html";
        $DB->insert_record('ildmeta_spdx_licenses', $default);
        // All rights reserved.
        $default["moodle_license"] = 5;
        $default["spdx_shortname"] = 'CC-BY-ND-3.0-DE';
        $default["spdx_fullname"] = 'Creative Commons Attribution No Derivatives 3.0 Germany';
        $default["spdx_url"] = "https://spdx.org/licenses/CC-BY-ND-3.0-DE.html";
        $DB->insert_record('ildmeta_spdx_licenses', $default);
        // All rights reserved.
        $default["moodle_license"] = 6;
        $default["spdx_shortname"] = 'CC-BY-NC-ND-3.0-DE';
        $default["spdx_fullname"] = 'Creative Commons Attribution Non Commercial No Derivatives 3.0 Germany';
        $default["spdx_url"] = "https://spdx.org/licenses/CC-BY-NC-ND-3.0-DE.html";
        $DB->insert_record('ildmeta_spdx_licenses', $default);
        // All rights reserved.
        $default["moodle_license"] = 7;
        $default["spdx_shortname"] = 'CC-BY-NC-3.0-DE';
        $default["spdx_fullname"] = 'Creative Commons Attribution Non Commercial 3.0 Germany';
        $default["spdx_url"] = "https://spdx.org/licenses/CC-BY-NC-3.0-DE.html";
        $DB->insert_record('ildmeta_spdx_licenses', $default);
        // All rights reserved.
        $default["moodle_license"] = 8;
        $default["spdx_shortname"] = 'CC-BY-NC-SA-3.0-DE';
        $default["spdx_fullname"] = 'Creative Commons Attribution Non Commercial Share Alike 3.0 Germany';
        $default["spdx_url"] = "https://spdx.org/licenses/CC-BY-NC-SA-3.0-DE.html";
        $DB->insert_record('ildmeta_spdx_licenses', $default);
        // All rights reserved.
        $default["moodle_license"] = 9;
        $default["spdx_shortname"] = 'CC-BY-SA-3.0-DE';
        $default["spdx_fullname"] = 'Creative Commons Attribution Share Alike 3.0 Germany';
        $default["spdx_url"] = "https://spdx.org/licenses/CC-BY-SA-3.0-DE.html";
        $DB->insert_record('ildmeta_spdx_licenses', $default);
    }

    return $result;
}
