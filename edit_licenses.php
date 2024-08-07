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
 * Page for edditing SPDX licenses mapping.
 *
 * @package     local_ildmeta
 * @author      Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @copyright   2022 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

$context = context_system::instance();

if (has_capability('moodle/site:config', $context)) {
    $PAGE->set_context($context);
    $PAGE->set_url('/local/ildmeta/edit_licenses.php');
    $PAGE->set_title(get_string('edit_licenses', 'local_ildmeta'));
    $PAGE->set_heading(get_string('edit_licenses', 'local_ildmeta'));

    // Inform moodle which menu entry currently is active!
    admin_externalpage_setup('localildmeta_edit_licenses');

    // Projekte.
    $url = new moodle_url('/local/ildmeta/edit_licenses.php');

    $mform = new local_ildmeta\output\form\edit_licenses_form();

    $licenses = $DB->get_records('license');
    $spdxlicenses = $DB->get_records('ildmeta_spdx_licenses');

    if ($mform->is_cancelled()) {
        redirect($url);
    } else if ($data = $mform->get_data()) {
        # moodle license
        foreach ($licenses as $license) {
            $gotid = FALSE;
            # search for the spdxlicense with with moodle_license as moodle_license_id
            foreach ($spdxlicenses as $spdxlicense) {
                if ($spdxlicense->moodle_license === $license->id) {
                    $gotid = TRUE;
                    $spdxlicense->moodle_license = $data->{"moodle_license_" . $license->id};
                    $spdxlicense->spdx_shortname = $data->{"shortname_" . $license->id};
                    $spdxlicense->spdx_fullname = $data->{"fullname_" . $license->id};
                    $spdxlicense->spdx_url = $data->{"url_" . $license->id};
                    $DB->update_record('ildmeta_spdx_licenses', $spdxlicense);
                    break;
                }
            }
            if(!$gotid) {
                $spdxlicense->moodle_license = $data->{"moodle_license_" . $license->id};
                $spdxlicense->spdx_shortname = $data->{"shortname_" . $license->id};
                $spdxlicense->spdx_fullname = $data->{"fullname_" . $license->id};
                $spdxlicense->spdx_url = $data->{"url_" . $license->id};
                $DB->insert_record('ildmeta_spdx_licenses', $spdxlicense);
            }
        }

        redirect($url);
    }

    echo $OUTPUT->header();

    $toform = array();
    // Defaults.
    // License not specified.
    $toform["moodle_license_1"] = 1;
    $toform["shortname_1"] = 'unknown';
    $toform["fullname_1"] = 'Licence not specified';
    $toform["url_1"] = "";
    // All rights reserved.
    $toform["moodle_license_2"] = 2;
    $toform["shortname_2"] = 'Proprietary';
    $toform["fullname_2"] = 'All rights reserved';
    $toform["url_2"] = "";
    // Public domain.
    $toform["moodle_license_3"] = 3;
    $toform["shortname_3"] = 'GPL-3.0-or-later';
    $toform["fullname_3"] = 'GNU General Public License v3.0 or later';
    $toform["url_3"] = "https://spdx.org/licenses/GPL-3.0-or-later.html";
    // All rights reserved.
    $toform["moodle_license_4"] = 4;
    $toform["shortname_4"] = 'CC-BY-3.0-DE';
    $toform["fullname_4"] = 'Creative Commons Attribution 3.0 Germany';
    $toform["url_4"] = "https://spdx.org/licenses/CC-BY-3.0-DE.html";
    // All rights reserved.
    $toform["moodle_license_5"] = 5;
    $toform["shortname_5"] = 'CC-BY-ND-3.0-DE';
    $toform["fullname_5"] = 'Creative Commons Attribution No Derivatives 3.0 Germany';
    $toform["url_5"] = "https://spdx.org/licenses/CC-BY-ND-3.0-DE.html";
    // All rights reserved.
    $toform["moodle_license_6"] = 6;
    $toform["shortname_6"] = 'CC-BY-NC-ND-3.0-DE';
    $toform["fullname_6"] = 'Creative Commons Attribution Non Commercial No Derivatives 3.0 Germany';
    $toform["url_6"] = "https://spdx.org/licenses/CC-BY-NC-ND-3.0-DE.html";
    // All rights reserved.
    $toform["moodle_license_7"] = 7;
    $toform["shortname_7"] = 'CC-BY-NC-3.0-DE';
    $toform["fullname_7"] = 'Creative Commons Attribution Non Commercial 3.0 Germany';
    $toform["url_7"] = "https://spdx.org/licenses/CC-BY-NC-3.0-DE.html";
    // All rights reserved.
    $toform["moodle_license_8"] = 8;
    $toform["shortname_8"] = 'CC-BY-NC-SA-3.0-DE';
    $toform["fullname_8"] = 'Creative Commons Attribution Non Commercial Share Alike 3.0 Germany';
    $toform["url_8"] = "https://spdx.org/licenses/CC-BY-NC-SA-3.0-DE.html";
    // All rights reserved.
    $toform["moodle_license_9"] = 9;
    $toform["shortname_9"] = 'CC-BY-SA-3.0-DE';
    $toform["fullname_9"] = 'Creative Commons Attribution Share Alike 3.0 Germany';
    $toform["url_9"] = "https://spdx.org/licenses/CC-BY-SA-3.0-DE.html";

    foreach ($spdxlicenses as $spdx) {
        $toform["moodle_license_" . $spdx->moodle_license] = $spdx->moodle_license;
        $toform["shortname_" . $spdx->moodle_license] = $spdx->spdx_shortname;
        $toform["fullname_" . $spdx->moodle_license] = $spdx->spdx_fullname;
        $toform["url_" . $spdx->moodle_license] = $spdx->spdx_url;
    }

    $mform->set_data($toform);
    $mform->display();

    echo $OUTPUT->footer();
} else {
    redirect($CFG->wwwroot);
}
