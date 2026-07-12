<?php
// This file is part of Moodle - https://moodle.org/

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
