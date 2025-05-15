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
 * TODO describe file addfield
 *
 * @package    assignsubmission_mawang
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../../config.php');

$id = required_param('id', PARAM_INT);
$fieldid = optional_param('fieldid', '', PARAM_INT);
$action = required_param('action', PARAM_TEXT);
$returnurl = optional_param('returnurl', '', PARAM_RAW);
list ($course, $cm) = get_course_and_cm_from_instance($id, 'assign');
$context = context_module::instance($cm->id);
require_login($course, true, $cm);
$url = new moodle_url(urldecode($returnurl));
$url->set_anchor('id_submissiontypescontainer');

if (has_capability('mod/assign:addinstance', $context)) {
    switch ($action) {
        case 'addfield':
            $field = new \assignsubmission_mawang\local\persistent\field();
            $field->set('assignmentid', $cm->instance);
            $field->set('type', 'text');
            $field->set('name', 'New field');
            $field->save();
            break;
        case 'deletefield':
            if ($fieldid) {
                $field = new \assignsubmission_mawang\local\persistent\field($fieldid);
                $field->delete();
            }
            break;
    }
    redirect($url);
} else {
    redirect($url);
}


$url = new moodle_url('/mod/assign/submission/mawang/action.php', []);
$PAGE->set_url($url);
$PAGE->set_context($context);

$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();
echo $OUTPUT->footer();
