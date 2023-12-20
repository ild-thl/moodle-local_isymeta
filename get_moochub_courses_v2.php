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

use local_ildmeta\manager;
use Opis\JsonSchema\{
    Validator,
    ValidationResult,
    Helper,
    Errors\ErrorFormatter,
};

require_once(__DIR__ . '/../../config.php');

global $CFG;
require_once($CFG->libdir . '/filelib.php');
require('./vendor/autoload.php');

$download = optional_param('download', false, PARAM_BOOL);

$metas = [];
$metarecords = $DB->get_records('ildmeta');

// Create Links for pagination as proposed by the JSON:API schema.
$jsonlink = $CFG->httpswwwroot . '/local/ildmeta/get_moochub_courses.php';
$metaslinks = ['self' => $jsonlink, 'first' => $jsonlink, 'last' => $jsonlink];
$metas['links'] = $metaslinks;

if (!isset($_GET['idn']) && !isset($_GET['id'])) {
    $metarecords = $DB->get_records('ildmeta');
}
if (isset($_GET['idn'])) {
    $idnumbers = $_GET['idn'];
    $idnumbers = pproc_input($idnumbers);
    if (!empty($idnumbers)) {
        // Get all meta records for the given idnumers.
        [$insql, $inparams] = $DB->get_in_or_equal($idnumbers);
        $sql = "SELECT * FROM {ildmeta} meta
                LEFT JOIN {course} course ON meta.courseid = course.id
                WHERE course.idnumber $insql";
        $metarecords = $DB->get_records_sql($sql, $inparams);
    }
}
if (isset($_GET['id'])) {
    $idnumbers = $_GET['id'];
    $idnumbers = pproc_input($idnumbers);
    if (!empty($idnumbers)) {
        // Get all meta records for the given course ids.
        [$insql, $inparams] = $DB->get_in_or_equal($idnumbers);
        $sql = "SELECT * FROM {ildmeta} meta
                LEFT JOIN {course} course ON meta.courseid = course.id
                WHERE course.id $insql";
        $metarecords = $DB->get_records_sql($sql, $inparams);
    }
}

// Return empty data, if there was no course.
if (!isset($metarecords) or empty($metarecords)) {
    $metas['data'] = [];
} else {
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
        // Skip courses that are not supposed to be exported or a course record does not exist.
        if ($meta->noindexcourse != 0 || !$DB->record_exists('course', array('id' => $meta->courseid))) {
            continue;
        }

        $metaentry = [];
        $metaentry['type'] = 'courses';
        // Create an ID by adding the moodle course id to the host name of the current moodle site.
        $metaentry['id'] = parse_url($CFG->wwwroot, PHP_URL_HOST) . $meta->courseid;
        $metaentry['attributes'] = [];
        $metaentry['attributes']['name'] = $meta->coursetitle;
        $metaentry['attributes']['courseCode'] = null;
        $metaentry['attributes']['courseMode'] = 'MOOC';

        $metaentry['attributes']['url'] = manager::get_external_course_link($meta->courseid);

        if (isset($meta->teasertext) && !empty($meta->teasertext)) {
            $metaentry['attributes']['description'] = $meta->teasertext;
        } else {
            $metaentry['attributes']['description'] = null;
        }
        $metaentry['attributes']['abstract'] = null;

        // Set teaching language.
        // TODO: Enable selection of multiple teaching languages.
        // TODO: Add more languages and manage them like the other vocabularies.
        $langlist = [
            'de',
            'en',
            'uk',
            'ru'
        ];
        $metaentry['attributes']['languages'] = [$langlist[$meta->courselanguage]];

        $metaentry['attributes']['startDate'] = date('c', $meta->starttime);
        $metaentry['attributes']['endDate'] = null;

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

        // Set image metadata.
        if (!isset($fileurl) || empty((string)$fileurl) || !isset($imagefile)) {
            $metaentry['attributes']['image'] = null;
        } else {
            $metaentry['attributes']['image'] = array();
            $metaentry['attributes']['image']['url'] = trim((string)$fileurl);

            // Get License of image and convert to an SPDX license.
            $license = $DB->get_record('license', array('shortname' => $imagefile->get_license()), '*', MUST_EXIST);
            if (isset($license) && !empty($license) && $license->shortname != 'unknown') {
                $spdxlicense = $DB->get_record('ildmeta_spdx_licenses', array('moodle_license' => $license->id), '*', MUST_EXIST);
                $spdxurl = !empty($spdxlicense->spdx_url) ? $spdxlicense->spdx_url : null;
                $metaentry['attributes']['image']['licenses'] = array();
                $metaentry['attributes']['image']['licenses'][0]['id'] = $spdxlicense->spdx_shortname;
                $metaentry['attributes']['image']['licenses'][0]['url'] = $spdxurl;
                $metaentry['attributes']['image']['licenses'][0]['name'] = $spdxlicense->spdx_fullname;
                $metaentry['attributes']['image']['licenses'][0]['author'] = $imagefile->get_author();
            } else {
                $metaentry['attributes']['image'] = null;
            }
        }

        // TODO: Add option to describe learning objectives for a course.
        // $metaentry['attributes']['learningObjectives'] = [];

        // Set duration by converting amount of hours to ISO 8601 duration.
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

        // Set instructors.
        $lecturer = explode(', ', $meta->lecturer);
        $metaentry['attributes']['instructors'] = array();
        for ($i = 0; $i < count($lecturer); $i++) {
            if ($lecturer[$i] != '') {
                $metaentry['attributes']['instructors'][$i] = new \stdClass;
                $metaentry['attributes']['instructors'][$i]->name = $lecturer[$i];
            }
        }

        // Set video.
        $metaentry['attributes']['video'] = null;
        if (isset($meta->videocode) && !empty(trim($meta->videolicense))) {
            $license = $DB->get_record('license', array('id' => $meta->videolicense), '*', IGNORE_MISSING);
            // Only set video if video license is known.
            if (isset($license) && !empty($license) && $license->shortname != 'unknown') {
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

        // Set course license.
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

        // Set course provider.
        $provider = $providers[$meta->provider];
        $metaentry['attributes']['moocProvider']['name'] = $provider['name'];
        $urlwithprotocol = $provider['url'];
        // Make sure the provider url includes a protocol, add https if missing.
        if (strpos($provider['url'], 'http') === false) {
            $urlwithprotocol = 'https://' . $provider['url'];
        }
        $metaentry['attributes']['moocProvider']['url'] = $urlwithprotocol;
        $metaentry['attributes']['moocProvider']['logo'] = $provider['logo'];

        // Set access to "free". Currently there is no option to track paid courses.
        $metaentry['attributes']['access'] = ['free'];

        $metas['data'][] = $metaentry;
    }
}

// Schema Validation.
$validator = new Validator();
$schemaurl = "https://raw.githubusercontent.com/MOOChub/schema/d32a0476c8a8ef48af54439f329bffaaf088bf1c/moochub-schema.json";
// Get schema from github.
$schemajson = file_get_contents($schemaurl);
$schema = Helper::toJSON(json_decode($schemajson));
$data = Helper::toJSON($metas);


// Get validation result.
/** @var ValidationResult $result */
$result = $validator->validate($data, $schema);

// Checking if $data is valid.
if ($result->isValid()) {
    // Send Json response.
    $json = json_encode($metas, JSON_UNESCAPED_SLASHES);
    // If download flag is set trigger download on client browser.
    if ($download) {
        send_file($json, 'courses_moochub.json', 0, 0, true, $download);
    } else {
        header('Content-Type: application/vnd.api+json; moochub-version=2.3');
        echo $json;
    }
}

// Checking if there is an error.
if ($result->hasError()) {
    // Get the error.
    $error = $result->error();

    // Create an error formatter.
    $formatter = new ErrorFormatter();
    $formattederror = $formatter->format($error, true);

    $errormessage = json_encode(
        $formattederror,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    );

    // Notify moodle admin per email.
    $PAGE->set_context(context_system::instance());
    $admin = get_admin();
    $adminuser = $DB->get_record('user', ['id' => $admin->id]);
    $adminemail = $adminuser->email;
    $subject = 'ILD Meta Data: JSON Schema Validation Error';
    $message = 'There was an error while validating the JSON Schema for the moochub courses. Please check the error message below.';
    $message .= '<br><br>';
    $message .= $errormessage;

    // Send email.
    email_to_user($adminuser, $adminuser, $subject, $message, '', '', '', true);

    // Send error 500 response.
    $error = [
        'errors' => $formattederror,
        'schema' => $schema,
        'source' => $metas
    ];

    header('Content-Type: application/vnd.api+json; moochub-version=2.3');
    $json = json_encode($error, JSON_UNESCAPED_SLASHES);
    http_response_code(500);
    echo $json;
}
