<?php

if ( $hassiteconfig ){

    $settings = new admin_settingpage( 'local_isymeta', 'iSyMeta Meta-Settings' );

    // Create 
    $ADMIN->add( 'localplugins', $settings );

    // DE
    $settings->add( new admin_setting_configtext(
        'local_isymeta/meta1_de', // ref
        'Meta 1 - DE',
        '',
        'Zielgruppe',
        PARAM_TEXT
    ) );

    $settings->add( new admin_setting_configtext(
        'local_isymeta/meta2_de', // ref
        'Meta 2 - DE',
        '',
        'Programm',
        PARAM_TEXT
    ) );

    $settings->add( new admin_setting_configtext(
        'local_isymeta/meta3_de', // ref
        'Meta 3 - DE',
        '',
        'Autor/in',
        PARAM_TEXT
    ) );

    $settings->add( new admin_setting_configtext(
        'local_isymeta/meta4_de', // ref
        'Meta 4 - DE',
        '',
        'Arbeitsaufwand',
        PARAM_TEXT
    ) );

    $settings->add( new admin_setting_configtext(
        'local_isymeta/meta5_de', // ref
        'Meta 5 - DE',
        '',
        'Kursbeginn',
        PARAM_TEXT
    ) );

    $settings->add( new admin_setting_configtext(
        'local_isymeta/meta6_de', // ref
        'Meta 6 - DE',
        '',
        'Format',
        PARAM_TEXT
    ) );

    // EN
    $settings->add( new admin_setting_configtext(
        'local_isymeta/meta1_en', // ref
        'Meta 1 - EN',
        '',
        'Target group',
        PARAM_TEXT
    ) );

    $settings->add( new admin_setting_configtext(
        'local_isymeta/meta2_en', // ref
        'Meta 2 - EN',
        '',
        'Program',
        PARAM_TEXT
    ) );

    $settings->add( new admin_setting_configtext(
        'local_isymeta/meta3_en', // ref
        'Meta 3 - EN',
        '',
        'Lecturer',
        PARAM_TEXT
    ) );

    $settings->add( new admin_setting_configtext(
        'local_isymeta/meta4_en', // ref
        'Meta 4 - EN',
        '',
        'Workload',
        PARAM_TEXT
    ) );

    $settings->add( new admin_setting_configtext(
        'local_isymeta/meta5_en', // ref
        'Meta 5 - EN',
        '',
        'Course start',
        PARAM_TEXT
    ) );

    $settings->add( new admin_setting_configtext(
        'local_isymeta/meta6_en', // ref
        'Meta 6 - EN',
        '',
        'Format',
        PARAM_TEXT
    ) );
}