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
 * @copyright   2023 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_ildmeta\manager;
use Opis\JsonSchema\{
    Validator,
    ValidationResult,
    Helper,
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
$metarecords = $DB->get_records('ildmeta');

// Create Links for pagination as proposed by the JSON:API schema.
$jsonlink = $CFG->httpswwwroot . '/local/ildmeta/get_moochub_courses.php';
$metaslinks = ['self' => $jsonlink, 'first' => $jsonlink, 'last' => $jsonlink];
$metas['links'] = $metaslinks;
$metas['data'] = [];

// Schema Validation.
$validator = new Validator();
$schemaurl = "https://raw.githubusercontent.com/MaxThomasHPI/schemaV3/f1f86b2bec99d0b3d78749bdd1d1a461121590ef/moochub-schema.json";
// Get schema from github.
$schemajson = file_get_contents($schemaurl);
$schema = Helper::toJSON(json_decode($schemajson));
$data = Helper::toJSON($metas);


// Get validation result.
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
    // Notify moodle admin per email.
    $PAGE->set_context(context_system::instance());
    $admin = get_admin();
    $adminuser = $DB->get_record('user', ['id' => $admin->id]);
    $adminemail = $adminuser->email;
    $subject = 'ILD Meta Data: JSON Schema Validation Error';
    $message = 'There was an error while validating the JSON Schema for the moochub courses. Please check the error message below.';
    $message .= '<br><br>';
    $message .= $error->keyword() . ': ' . $error->message();

    // Send email.
    email_to_user($adminuser, $adminuser, $subject, $message, '', '', '', true);

    // Send error 500 response.
    $error = [
        'error' => $error->keyword() . ': ' . $error->message(),
        'schema' => $schema,
        'source' => $metas
    ];
    $json = json_encode($error, JSON_UNESCAPED_SLASHES);
    header('Content-Type: application/vnd.api+json; moochub-version=3');
    http_response_code(500);
    echo $json;
}
