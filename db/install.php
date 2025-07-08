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

use local_ildmeta\manager;

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
        manager::set_default_vocabulary();
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
        $default["spdx_shortname"] = 'CC-BY-4.0';
        $default["spdx_fullname"] = 'Creative Commons - 4.0 International';
        $default["spdx_url"] = "https://spdx.org/licenses/CC-BY-4.0.html";
        $DB->insert_record('ildmeta_spdx_licenses', $default);
        // All rights reserved.
        $default["moodle_license"] = 4;
        $default["spdx_shortname"] = 'CC-BY-4.0';
        $default["spdx_fullname"] = 'Creative Commons - 4.0 International';
        $default["spdx_url"] = "https://spdx.org/licenses/CC-BY-4.0.html";
        $DB->insert_record('ildmeta_spdx_licenses', $default);
        // All rights reserved.
        $default["moodle_license"] = 5;
        $default["spdx_shortname"] = 'CC-BY-NC-4.0';
        $default["spdx_fullname"] = 'Creative Commons - NonCommercial 4.0 International';
        $default["spdx_url"] = "https://spdx.org/licenses/CC-BY-NC-4.0.html";
        $DB->insert_record('ildmeta_spdx_licenses', $default);
        // All rights reserved.
        $default["moodle_license"] = 6;
        $default["spdx_shortname"] = 'CC-BY-ND-4.0';
        $default["spdx_fullname"] = 'Creative Commons - NoDerivatives 4.0 International';
        $default["spdx_url"] = "https://spdx.org/licenses/CC-BY-ND-4.0.html";
        $DB->insert_record('ildmeta_spdx_licenses', $default);
        // All rights reserved.
        $default["moodle_license"] = 7;
        $default["spdx_shortname"] = 'CC-BY-NC-ND-4.0';
        $default["spdx_fullname"] = 'Creative Commons - NonCommercial-NoDerivatives 4.0 International';
        $default["spdx_url"] = "https://spdx.org/licenses/CC-BY-NC-ND-4.0.html";
        $DB->insert_record('ildmeta_spdx_licenses', $default);
        // All rights reserved.
        $default["moodle_license"] = 8;
        $default["spdx_shortname"] = 'CC-BY-NC-SA-4.0';
        $default["spdx_fullname"] = 'Creative Commons - NonCommercial-ShareAlike 4.0 International';
        $default["spdx_url"] = "https://spdx.org/licenses/CC-BY-NC-SA-4.0.html";
        $DB->insert_record('ildmeta_spdx_licenses', $default);
        // All rights reserved.
        $default["moodle_license"] = 9;
        $default["spdx_shortname"] = 'CC-BY-SA-4.0';
        $default["spdx_fullname"] = 'Creative Commons - ShareAlike 4.0 International';
        $default["spdx_url"] = "https://spdx.org/licenses/CC-BY-SA-4.0.html";
        $DB->insert_record('ildmeta_spdx_licenses', $default);
    }

    return $result;
}
