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

/**
 * Form to edit BIRD coursetypes.
 *
 * @package     local_ildmeta
 * @author      Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @copyright   2022 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit_licenses_form extends \moodleform {
    /**
     * Form definition.
     * @return void
     */
    public function definition() {
        global $DB;

        $mform = $this->_form;

        $licenses = $DB->get_records('license');

        foreach ($licenses as $license) {
            $mform->addElement('header', 'header_' . $license->id, $license->fullname);
            $mform->addElement('hidden',  'moodle_license_' . $license->id,  $license->id);
            $mform->setType('moodle_license_' . $license->id, PARAM_INT);
            $mform->addElement('text', 'shortname_' . $license->id, $license->shortname);
            $mform->setType('shortname_' . $license->id, PARAM_NOTAGS);
            $mform->addElement('text', 'fullname_' . $license->id, $license->fullname);
            $mform->setType('fullname_' . $license->id, PARAM_NOTAGS);
            $mform->addElement('text', 'url_' . $license->id,  $license->source);
            $mform->setType('url_' . $license->id, PARAM_URL);
        }

        $this->add_action_buttons(true);
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
}
