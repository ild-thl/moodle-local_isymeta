<?php

defined('MOODLE_INTERNAL') || die();

function local_ildmeta_extend_settings_navigation($settingsnav, $context) {
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
        $strfoo = get_string('pluginname', 'local_ildmeta');
        $url = new moodle_url('/local/ildmeta/pages/ildmeta.php', array('courseid' => $PAGE->course->id));
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

function local_ildmeta_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {

    global $DB;
    if ($context->contextlevel != CONTEXT_COURSE /*&& $context->contextlevel != CONTEXT_SYSTEM*/) {

        return false;
    }
    //require_login();

    // temporary deactivated because of the dynamic generated fileareas detailslecturer_image_'.$i
  /* if ($filearea != 'overviewimage') {
        return false;
    }*/
    $itemid = (int)array_shift($args);

    /*
    if ($itemid != 5) {
        return false;
    }
*/

    $fs = get_file_storage();
    $filename = array_pop($args);
    if (empty($args)) {
        $filepath = '/';
    } else {
        $filepath = '/'.implode('/', $args).'/';
    }
    $file = $fs->get_file($context->id, 'local_ildmeta', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }
    // finally send the file
    send_stored_file($file, 0, 0, true, $options); // download MUST be forced - security!
}
