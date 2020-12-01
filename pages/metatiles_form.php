<?php

//defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class metatiles_form extends moodleform
{
    function definition()
    {
        global $CFG, $DB;
        $mform = $this->_form; // Don't forget the underscore!

        $lecturer = $this->_customdata['lecturer'];
        $max_lecturer = $this->_customdata['max_lecturer'];
        $courseid = $this->_customdata['courseid'];


        $filemanageropts = $this->_customdata['filemanageropts'];
        $editoropts = $this->_customdata['editoropts'];

        $lang_list = [
            'Deutsch',
            'Englisch'
        ];

        //$context  = context_module::instance($_GET['courseid']);

        $mform->addElement('html', '<h2>Meta: Übersichtsseite</h2>');

        // Indexierung
        $context = context_system::instance();

        if (has_capability('local/metatiles:indexation', $context)) {
            $mform->addElement('select', 'noindexcourse', get_string('noindexcourse', 'local_metatiles'), array(get_string('noindexcourse_yes', 'local_metatiles'), get_string('noindexcourse_no', 'local_metatiles'), get_string('noindexcourse_limited', 'local_metatiles')));
            $mform->setType('index', PARAM_RAW);
        }

        // Anbietende Unis
        $universities = $DB->get_record('user_info_field', array('shortname' => 'targetgroups'));

        $select = $mform->addElement('select', 'university', get_string('university', 'local_metatiles'), explode("\n", $universities->param1));
        $mform->setType('university', PARAM_RAW);
        $select->setMultiple(true);
        $mform->addElement('static', 'text_university', '', get_string('text_university', 'local_metatiles'));

        // Fachbereich/Wissensgebiet
        $subjectareas = $DB->get_record('user_info_field', array('shortname' => 'formats'));

        $mform->addElement('select', 'subjectarea', get_string('subjectarea', 'local_metatiles'), explode("\n", $subjectareas->param1));
        $mform->setType('subjectarea', PARAM_RAW);
        $mform->addElement('static', 'text_subjectarea', '', get_string('text_subjectarea', 'local_metatiles'));

        // Übersichtsbild
        $mform->addElement('filemanager', 'overviewimage', get_string('overviewimage', 'local_metatiles'), null, $filemanageropts);

        // Detailbild
        $mform->addElement('filemanager', 'detailimage', get_string('detailimage', 'local_metatiles'), null, $filemanageropts);

        // Videocode
        $mform->addElement('text', 'videocode', get_string('videocode', 'local_metatiles'));
        $mform->setType('videocode', PARAM_TEXT);

        // Kurstitel
        $mform->addElement('text', 'coursetitle', get_string('coursetitle', 'local_metatiles'));
        $mform->setType('coursetitle', PARAM_TEXT);

        // Dozent
        $mform->addElement('text', 'lecturer', get_string('lecturer', 'local_metatiles'));
        $mform->setType('lecturer', PARAM_TEXT);

        // Kurssprache
        $mform->addElement('select', 'courselanguage', get_string('courselanguage', 'local_metatiles'), $lang_list);
        $mform->setType('courselanguage', PARAM_RAW);

        // Bearbeitungszeit in Stunden
        $mform->addElement('text', 'processingtime', get_string('processingtime', 'local_metatiles'));
        $mform->setType('processingtime', PARAM_INT);
        $mform->addRule('processingtime', get_string('text_processingtime', 'local_metatiles'), 'numeric');
        $mform->addElement('static', 'text_processingtime', '', get_string('text_processingtime', 'local_metatiles'));

        // Startzeit
        $mform->addElement('date_selector', 'starttime', get_string('starttime', 'local_metatiles'));

        $mform->addElement('html', '<h2>Meta: Detailseite</h2>');

        // Teasertext
        $mform->addElement('editor', 'teasertext', get_string('teasertext', 'local_metatiles'));
        $mform->setType('teasertext', PARAM_RAW);
        //  $mform->setDefault('teasertext', array('text'=>''));

        // Zielgruppe
        $mform->addElement('editor', 'targetgroup', get_string('targetgroup', 'local_metatiles'));
        $mform->setType('targetgroup', PARAM_RAW);
        //  $mform->setDefault('targetgroup', array('text'=>''));

        // Lernziele
        $mform->addElement('editor', 'learninggoals', get_string('learninggoals', 'local_metatiles'));
        $mform->setType('learninggoals', PARAM_RAW);

        // Gliederung
        $mform->addElement('editor', 'structure', get_string('structure', 'local_metatiles'));
        $mform->setType('structure', PARAM_RAW);


                 /*
                 * We need editor + filemanager for each lecturer.
                 * The data will be stored in the new table "mdl_metatiles_additional" with "courseid", "name" and "value".
                 * ??? SURE ??? The "name" will be saved as reference in the table "mdl_metatiles".
                 * Each record will be selected by "courseid" and "name"
                 */


        $mform->addElement('html', '<h2>Angaben zu Autoren*innen und Anbieter*innen</h2>');
        $i = 1;
        
        // above $i will be used here!
        if (empty($lecturer)) {

            while ($i <= $max_lecturer) {

                // Anbieter*innen / Autor*innen
                $radioarray = array();
                $radioarray[] = $mform->createElement('radio', 'lecturer_type_' . $i, '', get_string('lecturer_type_0', 'local_metatiles'), 0);
                $radioarray[] = $mform->createElement('radio', 'lecturer_type_' . $i, '', get_string('lecturer_type_1', 'local_metatiles'), 1);
                $mform->addGroup($radioarray, 'radioar', get_string('lecturer_type', 'local_metatiles'), array(' '), false);
                if ($i > 1) {
                    $mform->setDefault('lecturer_type_' . $i, 1);
                }

                // Bild Anbieter*innen / Autor*innen
                $mform->addElement('filemanager', 'detailslecturer_image_' . $i, get_string('detailslecturer_image', 'local_metatiles'), null, $filemanageropts);

                // Details Anbieter*innen / Autor*innen
                $mform->addElement('editor', 'detailslecturer_editor_' . $i, get_string('detailslecturer', 'local_metatiles'), null, $editoropts);
                $mform->setType('detailslecturer_editor', PARAM_RAW);

                $url = new moodle_url('/local/metatiles/pages/metatiles_delete_lecturer.php', array('courseid' => $courseid, 'id' => $i));

                $mform->addElement('html', html_writer::link($url, 'Eingabefeld entfernen'));

                $mform->addElement('html', '<h>');

                $i++;
            }
        } else {
            foreach($lecturer as $lect) {
                if(strpos($lect->name, 'type')) {
                    // Anbieter*innen / Autor*innen
                    $radioarray = array();
                    $radioarray[] = $mform->createElement('radio', $lect->name, '', get_string('lecturer_type_0', 'local_metatiles'), 0);
                    $radioarray[] = $mform->createElement('radio', $lect->name, '', get_string('lecturer_type_1', 'local_metatiles'), 1);
                    $mform->addGroup($radioarray, 'radioar', get_string('lecturer_type', 'local_metatiles'), array(' '), false);
                    if ($i > 1) {
                        $mform->setDefault($lect->name, 1);
                    }
                }
                if(strpos($lect->name, 'image')) {
                    // Bild Anbieter*innen / Autor*innen
                    $mform->addElement('filemanager', $lect->name, get_string('detailslecturer_image', 'local_metatiles'), null, $filemanageropts);
                }
                if(strpos($lect->name, 'editor')) {
                    // Details Anbieter*innen / Autor*innen
                    $mform->addElement('editor', $lect->name, get_string('detailslecturer', 'local_metatiles'), null, $editoropts);
                    $mform->setType('detailslecturer_editor', PARAM_RAW);

                    $id = substr($lect->name, -1);
                    $url = new moodle_url('/local/metatiles/pages/metatiles_delete_lecturer.php', array('courseid' => $courseid, 'id' => $id));
                    $mform->addElement('html', html_writer::link($url, 'Eingabefeld entfernen'));
                    $mform->addElement('html', '<h>');

                    $i++;
                }
            }
        }

        $mform->addElement('html', '<hr>');

        $mform->addElement('text', 'additional_lecturer', 'Zusätzliche Felder');
        $mform->setDefault('additional_lecturer', 0);
        $mform->setType('additional_lecturer', PARAM_INT);
        $mform->addRule('additional_lecturer', 'Bitte eine Zahl angeben', 'numeric', '', 'client');
        $mform->addElement('static', 'text_additional_lecturer', '', 'Bitte die Anzahl der zusätzlich benötigten Felder zum Anlegen weiterer Autor*innen und Anbieter*innen angeben.');
        $this->add_action_buttons($cancel = false, $submitlabel = 'Felder hinzufügen');

        $mform->addElement('html', '<h2>Weitere Informationen</h2>');

        $licenses = $DB->get_records('license');
        $licenses_arr = [];

        foreach ($licenses as $license) {
            $licenses_arr[] = $license->shortname;
        }

        $mform->addElement('select', 'license', get_string('license', 'local_metatiles'), $licenses_arr);
        $mform->setType('license', PARAM_RAW);

        // Leistungsnachweis
        $mform->addElement('editor', 'certificateofachievement', get_string('certificateofachievement', 'local_metatiles'));
        $mform->setType('certificateofachievement', PARAM_RAW);


        // Schlagwörter
        $mform->addElement('text', 'tags', get_string('tags', 'local_metatiles'));
        $mform->setType('tags', PARAM_TEXT);

        $this->add_action_buttons();
    }

    function validation($data, $files)
    {
        return array();
    }
	// Funktioniert hier nicht. Falsche Stelle
	function data_preprocessing(&$default_values) {
		$lecturer = $this->_customdata['lecturer'];
		print($lecturer);die();
		if ($this->current->instance) {
			foreach ($lecturer as $lect) {
				$draftitemid = file_get_submitted_draft_itemid($lect->name);
				$context = context_course::instance($this->_customdata['courseid']);
				file_prepare_draft_area($draftitemid, $context->id, 'local_metatiles', $lect->name, 0);
				$default_values[$lect->name] = $draftitemid;
			}
		}
		
		// TODO overviewimage nicht vergessen

	}


}
