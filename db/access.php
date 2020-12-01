<?php
$capabilities = array(

    'local/metatiles:allowaccess' => array(
        'riskbitmask' => RISK_SPAM | RISK_CONFIG,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
            #'kursmanager' => CAP_ALLOW,
            'coursecreator' => CAP_ALLOW
        )
    ),

    'local/metatiles:addinstance' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            #'kursmanager' => CAP_ALLOW,
            'coursecreator' => CAP_ALLOW
        ),
    ),

    'local/metatiles:delete_lecturer' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            #'kursmanager' => CAP_ALLOW,
            'coursecreator' => CAP_ALLOW
        ),
    ),

    'local/metatiles:indexation' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_PROHIBIT,
            #'kursmanager' => CAP_PROHIBIT,
            'coursecreator' => CAP_PROHIBIT
        ),
    )

);