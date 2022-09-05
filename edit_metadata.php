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
 * Page that shows a form to manage and set additional metadata dor a course.
 *
 * @package     local_ildmeta
 * @copyright   2022 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once('lib.php');

use local_ildmeta\output\form\edit_metadata_form;

$id = required_param('id', PARAM_INT);
$coursecontext = context_course::instance($id);

// Check capabilities.
if (!has_capability('local/ildmeta:allowaccess', $coursecontext)) {
    redirect(new moodle_url('/'));
}
// User has to be logged in.
require_login($id, false);


$url = new moodle_url('/local/ildmeta/edit_metadata.php', array('id' => $id));

$tbl = 'ildmeta';

// Dozenten Bilder.
$tbllecturer = 'ildmeta_additional';

$context = context_system::instance();

$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title(get_string('title', 'local_ildmeta'));
$PAGE->set_heading(get_string('heading', 'local_ildmeta'));

$record = $DB->get_record($tbl, ['courseid' => $id]);


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
    $maxlecturer = $record->detailslecturer;
} else {
    $maxlecturer = 2;
}

$recordslect = $DB->get_records($tbllecturer, array('courseid' => $id));

$customdata = array('filemanageropts' => $filemanageropts, 'editoropts' => $editoropts, 'max_lecturer' => $maxlecturer, 'courseid' => $id, 'lecturer' => $recordslect);

$mform = new edit_metadata_form($url . '?courseid=' . $id, $customdata);

if ($mform->is_cancelled()) {

    $redirectto = new moodle_url('/');
    redirect($redirectto);
} else if ($fromform = $mform->get_data()) {
    // First of all, check for additional lecturer fields.
    if ($fromform->additional_lecturer > 0) {
        $addlect = new stdClass();
        $addlect->id = $record->id;
        $addlect->detailslecturer = $fromform->additional_lecturer + $record->detailslecturer;
        $DB->update_record($tbl, $addlect);


        // Add empty fields in ildmeta_additional.
        // New logic required due to delete options...

        // Get last lecturer id.

        $recordlectlast = $DB->get_record_sql(
            "SELECT * FROM {ildmeta_additional} WHERE courseid = ? ORDER BY id DESC",
            array('courseid' => $id)
        );

        if (!empty($recordlectlast)) {
            $i = explode("_", $recordlectlast->name)[2] + 1;
        } else {
            $i = 1;
        }

        $maxi = ($i - 1) + $fromform->additional_lecturer;

        while ($i <= $maxi) {
            $str1 = "lecturer_type_" . $i;
            $str2 = "detailslecturer_image_" . $i;
            $str3 = "detailslecturer_editor_" . $i;

            $fields = array($str1, $str2, $str3);

            foreach ($fields as $f) {
                $ins = new stdClass();
                $ins->courseid = $id;
                $ins->name = $f;
                $ins->value = '';
                $DB->insert_record($tbllecturer, $ins);
            }
            $i++;
        }
        // If additional lecturer the user will be redirected to the edit_metadata.php for further editing.
        $url = new moodle_url('/local/ildmeta/edit_metadata.php', array('id' => $id));
    } else {
        // Otherweise he will be forwarded to the detailpage.php.
        $url = new moodle_url('/blocks/ildmetaselect/detailpage.php', array('id' => $id));
    }


    $todb = new stdClass;
    $todb->courseid = $id;
    $todb->coursetitle = $fromform->coursetitle;
    $todb->lecturer = $fromform->lecturer;
    if (isset($fromform->noindexcourse)) {
        $todb->noindexcourse = $fromform->noindexcourse;
    }
    if (isset($fromform->customoverviewimage) && $fromform->customoverviewimage) {
        if (isset($fromform->overviewimage)) {
            $draftitemid = file_get_submitted_draft_itemid('overviewimage');
            file_prepare_draft_area($draftitemid, $coursecontext->id, 'local_ildmeta', 'overviewimage', $draftitemid);
            file_save_draft_area_files($fromform->overviewimage, $coursecontext->id, 'local_ildmeta', 'overviewimage', 0);
            $todb->overviewimage = $fromform->overviewimage;
        }
    } else {
        $todb->overviewimage = null;
    }
    if (isset($fromform->detailimage)) {
        $draftitemid = file_get_submitted_draft_itemid('detailimage');
        file_prepare_draft_area($draftitemid, $coursecontext->id, 'local_ildmeta', 'detailimage', $draftitemid);
        file_save_draft_area_files($fromform->detailimage, $coursecontext->id, 'local_ildmeta', 'detailimage', 0);
        $todb->detailimage = $fromform->detailimage;
    }

    $todb->provider = $fromform->provider;
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
    if (isset($fromform->videolicense)) {
        $todb->videolicense = $fromform->videolicense;
    }

    $todb->tags = $fromform->tags;


    // Bird/DC properties.
    $todb->exporttobird = $fromform->exporttobird;
    if (isset($fromform->shortname)) {
        $todb->shortname = $fromform->shortname;
    }
    if (isset($fromform->abstract['text'])) {
        $todb->abstract = $fromform->abstract['text'];
    }
    if (isset($fromform->coursetype)) {
        $todb->coursetype = $fromform->coursetype;
    }
    if (isset($fromform->courseformat)) {
        $todb->courseformat = $fromform->courseformat;
    }
    if (isset($fromform->selfpaced)) {
        $todb->selfpaced = $fromform->selfpaced;
    }
    if (isset($fromform->audience)) {
        $todb->audience = $fromform->audience;
    }
    if (isset($fromform->courseprerequisites)) {
        $todb->courseprerequisites = $fromform->courseprerequisites['text'];
    }
    if (isset($fromform->availablefrom)) {
        $todb->availablefrom = $fromform->availablefrom;
    }
    if (isset($fromform->availableuntil)) {
        $todb->availableuntil = $fromform->availableuntil;
    }

    // If course is not in db yet.
    if (!$DB->get_record($tbl, array('courseid' => $id))) {

        // If noindexcourse in todb is not set.
        if (!isset($todb->noindexcourse)) {
            // Use the default value "no indexination".
            $todb->noindexcourse = 1;
        }
        $DB->insert_record($tbl, $todb);

        // If course is in db, update.
    } else {
        $primkey = $DB->get_record($tbl, array('courseid' => $id));

        $todb->id = $primkey->id;
        // If noindexcourse in todb is not set.
        if (!isset($todb->noindexcourse)) {
            // Use the old value from the db.
            $todb->noindexcourse = $primkey->noindexcourse;
        }
        $DB->update_record($tbl, $todb);
    }

    // Get lecturer editor + filemanager.

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
            $lecturer->$key = ($fromform->$key)['text'];
        }
    }

    foreach ($lecturer as $key => $value) {

        $lectodb = new stdClass();
        $lectodb->courseid = $id;
        $lectodb->name = $key;
        $lectodb->value = $value;


        if (!$DB->get_record($tbllecturer, array('name' => $lectodb->name, 'courseid' => $id))) {
            $DB->insert_record($tbllecturer, $lectodb);
        } else {

            $primkey = $DB->get_record($tbllecturer, array('courseid' => $id, 'name' => $lectodb->name));

            $lectodb->id = $primkey->id;

            $DB->update_record($tbllecturer, $lectodb);
        }
    }

    // Redirect to detailpage.
    redirect($url, 'Daten erfolgreich gespeichert', null, \core\output\notification::NOTIFY_SUCCESS);
} else {
    // Prefill forms from db.
    $getdb = $DB->get_record($tbl, array('courseid' => $id));

    $getlect = $DB->get_records($tbllecturer, array('courseid' => $id));


    if ($getdb != null) {
        $toform = new stdClass;
        $toform->coursetitle = $getdb->coursetitle;
        $toform->lecturer = $getdb->lecturer;
        if (isset($getdb->overviewimage) && !empty($getdb->overviewimage)) {
            $toform->customoverviewimage = 1;
        }
        $toform->overviewimage = $getdb->overviewimage;
        $toform->detailimage = $getdb->detailimage;
        $toform->provider = $getdb->provider;
        $toform->noindexcourse = $getdb->noindexcourse;
        $toform->subjectarea = $getdb->subjectarea;
        $toform->courselanguage = $getdb->courselanguage;
        $toform->processingtime = $getdb->processingtime;
        $toform->starttime = $getdb->starttime;
        $toform->teasertext['text'] = $getdb->teasertext;
        $toform->targetgroup['text'] = $getdb->targetgroup;
        $toform->learninggoals['text'] = $getdb->learninggoals;
        $toform->structure['text'] = $getdb->structure;
        $toform->additional_lecturer = '0';
        $toform->certificateofachievement['text'] = $getdb->certificateofachievement;
        $toform->license = $getdb->license;
        $toform->videocode = $getdb->videocode;
        $toform->videolicense = $getdb->videolicense;
        $toform->tags = $getdb->tags;

        // Bird/DC properties.
        $toform->shortname = $getdb->shortname;
        $toform->abstract['text'] = $getdb->abstract;
        $toform->exporttobird = $getdb->exporttobird;
        $toform->coursetype = $getdb->coursetype;
        $toform->courseformat = $getdb->courseformat;
        $toform->selfpaced = $getdb->selfpaced;
        $toform->audience = $getdb->audience;
        $toform->courseprerequisites['text'] = $getdb->courseprerequisites;
        $toform->availablefrom = $getdb->availablefrom;
        $toform->availableuntil = $getdb->availableuntil;


        if (!empty($getlect)) {
            foreach ($getlect as $lec) {
                if (strpos($lec->name, '_editor')) {
                    $key = $lec->name;
                    ($toform->$key)['text'] = $lec->value;
                } else {
                    $key = $lec->name;
                    $toform->$key = $lec->value;
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
        foreach ($files as $file) {
            $draftitemid = file_get_submitted_draft_itemid($file->filearea);
            file_prepare_draft_area($draftitemid, $coursecontext->id, 'local_ildmeta', $file->filearea, 0);
            $lectname = $file->filearea;
            $toform->$lectname = $draftitemid;
        }
    } else {

        $toform = new stdClass();
        $course = $DB->get_record('course', array('id' => $id), 'fullname, shortname, summary, startdate', MUST_EXIST);
        $sql = "SELECT t.name
                FROM mdl_tag_instance ti
                JOIN mdl_tag t
                ON ti.tagid = t.id
                WHERE ti.itemtype = 'course'
                AND ti.itemid = :courseid;";


        $toform->coursetitle = $course->fullname;
        $toform->teasertext['text'] = isset($course->summary) ? $course->summary : '';
        if (isset($course->startdate)) {
            $toform->starttime = $course->startdate;
        } else {
            $toform->starttime = 0;
        }

        $coursetags = $DB->get_records_sql($sql, array('courseid' => $id));
        if (isset($coursetags) && !empty($coursetags)) {
            $coursetags = array_map(fn ($a) => $a->name, $coursetags);
            $toform->tags = implode(' ', $coursetags);
        }

        $toform->courseid = $id;
        $toform->lecturer = '';
        $toform->noindexcourse = 0;
        $toform->overviewimage = null;
        $toform->detailimage = null;
        $toform->provider = 0;
        $toform->subjectarea = 0;
        $toform->courselanguage = 0;
        $toform->processingtime = 0;
        $toform->targetgroup = null;
        $toform->learninggoals = null;
        $toform->structure = null;
        $toform->detailslecturer = 2;
        $toform->detailsmorelecturer = null;
        $toform->detailslecturerimage = '';
        $toform->additional_lecturer = 2;
        $toform->certificateofachievement = null;
        $toform->license = 0;
        $toform->videocode = null;
        $toform->videolicense = null;

        // Bird/DC properties.
        $toform->exporttobird = 0;
        $toform->shortname = $course->shortname;
        $toform->coursetype = null;
        $toform->courseformat = null;
        $toform->selfpaced = 0;
        $toform->audience = null;
        $toform->courseprerequisites = null;
        $toform->availablefrom = null;
        $toform->availableuntil = null;

        $todb = clone $toform;
        $todb->teasertext = $toform->teasertext['text'];
        $toform->id = $DB->insert_record($tbl, $todb);
    }


    echo $OUTPUT->header();

    $mform->set_data($toform);
    $mform->display();

    echo $OUTPUT->footer();
}
