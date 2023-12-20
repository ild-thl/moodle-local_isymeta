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
 * Per url parameter idn=idnumber (e.g. in Moodle with manually provided additional course-id)
 * course data for a single course is returned.
 *
 * @package     local_ildmeta
 * @author      Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @author      Tina John <tina.john@th-luebeck.de>
 * @copyright   2023 ILD TH Lübeck <dev.ild@th-luebeck.de>
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

// Read Accept header to determine wich version of moochub schema is requested.
// If a version lower than 3 is requested run get_moochub_courses_v2.php instead.
if (isset($_SERVER['HTTP_ACCEPT'])) {
    $accept = $_SERVER['HTTP_ACCEPT'];
    // Get requested version from Accept header with regex moochub-version=2.1 => 2.1 .
    $requestedversion = preg_replace('/.*moochub-version=(\d+(\.\d+)?)*/', '$1', $accept);
    if (isset($requestedversion) and !empty($requestedversion)) {
        $requestedversion = floatval($requestedversion);
        if ($requestedversion < 3) {
            require('get_moochub_courses_v2.php');
            exit;
        }
    }
}

global $CFG;
require_once($CFG->libdir . '/filelib.php');
require('./vendor/autoload.php');
$download = optional_param('download', false, PARAM_BOOL);

$metas = [];

// Create Links for pagination as proposed by the JSON:API schema.
$jsonlink = $CFG->httpswwwroot . '/local/ildmeta/get_moochub_courses.php';
$metaslinks = ['self' => $jsonlink, 'first' => $jsonlink, 'last' => $jsonlink];
$metas['links'] = $metaslinks;
$metas['data'] = [];

// Function to process input.
function pproc_input($idnumbers) {
    // Create array for input.
    if (!is_array($idnumbers) and is_string($idnumbers)) {
        $idnumbers = [$idnumbers];
    }
    // Remove empty values.
    $idnumbers = array_filter($idnumbers, function ($value) {
        return trim($value) !== '';
    });
    return($idnumbers);
}

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
        $course = $DB->record_exists('course', array('id' => $meta->courseid));
        if ($meta->noindexcourse != 0 || !$course) {
            continue;
        }

        $metaentry = [];
        $metaentry['id'] = $meta->uuid;
        $metaentry['type'] = 'Course';

        $metaentry['attributes'] = [];
        $metaentry['attributes']['name'] = $meta->coursetitle;
        $metaentry['attributes']['courseCode'] = $course->idnumber ?? null;
        $metaentry['attributes']['courseMode'] = ['online', 'asynchronous'];
        $metaentry['attributes']['learningResourceType'] = [
            "identifier" => "https://w3id.org/kim/hcrt/course",
            "type" => "Concept",
            "inScheme" => "https://w3id.org/kim/hcrt/scheme"
        ];
        $metaentry['attributes']['description'] = $meta->teasertext;
        $langlist = [
            'de',
            'en',
            'uk',
            'ru'
        ];
        $metaentry['attributes']['inLanguage'] = [$langlist[$meta->courselanguage]];
        $metaentry['attributes']['endDate'] = date('c', $meta->starttime);
        $metaentry['attributes']['endDate'] = null;
        $metaentry['attributes']['expires'] = null;
        if (isset($meta->availableuntil) && !empty($meta->availableuntil)) {
            $metaentry['attributes']['expires'] = [date('c', $meta->availableuntil)];
        }

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
        if (isset($fileurl) && !empty((string)$fileurl) and isset($imagefile)) {
            $metaentry['attributes']['image'] = array();
            $metaentry['attributes']['image']['type'] = "ImageObject";
            $metaentry['attributes']['image']['contentUrl'] = trim((string)$fileurl);

            // Get License of image and convert to an SPDX license.
            $license = $DB->get_record('license', array('shortname' => $imagefile->get_license()), '*', MUST_EXIST);
            if (isset($license) && !empty($license) && $license->shortname != 'unknown') {
                $spdxlicense = $DB->get_record('ildmeta_spdx_licenses', array('moodle_license' => $license->id), '*', MUST_EXIST);
                $spdxurl = !empty($spdxlicense->spdx_url) ? $spdxlicense->spdx_url : null;
                $metaentry['attributes']['image']['license'] = array();
                $metaentry['attributes']['image']['license'][0]['identifier'] = $spdxlicense->spdx_shortname;
                $metaentry['attributes']['image']['license'][0]['url'] = $spdxurl;
            }
        }

        // Set video.
        if (isset($meta->videocode) && !empty(trim($meta->videolicense))) {
            $license = $DB->get_record('license', array('id' => $meta->videolicense), '*', IGNORE_MISSING);
            // Only set video if video license is known.
            if (isset($license) && !empty($license) && $license->shortname != 'unknown') {
                $metaentry['attributes']['trailer'] = array();
                $metaentry['attributes']['trailer']['type'] = "VideoObject";
                $metaentry['attributes']['trailer']['contentUrl'] = trim($meta->videocode);

                $spdxlicense = $DB->get_record('ildmeta_spdx_licenses', array('moodle_license' => $license->id), '*', MUST_EXIST);
                $spdxurl = !empty($spdxlicense->spdx_url) ? $spdxlicense->spdx_url : null;
                $metaentry['attributes']['trailer']['licenses'] = array();
                $metaentry['attributes']['trailer']['licenses'][0]['identifier'] = $spdxlicense->spdx_shortname;
                $metaentry['attributes']['trailer']['licenses'][0]['url'] = $spdxurl;
            }
        }

        // Set instructors.
        $lecturer = explode(', ', $meta->lecturer);
        $metaentry['attributes']['instructor'] = array();
        for ($i = 0; $i < count($lecturer); $i++) {
            if ($lecturer[$i] != '') {
                $metaentry['attributes']['instructor'][$i] = array();
                $metaentry['attributes']['instructor'][$i]['name'] = $lecturer[$i];
                $metaentry['attributes']['instructor'][$i]['type'] = 'Person';
                // TODO: Add image of instructor.
            }
        }

        // TODO Set teaches.

        // Set duration by converting amount of hours to ISO 8601 duration.
        if (isset($meta->processingtime) && !empty($meta->processingtime)) {
            $duration = 'PT' . $meta->processingtime . 'H';
            $metaentry['attributes']['duration'] = $duration;
        }

        // Set course provider.
        $provider = $providers[$meta->provider];
        $metaentry['attributes']['publisher']['name'] = $provider['name'];
        $urlwithprotocol = $provider['url'];
        // Make sure the provider url includes a protocol, add https if missing.
        if (strpos($provider['url'], 'http') === false) {
            $urlwithprotocol = 'https://' . $provider['url'];
        }
        $metaentry['attributes']['publisher']['identifier'] = $urlwithprotocol;
        $metaentry['attributes']['publisher']['type'] = 'Organization';
        $metaentry['attributes']['publisher']['image'] = array();
        $metaentry['attributes']['publisher']['image']['type'] = 'ImageObject';
        $metaentry['attributes']['publisher']['image']['contentUrl'] = $provider['logo'];
        $metaentry['attributes']['publisher']['image']['license'] = array();
        $metaentry['attributes']['publisher']['image']['license'][0]['identifier'] = 'proprietary';
        $metaentry['attributes']['publisher']['image']['license'][0]['url'] = null;

        $metaentry['attributes']['url'] = manager::get_external_course_link($meta->courseid);

        // Set course license.
        $license = $DB->get_record('license', array('id' => $meta->license), '*', IGNORE_MISSING);
        if (isset($license) && !empty($license) && $license->shortname != 'unknown') {
            $spdxlicense = $DB->get_record('ildmeta_spdx_licenses', array('moodle_license' => $license->id), '*', MUST_EXIST);
            $spdxurl = !empty($spdxlicense->spdx_url) ? $spdxlicense->spdx_url : null;
            $metaentry['attributes']['license'] = array();
            $metaentry['attributes']['license'][0]['identifier'] = $spdxlicense->spdx_shortname;
            $metaentry['attributes']['license'][0]['url'] = $spdxurl;
        } else {
            $metaentry['attributes']['license'] = [];
            $metaentry['attributes']['license'][0]['identifier'] = 'Proprietary';
            $metaentry['attributes']['license'][0]['url'] = null;
        }

        $metaentry['attributes']['access'] = ['free'];

        // TODO Set audience.
        // TODO Set educationalAlignement.
        // TODO Set educationalLevel.
        // TODO Set actual creator. For now copy publisher.
        $metaentry['attributes']['creator'] = [$metaentry['attributes']['publisher']];

        $metaentry['attributes']['keywords'] = explode(', ', $meta->tags);

        // TODO Set numberOfCredits.
        // TODO Set educationalCredentialsAwarded.
        // TODO Set competencyRequired.
        // TODO Set accessMode.
        // Set dateCreated.
        // Set dateModified.

        $metas['data'][] = $metaentry;
    }
}

// Schema Validation.
$validator = new Validator();
$schemaurl = "https://raw.githubusercontent.com/MOOChub/schema/b89a218d74fec89fe01ea5ad68b95b07dcfe17a6/moochub-schema.json";
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
        header('Content-Type: application/vnd.api+json; moochub-version=3');
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

    header('Content-Type: application/vnd.api+json; moochub-version=3');
    $json = json_encode($error, JSON_UNESCAPED_SLASHES);
    http_response_code(500);
    echo $json;
}
