<?php

defined('MOODLE_INTERNAL') || die();

$tasks = array(
	array(
		'classname' => 'local_metatiles\task\generate_moochub_task',
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*',
		'day' => '*',
		'month' => '*',
		'dayofweek' => '*'
	)
);