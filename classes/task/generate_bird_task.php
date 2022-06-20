<?php

namespace local_ildmeta\task;

use context_course;
use moodle_url;

class generate_bird_task extends \core\task\scheduled_task
{

	public function get_name()
	{
		return get_string('generate_bird_task', 'local_ildmeta');
	}

	public function execute()
	{
		global $CFG, $DB;

		$metas = [];
		$meta_entry = [];
		$meta_records = $DB->get_records('ildmeta');

		$jsonlink = 'https://futurelearnlab.de/hub/courses_moochub.json';

		$metaslinks = ['self' => $jsonlink, 'first' => $jsonlink, 'last' => $jsonlink];

		$metas['links'] = $metaslinks;

		$coursecount = 0;

		foreach ($meta_records as $meta) {
			// Skip courses that are not supposed to be exported to bird or a course record does not exist.
			if ($meta->exporttobird == false || !$DB->record_exists('course', array('id' => $meta->courseid))) {
				continue;
			}

			$fs = get_file_storage();
			$fileurl = '';
			$context = context_course::instance($meta->courseid);
			$files = $fs->get_area_files($context->id, 'local_ildmeta', 'overviewimage', 0);
			$fileurl = '';
			$imagefile = null;

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
					$imagefile = $file;
				}
			}


			$duration = null;
			if (!$meta->processingtime == null) {
				$duration .= 'P' . $meta->processingtime . 'H';
			}

			$meta_entry = [];
			$meta_entry['type'] = 'courses';
			$meta_entry['id']	 = 'futurelearnlab' . $meta->courseid;
			$meta_entry['attributes'] = [];
			$meta_entry['attributes']['name']		 = $meta->coursetitle;
			$meta_entry['attributes']['courseCode']	 = 'futurelearnlab' . $meta->courseid;
			$meta_entry['attributes']['courseMode']  = ['MOOC'];

			$meta_entry['attributes']['url'] = $CFG->wwwroot . '/blocks/ildmetaselect/detailpage.php?id=' . $meta->courseid;
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
				$meta_entry['attributes']['image']['url'] = trim((string)$fileurl);

				$license = $DB->get_record('license', array('shortname' => $imagefile->get_license()), '*', MUST_EXIST);
				$spdx_license = $DB->get_record('ildmeta_spdx_licenses', array('moodle_license' => $license->id), '*', MUST_EXIST);
				if ($license->shortname != 'unknown') {
					$meta_entry['attributes']['image']['licenses'] = array();
					$meta_entry['attributes']['image']['licenses'][0]['id'] = $spdx_license->spdx_shortname;
					$meta_entry['attributes']['image']['licenses'][0]['url'] = $spdx_license->spdx_url;
					$meta_entry['attributes']['image']['licenses'][0]['name'] = $spdx_license->spdx_fullname;
					$meta_entry['attributes']['image']['licenses'][0]['author'] = $imagefile->get_author();
				}
			}


			$meta_entry['attributes']['availableUntil']	 = null;


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

				if (!empty($meta->videolicense)) {
					$license = $DB->get_record('license', array('id' => $meta->videolicense), '*', IGNORE_MISSING);
					if (isset($license) && !empty($license) && $license->shortname != 'unknown') {
						$spdx_license = $DB->get_record('ildmeta_spdx_licenses', array('moodle_license' => $license->id), '*', MUST_EXIST);
						$meta_entry['attributes']['video']['licenses'] = array();
						$meta_entry['attributes']['video']['licenses'][0]['id'] = $spdx_license->spdx_shortname;
						$meta_entry['attributes']['video']['licenses'][0]['url'] = $spdx_license->spdx_url;
						$meta_entry['attributes']['video']['licenses'][0]['name'] = $spdx_license->spdx_fullname;
					}
				}
			}


			$meta_entry['attributes']['courseLicenses'] 		= [];
			$meta_entry['attributes']['courseLicenses'][0]['id'] 	 = 'Proprietary';
			$meta_entry['attributes']['courseLicenses'][0]['url']    = null;

			$meta_entry['attributes']['moocProvider']['name'] = 'Institut für interaktive Systeme';
			$meta_entry['attributes']['moocProvider']['url'] = 'https://www.th-luebeck.de/isy';
			$meta_entry['attributes']['moocProvider']['logo'] = 'https://www.th-luebeck.de/fileadmin/theme/th-luebeck/images/svg/TH_Logo_A4_RGB.svg';

			$meta_entry['attributes']['access'] = ['free'];

			// Bird attributes.

			// Selbstlerkurs.
			if ($meta->selfpaced) {
				$meta_entry['attributes']['courseMode'][] = 'https://ceds.ed.gov/element/001311#Asynchronous';
			} else {
				$meta_entry['attributes']['courseMode'][] = 'https://ceds.ed.gov/element/001311/#Synchronous';
			}

			// Get Vocabulary from ildmeta_settings.
			$records = $DB->get_records('ildmeta_settings');
			$ildmeta_settings = reset($records);

			// Kursformat.
			$meta_entry['attributes']['courseMode'][] = $meta->courseformat;

			// Audience.
			$meta_entry['attributes']['audience'] = [$meta->audience];

			// Kurstyp.
			$meta_entry['attributes']['educationalAlignment'][0] = [
				'educationalFramework' => 'Bird Kurstyp',
				'targetName' => $meta->coursetype,
			];

			// Erforderliche Vorraussetzungen
			$meta_entry['attributes']['coursePrerequisites'] = $meta->courseprerequisites;


			$metas['data'][] = $meta_entry;

			mtrace('+ Kurs ' . $meta->coursetitle . ' mit der Kurs-ID ' . $meta->courseid . ' hinzugefügt.');
			$coursecount++;
		}

		if ($fp2 = fopen($CFG->dirroot . '/courses_bird.json', 'w')) {
			fwrite($fp2, json_encode($metas, JSON_UNESCAPED_SLASHES)); //JSON_PRETTY_PRINT
			fclose($fp2);
		} else {
			mtrace('Error opening file:' . $CFG->dirroot . '/courses_bird.json');
		}

		mtrace($coursecount . ' Kurse für den Moochub eingebunden.');
	}
}
