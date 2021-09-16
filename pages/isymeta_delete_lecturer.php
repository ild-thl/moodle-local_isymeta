<?php

require_once('../../../config.php');
require_once('../lib.php');
require_once('isymeta_delete_lecturer_form.php');
defined('MOODLE_INTERNAL') || die();

$courseid = optional_param('courseid', array(), PARAM_INT);
$lecturer_id = optional_param('id', array(), PARAM_INT);

$context = context_system::instance();

$url = new moodle_url('/local/isymeta/pages/isymeta_delete_lecturer.php', array('courseid' => $courseid));
// Prevent access for students/guests
if (!has_capability('local/isymeta:delete_lecturer', context_system::instance())) redirect(new moodle_url('/'));

require_login();


$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title(get_string('title', 'local_isymeta'));
$PAGE->set_heading(get_string('heading', 'local_isymeta'));


$tbl_meta = 'isymeta';
$tbl_lecturer = 'isymeta_additional';

$url = new moodle_url('/local/isymeta/pages/isymeta_delete_lecturer.php', array('courseid' => $courseid, 'id' => $lecturer_id));
$mform = new isymeta_delete_lecturer_form($url);




if ($mform->is_cancelled()) {

    $url = new moodle_url('/local/isymeta/pages/isymeta.php', array('courseid' => $courseid));
    redirect($url);

} else if ($fromform = $mform->get_data()) {

    if (!$fromform->submitbutton) {
        $url = new moodle_url('/local/isymeta/pages/isymeta.php', array('courseid' => $courseid));
        redirect($url);
    } else {

        // first delete from isymeta_additional
        $fields = $DB->get_records_sql('SELECT name FROM {isymeta_additional} WHERE courseid = ? AND name LIKE ?', array('courseid' => $courseid, 'name' => '%' . $lecturer_id . ''));

        $error = false;

        foreach ($fields as $f) {
            $params = array('courseid' => $courseid, 'name' => $f->name);

            if (!$DB->delete_records($tbl_lecturer, $params)) {
                $error = true;
            }
        }

        // then adjust the counter in isymeta
        $sql = "UPDATE {isymeta} SET detailslecturer=detailslecturer-1 WHERE courseid = ?";
        $params = array('courseid' => $courseid);

        if (!$DB->execute($sql, $params)) {
            $error = true;
        }

        $url = new moodle_url('/local/isymeta/pages/isymeta.php', array('courseid' => $courseid));
        if ($error) {
            redirect($url, 'Fehler beim Löschen der Datensätze!', null, \core\output\notification::NOTIFY_ERROR);
        } else {
            redirect($url, 'Datensätze erfolgreich gelöscht!', null, \core\output\notification::NOTIFY_SUCCESS);
        }

    }

} else {

    echo $OUTPUT->header();

    $mform->display();

    echo $OUTPUT->footer();

}
