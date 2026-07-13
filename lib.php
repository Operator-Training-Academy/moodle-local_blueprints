<?php
// This file is part of Moodle - https://moodle.org/

defined('MOODLE_INTERNAL') || die();

function local_blueprints_root_category_id(): int {
    return (int)get_config('local_blueprints', 'rootcategoryid');
}

function local_blueprints_root_category(): ?core_course_category {
    $rootid = local_blueprints_root_category_id();
    if (!$rootid) {
        return null;
    }

    try {
        return core_course_category::get($rootid, IGNORE_MISSING, true);
    } catch (moodle_exception $e) {
        return null;
    }
}

function local_blueprints_category_ids(int $rootid): array {
    global $DB;

    $like = $DB->sql_like('path', ':path', false);
    $records = $DB->get_records_select(
        'course_categories',
        'id = :rootid OR ' . $like,
        ['rootid' => $rootid, 'path' => '%/' . $rootid . '/%'],
        'sortorder ASC',
        'id, name, parent, depth, path'
    );

    return array_map('intval', array_keys($records));
}

function local_blueprints_categories(int $rootid): array {
    global $DB;

    $like = $DB->sql_like('path', ':path', false);
    return $DB->get_records_select(
        'course_categories',
        'id = :rootid OR ' . $like,
        ['rootid' => $rootid, 'path' => '%/' . $rootid . '/%'],
        'sortorder ASC',
        'id, name, parent, depth, path'
    );
}

function local_blueprints_get_blueprints(int $rootid, int $categoryid = 0, string $search = ''): array {
    global $DB;

    $categoryids = $categoryid ? [$categoryid] : local_blueprints_category_ids($rootid);
    if (!$categoryids) {
        return [];
    }

    [$insql, $params] = $DB->get_in_or_equal($categoryids, SQL_PARAMS_NAMED, 'cat');
    $where = 'c.id <> :siteid AND c.category ' . $insql;
    $params['siteid'] = SITEID;

    if ($search !== '') {
        $searchwhere = [];
        $searchparams = [];
        foreach (['fullname', 'shortname', 'idnumber'] as $field) {
            $param = 'search' . $field;
            $searchwhere[] = $DB->sql_like('c.' . $field, ':' . $param, false, false);
            $searchparams[$param] = '%' . $DB->sql_like_escape($search) . '%';
        }
        $where .= ' AND (' . implode(' OR ', $searchwhere) . ')';
        $params += $searchparams;
    }

    return $DB->get_records_sql(
        "SELECT c.id, c.fullname, c.shortname, c.category, c.summary, cc.name AS categoryname
           FROM {course} c
           JOIN {course_categories} cc ON cc.id = c.category
          WHERE $where
       ORDER BY cc.sortorder ASC, c.sortorder ASC, c.fullname ASC",
        $params
    );
}

function local_blueprints_destination_categories(): array {
    $categories = core_course_category::make_categories_list('local/blueprints:createfromblueprint');
    return $categories ?: [];
}

function local_blueprints_course_image_url(int $courseid): ?moodle_url {
    global $CFG;

    $course = new core_course_list_element(get_course($courseid));

    foreach ($course->get_course_overviewfiles() as $file) {
        if (!$file->is_valid_image()) {
            continue;
        }

        return moodle_url::make_file_url(
            "$CFG->wwwroot/pluginfile.php",
            '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                $file->get_filearea() . $file->get_filepath() . $file->get_filename(),
            false
        );
    }

    return null;
}

function local_blueprints_disable_user_data_settings($plan): void {
    if (!method_exists($plan, 'get_settings')) {
        return;
    }

    $userdata = [
        'anonymize',
        'badges',
        'calendars',
        'comments',
        'grade_histories',
        'logs',
        'role_assignments',
        'users',
        'userscompletion',
    ];

    foreach ($plan->get_settings() as $setting) {
        if (!method_exists($setting, 'get_name') || !method_exists($setting, 'set_value')) {
            continue;
        }

        $name = $setting->get_name();
        $isuserinfo = substr($name, -9) === '_userinfo';
        if (!in_array($name, $userdata, true) && !$isuserinfo) {
            continue;
        }

        if (method_exists($setting, 'get_status') && $setting->get_status() !== base_setting::NOT_LOCKED) {
            continue;
        }

        $setting->set_value(false);
    }
}

function local_blueprints_clone_course(
    int $blueprintid,
    string $fullname,
    string $shortname,
    int $categoryid,
    int $startdate = 0,
    ?int $actorid = null
): int {
    global $CFG, $USER;

    require_once($CFG->dirroot . '/course/lib.php');
    require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
    require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

    $actorid = $actorid ?? (int)$USER->id;
    $root = local_blueprints_root_category();
    if (!$root) {
        throw new moodle_exception('missingrootcategory', 'local_blueprints');
    }

    $blueprint = get_course($blueprintid);
    if (!in_array((int)$blueprint->category, local_blueprints_category_ids($root->id), true)) {
        throw new moodle_exception('invalidcourseid');
    }

    require_capability(
        'local/blueprints:createfromblueprint',
        context_coursecat::instance($root->id),
        $actorid
    );

    $destinationcontext = context_coursecat::instance($categoryid);
    require_capability('local/blueprints:createfromblueprint', $destinationcontext, $actorid);

    $admin = get_admin();
    $privilegeduserid = (int)$admin->id;
    $newcourseid = restore_dbops::create_new_course($fullname, $shortname, $categoryid);

    try {
        $backup = new backup_controller(
            backup::TYPE_1COURSE,
            $blueprintid,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_IMPORT,
            $privilegeduserid
        );
        local_blueprints_disable_user_data_settings($backup->get_plan());
        $backup->execute_plan();
        $backupid = $backup->get_backupid();
        $backup->destroy();

        $restore = new restore_controller(
            $backupid,
            $newcourseid,
            backup::INTERACTIVE_NO,
            backup::MODE_IMPORT,
            $privilegeduserid,
            backup::TARGET_NEW_COURSE
        );
        local_blueprints_disable_user_data_settings($restore->get_plan());

        $precheck = $restore->execute_precheck();
        if (!$precheck) {
            $restore->destroy();
            throw new moodle_exception('restoreprecheckerror', 'backup');
        }

        $restore->execute_plan();
        $restore->destroy();

        update_course((object)[
            'id' => $newcourseid,
            'fullname' => $fullname,
            'shortname' => $shortname,
            'startdate' => $startdate,
        ]);
    } catch (Throwable $e) {
        delete_course($newcourseid, false);
        throw $e;
    }

    rebuild_course_cache($newcourseid, true);

    \local_blueprints\event\course_created_from_blueprint::create([
        'context' => context_course::instance($newcourseid),
        'objectid' => $newcourseid,
        'userid' => $actorid,
        'other' => [
            'blueprintid' => $blueprintid,
            'categoryid' => $categoryid,
            'privilegeduserid' => $privilegeduserid,
        ],
    ])->trigger();

    return $newcourseid;
}

function local_blueprints_before_footer(): string {
    global $PAGE, $OUTPUT;

    if (!isloggedin() || isguestuser()) {
        return '';
    }

    $pagetype = $PAGE->pagetype ?? '';
    $pagepath = $PAGE->url->get_path(false);
    if (!in_array($pagetype, ['course-index-category', 'course-management', 'my-index', 'my-courses'], true)
            && $pagepath !== '/my/courses.php') {
        return '';
    }

    $root = local_blueprints_root_category();
    if (!$root) {
        return '';
    }

    $context = context_coursecat::instance($root->id);
    if (!has_capability('local/blueprints:createfromblueprint', $context)) {
        return '';
    }

    $url = new moodle_url('/local/blueprints/index.php');
    $label = get_string('blueprints', 'local_blueprints');
    $button = html_writer::link($url, $label, [
        'class' => 'btn btn-primary local-blueprints-launch',
        'data-local-blueprints-launch' => '1',
    ]);
    $button = html_writer::div($button, 'singlebutton local-blueprints-singlebutton');

    $jsbutton = json_encode($button);
    $js = "
        (function() {
            var button = $jsbutton;
            if (document.querySelector('[data-local-blueprints-launch]')) {
                return;
            }
            var createForm = document.querySelector('form[action*=\"/course/edit.php\"]');
            if (createForm) {
                var createButton = createForm.closest('.singlebutton') || createForm;
                createButton.insertAdjacentHTML('afterend', button);
                return;
            }

            var target = document.querySelector([
                '#action_bar',
                '[data-region=\"header-actions-container\"]',
                '.coursecat-management-buttons',
                '.page-header-actions',
                '.buttons'
            ].join(', '));
            if (target) {
                target.insertAdjacentHTML('beforeend', button);
            }
        }());
    ";

    $PAGE->requires->js_init_code($js);
    return '';
}
