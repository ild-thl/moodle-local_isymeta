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
 * @author      Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @copyright   2022 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

$context = context_system::instance();

if (!has_capability('moodle/site:config', $context)) {
    redirect($CFG->wwwroot);
}

$PAGE->set_context($context);
$url = new moodle_url('/local/ildmeta/edit_vocabulary.php');
$PAGE->set_url($url);
$PAGE->set_title(get_string('edit_vocabulary', 'local_ildmeta'));
$PAGE->set_heading(get_string('edit_vocabulary', 'local_ildmeta'));

// Inform moodle which menu entry currently is active!
admin_externalpage_setup('localildmeta_edit_vocabulary');


$mform = new local_ildmeta\output\form\edit_vocabulary_form();

$vocabularies = $DB->get_records('ildmeta_vocabulary');
if (empty($vocabularies)) {
    // Fill database tables with default values.
    include("./db/install.php");
    xmldb_local_ildmeta_install();

    $vocabularies = $DB->get_records('ildmeta_vocabulary');
}

if ($mform->is_cancelled()) {
    redirect($url);
} else if ($data = $mform->get_data()) {
    foreach ($vocabularies as $vocabulary) {
        if ($data->{$vocabulary->title}) {
            $vocabulary->terms = $data->{$vocabulary->title};
            $DB->update_record('ildmeta_vocabulary', $vocabulary);
        }
    }

    redirect($url);
}

// Display page.
echo $OUTPUT->header();


$toform = array();

foreach ($vocabularies as $vocabulary) {
    $toform[$vocabulary->title] = $vocabulary->terms;
}

$mform->set_data($toform);
$mform->display();

echo $OUTPUT->footer();
