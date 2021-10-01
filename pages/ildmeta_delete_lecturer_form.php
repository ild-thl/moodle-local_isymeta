<?php

require_once("$CFG->libdir/formslib.php");

class ildmeta_delete_lecturer_form extends moodleform
{
//Add elements to form
    function definition()
    {
        $mform = $this->_form; // Don't forget the underscore!

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