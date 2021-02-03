<?php
require_once('../../../config.php');
require_once('../lib.php');
require_once('ildmeta_form.php');
defined('MOODLE_INTERNAL') || die();

// Prevent access for students/guests

// Using coursecontext for has_capability()
$courseid = optional_param('courseid', array(), PARAM_INT);
$coursecontext = context_course::instance($courseid);

if (!has_capability('local/ildmeta:allowaccess', $coursecontext)) redirect(new moodle_url('/'));

$url = new moodle_url('/local/ildmeta/pages/ildmeta.php');
require_login();

$tbl = 'ildmeta';

// Dozenten Bilder
$tbl_lecturer = 'ildmeta_additional';

$context = context_system::instance();

$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title(get_string('title', 'local_ildmeta'));
$PAGE->set_heading(get_string('heading', 'local_ildmeta'));

$course_id = optional_param('courseid', 0, PARAM_INT);

$record = $DB->get_record($tbl, ['courseid' => $course_id]);


$filemanageropts = array(
    'subdirs' => 0,
    'maxbytes' => '0',
    'maxfiles' => 1,
    'context' => $context
);

$editoropts = array(
    'subdirs' => 0,
    'maxbytes' => '100000',
    'maxfiles' => 10,
    'context' => $context,
    'trusttext' => true,
    'enable_filemanagement' => true
);

if (isset($record->detailslecturer)) {
    $max_lecturer = $record->detailslecturer;
} else {
    $max_lecturer = 2;
}

$records_lect = $DB->get_records($tbl_lecturer, array('courseid' => $courseid));

$customdata = array('filemanageropts' => $filemanageropts, 'editoropts' => $editoropts, 'max_lecturer' => $max_lecturer, 'courseid' => $courseid, 'lecturer' => $records_lect);

$mform = new ildmeta_form($url . '?courseid=' . $courseid, $customdata);

$itemid = 0;

#$draftitemid = file_get_submitted_draft_itemid('overviewimage');
#file_prepare_draft_area($draftitemid, $coursecontext->id, 'local_ildmeta', 'overviewimage', $draftitemid);


if ($mform->is_cancelled()) {

    $redirectto = new moodle_url('/');
    redirect($redirectto);

} else if ($fromform = $mform->get_data()) {

    $draftitemid = file_get_submitted_draft_itemid('overviewimage');
    file_prepare_draft_area($draftitemid, $coursecontext->id, 'local_ildmeta', 'overviewimage', $draftitemid);

    $draftitemid_di = file_get_submitted_draft_itemid('detailimage');
    file_prepare_draft_area($draftitemid_di, $coursecontext->id, 'local_ildmeta', 'detailimage', $draftitemid_di);

    #$overimage = $DB->get_record($tbl, ['courseid' => $courseid])->overviewimage;
    file_save_draft_area_files($fromform->overviewimage, $coursecontext->id, 'local_ildmeta', 'overviewimage', 0);

    #$detailimage = $DB->get_record($tbl, ['courseid' => $courseid])->detailimage;
    file_save_draft_area_files($fromform->detailimage, $coursecontext->id, 'local_ildmeta', 'detailimage', 0);

    // first of all, check for additional lecturer fields

    if ($fromform->additional_lecturer > 0) {
        $addlect = new stdClass();
        $addlect->id = $record->id;
        $addlect->detailslecturer = $fromform->additional_lecturer + $record->detailslecturer;
        $DB->update_record($tbl, $addlect);


        // add empty fields in ildmeta_additional
        // new logic required due to delete options...

        //get last lecturer id

        $record_lect_last = $DB->get_record_sql("SELECT * FROM {ildmeta_additional} WHERE courseid = ? ORDER BY id DESC", array('courseid' => $courseid));

        //$i = substr($record_lect_last->name, -1) + 1;
        $i = explode("_", $record_lect_last->name)[2] + 1;

        $maxi = ($i - 1) + $fromform->additional_lecturer;

        while ($i <= $maxi) {
            $str1 = "lecturer_type_" . $i;
            $str2 = "detailslecturer_image_" . $i;
            $str3 = "detailslecturer_editor_" . $i;

            $fields = array($str1, $str2, $str3);

            foreach ($fields as $f) {
                $ins = new stdClass();
                $ins->courseid = $courseid;
                $ins->name = $f;
                $ins->value = '';
                $DB->insert_record($tbl_lecturer, $ins);
            }
            $i++;
        }
        // if additional lecturer the user will be redirected to the ildmeta.php for further editing
        $url = new moodle_url('/local/ildmeta/pages/ildmeta.php', array('courseid' => $courseid));
    } else {
        // otherweise he will be forwarded to the detailpage.php
        $url = new moodle_url('/blocks/ildmetaselect/detailpage.php', array('id' => $courseid));
    }


    $todb = new stdClass;
    $todb->courseid = $courseid;
    $todb->overviewimage = $draftitemid;
    $todb->coursetitle = $fromform->coursetitle;
    $todb->lecturer = $fromform->lecturer;
    if(isset($fromform->noindexcourse)){
        $todb->noindexcourse = $fromform->noindexcourse;
    }
    $todb->overviewimage = $draftitemid;
    $todb->detailimage = $fromform->detailimage;
    $todb->university = implode(",", $fromform->university);
    $todb->subjectarea = $fromform->subjectarea;
    $todb->courselanguage = $fromform->courselanguage;
    $todb->processingtime = $fromform->processingtime;
    $todb->starttime = $fromform->starttime;
    $todb->teasertext = $fromform->teasertext['text'];
    $todb->targetgroup = $fromform->targetgroup['text'];
    $todb->learninggoals = $fromform->learninggoals['text'];
    $todb->structure = $fromform->structure['text'];
    $todb->certificateofachievement = $fromform->certificateofachievement['text'];
    $todb->license = $fromform->license;
    $todb->videocode = $fromform->videocode;

    $todb->tags = $fromform->tags;

// !

    // if course is not in db yet
    if (!$DB->get_record($tbl, array('courseid' => $course_id))) {

        //if noindexcourse in todb is not set
        if(!isset($todb->noindexcourse)){
            //use the default value "no indexination"
            $todb->noindexcourse = 1;
        }
        $DB->insert_record($tbl, $todb);

        //if course is in db, update
    } else {
        $primkey = $DB->get_record($tbl, array('courseid' => $course_id));

        $todb->id = $primkey->id;
        //if noindexcourse in todb is not set
        if(!isset($todb->noindexcourse)){
            //use the old value from the db
            $todb->noindexcourse = $primkey->noindexcourse;
        }
        $DB->update_record($tbl, $todb);
    }

    // Get lecturer editor + filemanager

    $lecturer = new stdClass();

    foreach ($fromform as $key => $value) {
        if (strpos($key, '_type')) {
            $lecturer->$key = $fromform->$key;
        }
        if (strpos($key, '_image')) {
            $lecturer->$key = $fromform->$key;

            $draftlecturer = file_get_submitted_draft_itemid($key);
            file_prepare_draft_area($draftlecturer, $coursecontext->id, 'local_ildmeta', $key, 0);
            file_save_draft_area_files($draftlecturer, $coursecontext->id, 'local_ildmeta', $key, 0);
        }
        if (strpos($key, '_editor')) {
            $lecturer->$key = $fromform->$key['text'];
        }
    }

    foreach ($lecturer as $key => $value) {

        $lectodb = new stdClass();
        $lectodb->courseid = $courseid;
        $lectodb->name = $key;
        $lectodb->value = $value;


        if (!$DB->get_record($tbl_lecturer, array('name' => $lectodb->name, 'courseid' => $courseid))) {
            $DB->insert_record($tbl_lecturer, $lectodb);
        } else {

            $primkey = $DB->get_record($tbl_lecturer, array('courseid' => $courseid, 'name' => $lectodb->name));

            $lectodb->id = $primkey->id;

            $DB->update_record($tbl_lecturer, $lectodb);
        }

    }

    // after database redirect to detailpage
    // $url defined after check for additional lecturer
    redirect($url, 'Daten erfolgreich gespeichert', null, \core\output\notification::NOTIFY_SUCCESS);

} else {
    // prefill forms from db
    $getdb = $DB->get_record($tbl, array('courseid' => $course_id));

    $getlect = $DB->get_records($tbl_lecturer, array('courseid' => $courseid));


    if ($getdb != null) {
        $new = new stdClass;
        $new->coursetitle = $getdb->coursetitle;
        $new->lecturer = $getdb->lecturer;
        $new->overviewimage = $getdb->overviewimage;
        $new->detailimage = $getdb->detailimage;
        $new->university = $getdb->university;
        $new->noindexcourse = $getdb->noindexcourse;
        $new->subjectarea = $getdb->subjectarea;
        $new->courselanguage = $getdb->courselanguage;
        $new->processingtime = $getdb->processingtime;
        $new->starttime = $getdb->starttime;
        $new->teasertext['text'] = $getdb->teasertext;
        $new->targetgroup['text'] = $getdb->targetgroup;
        $new->learninggoals['text'] = $getdb->learninggoals;
        $new->structure['text'] = $getdb->structure;
        $new->additional_lecturer = '0';
        $new->certificateofachievement['text'] = $getdb->certificateofachievement;
        $new->license = $getdb->license;
        $new->videocode = $getdb->videocode;
        $new->tags = $getdb->tags;


        if (!empty($getlect)) {

            foreach ($getlect as $lec) {
                if (strpos($lec->name, '_editor')) {
                    echo $lec_name . "<br>";
                    $key = $lec->name;
                    $new->$key['text'] = $lec->value;
                } else {
                    $key = $lec->name;
                    $new->$key = $lec->value;
                }
            }

        }
		
		$sql = 'SELECT filearea 
					    FROM {files} 
					 WHERE component = :component 
					      AND contextid = :contextid 
						  AND filename != :filename 
						  AND itemid = 0';
		$params = array('component' => 'local_ildmeta', 'contextid' => $coursecontext->id, 'filename' => '.');
		$files = $DB->get_records_sql($sql, $params);
		//print_object($files);
		foreach ($files as $file) {
			$draftitemid = file_get_submitted_draft_itemid($file->filearea);
			//file_save_draft_area_files($draftlecturer, $coursecontext->id, 'local_ildmeta', $key, 0);
			file_prepare_draft_area($draftitemid, $coursecontext->id, 'local_ildmeta', $file->filearea, 0);
			$lectname = $file->filearea;
			$new->$lectname = $draftitemid;
		}
        $mform->set_data($new);

    } else {
        $new = new stdClass;
        $new->courseid = $course_id;
        $new->overviewimage = '';
        $new->coursetitle = $DB->get_record('course', array('id' => $course_id))->fullname;
        $new->lecturer = '';
        $new->noindexcourse = 0;
        $new->detailimage = '';
        $new->university = 0;
        $new->subjectarea = 0;
        $new->courselanguage = 0;
        $new->processingtime = 0;
        $new->starttime = 0;
        $new->teasertext = '';
        $new->targetgroup = '';
        $new->learninggoals = '';
        $new->structure = '';
        $new->detailslecturer = 2;
        $new->detailsmorelecturer = '';
        $new->detailslecturerimage = '';
        $new->additional_lecturer = 2;
        $new->certificateofachievement = '';
        $new->license = 0;
        $new->videocode = '';
        $new->tags = '';


        $DB->insert_record($tbl, $new);
    }


    echo $OUTPUT->header();
    $toform = array('additional_lecturer' => 2);
    $mform->display($toform);

//$mform->display();

//$cluster = $DB->get_records($tbl);

    echo $OUTPUT->footer();
}
