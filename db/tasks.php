<?php

defined('MOODLE_INTERNAL') || die();

/* Durch Cronjob in Moodle zu starten */

$tasks = array(
	array(
		'classname' => 'local_isymeta\task\generate_moochub_task',
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*',
		'day' => '*',
		'month' => '*',
		'dayofweek' => '*'
	)
);