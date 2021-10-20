<?php

defined('MOODLE_INTERNAL') || die();


require_once('../../../config.php');
require_once('../lib.php');
require_once('isymeta_form.php');

$courseid = optional_param('courseid', array(), PARAM_INT);
$coursecontext = context_course::instance($courseid);
$context = context_system::instance();

// prevent access for guest/students
if (!has_capability('local/isymeta:allowaccess', $coursecontext)) redirect(new moodle_url('/'));

$url = new moodle_url('/local/isymeta/pages/isymeta.php');

$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title(get_string('title', 'local_isymeta'));
$PAGE->set_heading(get_string('heading', 'local_isymeta'));
require_login();

$tbl = 'isymeta';
$record = $DB->get_record($tbl, ['courseid' => $courseid]);

$filemanageropts = [
    'subdirs' => 0,
    'maxbytes' => '0',
    'maxfiles' => 1,
    'context' => $context
];

$editoropts = [
    'subdirs' => 0,
    'maxbytes' => '100000',
    'maxfiles' => 10,
    'context' => $context,
    'trusttext' => true,
    'enable_filemanagement' => true
];

$customdata = [
    'filemanageropts' => $filemanageropts,
    'editoropts' => $editoropts,
    'courseid' => $courseid
];

$mform = new isymeta_form($url . '?courseid=' . $courseid, $customdata);
$itemid = 0;

if ($mform->is_cancelled()) {

    redirect(new moodle_url('/'));

} else if ($fromform = $mform->get_data()) {

    $draftitemid = file_get_submitted_draft_itemid('overviewimage');
    file_prepare_draft_area($draftitemid, $coursecontext->id, 'local_isymeta', 'overviewimage', $draftitemid);
    file_save_draft_area_files($fromform->overviewimage, $coursecontext->id, 'local_isymeta', 'overviewimage', 0);

    $todb = new stdClass;
    $todb->courseid = $courseid;
    $todb->meta1 = $fromform->meta1;
    $todb->meta2 = implode(",", $fromform->meta2);
    if(isset($todb->meta3)) { $todb->meta3 = $fromform->meta3; } // Dozent*in
    $todb->meta4 = $fromform->meta4;
    $todb->meta5 = $fromform->meta5;
    $todb->meta6 = $fromform->meta6;
    $todb->overviewimage = $draftitemid;


    // if course is not in db yet
    if (!$record) {
        $DB->update_record($tbl, $todb);
        redirect($url, 'Daten erfolgreich gespeichert', null, \core\output\notification::NOTIFY_SUCCESS);
    }

} else {
    // prefill forms from db

    if ($record != null) {
        $new = new stdClass;
        $new->overviewimage = $record->overviewimage;
        $new->meta1 = $record->meta1;
        $new->meta2 = $record->meta2;
        $new->meta3 = $record->meta3;
        $new->meta4 = $record->meta4;
        $new->meta5 = $record->meta5;
        $new->meta6 = $record->meta6;
       
        $mform->set_data($new);

    } else {
        $new = new stdClass;
        $new->courseid = $courseid;
        $new->meta1 = 0;
        $new->meta2 = 0;
        $new->meta3 = 0; //todo
        $new->meta4 = 0;
        $new->meta5 = 0;
        $new->meta6 = 0;
        $new->overviewimage = '';

        $DB->insert_record($tbl, $new);
    }

    echo $OUTPUT->header();
    $mform->display();

    echo $OUTPUT->footer();
}
