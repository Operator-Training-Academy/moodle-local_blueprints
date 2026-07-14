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
 * Course created from blueprint event for the local_blueprints plugin.
 *
 * @package   local_blueprints
 * @copyright 2026, Operator Training Academy <otancoic@operatortraining.academy>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_blueprints\event;

defined('MOODLE_INTERNAL') || die();

class course_created_from_blueprint extends \core\event\base {
    protected function init(): void {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'course';
    }

    public static function get_name(): string {
        return get_string('eventcoursecreatedfromblueprint', 'local_blueprints');
    }

    public function get_description(): string {
        return "The user with id '{$this->userid}' created course with id '{$this->objectid}' " .
            "from blueprint course with id '{$this->other['blueprintid']}'.";
    }

    public function get_url(): \moodle_url {
        return new \moodle_url('/course/view.php', ['id' => $this->objectid]);
    }

    public static function get_objectid_mapping(): array {
        return ['db' => 'course', 'restore' => 'course'];
    }

    public static function get_other_mapping(): array {
        return [
            'blueprintid' => ['db' => 'course', 'restore' => 'course'],
            'categoryid' => ['db' => 'course_categories', 'restore' => 'course_category'],
            'privilegeduserid' => ['db' => 'user', 'restore' => 'user'],
        ];
    }
}
