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

namespace local_ildmeta;

/**
 * Library of utility functions for local_ildmeta.
 *
 * @package     local_ildmeta
 * @copyright   2022 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {
    /**
     * Filters an object of arrays of terms in diffrent languages, so that only the terms in the targeted language remain.
     *
     * @param stdClass $vocabulary An object of arrays of terms in diffrent languages.
     *              For example:    [
     *                                 ["de" => "Schüler*innen", "en" => "Pupils"],
     *                                 ["de" => "Studieninteressierte", "en" => "Prospective Students"]
     *                              ]
     * @param string $lang The language code of the targeted language.
     * @return array List of terms in a set language.
     */
    public static function filter_vocabulary_lang($vocabulary, $lang = null) {
        global $USER;
        if (!isset($lang)) {
            $lang = $USER->lang;
        }

        $result = array();

        if (!isset($vocabulary->terms)) {
            return null;
        }

        $terms = json_decode($vocabulary->terms);

        if (!is_array($terms) || empty($terms)) {
            return null;
        }

        // Get term in current language.
        foreach ($terms as $term) {
            $term = (array)$term;
            if (count($term) == 1 || !isset($term[$lang])) {
                $result[] = reset($term);
            } else {
                $result[] = $term[$lang];
            }
        }

        return $result;
    }

    /**
     * Get the list of Mooc providers.
     *
     * @param string $lang The language code of the targeted language.
     * @return array List of providers [[id] => [name, url, logo]].
     */
    public static function get_providers($lang = null) {
        global $DB;
        if (!isset($lang)) {
            $lang = current_language();
        }

        $result = array();

        $records = $DB->get_records('ildmeta_provider');

        foreach ($records as $provider) {
            $fs = get_file_storage();
            $context = \context_system::instance();
            $logourl = '';

            // Get url of logo.
            $files = $fs->get_area_files($context->id, 'local_ildmeta', 'provider', $provider->id);
            foreach ($files as $file) {
                if ($file->is_valid_image()) {
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

            $result[$provider->id] = [
                "name" => current_language() == 'de' ? $provider->name_de : $provider->name_en,
                "url" => $provider->url,
                "logo" => (new \moodle_url($logourl))->out(),
            ];
        }

        return $result;
    }

    /**
     * Get the list of Mooc providers.
     *
     * @param int $id ID of a mooc provider.
     * @param string $lang The language code of the targeted language.
     * @return array An array containing provider data [id, name, url, logo].
     */
    public static function get_provider($id, $lang = null) {
        global $DB;
        if (!isset($lang)) {
            $lang = current_language();
        }

        $result = array();

        if (!$provider = $DB->get_record('ildmeta_provider', array('id' => $id))) {
            return null;
        }

        $fs = get_file_storage();
        $context = \context_system::instance();
        $logourl = '';

        // Get url of logo.
        $files = $fs->get_area_files($context->id, 'local_ildmeta', 'provider', $provider->id);
        foreach ($files as $file) {
            if ($file->is_valid_image()) {
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

        $result = [
            "id" => $provider->id,
            "name" => current_language() == 'de' ? $provider->name_de : $provider->name_en,
            "url" => $provider->url,
            "logo" => (new \moodle_url($logourl))->out(),
        ];

        return $result;
    }
}
