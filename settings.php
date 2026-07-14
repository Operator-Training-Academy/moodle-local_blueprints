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

    $settings->add(new admin_setting_configtext(
        'local_blueprints/shortnameplaceholder',
        get_string('shortnameplaceholder', 'local_blueprints'),
        get_string('shortnameplaceholder_desc', 'local_blueprints'),
        get_string('shortnameplaceholder_default', 'local_blueprints'),
        PARAM_TEXT
    ));

    $ADMIN->add('localplugins', $settings);
}
