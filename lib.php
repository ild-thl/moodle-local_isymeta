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
 * Library of standard moolde plugin functions.
 *
 * @package     local_ildmeta
 * @copyright   2022 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function local_ildmeta_extend_settings_navigation($settingsnav, $context) {
    global $PAGE;

    // Only add this settings item on non-site course pages.
    if (!$PAGE->course or $PAGE->course->id == 1) {
        return;
    }

    // Only let users with the appropriate capability see this settings item.
    if (!has_capability('moodle/backup:backupcourse', context_course::instance($PAGE->course->id))) {
        return;
    }

    if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
        $strfoo = get_string('pluginname', 'local_ildmeta');
        $url = new moodle_url('/local/ildmeta/edit_metadata.php', array('id' => $PAGE->course->id));
        $foonode = navigation_node::create(
            $strfoo,
            $url,
            navigation_node::NODETYPE_LEAF,
            'ildmeta',
            'ildmeta',
            new pix_icon('t/addcontact', $strfoo)
        );
        if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
            $foonode->make_active();
        }
        $settingnode->add_node($foonode);
    }
}

function local_ildmeta_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    // Require login for every other filearea than 'overviewimage'.
    if ($filearea !== 'overviewimage' && $filearea !== 'provider' && strpos($filearea, 'detailslecturer_image_') !== 0) {
        // Make sure the user is logged in and has access to the module.
        require_login($course, true, $cm);

        // Check the relevant capabilities.
        if (!has_capability('local/ildmeta:allowaccess', $context)) {
            return false;
        }
    }

    if ($filearea == 'overviewimage') {
        // Check the contextlevel is as expected.
        if ($context->contextlevel != CONTEXT_COURSE) {
            return false;
        }
    }

    $itemid = (int)array_shift($args); // The first item in the $args array.

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // Array $args is empty => the path is '/'.
    } else {
        $filepath = '/' . implode('/', $args) . '/'; // Array $args contains elements of the filepath.
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_ildmeta', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    // Finally send the file - in this case with a cache lifetime of 0 seconds and no filtering.
    send_stored_file($file, 0, 0, $forcedownload, $options);
}
