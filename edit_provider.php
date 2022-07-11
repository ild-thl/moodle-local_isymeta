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
 * Page for edditing and creating new Mooc providers.
 *
 * @package     local_ildmeta
 * @author      Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @copyright   2022 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

$id = optional_param('id', null, PARAM_INT);
$mode = optional_param('mode', null, PARAM_ALPHA);

$context = context_system::instance();

// Check capabilities.
if (!has_capability('moodle/site:config', $context)) {
    redirect($CFG->wwwroot);
}

$PAGE->set_context($context);
$pageurl = new moodle_url('/local/ildmeta/edit_provider.php', array('id' => $id));
$home = new moodle_url('/local/ildmeta/edit_provider.php');
$PAGE->set_url($pageurl);
$PAGE->set_title(get_string('edit_provider', 'local_ildmeta'));
$PAGE->set_heading(get_string('edit_provider', 'local_ildmeta'));

// Inform moodle which menu entry currently is active!
admin_externalpage_setup('localildmeta_edit_provider');



$mform = new local_ildmeta\output\form\edit_provider_form();
$provider = null;
$allproviders = $DB->get_records('ildmeta_provider');
if (isset($id) && array_key_exists($id, $allproviders)) {
    $provider = $allproviders[$id];
}

if (isset($mode) && $mode == 'delete' && isset($provider)) {
    $DB->delete_records('ildmeta_provider', array('id' => $provider->id));
    redirect($home);
}

if ($mform->is_cancelled()) {
    redirect($home);
} else if ($data = $mform->get_data()) {
    if (isset($data->id) && !empty($data->id)) {
        $DB->update_record('ildmeta_provider', $data);
    } else {
        $data->id = $DB->insert_record('ildmeta_provider', $data);
    }

    // Save logo to filestorage.
    file_save_draft_area_files($data->logo, $context->id, 'local_ildmeta', 'provider', $data->id);

    redirect($home);
} else {
    // Set default data (if any).

    if (isset($provider)) {
        $draftitemid = file_get_submitted_draft_itemid('logo');
        file_prepare_draft_area($draftitemid, $context->id, 'local_ildmeta', 'provider', $provider->id);
        $provider->logo = $draftitemid;
        $mform->set_data($provider);
    }
}

// Display page.
echo $OUTPUT->header();


if ($mode == 'edit' || $mode == 'new') {
    if (isset($provider)) {
        echo $OUTPUT->heading(get_string('edit_provider', 'local_ildmeta'));
    } else {
        echo $OUTPUT->heading(get_string('provider_new', 'local_ildmeta'));
    }
    $mform->display();
} else {
    echo $OUTPUT->heading(get_string('provider', 'local_ildmeta'));

    $table = new \local_ildmeta\output\table\provider_table('allproviders');
    $table->set_sql('*', "{ildmeta_provider}", '1=1');
    $table->define_baseurl($home);


    echo '<a class="btn btn-primary" href="'
        . new moodle_url('/local/ildmeta/edit_provider.php', array('mode' => 'new')) . '">'
        . get_string('provider_new', 'local_ildmeta') . '</a></br></br>';

    $table->out(40, false);
}

echo $OUTPUT->footer();
