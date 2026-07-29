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
 * Blueprint course browser for the local_blueprints plugin.
 *
 * @package   local_blueprints
 * @copyright 2026, Operator Training Academy <otancoic@operatortraining.academy>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$categoryid = optional_param('categoryid', 0, PARAM_INT);
$search = trim(optional_param('search', '', PARAM_TEXT));

require_login();

$root = local_blueprints_root_category();
if (!$root) {
    $PAGE->set_url(new moodle_url('/local/blueprints/index.php'));
    $PAGE->set_context(context_system::instance());
    $PAGE->set_title(get_string('blueprints', 'local_blueprints'));
    $PAGE->set_heading(get_string('blueprints', 'local_blueprints'));
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('missingrootcategory', 'local_blueprints'), 'warning');
    echo $OUTPUT->footer();
    exit;
}

$rootcontext = context_coursecat::instance($root->id);
require_capability('local/blueprints:createfromblueprint', $rootcontext);

$url = new moodle_url('/local/blueprints/index.php');
if ($categoryid) {
    $url->param('categoryid', $categoryid);
}
if ($search !== '') {
    $url->param('search', $search);
}

$PAGE->set_url($url);
$PAGE->set_context($rootcontext);
$PAGE->set_title(get_string('blueprints', 'local_blueprints'));
$PAGE->set_heading(get_string('blueprints', 'local_blueprints'));

$categories = local_blueprints_categories($root->id);
$validcategoryids = array_map('intval', array_keys($categories));
if ($categoryid && !in_array($categoryid, $validcategoryids, true)) {
    throw new moodle_exception('invalidcategoryid');
}

$blueprints = local_blueprints_get_blueprints($root->id, $categoryid, $search);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('blueprints', 'local_blueprints'));

echo html_writer::start_tag('form', ['method' => 'get', 'class' => 'local-blueprints-filters mb-3']);
echo html_writer::start_div('form-row align-items-end');

echo html_writer::start_div('form-group col-md-5');
echo html_writer::label(get_string('choosecategory', 'local_blueprints'), 'local-blueprints-category');
$options = [0 => get_string('choosecategory', 'local_blueprints')];
foreach ($categories as $category) {
    $prefix = str_repeat('-- ', max(0, (int)$category->depth - (int)$root->depth));
    $options[(int)$category->id] = $prefix . format_string($category->name);
}
echo html_writer::select($options, 'categoryid', $categoryid, false, [
    'id' => 'local-blueprints-category',
    'class' => 'form-control',
    'data-autosubmit' => '1',
]);
echo html_writer::end_div();

echo html_writer::start_div('form-group col-md-5');
echo html_writer::label(get_string('search', 'local_blueprints'), 'local-blueprints-search');
echo html_writer::empty_tag('input', [
    'type' => 'search',
    'name' => 'search',
    'id' => 'local-blueprints-search',
    'class' => 'form-control',
    'value' => s($search),
    'placeholder' => get_string('searchplaceholder', 'local_blueprints'),
]);
echo html_writer::end_div();

echo html_writer::start_div('form-group col-md-2');
echo html_writer::empty_tag('input', ['type' => 'submit', 'class' => 'btn btn-secondary', 'value' => get_string('search')]);
echo html_writer::end_div();

echo html_writer::end_div();
echo html_writer::end_tag('form');

$PAGE->requires->js_call_amd('local_blueprints/category_filter', 'init');

if (!$blueprints) {
    echo $OUTPUT->notification(get_string('noblueprints', 'local_blueprints'), 'info');
} else {
    echo html_writer::start_div('local-blueprints-list');
    foreach ($blueprints as $course) {
        $courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);
        $createurl = new moodle_url('/local/blueprints/create.php', ['blueprintid' => $course->id]);
        $imageurl = local_blueprints_course_image_url((int)$course->id);
        $coursename = format_string($course->fullname);

        echo html_writer::start_div('local-blueprints-item');
        echo html_writer::start_div('local-blueprints-image');
        if ($imageurl) {
            echo html_writer::empty_tag('img', [
                'src' => $imageurl->out(false),
                'alt' => '',
                'loading' => 'lazy',
            ]);
        } else {
            echo html_writer::tag('span', core_text::strtoupper(core_text::substr($coursename, 0, 1)));
        }
        echo html_writer::end_div();

        echo html_writer::start_div('local-blueprints-main');
        echo html_writer::tag('h3', html_writer::link($courseurl, $coursename), ['class' => 'local-blueprints-title']);
        echo html_writer::tag('div', format_string($course->categoryname), ['class' => 'local-blueprints-categoryname']);
        echo html_writer::end_div();

        echo html_writer::start_div('local-blueprints-actions');
        echo html_writer::link($createurl, get_string('createfromblueprint', 'local_blueprints'), ['class' => 'btn btn-primary']);
        echo html_writer::end_div();

        echo html_writer::end_div();
    }
    echo html_writer::end_div();
}

echo $OUTPUT->footer();
