<?php

require_once("$CFG->libdir/formslib.php");

class ildmeta_delete_lecturer_form extends moodleform
{
//Add elements to form
    function definition()
    {
        $mform = $this->_form; // Don't forget the underscore!

        /*
        $radioarray = array();
        $radioarray[] = $mform->createElement('radio', 'lecturer_false_true', '', get_string('lecturer_false', 'local_ildmeta'), 0, '');
        $radioarray[] = $mform->createElement('radio', 'lecturer_false_true', '', get_string('lecturer_true', 'local_ildmeta'), 1, '');
        $mform->addGroup($radioarray, 'radioar', get_string('lecturer_delete', 'local_ildmeta'), array(' '), false);
        $mform->setDefault('lecturer_false_true', 0);

        $this->add_action_buttons(true, get_string('lecturer_delete_confirm', 'local_ildmeta'));
        */

        $mform->addElement('html', '<h2>Eingabeblock löschen</h2>');
        $mform->addElement('html', '<p>Soll der ausgewählte Eingabeblock wirklich gelöscht werden?</p>');
        $buttonarray=array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('yes'));
        $buttonarray[] = $mform->createElement('cancel', 'cancel', get_string('no'));
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
    }

//Custom validation should be added here
    function validation($data, $files)
    {
        return array();
    }
}