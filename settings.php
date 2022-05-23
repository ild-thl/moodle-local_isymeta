<?php

defined('MOODLE_INTERNAL') || die;

if ( $hassiteconfig ){

    /*
        WIP Method works fine but needs further testing
        Alternative to user_info_field method
    */

    $settings = new admin_settingpage( 'local_isymeta', 'iSyMeta Meta-Settings' );
    // $settings->add(new admin_setting_heading('paygw_paypal_settings', '', 'asddasd'));

    $ADMIN->add( 'localplugins', $settings );

    if ($ADMIN->fulltree) {
        // DE
        $settings->add( new admin_setting_configtext(
            'local_isymeta/metastring1_de', // ref
            'Meta 1 - DE',
            '',
            'Zielgruppe',
            PARAM_TEXT
        ) );

        $settings->add( new admin_setting_configtext(
            'local_isymeta/metastring2_de', // ref
            'Meta 2 - DE',
            '',
            'Programm',
            PARAM_TEXT
        ) );

        $settings->add( new admin_setting_configtext(
            'local_isymeta/metastring3_de', // ref
            'Meta 3 - DE',
            '',
            'Autor/in',
            PARAM_TEXT
        ) );

        $settings->add( new admin_setting_configtext(
            'local_isymeta/metastring4_de', // ref
            'Meta 4 - DE',
            '',
            'Arbeitsaufwand',
            PARAM_TEXT
        ) );

        $settings->add( new admin_setting_configtext(
            'local_isymeta/metastring5_de', // ref
            'Meta 5 - DE',
            '',
            'Kursbeginn',
            PARAM_TEXT
        ) );

        $settings->add( new admin_setting_configtext(
            'local_isymeta/metastring6_de', // ref
            'Meta 6 - DE',
            '',
            'Format',
            PARAM_TEXT
        ) );

        // EN
        $settings->add( new admin_setting_configtext(
            'local_isymeta/metastring1_en', // ref
            'Meta 1 - EN',
            '',
            'Target group',
            PARAM_TEXT
        ) );

        $settings->add( new admin_setting_configtext(
            'local_isymeta/metastring2_en', // ref
            'Meta 2 - EN',
            '',
            'Program',
            PARAM_TEXT
        ) );

        $settings->add( new admin_setting_configtext(
            'local_isymeta/metastring3_en', // ref
            'Meta 3 - EN',
            '',
            'Lecturer',
            PARAM_TEXT
        ) );

        $settings->add( new admin_setting_configtext(
            'local_isymeta/metastring4_en', // ref
            'Meta 4 - EN',
            '',
            'Workload',
            PARAM_TEXT
        ) );

        $settings->add( new admin_setting_configtext(
            'local_isymeta/metastring5_en', // ref
            'Meta 5 - EN',
            '',
            'Course start',
            PARAM_TEXT
        ) );

        $settings->add( new admin_setting_configtext(
            'local_isymeta/metastring6_en', // ref
            'Meta 6 - EN',
            '',
            'Format',
            PARAM_TEXT
        ) );

        $setting = new admin_setting_confightmleditor(
            'local_isymeta/meta1_selection',
            'Meta 1 (Standard: Zielgruppe)',
            'Syntax: (ID):(DE-String)|(EN-String)<br>Exaktes Format beibehalten. Wenn Einträge gelöscht werden sollen, komplette Zeile entfernen, ID (Zahl) der übrigen Einträge nicht anpassen. Neuer Eintrag in neue Zeile, ID (Zahl) hochzählen.',
            '1:Zielgruppe 1|Target group 1<br>2:Zielgruppe 2|Target group 2<br>3:Zielgruppe 3|Target group 3');
        $setting->set_force_ltr(true);
        $settings->add($setting);

        $setting2 = new admin_setting_confightmleditor(
            'local_isymeta/meta2_selection',
            'Meta 2 (Standard: Programm)',
            'Syntax: (ID):(DE-String)|(EN-String)<br>Exaktes Format beibehalten. Wenn Einträge gelöscht werden sollen, komplette Zeile entfernen, ID (Zahl) der übrigen Einträge nicht anpassen. Neuer Eintrag in neue Zeile, ID (Zahl) hochzählen.',
            '1:Programm 1|Program 1<br>2:Programm 2|Program 2<br>3:Programm 3|Program 3');
        $setting2->set_force_ltr(true);
        $settings->add($setting2);

        $setting3 = new admin_setting_confightmleditor(
            'local_isymeta/meta6_selection',
            'Meta 6 (Standard: Format)',
            'Syntax: (ID):(DE-String)|(EN-String)<br>Exaktes Format beibehalten. Wenn Einträge gelöscht werden sollen, komplette Zeile entfernen, ID (Zahl) der übrigen Einträge nicht anpassen. Neuer Eintrag in neue Zeile, ID (Zahl) hochzählen.',
            '1:Format 1|Format 1<br>2:Format 2|Format 2<br>3:Format 3|Format 3');
        $setting3->set_force_ltr(true);
        $settings->add($setting3);

    }
}