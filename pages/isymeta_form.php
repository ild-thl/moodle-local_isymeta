<?php

defined('MOODLE_INTERNAL') || die();

require_once "$CFG->libdir/formslib.php";
require_once '../../../blocks/isymetaselect/classes/metastring.php';
require_once '../../../blocks/isymetaselect/classes/metaselection.php';

/*

*/

class isymeta_form extends moodleform
{
    function definition()
    {
        global $CFG, $DB;

        $metastring = new Metastring(); // helper class for i8n strings
        $metaselection = new Metaselection(); // helper class for selection form

        $mform = $this->_form;
        $courseid = $this->_customdata['courseid'];
        $filemanageropts = $this->_customdata['filemanageropts'];
        $editoropts = $this->_customdata['editoropts'];

        // $lecturer_editor = $this->_customdata['lecturer'];
        //$sponsor = $this->_customdata['sponsor'];
        // $max_lecturer = $this->_customdata['max_lecturer'];
        // $max_sponsor = $this->_customdata['max_sponsor'];
        

        /*
            Form elements for overall metas
        */

        $mform->addElement('header', 'essentialsheader', 'Allgemeine Daten');

        // Kurstitel
        $mform->addElement('static', 'coursetitle_text', '', 'Originaltitel des Kurses: <strong>' . $DB->get_record('course', array('id' => $courseid))->fullname . '</strong>');
        $mform->addElement('text', 'coursetitle', get_string('coursetitle', 'local_isymeta'));
        $mform->setType('coursetitle', PARAM_TEXT);

        // Kurstyp
        $mform->addElement('select', 'supervised', get_string('supervised', 'local_isymeta'), array(get_string('supervised_no', 'local_isymeta'), get_string('supervised_yes', 'local_isymeta')));
        $mform->setType('supervised', PARAM_RAW);

        // Lizenz
        $licenses = $DB->get_records('license');
        $licenses_arr = [];
        
        foreach ($licenses as $license) {
            $licenses_arr[] = $license->shortname;
        }

        $mform->addElement('select', 'license', get_string('license', 'local_isymeta'), $licenses_arr);
        $mform->setType('license', PARAM_RAW);

        // Indexierung
        $mform->addElement('select', 'noindexcourse', get_string('showtile', 'local_isymeta'), array(get_string('noindexcourse_yes', 'local_isymeta'), get_string('noindexcourse_no', 'local_isymeta')));
        $mform->setType('noindexcourse', PARAM_RAW);

        /*
            Form elements for tile metas
        */

        $mform->addElement('header', 'tilecontentheader', 'Kachelinhalt');
        
        // Tile Image
        $mform->addElement('filemanager', 'overviewimage', get_string('overviewimage', 'local_isymeta'), null, $filemanageropts);

        // Meta 1 (Default: Zielgruppe)
        $mform->addElement('select', 'meta1', $metastring->get(0), $metaselection->get_meta(1));
        $mform->setType('meta1', PARAM_RAW);

        // Meta 2 (Default: Programm)
        $mform->addElement('select', 'meta2', $metastring->get(1), $metaselection->get_meta(2));
        $mform->setType('meta2', PARAM_RAW);

        // Meta 3 (Default: Autor/in)
        $mform->addElement('text', 'meta3', $metastring->get(2));
        $mform->setType('meta3', PARAM_TEXT);

        // Meta 4 (Default: Arbeitsaufwand)
        $mform->addElement('text', 'meta4', $metastring->get(3));
        $mform->setType('meta4', PARAM_TEXT);

        // Meta 5 (Default: Kursbeginn)
        $mform->addElement('date_selector', 'meta5', $metastring->get(4));

        // Meta 6 (Default: Kursbeginn)
        $mform->addElement('select', 'meta6', $metastring->get(5), $metaselection->get_meta(6));
        
        /*
            Form elements for course detail page
        */

        $mform->addElement('header', 'coursedetailheader', 'Kursdetailseite');

        // Schlagwörter
        $mform->addElement('text', 'tags', get_string('tags', 'local_isymeta'));
        $mform->setType('tags', PARAM_TEXT);

        // Videocode
        $mform->addElement('text', 'videocode', get_string('videocode', 'local_isymeta'));
        $mform->setType('videocode', PARAM_TEXT);

        // Detailbild
        $mform->addElement('filemanager', 'detailimage', get_string('detailimage', 'local_isymeta'), null, $filemanageropts);

        // "Was erwartet Dich in diesem Kurs?"
        $mform->addElement('editor', 'teasertext', get_string('teasertext', 'local_isymeta'));
        $mform->setType('teasertext', PARAM_RAW);

        // "Zielgruppe"
        $mform->addElement('editor', 'targetgroup', get_string('targetgroup', 'local_isymeta'));
        $mform->setType('targetgroup', PARAM_RAW);

        // "Was kannst Du in diesem Kurs lernen?"
        $mform->addElement('editor', 'learninggoals', get_string('learninggoals', 'local_isymeta'));
        $mform->setType('learninggoals', PARAM_RAW);

        // "Gliederung"
        $mform->addElement('editor', 'structure', get_string('structure', 'local_isymeta'));
        $mform->setType('structure', PARAM_RAW);

        // "Teilnahmebestätigung"
        $mform->addElement('editor', 'certificateofachievement', get_string('certificateofachievement', 'local_isymeta'));
        $mform->setType('certificateofachievement', PARAM_RAW);



        
        /*
            Sponsors
        */

        $mform->addElement('header', 'sponsorheader', 'Förderung');

        // $mform->addElement('html', '<h2>Förderung</h2>');
        $max_sponsor = $this->_customdata['max_sponsor'];
        $sponsor = $this->_customdata['sponsor'];
        $i = 1;
        
        if (empty($sponsor)) {

            while ($i <= $max_sponsor) {
                // $mform->addElement('text', 'detailssponsor_link_' . $i, get_string('sponsor_link', 'local_isymeta'));

                // Bild
                $mform->addElement('filemanager', 'detailssponsor_image_' . $i, get_string('sponsor_image', 'local_isymeta'), null, $filemanageropts);

                // Details
                $mform->addElement('text', 'detailssponsor_link_' . $i, get_string('sponsor_link', 'local_isymeta'));
                $mform->setType('detailssponsor_link_' . $i, PARAM_RAW);
                // $mform->setType('detailssponsor_link_'.$i, PARAM_RAW);
                // $mform->setType('detailssponsor_link', PARAM_TEXT);

                $url = new moodle_url('/local/isymeta/pages/isymeta_delete_sponsor.php', array('courseid' => $courseid, 'id' => $i));

                $mform->addElement('html', html_writer::link($url, 'Eingabefeld entfernen'));

                $mform->addElement('html', '<hr>');

                $i++;
            }
        } else {

            foreach($sponsor as $spons) {

                if(strpos($spons->name, 'image')) {
                    // Bild
                    $mform->addElement('filemanager', $spons->name, get_string('sponsor_image', 'local_isymeta'), null, $filemanageropts);
                }
                if(strpos($spons->name, 'link')) {

                    // Details
                    $mform->addElement('text', $spons->name, get_string('sponsor_link', 'local_isymeta'));
                    $mform->setType($spons->name, PARAM_RAW);
                    // $mform->setType($spons->name, PARAM_RAW);
                    
                    $id = substr($spons->name, -1);
                    $url = new moodle_url('/local/isymeta/pages/isymeta_delete_sponsor.php', array('courseid' => $courseid, 'id' => $id));
                    $mform->addElement('html', html_writer::link($url, 'Eingabefeld entfernen'));
                    $mform->addElement('html', '<hr>');

                    $i++;
                }
            }
        }

        $mform->addElement('text', 'additional_sponsor', 'weitere...');
        $mform->setDefault('additional_sponsor', 0);
        $mform->setType('additional_sponsor', PARAM_INT);
        $mform->addRule('additional_sponsor', 'Bitte eine Zahl angeben', 'numeric', '', 'client');
        $this->add_action_buttons($cancel = false, $submitlabel = 'Förderer hinzufügen');
        
         /*
            Lecturers
        */
        $mform->addElement('header', 'lecturerheader', 'Lektoren');
        $mform->addElement('html', '<h2>Angaben zu Autoren*innen und Anbieter*innen</h2>');

        $max_lecturer = $this->_customdata['max_lecturer'];
        $lecturer = $this->_customdata['lecturer'];
        $i = 1;
        
        // above $i will be used here!
        if (empty($lecturer)) {
            
            while ($i <= $max_lecturer) {

                // Anbieter*innen / Autor*innen
                $radioarray = array();
                $radioarray[] = $mform->createElement('radio', 'lecturer_type_' . $i, '', get_string('lecturer_type_0', 'local_isymeta'), 0);
                $radioarray[] = $mform->createElement('radio', 'lecturer_type_' . $i, '', get_string('lecturer_type_1', 'local_isymeta'), 1);
                $mform->addGroup($radioarray, 'radioar', get_string('lecturer_type', 'local_isymeta'), array(' '), false);
                if ($i > 1) {
                    $mform->setDefault('lecturer_type_' . $i, 1);
                }

                // Bild Anbieter*innen / Autor*innen
                $mform->addElement('filemanager', 'detailslecturer_image_' . $i, get_string('detailslecturer_image', 'local_isymeta'), null, $filemanageropts);

                // Details Anbieter*innen / Autor*innen
                $mform->addElement('editor', 'detailslecturer_editor_' . $i, get_string('detailslecturer', 'local_isymeta'), null, $editoropts);
                $mform->setType('detailslecturer_editor_' . $i, PARAM_RAW);

                $url = new moodle_url('/local/isymeta/pages/isymeta_delete_lecturer.php', array('courseid' => $courseid, 'id' => $i));

                $mform->addElement('html', html_writer::link($url, 'Eingabefeld entfernen'));

                $mform->addElement('html', '<h>');

                $i++;
            }
        } else {
            foreach($lecturer as $lect) {
                
                if(strpos($lect->name, 'type')) {
                    // Anbieter*innen / Autor*innen
                    $radioarray = array();
                    $radioarray[] = $mform->createElement('radio', $lect->name, '', get_string('lecturer_type_0', 'local_isymeta'), 0);
                    $radioarray[] = $mform->createElement('radio', $lect->name, '', get_string('lecturer_type_1', 'local_isymeta'), 1);
                    $mform->addGroup($radioarray, 'radioar', get_string('lecturer_type', 'local_isymeta'), array(' '), false);
                    if ($i > 1) {
                        $mform->setDefault($lect->name, 1);
                    }
                }
                if(strpos($lect->name, 'image')) {
                    
                    // Bild Anbieter*innen / Autor*innen
                    $mform->addElement('filemanager', $lect->name, get_string('detailslecturer_image', 'local_isymeta'), null, $filemanageropts);
                }
                if(strpos($lect->name, 'editor')) {
                    // Details Anbieter*innen / Autor*innen

                    $mform->addElement('editor', $lect->name, get_string('detailslecturer', 'local_isymeta'), null, $editoropts);
                    $mform->setType($lect->name, PARAM_RAW);

                    $id = substr($lect->name, -1);
                    $url = new moodle_url('/local/isymeta/pages/isymeta_delete_lecturer.php', array('courseid' => $courseid, 'id' => $id));
                    $mform->addElement('html', html_writer::link($url, 'Eingabefeld entfernen'));
                    $mform->addElement('html', '<h>');

                    $i++;
                }
            }
        }

        $mform->addElement('text', 'additional_lecturer', 'weitere...');
        $mform->setDefault('additional_lecturer', 0);
        $mform->setType('additional_lecturer', PARAM_INT);
        $mform->addRule('additional_lecturer', 'Bitte eine Zahl angeben', 'numeric', '', 'client');
        $this->add_action_buttons($cancel = false, $submitlabel = 'Autoren*innen oder Anbieter*innen hinzufügen');

        $mform->addElement('html', '<hr>');

        $this->add_action_buttons();





        
    }

    function validation($data, $files)
    {
        return array();
    }
	function data_preprocessing(&$default_values) {
		$lecturer = $this->_customdata['lecturer'];
	
		if ($this->current->instance) {
			foreach ($lecturer as $lect) {
				$draftitemid = file_get_submitted_draft_itemid($lect->name);
				$context = context_course::instance($this->_customdata['courseid']);
				file_prepare_draft_area($draftitemid, $context->id, 'local_ildmeta', $lect->name, 0);
				$default_values[$lect->name] = $draftitemid;
			}
		}
        
        $sponsor = $this->_customdata['sponsor'];
		
		if ($this->current->instance) {
			foreach ($sponsor as $spons) {
				$draftitemid = file_get_submitted_draft_itemid($spons->name);
				$context = context_course::instance($this->_customdata['courseid']);
				file_prepare_draft_area($draftitemid, $context->id, 'local_ildmeta', $spons->name, 0);
				$default_values[$spons->name] = $draftitemid;
			}
		}

		// TODO overviewimage nicht vergessen

	}

}
