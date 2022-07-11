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

namespace local_ildmeta\output\table;

use context_system;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/tablelib.php");


/**
 * Table that lists Mooc providers.
 *
 * @package     local_ildmeta
 * @author      Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @copyright   2022 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider_table extends \table_sql {

    /**
     * Table defintion.
     *
     * @param string $uniqueid
     * @param boolean $showactions Wether action buttons should be added to the table, that allow managing the certificates.
     * @param int|null $courseid If this is null, the course column won't be included.
     * @param int $userid If this is null, the user name column won't be included.
     * @param string $lang The target language for the lang_strings used by the table.
     */
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);

        $headers[] = 'ID';
        $columns[] = 'id';
        $headers[] = get_string('provider', 'local_ildmeta');
        $columns[] = 'name';
        $headers[] = 'Homepage';
        $columns[] = 'url';
        $headers[] = 'Logo';
        $columns[] = 'logo';

        $headers[] = '';
        $columns[] = 'action';

        // Define the list of columns to show.
        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $this->define_headers($headers);

        // Set preferences.
        $this->is_downloadable(false);
        $this->initialbars(false);
        $this->sortable(false);
        $this->collapsible(false);
    }

    /**
     * This function is called for each data row to allow processing of the
     * name value.
     *
     * @param \stdClass $values Contains object with all the values of record.
     * @return $string Return html img displaying the current certificate status only
     *     when not downloading.
     */
    protected function col_name($values) {
        // If the data is being downloaded than we don't want to show HTML.
        return current_language() == 'de' ? $values->name_de : $values->name_en;
    }

    /**
     * This function is called for each data row to allow processing of the
     * logo value.
     *
     * @param \stdClass $values Contains object with all the values of record.
     * @return $string Return html img displaying the current certificate status only
     *     when not downloading.
     */
    protected function col_logo($values) {
        // Only show image if the table is not beeing downloaded.
        if (!$this->is_downloading()) {
            $fs = get_file_storage();
            $context = context_system::instance();
            $logourl = '';
            $logo = null;

            // Get url of logo.
            $files = $fs->get_area_files($context->id, 'local_ildmeta', 'provider', $values->id);
            foreach ($files as $file) {
                if ($file->is_valid_image()) {
                    $logo = $file;
                    $logourl = \moodle_url::make_pluginfile_url(
                        $file->get_contextid(),
                        $file->get_component(),
                        $file->get_filearea(),
                        $file->get_itemid(),
                        $file->get_filepath(),
                        $file->get_filename(),
                        false
                    );
                    break;
                }
            }

            if (isset($logo)) {
                return '<img height="100px" title="'
                    . $logo->get_filename()
                    . '" src="' .  new \moodle_url($logourl)
                    . '">';
            }
        }
    }


    /**
     * This function is called for each data row to allow processing of the
     * action value.
     *
     * @param \stdClass $values Contains object with all the values of record.
     * @return $string Return formated date of issueance.
     */
    protected function col_action($values) {
        // If the data is being downloaded than we don't want to show HTML.
        if ($this->is_downloading()) {
            return $values->id;
        } else {
            $actions = '<div class="btn-group">';
            // Edit action.
            $actionstring = get_string('edit');
            $actionurl = new \moodle_url('/local/ildmeta/edit_provider.php', array('id' => $values->id, 'mode' => 'edit'));
            $actions .= '<a class="btn btn-info" href="' . $actionurl . '">' . $actionstring . '</a>';
            // Delete action.
            $actionstring = get_string('delete');
            $actionurl = new \moodle_url('/local/ildmeta/edit_provider.php', array('id' => $values->id, 'mode' => 'delete'));
            $actions .= '<a class="btn btn-danger" href="' . $actionurl . '">' . $actionstring . '</a>';
            $actions .= '</div>';
            return $actions;
        }
    }
}
