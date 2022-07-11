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

class ildmeta_delete_lecturer_form extends \moodleform {
    // Add elements to form.
    public function definition() {
        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('html', '<h2>Eingabeblock löschen</h2>');
        $mform->addElement('html', '<p>Soll der ausgewählte Eingabeblock wirklich gelöscht werden?</p>');
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('yes'));
        $buttonarray[] = $mform->createElement('cancel', 'cancel', get_string('no'));
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
    }
}
