<?php

namespace local_isymeta\task;

use context_system;
use context_course;
use moodle_url;

class generate_moochub_task extends \core\task\scheduled_task {
 
    public function get_name() {
        return get_string('generate_moochub_task', 'local_isymeta');
    }
     
    public function execute() {
    	global $CFG, $DB;

    	$json = array();
    	$json['links'] = array(
			'self'=>'',
			'first'=>'',
			'last'=>'',
			'prev'=>'',
			'next'=>''
		);

		$json['data'] = array();

		$data_entry = array();

		$products = $DB->get_records('isymeta');
		foreach ($products as $product) {
			if ($product->noindexcourse == 0 && $DB->record_exists('course', array('id' => $product->courseid))) {
				$data_entry = array();
				$data_entry['type'] = 'courses';
				$data_entry['id'] = 'fs'.$product->courseid;
				$data_entry['attributes'] = array();
				$data_entry['attributes']['url'] = $CFG->wwwroot . '/blocks/isymetaselect/detailpage.php?id=' . $product->courseid;

				$universities = $DB->get_record('user_info_field', array('shortname' => 'universities'));
				$meta6s = $DB->get_record('user_info_field', array('shortname' => 'meta6s'));

				//Liste noch statisch, später dynamisch (?)
				$lang_list = [
				    'Deutsch',
				    'Englisch'
				];

				$fs = get_file_storage();
				$fileurl = '';
				$context = context_course::instance($product->courseid);
				$files = $fs->get_area_files($context->id, 'local_isymeta', 'overviewimage', 0);
				$fileurl = '';
				foreach ($files as $file) {
					if ($file->get_filename() !== '.') {
				        $fileurl = moodle_url::make_pluginfile_url(
				            $file->get_contextid(),
				            $file->get_component(),
				            $file->get_filearea(),
				            $file->get_itemid(),
				            $file->get_filepath(),
				            $file->get_filename()
				        );
				    }
				}

				$data_entry['attributes']['name'] = $product->coursetitle;
				$data_entry['attributes']['productImage'] = (string)$fileurl; //overviewimage
				$data_entry['attributes']['publisher'] = $product->lecturer;
				$data_entry['attributes']['meta2'] = explode("\n", $universities->param1)[$product->meta2];
				$data_entry['attributes']['languages'] = $lang_list[$product->courselanguage];
				$data_entry['attributes']['meta6'] = explode("\n", $meta6s->param1)[$product->meta6];
				$data_entry['attributes']['meta4'] = $product->meta4 . ' Stunden';
				$data_entry['attributes']['startDate'] = date('d.m.y', $product->meta5);
				$data_entry['attributes']['teasertext'] = $product->teasertext;

				$json['data'][] = $data_entry;
			}
			mtrace('product added: '.$product->courseid.' '.$product->coursetitle);
		}

		if ($fp = fopen($CFG->dirroot.'/courses.json', 'w')) {
			fwrite($fp, json_encode($json));
			fclose($fp);
		} else {
			mtrace('Error opening file:'.$CFG->dirroot.'/courses.json');
		}
    }
}