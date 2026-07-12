<?php
// This file is part of Moodle - https://moodle.org/

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_blueprints', get_string('pluginname', 'local_blueprints'));

    $settings->add(new admin_setting_configtext(
        'local_blueprints/rootcategoryid',
        get_string('rootcategoryid', 'local_blueprints'),
        get_string('rootcategoryid_desc', 'local_blueprints'),
        '',
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_blueprints/shortnameplaceholder',
        get_string('shortnameplaceholder', 'local_blueprints'),
        get_string('shortnameplaceholder_desc', 'local_blueprints'),
        get_string('shortnameplaceholder_default', 'local_blueprints'),
        PARAM_TEXT
    ));

    $ADMIN->add('localplugins', $settings);
}
