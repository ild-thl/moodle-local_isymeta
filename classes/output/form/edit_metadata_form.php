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

namespace local_ildmeta\output\form;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

use local_ildmeta\manager;
use moodle_url;

/**
 * Form to manage additional course metadata.
 *
 * @package     local_ildmeta
 * @copyright   2022 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit_metadata_form extends \moodleform {
    public function definition() {
        global $DB;

        // Get vocabularies from ildmeta_vocabulary for dropdown selection fields.
        $records = $DB->get_records('ildmeta_vocabulary');
        $vocabularies = new \stdClass();
        foreach ($records as $vocabulary) {
            $vocabularies->{$vocabulary->title} = manager::filter_vocabulary_lang($vocabulary, current_language());
        }

        // Get list of providers.
        $providers = manager::get_providers();
        // Reduce list to names only.
        $providers = array_map(fn($a) => $a['name'], $providers);

        $vocabularysettings = '/local/ildmeta/edit_vocabulary.php';
        $providersettings = '/local/ildmeta/edit_provider.php';

        $mform = $this->_form; // Don't forget the underscore!

        $lecturer = $this->_customdata['lecturer'];
        $maxlecturer = $this->_customdata['max_lecturer'];
        $courseid = $this->_customdata['courseid'];

        $filemanageropts = $this->_customdata['filemanageropts'];
        $editoropts = $this->_customdata['editoropts'];

        $langlist = [
            'Deutsch',
            'Englisch',
            'Ukrainisch',
            'Russisch'
        ];

        $mform->addElement('html', '<h2>Meta: Übersichtsseite</h2>');

        $context = \context_system::instance();

        if (has_capability('local/ildmeta:indexation', $context)) {
            // Indexierung. Required.
            $mform->addElement(
                'select',
                'noindexcourse',
                get_string('noindexcourse', 'local_ildmeta'),
                array(get_string('noindexcourse_yes', 'local_ildmeta'), get_string('noindexcourse_no', 'local_ildmeta'), get_string('noindexcourse_limited', 'local_ildmeta'))
            );
            $mform->setType('index', PARAM_RAW);
            // $mform->addRule('noindexcourse', get_string('required'), 'required', null, 'server');
        }

        // UUID. Required.
        $mform->addElement('text', 'uuid', get_string('uuid', 'local_ildmeta'));
        $mform->setType('uuid', PARAM_RAW);
        $mform->addRule('uuid', get_string('required'), 'required', null, 'client');
        // Check valid UUID.
        $mform->addRule('uuid', get_string('invaliduuid', 'local_ildmeta'), 'regex', '/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', 'client');
        // Kurstitel. Required.
        $mform->addElement('text', 'coursetitle', get_string('coursetitle', 'local_ildmeta'));
        $mform->setType('coursetitle', PARAM_TEXT);
        // $mform->addRule('coursetitle', get_string('required'), 'required', null, 'client');

        // Anbietende Institutionen. Required.
        $mform->addElement('select', 'provider', get_string('provider', 'local_ildmeta'),  $providers);
        $mform->setType('provider', PARAM_RAW);
        $mform->addElement(
            'static',
            'provider_help',
            '',
            get_string('provider_help', 'local_ildmeta')
                . ' -> <a href="' . new moodle_url($providersettings) . '" target="_blank">'
                . get_string('provider_to_settings', 'local_ildmeta') . '</a>'
        );
        // $mform->addRule('provider', get_string('required'), 'required', null, 'client');

        // Fachbereich/Wissensgebiet. Required.
        $mform->addElement('select', 'subjectarea', get_string('subjectarea', 'local_ildmeta'), $vocabularies->subjectarea);
        $mform->setType('subjectarea', PARAM_RAW);
        // $mform->addRule('subjectarea', get_string('required'), 'required', null, 'client');

        // Bildungsniveau. Required.
        edu_level_form_element::toHTML($mform);

        // Kurssprache. Required.
        $mform->addElement('select', 'courselanguage', get_string('courselanguage', 'local_ildmeta'), $langlist);
        $mform->setType('courselanguage', PARAM_RAW);
        // $mform->addRule('courselanguage', get_string('required'), 'required', null, 'client');

        // Startzeit. Required.
        $mform->addElement('date_selector', 'starttime', get_string('starttime', 'local_ildmeta'));
        // $mform->addRule('starttime', get_string('required'), 'required', null, 'client');

        // License. Required.
        $licenses = $DB->get_records('license');
        $licensesarr = [];
        foreach ($licenses as $license) {
            $licensesarr[$license->id] = $license->fullname;
        }

        $mform->addElement('select', 'license', get_string('license', 'local_ildmeta'), $licensesarr);
        $mform->setType('license', PARAM_RAW);
        // $mform->addRule('license', get_string('required'), 'required', null, 'client');

        // Dozent. Required.
        $mform->addElement('text', 'lecturer', get_string('lecturer', 'local_ildmeta'));
        $mform->setType('lecturer', PARAM_TEXT);
        $mform->addRule('lecturer', get_string('required'), 'required', null, 'client');

        // Erlaube ein individuelles Uebersichtsbild, das nicht dem Kursbild entscpricht.
        $mform->addElement('selectyesno', 'customoverviewimage', get_string('customoverviewimage', 'local_ildmeta'));
        $mform->addHelpButton('customoverviewimage', 'customoverviewimage', 'local_ildmeta');

        // Uebersichtsbild.
        $mform->addElement('filemanager', 'overviewimage', get_string('overviewimage', 'local_ildmeta'), null, $filemanageropts);
        // Disable overviewimage unless customoverviewimageis checked.
        $mform->hideIf('overviewimage', 'customoverviewimage', "eq", 0);

        // Videocode.
        $mform->addElement('url', 'videocode', get_string('videocode', 'local_ildmeta'));
        $mform->setType('videocode', PARAM_URL);

        // Videolizenz.
        $licenses = $DB->get_records('license');
        $licenseoptions = array_map(function ($license) {
            return $license->fullname;
        }, $licenses);
        $mform->addElement('select', 'videolicense', get_string('videolicense', 'local_ildmeta'), $licenseoptions);
        // Disabled if exporttobird is not set to 1 -> Yes.
        $mform->disabledIf('videolicense', 'videocode', 'eq', '');

        // Detailbild.
        $mform->addElement('filemanager', 'detailimage', get_string('detailimage', 'local_ildmeta'), null, $filemanageropts);

        // Bearbeitungszeit in Stunden.
        $mform->addElement('text', 'processingtime', get_string('processingtime', 'local_ildmeta'));
        $mform->setType('processingtime', PARAM_INT);
        $mform->addRule('processingtime', get_string('text_processingtime', 'local_ildmeta'), 'numeric');
        $mform->addElement('static', 'text_processingtime', '', get_string('text_processingtime', 'local_ildmeta'));

        $mform->addElement('html', '<h2>Meta: Detailseite</h2>');

        // Teasertext. Required.
        $mform->addElement('editor', 'teasertext', get_string('teasertext', 'local_ildmeta'));
        $mform->setType('teasertext', PARAM_RAW);
        $mform->addRule('teasertext', get_string('required'), 'required', null, 'client');

        // Zielgruppe.
        $mform->addElement('header', 'targetgroup_section', get_string('targetgroup', 'local_ildmeta'));
        // Zielgruppe Heading.
        $mform->addElement('text', 'targetgroupheading', get_string('description_heading', 'local_ildmeta'), 'maxlength="120"');
        $mform->setType('targetgroupheading', PARAM_TEXT);
        $mform->addRule('targetgroupheading', null, 'maxlength', 120, 'client');
        $mform->setDefault('targetgroupheading', get_string('targetgroup_heading', 'local_ildmeta'));
        $mform->addElement('editor', 'targetgroup', get_string('targetgroup', 'local_ildmeta'));
        $mform->setType('targetgroup', PARAM_RAW);

        // Lernziele.
        $mform->addElement('header', 'learninggoals_section', get_string('learninggoals', 'local_ildmeta'));
        // Lernziele Heading.
        $mform->addElement('text', 'learninggoalsheading', get_string('description_heading', 'local_ildmeta'), 'maxlength="120"');
        $mform->setType('learninggoalsheading', PARAM_TEXT);
        $mform->addRule('learninggoalsheading', null, 'maxlength', 120, 'client');
        $mform->setDefault('learninggoalsheading', get_string('learninggoals_heading', 'local_ildmeta'));
        $mform->addElement('editor', 'learninggoals', get_string('learninggoals', 'local_ildmeta'));
        $mform->setType('learninggoals', PARAM_RAW);

        // Gliederung.
        $mform->addElement('header', 'structure_section', get_string('structure', 'local_ildmeta'));
        // Gliederung Heading.
        $mform->addElement('text', 'structureheading', get_string('description_heading', 'local_ildmeta'), 'maxlength="120"');
        $mform->setType('structureheading', PARAM_TEXT);
        $mform->addRule('structureheading', null, 'maxlength', 120, 'client');
        $mform->setDefault('structureheading', get_string('structure_heading', 'local_ildmeta'));
        $mform->addElement('editor', 'structure', get_string('structure', 'local_ildmeta'));
        $mform->setType('structure', PARAM_RAW);

        /*
         * We need editor + filemanager for each lecturer.
         * The data will be stored in the new table "mdl_ildmeta_additional" with "courseid", "name" and "value".
         * ??? SURE ??? The "name" will be saved as reference in the table "mdl_ildmeta".
         * Each record will be selected by "courseid" and "name"
        */

        $mform->addElement('header', 'creator_section', 'Angaben zu Autor*innen und Anbieter*innen');
        $i = 1;

        // Above $i will be used here!
        if (empty($lecturer)) {

            while ($i <= $maxlecturer) {

                // Anbieter*innen / Autor*innen.
                $radioarray = array();
                $radioarray[] = $mform->createElement('radio', 'lecturer_type_' . $i, '', get_string('lecturer_type_0', 'local_ildmeta'), 0);
                $radioarray[] = $mform->createElement('radio', 'lecturer_type_' . $i, '', get_string('lecturer_type_1', 'local_ildmeta'), 1);
                $mform->addGroup($radioarray, 'radioar', get_string('lecturer_type', 'local_ildmeta'), array(' '), false);
                if ($i > 1) {
                    $mform->setDefault('lecturer_type_' . $i, 1);
                }

                // Bild Anbieter*innen / Autor*innen.
                $mform->addElement(
                    'filemanager',
                    'detailslecturer_image_' . $i,
                    get_string('detailslecturer_image', 'local_ildmeta'),
                    null,
                    $filemanageropts
                );

                // Details Anbieter*innen / Autor*innen.
                $mform->addElement(
                    'editor',
                    'detailslecturer_editor_' . $i,
                    get_string('detailslecturer', 'local_ildmeta'),
                    null,
                    $editoropts
                );
                $mform->setType('detailslecturer_editor_' . $i, PARAM_RAW);
                $mform->addRule('detailslecturer_editor_' . $i, get_string('required'), 'required', null, 'client');

                $url = new \moodle_url('/local/ildmeta/ildmeta_delete_lecturer.php', array('courseid' => $courseid, 'id' => $i));

                $mform->addElement('html', \html_writer::link($url, 'Eingabefeld entfernen'));

                $mform->addElement('html', '<h>');

                $i++;
            }
        } else {
            foreach ($lecturer as $lect) {
                if (strpos($lect->name, 'type')) {
                    // Anbieter*innen / Autor*innen.
                    $radioarray = array();
                    $radioarray[] = $mform->createElement('radio', $lect->name, '', get_string('lecturer_type_0', 'local_ildmeta'), 0);
                    $radioarray[] = $mform->createElement('radio', $lect->name, '', get_string('lecturer_type_1', 'local_ildmeta'), 1);
                    $mform->addGroup($radioarray, 'radioar', get_string('lecturer_type', 'local_ildmeta'), array(' '), false);
                    if ($i > 1) {
                        $mform->setDefault($lect->name, 1);
                    }
                }
                if (strpos($lect->name, 'image')) {
                    // Bild Anbieter*innen / Autor*innen.
                    $mform->addElement(
                        'filemanager',
                        $lect->name,
                        get_string('detailslecturer_image', 'local_ildmeta'),
                        null,
                        $filemanageropts
                    );
                }
                if (strpos($lect->name, 'editor')) {
                    // Details Anbieter*innen / Autor*innen.
                    $mform->addElement(
                        'editor',
                        $lect->name,
                        get_string('detailslecturer', 'local_ildmeta'),
                        null,
                        $editoropts
                    );
                    $mform->setType($lect->name, PARAM_RAW);
                    $mform->addRule($lect->name, get_string('required'), 'required', null, 'client');

                    $id = substr($lect->name, -1);
                    $url = new \moodle_url('/local/ildmeta/ildmeta_delete_lecturer.php', array('courseid' => $courseid, 'id' => $id));
                    $mform->addElement('html', \html_writer::link($url, 'Eingabefeld entfernen'));
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
        $mform->addElement(
            'static',
            'text_additional_lecturer',
            '',
            'Bitte die Anzahl der zusätzlich benötigten Felder zum Anlegen weiterer Autor*innen und Anbieter*innen angeben.'
        );
        $this->add_action_buttons($cancel = false, $submitlabel = 'Felder hinzufügen - bitte vorher speichern');

        $mform->addElement('header', 'misc', 'Weitere Informationen');

        // Leistungsnachweis.
        $mform->addElement('editor', 'certificateofachievement', get_string('certificateofachievement', 'local_ildmeta'));
        $mform->setType('certificateofachievement', PARAM_RAW);

        // Schlagwörter.
        $mform->addElement('text', 'tags', get_string('tags', 'local_ildmeta'));
        $mform->setType('tags', PARAM_TEXT);

        $mform->addElement('header', 'birdmetadata', get_string('birdmetadata', 'local_ildmeta'));

        // Export to bird.
        $mform->addElement('selectyesno', 'exporttobird', get_string('exporttobird', 'local_ildmeta'));
        $mform->addHelpButton('exporttobird', 'exporttobird', 'local_ildmeta');

        // Bird-Fachbereich.
        $mform->addElement('select', 'birdsubjectarea', get_string('subjectareabird', 'local_ildmeta'), $vocabularies->birdsubjectarea);
        $mform->setType('birdsubjectarea', PARAM_RAW);
        // Disabled if exporttobird is not set to 1 -> Yes.
        $mform->disabledIf('birdsubjectarea', 'exporttobird', 'eq', '0');

        // Shortname. Required.
        $mform->addElement('text', 'shortname', get_string('shortname', 'local_ildmeta'), 'maxlength="100" size="25"');
        $mform->setType('shortname', PARAM_ALPHANUM);
        // Disabled if exporttobird is not set to 1 -> Yes.
        $mform->disabledIf('shortname', 'exporttobird', 'eq', '0');

        // Abstract. Required in BirdCourse.
        $mform->addElement('editor', 'abstract', get_string('abstract', 'local_ildmeta'));
        $mform->setType('abstract', PARAM_RAW);
        // Disabled if exporttobird is not set to 1 -> Yes.
        $mform->disabledIf('abstract', 'exporttobird', 'eq', '0');

        // Kursformat.
        $mform->addElement('select', 'courseformat', get_string('courseformat', 'local_ildmeta'), $vocabularies->courseformats);
        // Disabled if exporttobird is not set to 1 -> Yes.
        $mform->disabledIf('courseformat', 'exporttobird', 'eq', '0');

        // Kurstyp.
        $mform->addElement('select', 'coursetype', get_string('coursetype', 'local_ildmeta'), $vocabularies->coursetypes);
        // Disabled if exporttobird is not set to 1 -> Yes.
        $mform->disabledIf('coursetype', 'exporttobird', 'eq', '0');

        // Language course type.
        $mform->addElement('select', 'languagesubject', get_string('languagesubject', 'local_ildmeta'), $vocabularies->languagesubject);
        // Disabled if exporttobird is not set to 1 -> Yes.
        $mform->disabledIf('languagesubject', 'exporttobird', 'eq', '0');
        $mform->disabledIf('languagesubject', 'coursetype', 'neq', '0');

        // Language course level goals.
        $mform->addElement('select', 'languagelevels', get_string('languagelevels', 'local_ildmeta'), $vocabularies->languagelevels);
        // Disabled if exporttobird is not set to 1 -> Yes.
        $mform->disabledIf('languagelevels', 'exporttobird', 'eq', '0');
        $mform->disabledIf('languagelevels', 'coursetype', 'neq', '0');

        // Selbstlernkurs.
        $mform->addElement('selectyesno', 'selfpaced', get_string('selfpaced', 'local_ildmeta'));
        // Disabled if exporttobird is not set to 1 -> Yes.
        $mform->disabledIf('selfpaced', 'exporttobird', 'eq', '0');

        // Bird/DC-Zielgruppe. Required in BirdCourse.
        $mform->addElement('select', 'audience', get_string('audience', 'local_ildmeta'), $vocabularies->audience);
        // Disabled if exporttobird is not set to 1 -> Yes.
        $mform->disabledIf('audience', 'exporttobird', 'eq', '0');

        // Erforderliche Vorkenntnisse.
        $mform->addElement('editor', 'courseprerequisites', get_string('courseprerequisites', 'local_ildmeta'));
        $mform->setType('courseprerequisites', PARAM_RAW);
        // Disabled if exporttobird is not set to 1 -> Yes.
        $mform->disabledIf('courseprerequisites', 'exporttobird', 'eq', '0');

        // Available from. Required in BirdCourse.
        $mform->addElement('date_selector', 'availablefrom', get_string('availablefrom', 'local_ildmeta'));
        // Disabled if exporttobird is not set to 1 -> Yes.
        $mform->disabledIf('availablefrom', 'exporttobird', 'eq', '0');

        // Wether the course has an expiration date.
        $expireoptions = array(
            1 => get_string('expires_yes', 'local_ildmeta'),
            0 => get_string('expires_no', 'local_ildmeta'),
        );
        $mform->addElement('select', 'expires', get_string('expires', 'local_ildmeta'), $expireoptions);
        $mform->addHelpButton('expires', 'expires', 'local_ildmeta');
        $mform->disabledIf('expires', 'exporttobird', 'eq', '0');

        // Available until. Required in BirdCourse.
        $mform->addElement('date_selector', 'availableuntil', get_string('availableuntil', 'local_ildmeta'));
        $mform->setDefault('expires', 1);
        // Disabled if exporttobird is not set to 1 -> Yes.
        $mform->disabledIf('availableuntil', 'exporttobird', 'eq', '0');
        // Disabled if exporttobird is not set to 1 -> Yes.
        $mform->disabledIf('availableuntil', 'expires', 'eq', '0');

        // TODO: Required in BirdAcademy, and missing here: City, Country.

        $this->add_action_buttons();
    }

    // Funktioniert hier nicht. Falsche Stelle.
    public function data_preprocessing(&$defaultvalues) {
        $lecturer = $this->_customdata['lecturer'];
        if ($this->current->instance) {
            foreach ($lecturer as $lect) {
                $draftitemid = file_get_submitted_draft_itemid($lect->name);
                $context = \context_course::instance($this->_customdata['courseid']);
                file_prepare_draft_area($draftitemid, $context->id, 'local_ildmeta', $lect->name, 0);
                $defaultvalues[$lect->name] = $draftitemid;
            }
        }

        // TODO overviewimage nicht vergessen.

    }

    /**
     * Gets input data of submitted form.
     *
     * @return object
     **/
    public function get_data() {
        $data = parent::get_data();

        if (empty($data)) {
            return false;
        }

        return $data;
    }


    // Custom validation.
    public function validation($data, $files) {
        $errors = array();

        if ($data['exporttobird']) {
            // Requires abstract.
            if (!isset($data['abstract']['text']) || empty($data['abstract']['text'])) {
                $errors['abstract'] = get_string('required');
            }
            // Requires teasertext.
            if (!isset($data['teasertext']['text']) || empty($data['teasertext']['text'])) {
                $errors['teasertext'] = get_string('required');
            }
            // Requires availableuntil.
            if (!isset($data['availableuntil'])) {
                $errors['availableuntil'] = get_string('required');
            }
            // Requires availablefrom.
            if (!isset($data['availablefrom'])) {
                $errors['availablefrom'] = get_string('required');
            }
            // Requires audience.
            if (!isset($data['audience'])) {
                $errors['audience'] = get_string('required');
            }
            // Requires courseformat.
            if (!isset($data['courseformat'])) {
                $errors['courseformat'] = get_string('required');
            }
        }
        return $errors;
    }
}
