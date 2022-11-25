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
 * Script that generates moochub course data in json format and saves it to a file.
 *
 * @package     local_ildmeta
 * @author      Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @copyright   2022 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use local_ildmeta\manager;

$download = optional_param('download', false, PARAM_BOOL);

$metas = [];
$metaentry = [];
$metarecords = $DB->get_records('ildmeta');

$jsonlink = $CFG->httpswwwroot . '/local/ildmeta/get_moochub_courses.php';

$metaslinks = ['self' => $jsonlink, 'first' => $jsonlink, 'last' => $jsonlink];

$metas['links'] = $metaslinks;

// Get vocabularies from ildmeta_vocabulary for dropdown selection fields.
$records = $DB->get_records('ildmeta_vocabulary');
$vocabularies = new \stdClass();
foreach ($records as $vocabulary) {
    $vocabularies->{$vocabulary->title} = manager::filter_vocabulary_lang($vocabulary, 'de');
}

// Get list of providers.
$providers = manager::get_providers('de');

foreach ($metarecords as $meta) {

    if ($meta->noindexcourse != 0 || !$DB->record_exists('course', array('id' => $meta->courseid))) {
        continue;
    }

    $fs = get_file_storage();
    $context = context_course::instance($meta->courseid);
    $fileurl = '';
    $imagefile = null;

    // Get url of overview image.
    // If no custom image ist set in ildmeta, then use the course image instead.
    if (isset($meta->overviewimage)) {
        $files = $fs->get_area_files($context->id, 'local_ildmeta', 'overviewimage', 0);
    } else {
        $files = $fs->get_area_files($context->id, 'course', 'overviewfiles', 0);
    }
    foreach ($files as $file) {
        if ($file->is_valid_image()) {
            $imagefile = $file;
            $fileurl = moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                isset($meta->overviewimage) ? $file->get_itemid() : null,
                $file->get_filepath(),
                $file->get_filename(),
                false
            );
            break;
        }
    }

    $metaentry = [];
    $metaentry['type'] = 'courses';
    $metaentry['id'] = 'futurelearnlab' . $meta->courseid;
    $metaentry['attributes'] = [];
    $metaentry['attributes']['name'] = $meta->coursetitle;
    $metaentry['attributes']['courseCode'] = 'futurelearnlab' . $meta->courseid;
    $metaentry['attributes']['courseMode'] = 'MOOC';
    $metaentry['attributes']['url'] = $CFG->wwwroot . '/blocks/ildmetaselect/detailpage.php?id=' . $meta->courseid;
    // $meta_entry['attributes']['publisher']     = $meta->lecturer;

    $metaentry['attributes']['abstract'] = null;
    if ($meta->teasertext == '') {
        $metaentry['attributes']['description'] = null;
    } else {
        $metaentry['attributes']['description'] = $meta->teasertext;
    }

    $metaentry['attributes']['languages'] = ['de-DE'];
    date_default_timezone_set("UTC");
    $metaentry['attributes']['startDate'] = date('c', $meta->starttime);
    $metaentry['attributes']['endDate'] = null;

    if (trim((string)$fileurl) == '') {
        $metaentry['attributes']['image'] = null;
    } else {
        $metaentry['attributes']['image'] = array();
        $metaentry['attributes']['image']['url'] = trim((string)$fileurl);

        // Get License of image and convert to an SPDX license.
        if (isset($imagefile)) {
            $license = $DB->get_record('license', array('shortname' => $imagefile->get_license()), '*', MUST_EXIST);
            if (isset($license) && !empty($license) && $license->shortname != 'unknown') {
                $spdxlicense = $DB->get_record('ildmeta_spdx_licenses', array('moodle_license' => $license->id), '*', MUST_EXIST);
                $spdxurl = !empty($spdxlicense->spdx_url) ? $spdxlicense->spdx_url : null;
                $metaentry['attributes']['image']['licenses'] = array();
                $metaentry['attributes']['image']['licenses'][0]['id'] = $spdxlicense->spdx_shortname;
                $metaentry['attributes']['image']['licenses'][0]['url'] = $spdxurl;
                $metaentry['attributes']['image']['licenses'][0]['name'] = $spdxlicense->spdx_fullname;
                $metaentry['attributes']['image']['licenses'][0]['author'] = $imagefile->get_author();
            }
        }
    }


    $duration = null;
    if (isset($meta->processingtime) && !empty($meta->processingtime)) {
        $duration = 'PT' . $meta->processingtime . 'H';
        $metaentry['attributes']['duration'] = $duration;
    } else {
        $metaentry['attributes']['duration'] = null;
    }

    if (isset($meta->availableuntil) && !empty($meta->availableuntil)) {
        $metaentry['attributes']['availableUntil'] = date('c', $meta->availableuntil);
    } else {
        $metaentry['attributes']['availableUntil'] = null;
    }

    $lecturer = explode(', ', $meta->lecturer);

    $metaentry['attributes']['instructors'] = array();
    for ($i = 0; $i < count($lecturer); $i++) {

        if ($lecturer[$i] != '') {
            $metaentry['attributes']['instructors'][$i] = new \stdClass;
            $metaentry['attributes']['instructors'][$i]->name = $lecturer[$i];
        }
    }

        $metaentry['attributes']['video'] = null;
        if (isset($meta->videocode) && !empty(trim($meta->videolicense))) {
            $license = $DB->get_record('license', array('id' => $meta->videolicense), '*', IGNORE_MISSING);
            if (isset($license) && !empty($license) && $license->shortname != 'unknown') {
                // Only set video if video license is known.
            $metaentry['attributes']['video'] = array();
            $metaentry['attributes']['video']['url'] = trim($meta->videocode);

            $spdxlicense = $DB->get_record('ildmeta_spdx_licenses', array('moodle_license' => $license->id), '*', MUST_EXIST);
            $spdxurl = !empty($spdxlicense->spdx_url) ? $spdxlicense->spdx_url : null;
            $metaentry['attributes']['video']['licenses'] = array();
            $metaentry['attributes']['video']['licenses'][0]['id'] = $spdxlicense->spdx_shortname;
            $metaentry['attributes']['video']['licenses'][0]['url'] = $spdxurl;
            $metaentry['attributes']['video']['licenses'][0]['name'] = $spdxlicense->spdx_fullname;
        }
    }

    $license = $DB->get_record('license', array('id' => $meta->license), '*', IGNORE_MISSING);
    if (isset($license) && !empty($license) && $license->shortname != 'unknown') {
        $spdxlicense = $DB->get_record('ildmeta_spdx_licenses', array('moodle_license' => $license->id), '*', MUST_EXIST);
        $spdxurl = !empty($spdxlicense->spdx_url) ? $spdxlicense->spdx_url : null;
        $metaentry['attributes']['courseLicenses'] = array();
        $metaentry['attributes']['courseLicenses'][0]['id'] = $spdxlicense->spdx_shortname;
        $metaentry['attributes']['courseLicenses'][0]['url'] = $spdxurl;
        $metaentry['attributes']['courseLicenses'][0]['name'] = $spdxlicense->spdx_fullname;
    } else {
        $metaentry['attributes']['courseLicenses'] = [];
        $metaentry['attributes']['courseLicenses'][0]['id'] = 'Proprietary';
        $metaentry['attributes']['courseLicenses'][0]['url'] = null;
    }

    $provider = $providers[$meta->provider];

    $metaentry['attributes']['moocProvider']['name'] = $provider['name'];
    $urlwithprotocol = $provider['url'];
    if (strpos($provider['url'], 'http') === false) {
        $urlwithprotocol = 'https://' . $provider['url'];
    }
    $metaentry['attributes']['moocProvider']['url'] = $urlwithprotocol;
    $metaentry['attributes']['moocProvider']['logo'] = $provider['logo'];

    $metaentry['attributes']['access'] = ['free'];

    $metas['data'][] = $metaentry;
}

// Send Json response.
header('Content-Type: application/json');
$json = json_encode($metas, JSON_UNESCAPED_SLASHES);
// If download flag is set trigger download on client browser.
send_file($json, 'courses_moochub.json', 0, 0, true, $download);
