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
 * @package     local_ildmeta
 * @author      Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @copyright   2022 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use local_ildmeta\manager;

$download = optional_param('download', false, PARAM_BOOL);

$metas = [];
$metarecords = $DB->get_records('ildmeta');

// Get vocabularies from ildmeta_vocabulary for dropdown selection fields.
$records = $DB->get_records('ildmeta_vocabulary');
$vocabularies = new \stdClass();
foreach ($records as $vocabulary) {
    $vocabularies->{$vocabulary->title} = manager::filter_vocabulary_lang($vocabulary, 'de');
}

// Get list of providers.
$providers = manager::get_providers('de');

// Create a json entry for every course, that is supposed to be shared.
foreach ($metarecords as $meta) {
    // Skip courses that are not supposed to be exported to bird or a course record does not exist.
    if ($meta->exporttobird == false || !$DB->record_exists('course', array('id' => $meta->courseid))) {
        continue;
    }

    $metaentry = [];

    // Set course_serviceprovider.
    $provider = $providers[$meta->provider];
    // TODO: Check ahat kind of id is neede here.
    $metaentry['course_serviceprovider_id'] = $provider['id'];
    $metaentry['course_serviceprovider_name']['de'] = $provider['name'];
    $urlwithprotocol = $provider['url'];
    // Make sure the provider url includes a protocol, add https if missing.
    if (strpos($provider['url'], 'http') === false) {
        $urlwithprotocol = 'https://' . $provider['url'];
    }
    $metaentry['course_serviceprovider_url_service']['de'] = $urlwithprotocol;
    $metaentry['course_serviceprovider_url_image']['de'] = $provider['logo'];
    $metaentry['course_type']['de'] = [$vocabularies->coursetypes[$meta->coursetype]];
    $metaentry['course_targetgroup']['de'] = [$vocabularies->audience[$meta->audience]];
    // TODO: course_level.
    // TODO: course_level_remarks.
    // TODO: course_level_goals.
    // TODO: course_subjectgroup.
    // Type of laguage course.
    if (isset($meta->birdsubjectarea)) {
        $metaentry['course_subjectgroup']['de'] = [$vocabularies->birdsubjectarea[$meta->birdsubjectarea]];
    }
    // TODO: course_participation_accessibility.
    // TODO: course_lessontimeunit_weekonline.
    // TODO: course_lessontimeunit_weekpresence.
    // TODO: course_lessontimeunit_duration.
    if (isset($meta->availablefrom) && !empty($meta->availablefrom)) {
        $metaentry['course_availability_from'] = date('c', $meta->availablefrom);
    }
    // TODO: course_languagecourse_workload.
    if (isset($meta->availableuntil) && !empty($meta->availableuntil)) {
        $metaentry['course_availability_until'] = date('c', $meta->availableuntil);
    }
    // TODO: course_preparingfor_general.
    $metaentry['course_charge']['de'] = "kostenlos";
    // TODO: course_charge_fee.
    // TODO: course_charge_currency.
    // TODO: course_certificate_type.

    $metaentry['course_url_landingpage']['de'] = manager::get_external_course_link($meta->courseid);

    // TODO: course_publication_until.
    $metaentry['course_description_long']['de'] = "";
    if (isset($meta->teasertext) && !empty($meta->teasertext)) {
        $metaentry['course_description_long']['de'] = $meta->teasertext;
    }
    $metaentry['course_description_short']['de'] = "";
    if (isset($meta->abstract) && !empty($meta->abstract)) {
        $metaentry['course_description_short']['de'] = $meta->abstract;
    }
    // Set image metadata.
    // Get overview image from filestorage.
    // If no custom image ist set in ildmeta, then use the course image instead.
    $fs = get_file_storage();
    $context = context_course::instance($meta->courseid);
    $fileurl = '';
    $imagefile = null;
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
    if (!isset($fileurl) || empty((string)$fileurl)) {
        if (!isset($provider['logo']) || empty($provider['logo'])) {
            continue;
        } else {
            $metaentry['course_url_image']['de'] = $provider['logo'];
        }
    } else {
        $metaentry['course_url_image']['de'] = trim((string)$fileurl);
    }
    $metaentry['course_learningoutcome']['de'] = "";
    if (isset($meta->learninggoals) && !empty($meta->learninggoals)) {
        $metaentry['course_learningoutcome']['de'] = $meta->learninggoals;
    }
    // TODO: course_lessontimeunit_totalonline.
    $license = $DB->get_record('license', array('id' => $meta->license), '*', IGNORE_MISSING);
    if (isset($license) && !empty($license) && $license->shortname != 'unknown') {
        $spdxlicense = $DB->get_record('ildmeta_spdx_licenses', array('moodle_license' => $license->id), '*', MUST_EXIST);
        $spdxurl = !empty($spdxlicense->spdx_url) ? $spdxlicense->spdx_url : null;
        $metaentry['course_license']['de'] = $spdxurl;
    }
    // TODO: course_ects.
    // TODO: course_participation_minimumage.
    $metaentry['course_lecture_type']['de'] = ["MOOC"];
    if (isset($meta->courseformat) && !empty($meta->courseformat)) {
        $metaentry['course_lecture_type']['de'][] = $vocabularies->courseformats[$meta->courseformat];
    }
    // TODO: course_preparingfor_languagecourse.
    // TODO: course_previousknowledge_optional.
    $metaentry['course_title']['de'] = $meta->coursetitle;
    // TODO: course_registrationperiod.
    if (isset($meta->structure) && !empty($meta->structure)) {
        $metaentry['course_content']['de'] = $meta->structure;
    }
    // TODO: course_capacity.
    $metaentry['course_duration_timeunit'] = ["de" => "Stunden", "en" => "Hours"];
    if (isset($meta->processingtime) && !empty($meta->processingtime)) {
        $metaentry['course_duration'] = intval($meta->processingtime);
    } else {
        $metaentry['course_duration'] = 0;
    }
    // TODO: course_participation_availableseats.
    // TODO: course_publication_from.
    if (isset($meta->courseprerequisites) && !empty($meta->courseprerequisites)) {
        $metaentry['course_previousknowledge_mandatory']['de'] = $meta->courseprerequisites;
    }
    // TODO: course_previousknowlodge_proof.
    // TODO: course_location_city.
    // TODO: course_location_address.
    // TODO: course_location_geodata.

    $langlist = [
        'de',
        'en',
        'uk',
        'ru'
    ];
    $metaentry['course_language']['de'] = [strtoupper($langlist[$meta->courselanguage])];
    // TODO: course_workload Wahts the difference to duration.
    $metaentry['course_languagecourse_language']['de'] = [strtoupper($langlist[$meta->courselanguage])];
    // TODO: course_languagecourse_language.

    // Educational level.
    // Language level goal.
    if (isset($meta->languagelevels)) {
        $metaentry['course_languagecourse_levelgoals']['de'] = [$vocabularies->languagelevels[$meta->languagelevels]];
    }
    // Type of laguage course.
    if (isset($meta->languagesubject)) {
        $metaentry['course_languagecourse_subject']['de'] = [$vocabularies->languagesubject[$meta->languagesubject]];
    }
    // TODO: course_schedule.
    // TODO: course_participation_condition.
    $metaentry['course_coursemode']['de'] = [$meta->selfpaced ? 'Selbstlernkurs' : 'Betreuter Kurs'];

    $metas[] = $metaentry;
}

// Send Json response.
header('Content-Type: application/json');
$json = json_encode($metas, JSON_UNESCAPED_SLASHES);
// If download flag is set trigger download on client browser.
send_file($json, 'courses_bird.json', 0, 0, true, $download);
