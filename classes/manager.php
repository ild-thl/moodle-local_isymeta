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
                "id" => $provider->id,
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

    public static function set_default_vocabulary() {
        global $DB;

        $DB->delete_records('ildmeta_vocabulary');

        $coursetypes = [
            'title' => 'coursetypes',
            'terms' => json_encode([
                ["de" => "Sprachkurs", "en" => "Language Course"],
                ["de" => "Fachkurs", "en" => "Specialised Course"],
                ["de" => "Propädeutika", "en" => "Propaedeutics"],
                ["de" => "Soft Skills", "en" => "Soft Skills"],
                ["de" => "Career Skills", "en" => "Career Skills"],
                ["de" => "Digital Skills", "en" => "Digital Skills"],
                ["de" => "Academic Skills", "en" => "Academic Skills"],
                ["de" => "Business Skills", "en" => "Business Skills"],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ];
        $DB->insert_record('ildmeta_vocabulary', $coursetypes);

        $courseformats = [
            'title' => 'courseformats',
            'terms' => json_encode([
                ["de" => "Präsenz", "en" => "Face To Face"],
                ["de" => "Online (Selbstlernkurs)", "en" => "Online Asynchronous"],
                ["de" => "Online mit festen Online-Gruppenterminen", "en" => "Online Synchronous"],
                ["de" => "Blended Learning mit festen Präsenz-Gruppenterminen", "en" => "Blended Learning"],
                // ["de" => "MOOC", "en" => "MOOC"],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ];
        $DB->insert_record('ildmeta_vocabulary', $courseformats);

        $audience = [
            'title' => 'audience',
            'terms' => json_encode([
                ["de" => "Schüler*innen", "en" => "Pupils"],
                ["de" => "Studieninteressierte", "en" => "Prospective Students"],
                ["de" => "Studierende", "en" => "Students"],
                ["de" => "Promotionsinteresse", "en" => "Prospective Doctoral Candidates"],
                ["de" => "PASCH-Schüler*innen", "en" => "PASCH-Pupils"],
                ["de" => "Lehrende", "en" => "Teachers"],
                ["de" => "Eltern", "en" => "Parents"],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ];
        $DB->insert_record('ildmeta_vocabulary', $audience);

        $subjectarea = [
            'title' => 'subjectarea',
            'terms' => json_encode([
                ["de" => "Einstiegskurse", "en" => "Preparation Courses"],
                ["de" => "Geistes- und Kulturwissenschaften", "en" => "Humanities and Cultural Studies"],
                ["de" => "Gesundheitswissenschaften", "en" => "Health Care / Health Management"],
                ["de" => "Informatik", "en" => "Computer Science"],
                ["de" => "Ingenieurwissenschaften", "en" => "Engineering"],
                ["de" => "Lehramt", "en" => "Teacher Education"],
                ["de" => "Softskills", "en" => "Softskills"],
                ["de" => "Medizin", "en" => "Medicine / Medical Science"],
                ["de" => "Naturwissenschaften", "en" => "Natural Sciences"],
                ["de" => "Rechtswissenschaft", "en" => "Law"],
                ["de" => "Schlüsselqualifikationen", "en" => "Key Skills"],
                ["de" => "Soziale Arbeit", "en" => "Social Work"],
                ["de" => "Sozialwissenschaften", "en" => "Social Sciences"],
                ["de" => "Sprachen", "en" => "Languages"],
                ["de" => "Wirtschaftsinformatik", "en" => "Information Systems"],
                ["de" => "Wirtschaftswissenschaften", "en" => "Economic Sciences"],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ];
        $DB->insert_record('ildmeta_vocabulary', $subjectarea);

        $birdsubjectarea = [
            'title' => 'birdsubjectarea',
            'terms' => json_encode([
                ["de" => "Keine Angabe"],
                ["de" => "Agrar- und Forstwissenschaften"],
                ["de" => "Gesellschafts- und Sozialwissenschaften"],
                ["de" => "Ingenieurwissenschaften"],
                ["de" => "Kunst, Musik, Design"],
                ["de" => "Lehramt"],
                ["de" => "Mathematik, Naturwissenschaften"],
                ["de" => "Medizin, Gesundheitswissenschaften"],
                ["de" => "Sprach-, Kulturwissenschaften"],
                ["de" => "Wirtschaftswissenschaften, Rechtswissenschaften"],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ];
        $DB->insert_record('ildmeta_vocabulary', $birdsubjectarea);

        $languagesubject = [
            'title' => 'languagesubject',
            'terms' => json_encode([
                ["de" => "allgemeinsprachlich"],
                ["de" => "Prüfungsvorbereitung"],
                ["de" => "Fachsprache"],
                ["de" => "spezielle sprachliche Fertigkeiten"],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ];
        $DB->insert_record('ildmeta_vocabulary', $languagesubject);

        $languagelevels = [
            'title' => 'languagelevels',
            'terms' => json_encode([
                ["de" => "A1"],
                ["de" => "A2"],
                ["de" => "B1"],
                ["de" => "B1.1"],
                ["de" => "B1.2"],
                ["de" => "B2"],
                ["de" => "B2.1"],
                ["de" => "B2.2"],
                ["de" => "C1"],
                ["de" => "C2"],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ];
        $DB->insert_record('ildmeta_vocabulary', $languagelevels);

        $edulevel_digcomp22 = [
            'title' => 'edulevel_digcomp22',
            'terms' => json_encode([
                [
                    "de" => "Grundlagen (Level 1)",
                    "en" => "Foundation (Level 1)",
                ],
                [
                    "de" => "Grundlagen (Level 2)",
                    "en" => "Foundation (Level 2)",
                ],
                [
                    "de" => "Aufbau (Level 3)",
                    "en" => "Intermediate (Level 3)",
                ],
                [
                    "de" => "Aufbau (Level 4)",
                    "en" => "Intermediate (Level 4)",
                ],
                [
                    "de" => "Fortgeschritten (Level 5)",
                    "en" => "Advanced (Level 5)",
                ],
                [
                    "de" => "Fortgeschritten (Level 6)",
                    "en" => "Advanced (Level 6)",
                ],
                [
                    "de" => "Hochspezialisiert (Level 7)",
                    "en" => "Highly specialised (Level 7)",
                ],
                [
                    "de" => "Hochspezialisiert (Level 8)",
                    "en" => "Highly specialised (Level 8)",
                ]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ];
        $DB->insert_record('ildmeta_vocabulary', $edulevel_digcomp22);
    }

    private static function detailpage_is_enabled() {
        return get_config('block_ildmetaselect', 'add_detail_page');
    }

    public static function get_external_course_link($courseid) {
        global $CFG;
        if (self::detailpage_is_enabled()) {
            return $CFG->wwwroot . '/blocks/ildmetaselect/detailpage.php?id=' . $courseid;
        }
        return $CFG->wwwroot . '/course/view.php?id=' . $courseid;
    }

    /**
     * Generates a UUID v4.
     *
     * @return string
     */
    public static function guidv4() {
        // Generate 16 bytes (128 bits) of random data.
        $data = random_bytes(16);

        // Set version to 0100.
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10.
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
