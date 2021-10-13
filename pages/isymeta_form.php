<?php

//defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class isymeta_form extends moodleform
{
    function definition()
    {
        global $CFG, $DB;
        $mform = $this->_form; // Don't forget the underscore!

        $lecturer_editor = $this->_customdata['lecturer'];
        $sponsor = $this->_customdata['sponsor'];
        $max_lecturer = $this->_customdata['max_lecturer'];
        $max_sponsor = $this->_customdata['max_sponsor'];
        $courseid = $this->_customdata['courseid'];
        $filemanageropts = $this->_customdata['filemanageropts'];
        $editoropts = $this->_customdata['editoropts'];

        $lang_list = [
            'Deutsch',
            'Englisch'
        ];

        //$context  = context_module::instance($_GET['courseid']);

        $mform->addElement('html', '<h2>Meta: Übersichtsseite</h2>');

        $mform->addElement('html', '<h2>Meta-Kachelinhalt</h2>');

        // Indexierung
        $context = context_system::instance();

        if (has_capability('local/isymeta:indexation', $context)) {
            $mform->addElement('select', 'noindexcourse', get_string('noindexcourse', 'local_isymeta'), array(get_string('noindexcourse_yes', 'local_isymeta'), get_string('noindexcourse_no', 'local_isymeta'), get_string('noindexcourse_limited', 'local_isymeta')));
            $mform->setType('index', PARAM_RAW);
        }

        // Anbietende Unis
        $universities = $DB->get_record('user_info_field', array('shortname' => 'isymeta_de_targetgroups'));

        $select = $mform->addElement('select', 'meta2', get_string('meta2', 'local_isymeta'), explode("\n", $universities->param1));
        $mform->setType('meta2', PARAM_RAW);
        $select->setMultiple(true);
        $mform->addElement('static', 'text_meta2', '', get_string('text_meta2', 'local_isymeta'));

        // Fachbereich/Wissensgebiet
        $meta6s = $DB->get_record('user_info_field', array('shortname' => 'isymeta_de_formats'));

        $mform->addElement('select', 'meta6', get_string('meta6', 'local_isymeta'), explode("\n", $meta6s->param1));
        $mform->setType('meta6', PARAM_RAW);
        $mform->addElement('static', 'text_meta6', '', get_string('text_meta6', 'local_isymeta'));

        // Übersichtsbild
        $mform->addElement('filemanager', 'overviewimage', get_string('overviewimage', 'local_isymeta'), null, $filemanageropts);

        // Detailbild
        $mform->addElement('filemanager', 'detailimage', get_string('detailimage', 'local_isymeta'), null, $filemanageropts);

        // Videocode
        $mform->addElement('text', 'videocode', get_string('videocode', 'local_isymeta'));
        $mform->setType('videocode', PARAM_TEXT);

        // Kurstitel
        $mform->addElement('text', 'coursetitle', get_string('coursetitle', 'local_isymeta'));
        $mform->setType('coursetitle', PARAM_TEXT);

        // Dozent
        $mform->addElement('text', 'lecturer', get_string('lecturer', 'local_isymeta'));
        $mform->setType('lecturer', PARAM_TEXT);

        // Kurssprache
        $mform->addElement('select', 'courselanguage', get_string('courselanguage', 'local_isymeta'), $lang_list);
        $mform->setType('courselanguage', PARAM_RAW);

        // Bearbeitungszeit in Stunden
        $mform->addElement('text', 'meta4', get_string('meta4', 'local_isymeta'));
        $mform->setType('meta4', PARAM_TEXT);
        // $mform->addRule('meta4', get_string('text_meta4', 'local_isymeta'), 'numeric');
        // $mform->addElement('static', 'text_meta4', '', get_string('text_meta4', 'local_isymeta'));

        // Startzeit
        $mform->addElement('date_selector', 'meta5', get_string('meta5', 'local_isymeta'));

        $mform->addElement('html', '<h2>Meta: Detailseite</h2>');

        // Teasertext
        $mform->addElement('editor', 'teasertext', get_string('teasertext', 'local_isymeta'));
        $mform->setType('teasertext', PARAM_RAW);
        //  $mform->setDefault('teasertext', array('text'=>''));

        // Zielgruppe
        $mform->addElement('editor', 'targetgroup', get_string('targetgroup', 'local_isymeta'));
        $mform->setType('targetgroup', PARAM_RAW);
        //  $mform->setDefault('targetgroup', array('text'=>''));

        // Lernziele
        $mform->addElement('editor', 'learninggoals', get_string('learninggoals', 'local_isymeta'));
        $mform->setType('learninggoals', PARAM_RAW);

        // Gliederung
        $mform->addElement('editor', 'structure', get_string('structure', 'local_isymeta'));
        $mform->setType('structure', PARAM_RAW);


                 /*
                 * We need editor + filemanager for each lecturer.
                 * The data will be stored in the new table "mdl_isymeta_additional" with "courseid", "name" and "value".
                 * ??? SURE ??? The "name" will be saved as reference in the table "mdl_isymeta".
                 * Each record will be selected by "courseid" and "name"
                 */


        $mform->addElement('html', '<h2>Angaben zu Autoren*innen und Anbieter*innen</h2>');
        $i = 1;
        
        // above $i will be used here!
        if (empty($lecturer_editor)) {
            
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
                $mform->setType('detailslecturer_editor', PARAM_RAW);

                $url = new moodle_url('/local/isymeta/pages/isymeta_delete_lecturer.php', array('courseid' => $courseid, 'id' => $i));

                $mform->addElement('html', html_writer::link($url, 'Eingabefeld entfernen'));

                $mform->addElement('html', '<h>');

                $i++;
            }
        } else {
            foreach($lecturer_editor as $lect) {
                
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
                    $mform->setType('detailslecturer_editor', PARAM_RAW);

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














        $mform->addElement('html', '<h2>Förderung</h2>');
        $i = 1;
        
        // above $i will be used here!
        if (empty($sponsor)) {
//  print_r($sponsor); die();

            while ($i <= $max_sponsor) {

                // Bild Fördernde
                $mform->addElement('filemanager', 'detailssponsor_image_' . $i, get_string('sponsor_image', 'local_isymeta'), null, $filemanageropts);

                // Details Anbieter*innen / Autor*innen
                $mform->addElement('text', 'detailssponsor_link_' . $i, get_string('sponsor_link', 'local_isymeta'));
                // $mform->setType('detailssponsor_link_'.$i, PARAM_TEXT);
                // $mform->addElement('editor', 'sponsor_link_' . $i, get_string('sponsor_link', 'local_isymeta'), null, $editoropts);
                $mform->setType('detailssponsor_link', PARAM_TEXT);

                $url = new moodle_url('/local/isymeta/pages/isymeta_delete_sponsor.php', array('courseid' => $courseid, 'id' => $i));

                $mform->addElement('html', html_writer::link($url, 'Eingabefeld entfernen'));

                $mform->addElement('html', '<h>');

                $i++;
            }
        } else {

            foreach($sponsor as $spons) {
// print_r($spons); die();
                if(strpos($spons->name, 'image')) {
                    // Bild Anbieter*innen / Autor*innen
                    $mform->addElement('filemanager', $spons->name, get_string('sponsor_image', 'local_isymeta'), null, $filemanageropts);
                }
                if(strpos($spons->name, 'link')) {
                    // Details Anbieter*innen / Autor*innen

                    // print_r($spons); die();

                    $mform->addElement('text', $spons->name, get_string('sponsor_link', 'local_isymeta'));
                    $mform->setType('detailssponsor_link', PARAM_TEXT);

                    $id = substr($spons->name, -1);
                    $url = new moodle_url('/local/isymeta/pages/isymeta_delete_sponsor.php', array('courseid' => $courseid, 'id' => $id));
                    $mform->addElement('html', html_writer::link($url, 'Eingabefeld entfernen'));
                    $mform->addElement('html', '<h>');

                    $i++;
                }
            }
        }
    


        $mform->addElement('text', 'additional_sponsor', 'weitere...');
        $mform->setDefault('additional_sponsor', 0);
        $mform->setType('additional_sponsor', PARAM_INT);
        $mform->addRule('additional_sponsor', 'Bitte eine Zahl angeben', 'numeric', '', 'client');
        $this->add_action_buttons($cancel = false, $submitlabel = 'Förderer hinzufügen');

        $mform->addElement('html', '<hr>');









        $mform->addElement('html', '<h2>Weitere Informationen</h2>');

        $licenses = $DB->get_records('license');
        $licenses_arr = [];

        foreach ($licenses as $license) {
            $licenses_arr[] = $license->shortname;
        }

        $mform->addElement('select', 'license', get_string('license', 'local_isymeta'), $licenses_arr);
        $mform->setType('license', PARAM_RAW);

        // Leistungsnachweis
        $mform->addElement('editor', 'certificateofachievement', get_string('certificateofachievement', 'local_isymeta'));
        $mform->setType('certificateofachievement', PARAM_RAW);


        // Schlagwörter
        $mform->addElement('text', 'tags', get_string('tags', 'local_isymeta'));
        $mform->setType('tags', PARAM_TEXT);

        $this->add_action_buttons();
    }

    function validation($data, $files)
    {
        return array();
    }
	// Funktioniert hier nicht. Falsche Stelle
	function data_preprocessing(&$default_values) {
		$lecturer_editor = $this->_customdata['lecturer'];
	
		if ($this->current->instance) {
			foreach ($lecturer_editor as $lect) {
				$draftitemid = file_get_submitted_draft_itemid($lect->name);
				$context = context_course::instance($this->_customdata['courseid']);
				file_prepare_draft_area($draftitemid, $context->id, 'local_isymeta', $lect->name, 0);
				$default_values[$lect->name] = $draftitemid;
			}
		}
        
        $sponsor = $this->_customdata['sponsor'];
		
		if ($this->current->instance) {
			foreach ($sponsor as $spons) {
				$draftitemid = file_get_submitted_draft_itemid($spons->name);
				$context = context_course::instance($this->_customdata['courseid']);
				file_prepare_draft_area($draftitemid, $context->id, 'local_isymeta', $spons->name, 0);
				$default_values[$spons->name] = $draftitemid;
			}
		}

		// TODO overviewimage nicht vergessen

	}


}
