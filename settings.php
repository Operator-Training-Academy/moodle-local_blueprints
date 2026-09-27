<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Admin settings for the local_blueprints plugin.
 *
 * @package   local_blueprints
 * @copyright 2026, Operator Training Academy <otancoic@operatortraining.academy>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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

    $settings->add(new admin_setting_configcheckbox(
        'local_blueprints/showbutton',
        get_string('showbutton', 'local_blueprints'),
        get_string('showbutton_desc', 'local_blueprints'),
        1
    ));

    $settings->add(new admin_setting_configtext(
        'local_blueprints/shortnameplaceholder',
        get_string('shortnameplaceholder', 'local_blueprints'),
        get_string('shortnameplaceholder_desc', 'local_blueprints'),
        get_string('shortnameplaceholder_default', 'local_blueprints'),
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_heading(
        'local_blueprints/quizpasswordsettings',
        get_string('quizpasswordsettings', 'local_blueprints'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'local_blueprints/quizpasswordlength',
        get_string('quizpasswordlength', 'local_blueprints'),
        get_string('quizpasswordlength_desc', 'local_blueprints'),
        12,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_blueprints/quizpassworduppercase',
        get_string('quizpassworduppercase', 'local_blueprints'),
        get_string('quizpassworduppercase_desc', 'local_blueprints'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_blueprints/quizpasswordlowercase',
        get_string('quizpasswordlowercase', 'local_blueprints'),
        get_string('quizpasswordlowercase_desc', 'local_blueprints'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_blueprints/quizpasswordnumbers',
        get_string('quizpasswordnumbers', 'local_blueprints'),
        get_string('quizpasswordnumbers_desc', 'local_blueprints'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_blueprints/quizpasswordsymbols',
        get_string('quizpasswordsymbols', 'local_blueprints'),
        get_string('quizpasswordsymbols_desc', 'local_blueprints'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_blueprints/quizpasswordavoidambiguous',
        get_string('quizpasswordavoidambiguous', 'local_blueprints'),
        get_string('quizpasswordavoidambiguous_desc', 'local_blueprints'),
        1
    ));

    $ADMIN->add('localplugins', $settings);
}
