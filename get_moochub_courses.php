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

$jsonlink = 'https://futurelearnlab.de/hub/courses_moochub.json';

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

    if ($meta->noindexcourse == 0 && $DB->record_exists('course', array('id' => $meta->courseid))) {

        $fs = get_file_storage();
        $context = context_course::instance($meta->courseid);
        $fileurl = '';

        // Get url of overview image.
        // If no custom image ist set in ildmeta, then use the course image instead.
        if (isset($meta->overviewimage)) {
            $files = $fs->get_area_files($context->id, 'local_ildmeta', 'overviewimage', 0);
        } else {
            $files = $fs->get_area_files($context->id, 'course', 'overviewfiles', 0);
        }
        foreach ($files as $file) {
            if ($file->is_valid_image()) {
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

        $video = null;
        if (!$meta->videocode == null) {
            $video = $meta->videocode;
        }

        $duration = null;
        if (!$meta->processingtime == null) {
            $duration .= 'P' . $meta->processingtime . 'H';
        }

        $course = get_course($meta->courseid);

        $metaentry = [];
        $metaentry['type'] = 'courses';
        $metaentry['id'] = 'futurelearnlab' . $meta->courseid;
        $metaentry['attributes'] = [];
        $metaentry['attributes']['name'] = $meta->coursetitle;
        $metaentry['attributes']['courseCode'] = 'futurelearnlab' . $meta->courseid;
        $metaentry['attributes']['courseMode'] = 'MOOC';
        $metaentry['attributes']['url'] = $CFG->wwwroot . '/blocks/ildmetaselect/detailpage.php?id=' . $meta->courseid;
        // $meta_entry['attributes']['publisher']     = $meta->lecturer;
        if ($meta->lecturer == '') {
            mtrace($meta->courseid);
        }
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
        // $meta_entry['attributes']['image']         = (string)$fileurl;

        if (trim((string)$fileurl) == '') {
            $metaentry['attributes']['image'] = null;
        } else {
            $metaentry['attributes']['image'] = array();
            // $meta_entry['attributes']['image'] = [trim((string)$fileurl)];
            $metaentry['attributes']['image']['url'] = trim((string)$fileurl);
            $metaentry['attributes']['image']['licenses'] = array();
            $metaentry['attributes']['image']['licenses'][0]['id'] = 'CC-BY-4.0';
            $metaentry['attributes']['image']['licenses'][0]['url'] = 'https://creativecommons.org/licenses/by/4.0';
        }

        // $meta_entry['attributes']['duration'] = $duration;
        $metaentry['attributes']['availableUntil'] = null;

        // $lecturer = explode(', ', $meta->lecturer);
        // $meta_entry['attributes']['instructors'] = [];
        // for ($i = 0; $i < count($lecturer); $i++) {
        // $meta_entry['attributes']['instructors'][$i] = new \stdClass;

        // if($lecturer[$i] != '') {
        // $meta_entry['attributes']['instructors'][$i]->name = null;
        // } else {
        // $meta_entry['attributes']['instructors'][$i]->name = $lecturer[$i];
        // }

        // }

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
            $metaentry['attributes']['video']['licenses'] = array();
            $metaentry['attributes']['video']['licenses'][0]['id'] = "Proprietary";
            $metaentry['attributes']['video']['licenses'][0]['url'] = null;
        }

        $metaentry['attributes']['courseLicenses'] = [];
        $metaentry['attributes']['courseLicenses'][0]['id'] = 'Proprietary';
        $metaentry['attributes']['courseLicenses'][0]['url'] = null;

        $provider = $providers[$meta->provider];

        $metaentry['attributes']['moocProvider']['name'] = $provider['name'];
        $metaentry['attributes']['moocProvider']['url'] = $provider['url'];
        $metaentry['attributes']['moocProvider']['logo'] = $provider['logo'];

        $metaentry['attributes']['access'] = ['free'];

        $metas['data'][] = $metaentry;
    }
}

// Create json.
$json = json_encode($metas, JSON_UNESCAPED_SLASHES);

// Save json as a file in a public directory.
$filedestination = $CFG->dirroot . '/courses_moochub.json';
if ($fp = fopen($filedestination, 'w')) {
    fwrite($fp, $json);
    fclose($fp);
}

// Send Json response.
header('Content-Type: application/json');

// If download flag is set trigger download on client browser.
send_file($json, 'courses_moochub.json', 0, 0, true, $download);
