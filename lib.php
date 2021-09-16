<?php

defined('MOODLE_INTERNAL') || die();

function local_isymeta_extend_settings_navigation($settingsnav, $context) {
    global $CFG, $PAGE;

    // Only add this settings item on non-site course pages.
    if (!$PAGE->course or $PAGE->course->id == 1) {
        return;
    }

    // Only let users with the appropriate capability see this settings item.
    if (!has_capability('moodle/backup:backupcourse', context_course::instance($PAGE->course->id))) {
        return;
    }

    if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
        $strfoo = get_string('pluginname', 'local_isymeta');
        $url = new moodle_url('/local/isymeta/pages/isymeta.php', array('courseid' => $PAGE->course->id));

        $foonode = navigation_node::create(
            get_string('settings_coursemetas', 'local_isymeta'),
            new moodle_url('/local/isymeta/pages/isymeta.php', array('courseid' => $PAGE->course->id)),
            navigation_node::NODETYPE_LEAF,
            'isymeta',
            'isymeta',
            new pix_icon('t/editstring', get_string('settings_coursemetas', 'local_isymeta'))
        );

        if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
            $foonode->make_active();
        }
        $settingnode->add_node($foonode);

    }
}

function local_isymeta_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {

    global $DB;

    if ($context->contextlevel != CONTEXT_COURSE) {
        return false;
    }

    $itemid = (int)array_shift($args);

    $fs = get_file_storage();
    $filename = array_pop($args);
    
    if (empty($args)) {
        $filepath = '/';
    } else {
        $filepath = '/'.implode('/', $args).'/';
    }

    $file = $fs->get_file($context->id, 'local_isymeta', $filearea, $itemid, $filepath, $filename);

    if (!$file) {
        return false;
    }

    // finally send the file
    send_stored_file($file, 0, 0, true, $options); // download MUST be forced - security!
}