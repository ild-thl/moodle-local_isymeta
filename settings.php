<?php

defined('MOODLE_INTERNAL') || die;

if ( $hassiteconfig ){
 
	// Create the new settings page
	// - in a local plugin this is not defined as standard, so normal $settings->methods will throw an error as
	// $settings will be NULL
	$settings = new admin_settingpage( 'local_ildmeta', 'Your Settings Page Title' );
 
	// Create 
	$ADMIN->add( 'localplugins', $settings );
 
	// Add a setting field to the settings for this page
	//$settings->add( new admin_setting_configtext(
 
/*		$name = "Name",
		$title = "Title",
		$description = "asdd",
		$default = "asd",
		PARAM_NOTAGS
 */
	//) );
 
}