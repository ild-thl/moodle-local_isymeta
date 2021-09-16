<?php
require_once('../../../config.php');
require_once('../lib.php');
require_once('isymeta_form.php');
defined('MOODLE_INTERNAL') || die();

// Prevent access for students/guests

// Using coursecontext for has_capability()
$courseid = optional_param('courseid', array(), PARAM_INT);
$coursecontext = context_course::instance($courseid);

if (!has_capability('local/isymeta:allowaccess', $coursecontext)) redirect(new moodle_url('/'));

$url = new moodle_url('/local/isymeta/pages/isymeta.php');
require_login();

$tbl = 'isymeta';

// Dozenten Bilder
$tbl_lecturer = 'isymeta_additional';
$tbl_sponsor = 'isymeta_sponsors';

$context = context_system::instance();

$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title(get_string('title', 'local_isymeta'));
$PAGE->set_heading(get_string('heading', 'local_isymeta'));

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

if (isset($record->detailssponsor)) {
    $max_sponsor = $record->detailssponsor;
} else {
    $max_sponsor = 2;
}

$records_lect = $DB->get_records($tbl_lecturer, array('courseid' => $courseid));
$records_spons = $DB->get_records($tbl_sponsor, array('courseid' => $courseid)); //todo

$customdata = [
    'filemanageropts' => $filemanageropts,
    'editoropts' => $editoropts,
    'max_lecturer' => $max_lecturer,
    'max_sponsor' => $max_lecturer,
    'courseid' => $courseid,
    'lecturer' => $records_lect,
    'sponsor' => $records_spons
];

$mform = new isymeta_form($url . '?courseid=' . $courseid, $customdata);

$itemid = 0;

if ($mform->is_cancelled()) {

    $redirectto = new moodle_url('/');
    redirect($redirectto);

} else if ($fromform = $mform->get_data()) {

    $draftitemid = file_get_submitted_draft_itemid('overviewimage');
    file_prepare_draft_area($draftitemid, $coursecontext->id, 'local_isymeta', 'overviewimage', $draftitemid);

    $draftitemid_di = file_get_submitted_draft_itemid('detailimage');
    file_prepare_draft_area($draftitemid_di, $coursecontext->id, 'local_isymeta', 'detailimage', $draftitemid_di);

    file_save_draft_area_files($fromform->overviewimage, $coursecontext->id, 'local_isymeta', 'overviewimage', 0);

    file_save_draft_area_files($fromform->detailimage, $coursecontext->id, 'local_isymeta', 'detailimage', 0);

    // first of all, check for additional lecturer fields

    if ($fromform->additional_lecturer > 0) {
        $addlect = new stdClass();
        $addlect->id = $record->id;
        
        if(empty($record->detailslecturer)) {
            $reci = 0;
        } else {
            $reci = $record->detailslecturer;
        }

        $addlect->detailslecturer = $fromform->additional_lecturer + $reci;
        $DB->update_record($tbl, $addlect);


        // add empty fields in isymeta_additional
        // new logic required due to delete options...

        //get last lecturer id

        $record_lect_last = $DB->get_record_sql("SELECT * FROM {isymeta_additional} WHERE courseid = ? ORDER BY id DESC", array('courseid' => $courseid), true);

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
        // if additional lecturer the user will be redirected to the isymeta.php for further editing
        $url = new moodle_url('/local/isymeta/pages/isymeta.php', array('courseid' => $courseid));
    } else {
        // otherweise he will be forwarded to the detailpage.php
        $url = new moodle_url('/blocks/isymetaselect/detailpage.php', array('id' => $courseid));
    }


    if ($fromform->additional_sponsor > 0) {
        $addspons = new stdClass();
        $addspons->id = $record->id;
        $addspons->detailssponsor = $fromform->additional_sponsor + $record->detailssponsor;
        $DB->update_record($tbl, $addspons);

        // add empty fields in isymeta_additional

        //get last lecturer id

        $record_spons_last = $DB->get_record_sql("SELECT * FROM {isymeta_sponsors} WHERE courseid = ? ORDER BY id DESC", array('courseid' => $courseid));

        $i = explode("_", $record_spons_last->name)[2] + 1;

        $maxi2 = ($i - 1) + $fromform->additional_sponsor;

        while ($i <= $maxi2) {
            $str4 = "detailssponsor_image_" . $i;
            $str5 = "detailssponsor_link_" . $i;

            $fields2 = array($str4, $str5);

            foreach ($fields2 as $f) {
                $ins2 = new stdClass();
                $ins2->courseid = $courseid;
                $ins2->name = $f;
                $ins2->value = '';
                $DB->insert_record($tbl_sponsor, $ins2);
            }
            $i++;
        }
        // if additional lecturer the user will be redirected to the isymeta.php for further editing
        $url = new moodle_url('/local/isymeta/pages/isymeta.php', array('courseid' => $courseid));
    } else {
        // otherweise he will be forwarded to the detailpage.php
        $url = new moodle_url('/blocks/isymetaselect/coursedetails.php', array('id' => $courseid));
    }

    $todb = new stdClass;
    $todb->courseid = $courseid;
    $todb->overviewimage = $draftitemid;
    $todb->coursetitle = $fromform->coursetitle;
    if(isset($todb->meta3)) {
        $todb->meta3 = $fromform->meta3; // Dozent*in
    }
   
    if(isset($fromform->noindexcourse)){
        $todb->noindexcourse = $fromform->noindexcourse;
    }
    $todb->overviewimage = $draftitemid;
    $todb->detailimage = $fromform->detailimage;
    $todb->meta2 = implode(",", $fromform->meta2);
    $todb->meta6 = $fromform->meta6;
    $todb->courselanguage = $fromform->courselanguage;
    $todb->meta4 = $fromform->meta4;
    $todb->meta5 = $fromform->meta5;
    $todb->teasertext = $fromform->teasertext['text'];
    $todb->targetgroup = $fromform->targetgroup['text'];
    $todb->learninggoals = $fromform->learninggoals['text'];
    $todb->structure = $fromform->structure['text'];
    $todb->certificateofachievement = $fromform->certificateofachievement['text'];
    $todb->license = $fromform->license;
    $todb->videocode = $fromform->videocode;

    $todb->tags = $fromform->tags;

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
    $lecturer_editor = new stdClass();

    foreach ($fromform as $key => $value) {
       
        if (strpos($key, 'lecturer_type') !== false) {
            
            $lecturer_editor->$key = $fromform->$key;
        }
        if (strpos($key, 'detailslecturer_image') !== false) {
            $lecturer_editor->$key = $fromform->$key;

            $draftlecturer = file_get_submitted_draft_itemid($key);
            file_prepare_draft_area($draftlecturer, $coursecontext->id, 'local_isymeta', $key, 0);
            file_save_draft_area_files($draftlecturer, $coursecontext->id, 'local_isymeta', $key, 0);
        }
        if (strpos($key, 'detailslecturer_editor') !== false) {
            $lecturer_editor->$key = $fromform->$key['text'];
        }
    }

    foreach ($lecturer_editor as $key => $value) {

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

        // Get sponsor editor + filemanager

        $sponsor = new stdClass();
        
        foreach ($fromform as $key => $value) {

            if (strpos($key, 'detailssponsor_image') !== false) {
                
                $sponsor->$key = $fromform->$key;
    
                $draftsponsor = file_get_submitted_draft_itemid($key);
                file_prepare_draft_area($draftsponsor, $coursecontext->id, 'local_isymeta', $key, 0);
                file_save_draft_area_files($draftsponsor, $coursecontext->id, 'local_isymeta', $key, 0);
            }
            if (strpos($key, 'detailssponsor_link') !== false) {
                $sponsor->$key = $fromform->$key;  
            }
            
        }
       
        foreach ($sponsor as $key => $value) {
            $sponstodb = new stdClass();
            $sponstodb->courseid = $courseid;
            $sponstodb->name = $key;
            $sponstodb->value = $value;
    
            if (!$DB->get_record($tbl_sponsor, array('name' => $sponstodb->name, 'courseid' => $courseid))) {
                $DB->insert_record($tbl_sponsor, $sponstodb);
            } else {
    
                $primkey = $DB->get_record($tbl_sponsor, array('courseid' => $courseid, 'name' => $sponstodb->name));
    
                $sponstodb->id = $primkey->id;
    
                $DB->update_record($tbl_sponsor, $sponstodb);
            }
    
        }

    // after database redirect to detailpage
    // $url defined after check for additional lecturer
    redirect($url, 'Daten erfolgreich gespeichert', null, \core\output\notification::NOTIFY_SUCCESS);

} else {
    // prefill forms from db
    $getdb = $DB->get_record($tbl, array('courseid' => $course_id));

    $getlect = $DB->get_records($tbl_lecturer, array('courseid' => $courseid));
    $getspons = $DB->get_records($tbl_sponsor, array('courseid' => $courseid));

    if ($getdb != null) {
        $new = new stdClass;
        $new->coursetitle = $getdb->coursetitle;
        $new->lecturer = $getdb->lecturer;
        $new->sponsor = $getdb->sponsor;
        $new->overviewimage = $getdb->overviewimage;
        $new->detailimage = $getdb->detailimage;
        $new->meta2 = $getdb->meta2;
        $new->noindexcourse = $getdb->noindexcourse;
        $new->meta6 = $getdb->meta6;
        $new->courselanguage = $getdb->courselanguage;
        $new->meta4 = $getdb->meta4;
        $new->meta5 = $getdb->meta5;
        $new->teasertext['text'] = $getdb->teasertext;
        $new->targetgroup['text'] = $getdb->targetgroup;
        $new->learninggoals['text'] = $getdb->learninggoals;
        $new->structure['text'] = $getdb->structure;
        $new->additional_lecturer = '0';
        $new->additional_sponsor = '0'; //test
        $new->certificateofachievement['text'] = $getdb->certificateofachievement;
        $new->license = $getdb->license;
        $new->videocode = $getdb->videocode;
        $new->tags = $getdb->tags;

        if (!empty($getlect)) {

            foreach ($getlect as $lec) {
                if (strpos($lec->name, 'detailslecturer_editor') !== false) {
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
           
		$params = array('component' => 'local_isymeta', 'contextid' => $coursecontext->id, 'filename' => '.');
        
		$files = $DB->get_records_sql($sql, $params);

		foreach ($files as $file) {
			$draftitemid = file_get_submitted_draft_itemid($file->filearea);
			file_prepare_draft_area($draftitemid, $coursecontext->id, 'local_isymeta', $file->filearea, 0);
			$lectname = $file->filearea;
			$new->$lectname = $draftitemid;
        }
        
        if (!empty($getspons)) {
            foreach ($getspons as $spons) {
                if (strpos($spons->name, 'detailssponsor_link') !== false) {
                    $key = $spons->name;
                    $new->$key = $spons->value;
                } else {
                    $key = $spons->name;
                    $new->$key = $spons->value;
                }
            }
        }
		
		$sql2 = 'SELECT filearea 
					    FROM {files} 
					 WHERE component = :component 
					      AND contextid = :contextid 
						  AND filename != :filename 
						  AND itemid = 0';
		$params2 = array('component' => 'local_isymeta', 'contextid' => $coursecontext->id, 'filename' => '.');
		$files2 = $DB->get_records_sql($sql2, $params2);

		foreach ($files2 as $file) {
			$draftitemid = file_get_submitted_draft_itemid($file->filearea);
			file_prepare_draft_area($draftitemid, $coursecontext->id, 'local_isymeta', $file->filearea, 0);
			$sponsname = $file->filearea;
			$new->$sponsname = $draftitemid;
           
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
        $new->meta2 = 0;
        $new->meta6 = 0;
        $new->courselanguage = 0;
        $new->meta4 = 0;
        $new->meta5 = 0;
        $new->teasertext = '';
        $new->targetgroup = '';
        $new->learninggoals = '';
        $new->structure = '';
        $new->detailslecturer = 2;
        $new->detailssponsor = 2;
        $new->detailsmorelecturer = '';
        $new->detailslecturerimage = '';
        $new->detailsmoresponsor = '';
        $new->detailssponsorimage = '';
        $new->additional_lecturer = 2;
        $new->additional_sponsor = 2;
        $new->certificateofachievement = '';
        $new->license = 0;
        $new->videocode = '';
        $new->tags = '';

        
        $DB->insert_record($tbl, $new);
    }

    echo $OUTPUT->header();
    $toform = ['additional_lecturer' => 2, 'additional_sponsor' => 2];
    $mform->display();

    echo $OUTPUT->footer();
}
