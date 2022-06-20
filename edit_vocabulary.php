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
 * Page for edditing BIRD vocabulary.
 *
 * @package     local_ildmeta
 * @copyright   2022 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

$context = context_system::instance();

if (has_capability('moodle/site:config', $context)) {
    $PAGE->set_context($context);
    $PAGE->set_url('/local/ildmeta/edit_vocabulary.php');
    $PAGE->set_title(get_string('edit_vocabulary', 'local_ildmeta'));
    $PAGE->set_heading(get_string('edit_vocabulary', 'local_ildmeta'));

    // Inform moodle which menu entry currently is active!
    admin_externalpage_setup('localildmeta_edit_vocabulary');

    // Projekte.
    $url = new moodle_url('/local/ildmeta/edit_vocabulary.php');

    $mform = new local_ildmeta\output\form\edit_vocabulary_form();

    $records = $DB->get_records('ildmeta_settings');
    if (count($records) < 1) {
        $default = [
            'coursetypes' => '["Sprachkurs","Fachkurs","Propädeutik","Soft Skill","Professional Skill","Digital Skill","Academic Skills"]',
            'courseformats' => '["Präsenz","Online Selbstlernkurs","Online","Blended Learning"]',
            'audience' => '["Schüler*innen","Studieninteressierte","Studierende","Promotionsinteresse","PASCH-Schüler*innen","Lehrende","Eltern"]',
        ];
        $id = $DB->insert_record('ildmeta_settings', $default);
        $settings = $DB->get_record('ildmeta_settings', array('id' => $id), '*', MUST_EXIST);
    } else {
        $settings = reset($records);
    }

    if ($mform->is_cancelled()) {
        redirect($url);
    } else if ($data = $mform->get_data()) {
        $settings->coursetypes = json_encode($data->coursetypes);
        $settings->courseformats = json_encode($data->courseformats);
        $settings->audience = json_encode($data->audience);

        $DB->update_record('ildmeta_settings', $settings);
        redirect($url);
    }

    echo $OUTPUT->header();

    $toform = [
        'coursetypes' => json_decode($settings->coursetypes),
        'courseformats' => json_decode($settings->courseformats),
        'audience' => json_decode($settings->audience),
    ];

    $mform->set_data($toform);
    $mform->display();

    echo $OUTPUT->footer();
} else {
    redirect($CFG->wwwroot);
}
