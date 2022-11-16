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
 * Script that generates course data in json format meant for an export to bird and saves it to a file.
 *
 * @package local_ildmeta
 * @authorPascal Hürten <pascal.huerten@th-luebeck.de>
 * @copyright 2022 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use local_ildmeta\manager;

$download = optional_param('download', false, PARAM_BOOL);

$metas = [];
$metaentry = [];
$metarecords = $DB->get_records('ildmeta');

$jsonlink = $CFG->httpswwwroot . '/local/ildmeta/get_bird_courses.php';

$metaslinks = ['self' => $jsonlink, 'first' => $jsonlink, 'last' => $jsonlink];

$metas['links'] = $metaslinks;

// Get vocabularies from ildmeta_vocabulary for dropdown selection fields.
$records = $DB->get_records('ildmeta_vocabulary');
$vocabularies = new \stdClass();
foreach ($records as $vocabulary) {
    $vocabularies->{$vocabulary->title} = manager::filter_vocabulary_lang($vocabulary, current_language());
}

// Get list of providers.
$providers = manager::get_providers('de');

foreach ($metarecords as $meta) {
    // Skip courses that are not supposed to be exported to bird or a course record does not exist.
    if ($meta->exporttobird == false || !$DB->record_exists('course', array('id' => $meta->courseid))) {
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

    $duration = null;
    if (!$meta->processingtime == null) {
        $duration .= 'P' . $meta->processingtime . 'H';
    }

    $metaentry = [];
    $metaentry['type'] = 'courses';
    // ID has to be an integer, for compatibility with the BirdCourse-Format
    $metaentry['id'] = $meta->courseid;
    $metaentry['attributes'] = [];
    $metaentry['attributes']['name'] = $meta->coursetitle;

    // Shortname is no moochub attribute. Added for compatibility to BirdCourse.
    if (isset($meta->shortname) && !empty($meta->shortname)) {
        $metaentry['attributes']['shortname'] = $meta->shortname;
    } else {
        $metaentry['attributes']['shortname'] = null;
    }

    $metaentry['attributes']['courseCode'] = 'futurelearnlab' . $meta->courseid;
    $metaentry['attributes']['courseMode'] = ['MOOC'];

    $metaentry['attributes']['url'] = $CFG->wwwroot . '/blocks/ildmetaselect/detailpage.php?id=' . $meta->courseid;
    if (isset($meta->teasertext) && !empty($meta->teasertext)) {
        $metaentry['attributes']['description'] = $meta->teasertext;
    } else {
        $metaentry['attributes']['description'] = null;
    }
    if (isset($meta->abstract) && !empty($meta->abstract)) {
        $metaentry['attributes']['abstract'] = $meta->abstract;
    } else {
        $metaentry['attributes']['abstract'] = null;
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
                $metaentry['attributes']['image']['licenses'] = array();
                $metaentry['attributes']['image']['licenses'][0]['id'] = $spdxlicense->spdx_shortname;
                $metaentry['attributes']['image']['licenses'][0]['url'] = $spdxlicense->spdx_url;
                $metaentry['attributes']['image']['licenses'][0]['name'] = $spdxlicense->spdx_fullname;
                $metaentry['attributes']['image']['licenses'][0]['author'] = $imagefile->get_author();
            }
        }
    }

    $metaentry['attributes']['availableUntil'] = null;
    if (isset($meta->availableuntil) && !empty($meta->availableuntil)) {
        $metaentry['attributes']['availableUntil'] = $meta->availableuntil;
    } else {
        $metaentry['attributes']['availableUntil'] = null;
    }

    // AvailableFrom is no moochub attribute. Added for compatibility to BirdCourse.
    if (isset($meta->shortname) && !empty($meta->shortname)) {
        $metaentry['attributes']['availableFrom'] = $meta->availablefrom;
    } else {
        $metaentry['attributes']['availableFrom'] = null;
    }

    $lecturer = explode(', ', $meta->lecturer);

    $metaentry['attributes']['instructors'] = array();
    for ($i = 0; $i < count($lecturer); $i++) {

        if ($lecturer[$i] != '') {
            $metaentry['attributes']['instructors'][$i] = new \stdClass;
            $metaentry['attributes']['instructors'][$i]->name = $lecturer[$i];
        }
    }

    if (trim($meta->videocode) == '') {
        $metaentry['attributes']['video'] = null;
    } else {
        $metaentry['attributes']['video'] = array();
        $metaentry['attributes']['video']['url'] = trim($meta->videocode);

        if (!empty($meta->videolicense)) {
            $license = $DB->get_record('license', array('id' => $meta->videolicense), '*', IGNORE_MISSING);
            if (isset($license) && !empty($license) && $license->shortname != 'unknown') {
                $spdxlicense = $DB->get_record('ildmeta_spdx_licenses', array('moodle_license' => $license->id), '*', MUST_EXIST);
                $metaentry['attributes']['video']['licenses'] = array();
                $metaentry['attributes']['video']['licenses'][0]['id'] = $spdxlicense->spdx_shortname;
                $metaentry['attributes']['video']['licenses'][0]['url'] = $spdxlicense->spdx_url;
                $metaentry['attributes']['video']['licenses'][0]['name'] = $spdxlicense->spdx_fullname;
            }
        }
    }

    $metaentry['attributes']['courseLicenses'] = [];
    $metaentry['attributes']['courseLicenses'][0]['id'] = 'Proprietary';
    $metaentry['attributes']['courseLicenses'][0]['url'] = null;

    $provider = $providers[$meta->provider];

    $metaentry['attributes']['moocProvider']['name'] = $provider['name'];
    $metaentry['attributes']['moocProvider']['url'] = $provider['url'];
    $metaentry['attributes']['moocProvider']['logo'] = $provider['logo'];

    $metaentry['attributes']['access'] = ['free'];

    // Bird attributes.

    // Selbstlerkurs.
    $metaentry['attributes']['courseMode'][] = $meta->selfpaced ? 'Asynchronous' : 'Synchronous';

    // Kursformat.
    $metaentry['attributes']['courseMode'][] = $vocabularies->courseformats[$meta->courseformat];

    // Audience.
    $metaentry['attributes']['audience'] = [$vocabularies->audience[$meta->audience]];

    // Kurstyp.
    $metaentry['attributes']['educationalAlignment'][0] = [
        'educationalFramework' => 'Bird Kurstyp',
        'targetName' => $vocabularies->coursetypes[$meta->coursetype],
    ];
    $metaentry['attributes']['educationalAlignment'][1] = [
        'educationalFramework' => 'HRK Fachrichtung',
        'targetName' => $vocabularies->birdsubjectarea[$meta->birdsubjectarea],
    ];

    // Erforderliche Vorraussetzungen.
    $metaentry['attributes']['coursePrerequisites'] = $meta->courseprerequisites;

    $metas['data'][] = $metaentry;
}

// Send Json response.
header('Content-Type: application/json');
$json = json_encode($metas, JSON_UNESCAPED_SLASHES);
// If download flag is set trigger download on client browser.
send_file($json, 'courses_bird.json', 0, 0, true, $download);
