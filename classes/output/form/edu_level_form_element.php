<?php

namespace local_ildmeta\output\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once("$CFG->libdir/formslib.php");

class edu_level_form_element {
    public static function toHTML($mform) {
        global $OUTPUT;
        $radioarray = array();
        $options = [
            [
                'value' => 1,
                'id' => 'edulevel_1',
                'overall_level' => get_string('foundation', 'local_ildmeta'),
                'complexity' => get_string('simple_task', 'local_ildmeta'),
                'autonomy' => get_string('with_guidance', 'local_ildmeta'),
                'cognitive' => get_string('remembering', 'local_ildmeta'),
                'style' => 'background-color: #D9C49E;'
            ],
            [
                'value' => 2,
                'id' => 'edulevel_2',
                'complexity' => get_string('simple_task', 'local_ildmeta'),
                'autonomy' => get_string('autonomy_guidance_needed', 'local_ildmeta'),
                'cognitive' => get_string('remembering', 'local_ildmeta'),
                'style' => 'background-color: #C3A276;'
            ],
            [
                'value' => 3,
                'id' => 'edulevel_3',
                'overall_level' => get_string('intermediate', 'local_ildmeta'),
                'complexity' => get_string('well_defined_tasks', 'local_ildmeta'),
                'autonomy' => get_string('on_my_own', 'local_ildmeta'),
                'cognitive' => get_string('understanding', 'local_ildmeta'),
                'style' => 'background-color: #F48465;'
            ],
            [
                'value' => 4,
                'id' => 'edulevel_4',
                'complexity' => get_string('tasks_non_routine_problems', 'local_ildmeta'),
                'autonomy' => get_string('independent_needs', 'local_ildmeta'),
                'cognitive' => get_string('understanding', 'local_ildmeta'),
                'style' => 'background-color: #DE6C4C;'
            ],
            [
                'value' => 5,
                'id' => 'edulevel_5',
                'overall_level' => get_string('advanced', 'local_ildmeta'),
                'complexity' => get_string('different_tasks_problems', 'local_ildmeta'),
                'autonomy' => get_string('guiding_others', 'local_ildmeta'),
                'cognitive' => get_string('applying', 'local_ildmeta'),
                'style' => 'background-color: #DB587D;'
            ],
            [
                'value' => 6,
                'id' => 'edulevel_6',
                'complexity' => get_string('most_appropriate_tasks', 'local_ildmeta'),
                'autonomy' => get_string('adapt_others_complex', 'local_ildmeta'),
                'cognitive' => get_string('evaluating', 'local_ildmeta'),
                'style' => 'background-color: #7A325C;'
            ],
            [
                'value' => 7,
                'id' => 'edulevel_7',
                'overall_level' => get_string('highly_specialised', 'local_ildmeta'),
                'complexity' => get_string('resolve_complex_problems_limited', 'local_ildmeta'),
                'autonomy' => get_string('integrate_contribute_professional', 'local_ildmeta'),
                'cognitive' => get_string('creating', 'local_ildmeta'),
                'style' => 'background-color: #266A89;'
            ],
            [
                'value' => 8,
                'id' => 'edulevel_8',
                'complexity' => get_string('resolve_complex_problems_many', 'local_ildmeta'),
                'autonomy' => get_string('propose_new_ideas', 'local_ildmeta'),
                'cognitive' => get_string('creating', 'local_ildmeta'),
                'style' => 'background-color: #1E5169;'
            ]
        ];

        $template_data = ['options' => []];
        foreach ($options as $option) {
            $radioinput = $mform->createElement('radio', 'edulevel', '', $option['value'], $option['value'], ['id' => $option['id']]);
            $radioarray[] = $radioinput;
            $option['radio'] = $radioinput->toHtml();
            $option['label'] = $option['value'];
            $template_data['options'][] = $option;
        }

        $radioinput = $mform->createElement('radio', 'edulevel', '', get_string('skipselection', 'local_ildmeta'), 0, ['id' => 'edulevel_none']);
        $radioarray[] = $radioinput;
        $template_data['none_option'] = $radioinput->toHtml();

        // Call the method that generates the output
        $mform->addGroup($radioarray, 'edulevel_group', get_string('edulevel', 'local_ildmeta'), [' '], false);

        // Set the default value
        $mform->setDefault('edulevel', 0);

        // Render the custom HTML
        $html = $OUTPUT->render_from_template('local_ildmeta/edu_level_form_element', $template_data);
        $mform->addElement('html', $html);
    }
}
