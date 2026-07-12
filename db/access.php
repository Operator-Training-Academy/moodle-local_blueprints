<?php
// This file is part of Moodle - https://moodle.org/

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/blueprints:createfromblueprint' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => [
            'manager' => CAP_ALLOW,
            'coursecreator' => CAP_ALLOW,
        ],
        'clonepermissionsfrom' => 'moodle/course:create',
    ],
];
