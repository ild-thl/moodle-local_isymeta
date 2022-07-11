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
 * Form to create or edit a Mooc provider.
 *
 * @package     local_ildmeta
 * @author      Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @copyright   2022 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ildmeta\output\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/formslib.php");

/**
 * Form to create or edit a Mooc provider.
 *
 * @package     local_ildmeta
 * @copyright   2022 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit_provider_form extends \moodleform {
    /**
     * Form definition.
     * @return void
     */
    public function definition() {

        $mform = $this->_form;

        // Private key input.
        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);

        // Name german.
        $mform->addElement('text',  'name_de',  get_string('provider_name_de', 'local_ildmeta'));
        $mform->setType('name_de', PARAM_NOTAGS);
        $mform->addRule('name_de', get_string('required'), 'required', null, 'client');
        // Name english.
        $mform->addElement('text',  'name_en',  get_string('provider_name_en', 'local_ildmeta'));
        $mform->setType('name_en', PARAM_NOTAGS);
        $mform->addRule('name_en', get_string('required'), 'required', null, 'client');

        // URL.
        $mform->addElement('text',  'url',  get_string('provider_url', 'local_ildmeta'));
        $mform->setType('url', PARAM_URL);
        $mform->addRule('url', get_string('required'), 'required', null, 'client');

        // Logo.
        $filemanageroptions = array(
            'accepted_types' => 'image',
            'maxbytes' => 0,
            'maxfiles' => 1,
        );
        $mform->addElement('filemanager', 'logo', get_string('provider_logo', 'local_ildmeta'), null, $filemanageroptions);
        $mform->addRule('logo', get_string('required'), 'required', null, 'client');

        $this->add_action_buttons(true);
    }

    /**
     * Prepares the data to be displayed in the form.
     *
     * @param mixed &$defaultvalues
     * @return void
     **/
    public function data_preprocessing(&$defaultvalues) {
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('logo');
            file_prepare_draft_area($draftitemid, $this->context->id, 'local_ildmeta', 'provider', 3);
            $defaultvalues['logo'] = $draftitemid;
        }
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
