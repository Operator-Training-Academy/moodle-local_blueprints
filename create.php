<?php
// This file is part of Moodle - https://moodle.org/

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$blueprintid = required_param('blueprintid', PARAM_INT);

require_login();

$root = local_blueprints_root_category();
if (!$root) {
    throw new moodle_exception('missingrootcategory', 'local_blueprints');
}

$rootcontext = context_coursecat::instance($root->id);
require_capability('local/blueprints:createfromblueprint', $rootcontext);

$validblueprintids = array_map('intval', array_keys(local_blueprints_get_blueprints($root->id)));
if (!in_array($blueprintid, $validblueprintids, true)) {
    throw new moodle_exception('invalidcourseid');
}

$blueprint = get_course($blueprintid);
$availablecategories = local_blueprints_destination_categories();
if (!$availablecategories) {
    throw new moodle_exception('permissionerror', 'local_blueprints');
}
$destinationcategories = [0 => get_string('choosecategory', 'local_blueprints')] + $availablecategories;

$PAGE->set_url(new moodle_url('/local/blueprints/create.php', ['blueprintid' => $blueprintid]));
$PAGE->set_context($rootcontext);
$PAGE->set_title(get_string('createfromblueprint', 'local_blueprints'));
$PAGE->set_heading(get_string('createfromblueprint', 'local_blueprints'));

require_once(__DIR__ . '/classes/form/create_form.php');

$requestedstep = optional_param('step', 1, PARAM_INT);
$requestedstep = min(3, max(1, $requestedstep));
$displaystep = $requestedstep;

$SESSION->local_blueprints_wizard ??= [];
$wizardstate = $SESSION->local_blueprints_wizard[$blueprintid] ?? [];

$formdata = [
    'blueprintid' => $blueprintid,
    'step' => $requestedstep,
    'categoryid' => $wizardstate['categoryid'] ?? optional_param('categoryid', 0, PARAM_INT),
    'fullname' => $wizardstate['fullname'] ?? optional_param('fullname', $blueprint->fullname, PARAM_TEXT),
    'shortname' => $wizardstate['shortname'] ?? optional_param('shortname', '', PARAM_TEXT),
    'startdate' => $wizardstate['startdate'] ?? optional_param('startdate', usergetmidnight(time()), PARAM_INT),
];
if (trim($formdata['fullname']) === '') {
    $formdata['fullname'] = $blueprint->fullname;
}

$buildform = function(int $step) use ($destinationcategories, $blueprint): \local_blueprints\form\create_form {
    return new \local_blueprints\form\create_form(null, [
        'categories' => $destinationcategories,
        'blueprintfullname' => $blueprint->fullname,
        'shortnameplaceholder' => (string)get_config('local_blueprints', 'shortnameplaceholder'),
        'step' => $step,
    ]);
};

$mform = $buildform($requestedstep);
$mform->set_data($formdata);

if ($mform->is_cancelled()) {
    unset($SESSION->local_blueprints_wizard[$blueprintid]);
    redirect(new moodle_url('/local/blueprints/index.php'));
}

$isback = optional_param('wizardback', '', PARAM_RAW) !== '';
$isnext = optional_param('wizardnext', '', PARAM_RAW) !== '';

if ($isback) {
    $displaystep = max(1, $requestedstep - 1);
} else if ($data = $mform->get_data()) {
    $formdata = [
        'blueprintid' => (int)$data->blueprintid,
        'step' => $requestedstep,
        'categoryid' => (int)$data->categoryid,
        'fullname' => trim($data->fullname),
        'shortname' => trim($data->shortname),
        'startdate' => (int)$data->startdate,
    ];
    if ($formdata['fullname'] === '') {
        $formdata['fullname'] = $blueprint->fullname;
    }

    if ($isnext) {
        $displaystep = min(3, $requestedstep + 1);
    } else {
        require_sesskey();
        $destinationcontext = context_coursecat::instance((int)$formdata['categoryid']);
        require_capability('local/blueprints:createfromblueprint', $destinationcontext);

        $newcourseid = local_blueprints_clone_course(
            (int)$formdata['blueprintid'],
            $formdata['fullname'],
            $formdata['shortname'],
            (int)$formdata['categoryid'],
            (int)$formdata['startdate'],
            (int)$USER->id
        );

        unset($SESSION->local_blueprints_wizard[$blueprintid]);

        redirect(
            new moodle_url('/course/view.php', ['id' => $newcourseid]),
            get_string('coursecreated', 'local_blueprints'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }
}

if ($displaystep !== $requestedstep) {
    $SESSION->local_blueprints_wizard[$blueprintid] = [
        'categoryid' => (int)$formdata['categoryid'],
        'fullname' => $formdata['fullname'],
        'shortname' => $formdata['shortname'],
        'startdate' => (int)$formdata['startdate'],
    ];
    redirect(new moodle_url('/local/blueprints/create.php', [
        'blueprintid' => $blueprintid,
        'step' => $displaystep,
    ]));
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
