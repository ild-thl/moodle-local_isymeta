<?php

require_once('../../../config.php');
require_once('../lib.php');
require_once('isymeta_form.php');

defined('MOODLE_INTERNAL') || die();

$courseid = optional_param('courseid', array(), PARAM_INT);
$coursecontext = context_course::instance($courseid);
$context = context_system::instance();
$tbl = 'isymeta';
$record = $DB->get_record($tbl, ['courseid' => $courseid]);
$url = new moodle_url('/local/isymeta/pages/isymeta.php'  . '?courseid=' . $courseid);



if (!has_capability('local/isymeta:allowaccess', $coursecontext)) redirect(new moodle_url('/')); // prevent access for guest/students
require_login();

$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title(get_string('title', 'local_isymeta'));
$PAGE->set_heading(get_string('heading', 'local_isymeta'));

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

$tbl_sponsor = 'isymeta_sponsors';
$records_spons = $DB->get_records($tbl_sponsor, array('courseid' => $courseid)); //todo

if (isset($record->detailssponsor)) {
    $max_sponsor = $record->detailssponsor;
} else {
    $max_sponsor = 2;
}


$tbl_lecturer = 'isymeta_additional';
$records_lect = $DB->get_records($tbl_lecturer, array('courseid' => $courseid));


if (isset($record->detailslecturer)) {
    $max_lecturer = $record->detailslecturer;
} else {
    $max_lecturer = 2;
}



$customdata = [
    'filemanageropts' => $filemanageropts,
    'editoropts' => $editoropts,
    'courseid' => $courseid,
    'sponsor' => $records_spons,
    'max_sponsor' => $max_sponsor,
    'max_lecturer' => $max_lecturer,
    'lecturer' => $records_lect,
];

$mform = new isymeta_form($url . '?courseid=' . $courseid, $customdata);
$itemid = 0;

if ($mform->is_cancelled()) {

    redirect(new moodle_url('/'));

} else if ($fromform = $mform->get_data()) {

    $todb = new stdClass;

    // Essentials and tile metas
    $todb->courseid         = $courseid;
    $todb->noindexcourse    = $fromform->noindexcourse;
    $todb->coursetitle      = $fromform->coursetitle;
    $todb->supervised       = $fromform->supervised;
    $todb->license          = $fromform->license;
    
    $draftitemid = file_get_submitted_draft_itemid('overviewimage');
    file_prepare_draft_area($draftitemid, $coursecontext->id, 'local_isymeta', 'overviewimage', $draftitemid);
    file_save_draft_area_files($fromform->overviewimage, $coursecontext->id, 'local_isymeta', 'overviewimage', 0);
    $todb->overviewimage    = $draftitemid;

    $draftitemid_detailimage = file_get_submitted_draft_itemid('detailimage');
    file_prepare_draft_area($draftitemid_detailimage, $coursecontext->id, 'local_isymeta', 'detailimage', $draftitemid_detailimage);
    file_save_draft_area_files($fromform->detailimage, $coursecontext->id, 'local_isymeta', 'detailimage', 0);
    $todb->detailimage    = $draftitemid_detailimage;

    $todb->meta1            = $fromform->meta1;
    $todb->meta2            = $fromform->meta2;
    $todb->meta3            = $fromform->meta3;
    $todb->meta4            = $fromform->meta4;
    $todb->meta5            = $fromform->meta5;
    $todb->meta6            = $fromform->meta6;
    
    // Course detail page metas
    $todb->tags             = $fromform->tags; 
    $todb->videocode        = $fromform->videocode; 
    $todb->teasertext       = $fromform->teasertext['text'];
    $todb->targetgroup      = $fromform->targetgroup['text'];
    $todb->learninggoals    = $fromform->learninggoals['text'];
    $todb->structure        = $fromform->structure['text']; 
    $todb->certificateofachievement= $fromform->certificateofachievement['text']; 






    if (!$record) {
        $DB->insert_record($tbl, $todb);
        // redirect($url, 'Metas erfolgreich gespeichert.', null, \core\output\notification::NOTIFY_SUCCESS);
    } else {
        $todb->id = $record->id;
        $DB->update_record($tbl, $todb);
        // redirect($url, 'Metas erfolgreich aktualisiert.', null, \core\output\notification::NOTIFY_SUCCESS);
    }

    // Lecturer
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
    
// Get lecturer editor + filemanager
$lecturer = new stdClass();

foreach ($fromform as $key => $value) {

    if (strpos($key, 'lecturer_type') !== false) {
        
        $lecturer->$key = $fromform->$key;
    }
    if (strpos($key, 'detailslecturer_image') !== false) {
        $lecturer->$key = $fromform->$key;

        $draftlecturer = file_get_submitted_draft_itemid($key);
        file_prepare_draft_area($draftlecturer, $coursecontext->id, 'local_isymeta', $key, 0);
        file_save_draft_area_files($draftlecturer, $coursecontext->id, 'local_isymeta', $key, 0);
    }
    if (strpos($key, 'detailslecturer_editor') !== false) {
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





    // Sponsor
    if ($fromform->additional_sponsor > 0) {
        $addspons = new stdClass();
        $addspons->id = $record->id;
        $addspons->detailssponsor = $fromform->additional_sponsor + $record->detailssponsor;
        $DB->update_record($tbl, $addspons);

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










    redirect($url, 'Daten erfolgreich gespeichert', null, \core\output\notification::NOTIFY_SUCCESS);

} else {

    $record = $DB->get_record($tbl, array('courseid' => $courseid));
    $getspons = $DB->get_records($tbl_sponsor, array('courseid' => $courseid));
    $getlect = $DB->get_records($tbl_lecturer, array('courseid' => $courseid));

    // prefill forms from db
    if ($record != null) {

        $new = new stdClass;

        // Essentials and tile metas
        $new->noindexcourse = $record->noindexcourse;
        $new->coursetitle   = $record->coursetitle;
        $new->supervised    = $record->supervised;
        $new->license       = $record->license;
        $new->overviewimage = $record->overviewimage;
        $new->detailimage   = $record->detailimage;

        $new->meta1         = $record->meta1;
        $new->meta2         = $record->meta2;
        $new->meta3         = $record->meta3;
        $new->meta4         = $record->meta4;
        $new->meta5         = $record->meta5;
        $new->meta6         = $record->meta6;

        // Course detail page metas
        $new->tags = $record->tags; 
        $new->videocode = $record->videocode; 
        $new->teasertext['text']    = $record->teasertext;
        $new->targetgroup['text']   = $record->targetgroup;
        $new->learninggoals['text'] = $record->learninggoals; 
        $new->structure['text'] = $record->structure; 
        $new->certificateofachievement['text'] = $record->certificateofachievement; 


        // Lecturers
        

		// Sponsors
        // $new->sponsor = $record->sponsor;
        $new->additional_sponsor = '0'; //test

        // Sponsors
        $new->detailssponsor = 2;
        // $new->detailssponsorimage = '';
        $new->detailsmoresponsor = '';
        // $new->additional_sponsor = 2;

        // $getspons = $DB->get_records($tbl_sponsor, array('courseid' => $courseid));
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
		
        $sql2 = 'SELECT filearea FROM {files} WHERE component = :component AND contextid = :contextid AND filename != :filename AND itemid = 0';
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

        // Essentials and tile metas
        $new->courseid      = $courseid;
        $new->noindexcourse = 1;
        $new->coursetitle   = $DB->get_record('course', array('id' => $courseid))->fullname;
        $new->supervised    = 0;
        // $new->lecturer      = '';
        $new->license       = 0;
        $new->overviewimage = '0';
        $new->detailimage = '0';

        $new->meta1         = 0;
        $new->meta2         = 0; 
        $new->meta3         = '';
        $new->meta4         = ''; 
        $new->meta5         = 0;
        $new->meta6         = 0;

        // Course detail page metas
        $new->tags = '';
        $new->videocode = '';
        $new->teasertext    = '';
        $new->targetgroup   = '';
        $new->learninggoals = '';
        $new->structure = '';
        $new->certificateofachievement = '';

        // Sponsors
        // $new->sponsor = 0;
        $new->detailssponsor = 2;
        $new->detailsmoresponsor = '';
        $new->detailssponsorimage = '';
        $new->additional_sponsor = 2;

        $new->detailslecturer = 2;
        $new->detailsmorelecturer = '';
        $new->detailslecturerimage = '';
        $new->additional_lecturer = 2;

        $DB->insert_record($tbl, $new);
    }

    echo $OUTPUT->header();

    $toform = ['additional_sponsor' => 2, 'additional_lecturer' => 2];
    // $mform->set_data($new);
    $mform->display();

    echo $OUTPUT->footer();
}

