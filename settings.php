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
 * Plugin administration pages are defined here.
 *
 * @package     local_ildmeta
 * @category    admin
 * @copyright   2022 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $modfolder = new admin_category(
        'localildmetafolder',
        new lang_string(
            'pluginname',
            'local_ildmeta'
        )
    );
    $ADMIN->add('localplugins', $modfolder);

    // $ADMIN->add('localildmetafolder', $settings);
    $ADMIN->add(
        'localildmetafolder',
        new admin_externalpage(
            'localildmeta_edit_vocabulary',
            get_string('edit_vocabulary', 'local_ildmeta'),
            $CFG->wwwroot . '/local/ildmeta/edit_vocabulary.php'
        )
    );
    $ADMIN->add(
        'localildmetafolder',
        new admin_externalpage(
            'localildmeta_edit_provider',
            get_string('edit_provider', 'local_ildmeta'),
            $CFG->wwwroot . '/local/ildmeta/edit_provider.php'
        )
    );
    $ADMIN->add(
        'localildmetafolder',
        new admin_externalpage(
            'localildmeta_edit_licenses',
            get_string('edit_licenses', 'local_ildmeta'),
            $CFG->wwwroot . '/local/ildmeta/edit_licenses.php'
        )
    );
}

// Prevent Moodle from adding settings block in standard location.
$settings = null;
