<?php
// This file is part of Moodle - https://moodle.org/

namespace local_blueprints\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class create_form extends \moodleform {
    public function definition(): void {
        $mform = $this->_form;
        $categories = $this->_customdata['categories'] ?? [];
        $blueprintfullname = $this->_customdata['blueprintfullname'] ?? '';
        $shortnameplaceholder = $this->_customdata['shortnameplaceholder'] ?? '';
        $step = (int)($this->_customdata['step'] ?? 1);

        $mform->addElement('hidden', 'blueprintid');
        $mform->setType('blueprintid', PARAM_INT);

        $mform->addElement('hidden', 'step');
        $mform->setType('step', PARAM_INT);

        $mform->addElement('html', self::progress_bar($step));
        $mform->addElement('html', \html_writer::start_div('local-blueprints-create-steps'));

        if ($step === 1) {
            $mform->addElement('hidden', 'fullname');
            $mform->setType('fullname', PARAM_TEXT);
            $mform->addElement('hidden', 'shortname');
            $mform->setType('shortname', PARAM_TEXT);
            $mform->addElement('hidden', 'startdate');
            $mform->setType('startdate', PARAM_INT);

            $mform->addElement('html', \html_writer::start_div('local-blueprints-create-step'));
            $mform->addElement('html', \html_writer::tag('h3', get_string('stepcategory', 'local_blueprints')));
            $mform->addElement('select', 'categoryid', get_string('destinationcategory', 'local_blueprints'), $categories);
            $mform->setType('categoryid', PARAM_INT);
            $mform->addRule('categoryid', null, 'required', null, 'client');
            $mform->addElement('html', \html_writer::end_div());
        } else if ($step === 2) {
            $mform->addElement('hidden', 'categoryid');
            $mform->setType('categoryid', PARAM_INT);
            $mform->addElement('hidden', 'startdate');
            $mform->setType('startdate', PARAM_INT);

            $mform->addElement('html', \html_writer::start_div('local-blueprints-create-step'));
            $mform->addElement('html', \html_writer::tag('h3', get_string('stepnames', 'local_blueprints')));
            $mform->addElement('text', 'fullname', get_string('fullname', 'local_blueprints'), ['size' => 64]);
            $mform->setType('fullname', PARAM_TEXT);
            $mform->setDefault('fullname', $blueprintfullname);
            $mform->addRule('fullname', null, 'required', null, 'client');
            $mform->addElement('text', 'shortname', get_string('shortname', 'local_blueprints'), [
                'size' => 32,
                'placeholder' => $shortnameplaceholder,
            ]);
            $mform->setType('shortname', PARAM_TEXT);
            $mform->addRule('shortname', null, 'required', null, 'client');
            $mform->addElement('html', \html_writer::end_div());
        } else if ($step === 3) {
            $mform->addElement('hidden', 'categoryid');
            $mform->setType('categoryid', PARAM_INT);
            $mform->addElement('hidden', 'fullname');
            $mform->setType('fullname', PARAM_TEXT);
            $mform->addElement('hidden', 'shortname');
            $mform->setType('shortname', PARAM_TEXT);

            $mform->addElement('html', \html_writer::start_div('local-blueprints-create-step'));
            $mform->addElement('html', \html_writer::tag('h3', get_string('stepstartdate', 'local_blueprints')));
            $mform->addElement('date_selector', 'startdate', get_string('startdate', 'local_blueprints'));
            $mform->setType('startdate', PARAM_INT);
            $mform->addRule('startdate', null, 'required', null, 'client');
            $mform->addElement('html', \html_writer::end_div());
        }

        $mform->addElement('html', \html_writer::end_div());

        $mform->addElement('html', \html_writer::start_div('local-blueprints-wizard-actions'));
        $mform->addElement('cancel');
        if ($step > 1) {
            $mform->addElement('submit', 'wizardback', get_string('back', 'local_blueprints'));
        }
        if ($step < 3) {
            $mform->addElement('submit', 'wizardnext', get_string('next', 'local_blueprints'));
        } else {
            $mform->addElement('submit', 'submitbutton', get_string('createnewcourse', 'local_blueprints'));
        }
        $mform->addElement('html', \html_writer::end_div());
    }

    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        $step = (int)($data['step'] ?? 1);

        if ($step === 1 && empty($data['categoryid'])) {
            $errors['categoryid'] = get_string('required');
        }

        return $errors;
    }

    private static function progress_bar(int $currentstep): string {
        $items = [];

        for ($step = 1; $step <= 3; $step++) {
            $classes = ['local-blueprints-progress-item'];
            if ($step < $currentstep) {
                $classes[] = 'is-complete';
            } else if ($step === $currentstep) {
                $classes[] = 'is-active';
            }

            $attributes = ['class' => implode(' ', $classes)];
            if ($step === $currentstep) {
                $attributes['aria-current'] = 'step';
            }

            $items[] = \html_writer::tag(
                'li',
                \html_writer::span((string)$step, 'local-blueprints-progress-number'),
                $attributes
            );
        }

        return \html_writer::tag(
            'ol',
            implode('', $items),
            [
                'class' => 'local-blueprints-progress local-blueprints-progress-step-' . $currentstep,
                'aria-label' => get_string('progress', 'local_blueprints'),
            ]
        );
    }
}
