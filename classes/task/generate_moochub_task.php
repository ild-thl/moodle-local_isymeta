<?php

namespace local_ildmeta\task;

use context_system;
use context_course;
use moodle_url;

class generate_moochub_task extends \core\task\scheduled_task
{

	public function get_name()
	{
		return get_string('generate_moochub_task', 'local_ildmeta');
	}

	public function execute()
	{
		global $CFG, $DB;

		$json = array();
		$json['links'] = array(
			'self' => '',
			'first' => '',
			'last' => '',
			'prev' => '',
			'next' => ''
		);

		$json['data'] = array();

		$data_entry = array();

		$products = $DB->get_records('ildmeta');
		foreach ($products as $product) {
			if ($product->noindexcourse == 0 && $DB->record_exists('course', array('id' => $product->courseid))) {
				$data_entry = array();
				$data_entry['type'] = 'courses';
				$data_entry['id'] = 'fs' . $product->courseid;
				$data_entry['attributes'] = array();
				$data_entry['attributes']['url'] = $CFG->wwwroot . '/blocks/ildmetaselect/detailpage.php?id=' . $product->courseid;

				$universities = $DB->get_record('user_info_field', array('shortname' => 'universities'));
				$subjectareas = $DB->get_record('user_info_field', array('shortname' => 'subjectareas'));

				//Liste noch statisch, später dynamisch (?)
				$lang_list = [
					'Deutsch',
					'Englisch'
				];

				$fs = get_file_storage();
				$fileurl = '';
				$context = context_course::instance($product->courseid);
				$files = $fs->get_area_files($context->id, 'local_ildmeta', 'overviewimage', 0);
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
				$data_entry['attributes']['university'] = explode("\n", $universities->param1)[$product->university];
				$data_entry['attributes']['languages'] = $lang_list[$product->courselanguage];
				$data_entry['attributes']['subjectarea'] = explode("\n", $subjectareas->param1)[$product->subjectarea];
				$data_entry['attributes']['processingtime'] = $product->processingtime . ' Stunden';
				$data_entry['attributes']['startDate'] = date('d.m.y', $product->starttime);
				$data_entry['attributes']['teasertext'] = $product->teasertext;

				$json['data'][] = $data_entry;
			}
			mtrace('product added: ' . $product->courseid . ' ' . $product->coursetitle);
		}

		if ($fp = fopen($CFG->dirroot . '/courses.json', 'w')) {
			fwrite($fp, json_encode($json));
			fclose($fp);
		} else {
			mtrace('Error opening file:' . $CFG->dirroot . '/courses.json');
		}



		/*
			courses_moochub.json
		*/

		$metas = [];
		$meta_entry = [];
		$meta_records = $DB->get_records('ildmeta');

		$jsonlink = 'https://futurelearnlab.de/hub/courses_moochub.json';

		$metaslinks = ['self' => $jsonlink, 'first' => $jsonlink, 'last' => $jsonlink];

		$metas['links'] = $metaslinks;

		$coursecount = 0;

		$blacklist = [
			37, 40, 41, 42, 43, 44, 45, 53, 47, 46, 48, 38, 49, 50, 51, 52, 39, 8
		];

		foreach ($meta_records as $meta) {

			if ($meta->noindexcourse == 0 && $DB->record_exists('course', array('id' => $meta->courseid))) {

				foreach ($blacklist as $black) {
					if ($meta->courseid == $black) {
						mtrace('- Kurs ' . $meta->coursetitle . ' mit der Kurs-ID ' . $meta->courseid . ' übersprungen (blacklisted).');
						continue 2;
					}
				}

				$fs = get_file_storage();
				$fileurl = '';
				$context = context_course::instance($meta->courseid);
				$files = $fs->get_area_files($context->id, 'local_ildmeta', 'overviewimage', 0);
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

				$video = null;
				if (!$meta->videocode == null) {
					$video = $meta->videocode;
				}

				$duration = null;
				if (!$meta->processingtime == null) {
					$duration .= 'P' . $meta->processingtime . 'H';
				}


				$course = get_course($meta->courseid);




				$meta_entry = [];
				$meta_entry['type'] = 'courses';
				$meta_entry['id']	 = 'futurelearnlab' . $meta->courseid;
				$meta_entry['attributes'] = [];
				$meta_entry['attributes']['name']		 = $meta->coursetitle;
				$meta_entry['attributes']['courseCode']	 = 'futurelearnlab' . $meta->courseid;
				$meta_entry['attributes']['courseMode']  = 'MOOC';
				$meta_entry['attributes']['url'] 		 = $CFG->wwwroot . '/blocks/ildmetaselect/detailpage.php?id=' . $meta->courseid;
				// $meta_entry['attributes']['publisher']	 = $meta->lecturer;
				if ($meta->lecturer == '') {
					mtrace($meta->courseid);
				}
				$meta_entry['attributes']['abstract']	 = null;
				if ($meta->teasertext == '') {
					$meta_entry['attributes']['description'] = null;
				} else {
					$meta_entry['attributes']['description'] = $meta->teasertext;
				}


				$meta_entry['attributes']['languages']	 = ['de-DE'];
				date_default_timezone_set("UTC");
				$meta_entry['attributes']['startDate']	 = date('c', $meta->starttime);
				$meta_entry['attributes']['endDate']	 = null;
				// $meta_entry['attributes']['image']		 = (string)$fileurl;

				if (trim((string)$fileurl) == '') {
					$meta_entry['attributes']['image'] = null;
				} else {
					$meta_entry['attributes']['image'] = array();
					// $meta_entry['attributes']['image'] = [trim((string)$fileurl)];
					$meta_entry['attributes']['image']['url'] = trim((string)$fileurl);
					$meta_entry['attributes']['image']['licenses'] = array();
					$meta_entry['attributes']['image']['licenses'][0]['id'] = 'CC-BY-4.0';
					$meta_entry['attributes']['image']['licenses'][0]['url'] = 'https://creativecommons.org/licenses/by/4.0';
				}


				// $meta_entry['attributes']['duration']	 = $duration;
				$meta_entry['attributes']['availableUntil']	 = null;

				// $lecturer = explode(', ', $meta->lecturer);
				// $meta_entry['attributes']['instructors'] = [];
				// for ($i = 0; $i < count($lecturer); $i++) {
				//     $meta_entry['attributes']['instructors'][$i] = new \stdClass;

				// 	if($lecturer[$i] != '') {
				// 		$meta_entry['attributes']['instructors'][$i]->name = null;
				// 	} else {
				// 		$meta_entry['attributes']['instructors'][$i]->name = $lecturer[$i];
				// 	}

				// }


				$lecturer = explode(', ', $meta->lecturer);

				$meta_entry['attributes']['instructors'] = array();
				for ($i = 0; $i < count($lecturer); $i++) {


					if ($lecturer[$i] != '') {
						$meta_entry['attributes']['instructors'][$i] = new \stdClass;
						$meta_entry['attributes']['instructors'][$i]->name = $lecturer[$i];
					}
				}


				if (trim($meta->videocode) == '') {
					$meta_entry['attributes']['video'] = null;
				} else {
					$meta_entry['attributes']['video'] = array();
					$meta_entry['attributes']['video']['url'] = trim($meta->videocode);
					$meta_entry['attributes']['video']['licenses'] = array();
					$meta_entry['attributes']['video']['licenses'][0]['id'] = "Proprietary";
					$meta_entry['attributes']['video']['licenses'][0]['url'] = null;
				}





				$meta_entry['attributes']['courseLicenses'] 		= [];
				$meta_entry['attributes']['courseLicenses'][0]['id'] 	 = 'Proprietary';
				$meta_entry['attributes']['courseLicenses'][0]['url']    = null;

				$meta_entry['attributes']['moocProvider']['name'] = 'Institut für interaktive Systeme';
				$meta_entry['attributes']['moocProvider']['url'] = 'https://www.th-luebeck.de/isy';
				$meta_entry['attributes']['moocProvider']['logo'] = 'https://www.th-luebeck.de/fileadmin/theme/th-luebeck/images/svg/TH_Logo_A4_RGB.svg';

				$meta_entry['attributes']['access'] = ['free'];

				// print_r($meta->videocode);


				$metas['data'][] = $meta_entry;

				mtrace('+ Kurs ' . $meta->coursetitle . ' mit der Kurs-ID ' . $meta->courseid . ' hinzugefügt.');
				$coursecount++;
			}
		}

		if ($fp2 = fopen($CFG->dirroot . '/courses_moochub.json', 'w')) {
			fwrite($fp2, json_encode($metas, JSON_UNESCAPED_SLASHES)); //JSON_PRETTY_PRINT
			fclose($fp2);
		} else {
			mtrace('Error opening file:' . $CFG->dirroot . '/courses_moochub.json');
		}

		mtrace($coursecount . ' Kurse für den Moochub eingebunden.');
	}
}
