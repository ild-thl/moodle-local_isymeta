<?php

defined('MOODLE_INTERNAL') || die();

$tasks = array(
	array(
		'classname' => 'local_ildmeta\task\generate_moochub_task',
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*',
		'day' => '*',
		'month' => '*',
		'dayofweek' => '*'
	)
);