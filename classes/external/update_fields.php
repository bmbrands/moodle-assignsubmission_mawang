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

namespace assignsubmission_mawang\external;

use context_module;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use assignsubmission_mawang\external\get_fields as get_fields_external;

use assignsubmission_mawang\local\persistent\field;

/**
 * Class update_fields
 *
 * @package    assignsubmission_mawang
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update_fields extends external_api {
    /**
     * Returns the parameters for the save_fields function.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'assignmentid' => new external_value(PARAM_INT, 'The assignment ID'),
            'fields' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Field ID', VALUE_OPTIONAL),
                    'type' => new external_value(PARAM_TEXT, 'Field type'),
                    'name' => new external_value(PARAM_TEXT, 'Field name'),
                    'deleted' => new external_value(
                        PARAM_BOOL,
                        'Delete field if true',
                        VALUE_OPTIONAL, false
                    ),
                ])
            ),
        ]);
    }

    /**
     * Execute the save_fields function.
     *
     * @param int $assignmentid
     * @param array $fields
     * @return array
     */
    public static function execute(int $assignmentid, array $fields): array {

        list ($course, $cm) = get_course_and_cm_from_instance($assignmentid, 'assign');
        $context = context_module::instance($cm->id);
        self::validate_context($context);

        foreach ($fields as $fielddata) {
            if ($fielddata['deleted']) {
                // If delete is true, delete the field.
                if (!empty($fielddata['id'])) {
                    $field = field::get_record(['id' => $fielddata['id'], 'assignmentid' => $assignmentid]);
                    if ($field) {
                        $field->delete();
                    }
                }
                continue;
            }
            $field = field::get_record(['id' => $fielddata['id'], 'assignmentid' => $assignmentid]);
            if (!$field) {
                $field = new field();
            }
            $field->set('assignmentid', $assignmentid);
            $field->set('type', $fielddata['type']);
            $field->set('name', $fielddata['name']);
            $field->save();
        }

        $fielddata = get_fields_external::execute($assignmentid);
        if (empty($fielddata['fields'])) {
            // If no fields are returned, return an empty array.
            return [
                'fields' => [],
                'fieldtypes' => mawang::get_valid_fieldtypes(),
            ];
        }


        return $fielddata;
    }

    /**
     * Returns the description of the result value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'fields' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Field ID'),
                    'assignmentid' => new external_value(PARAM_INT, 'Assignment ID'),
                    'name' => new external_value(PARAM_TEXT, 'Field name'),
                    'type' => new external_value(PARAM_TEXT, 'Field type'),
                ])
            ),
            'fieldtypes' => new external_multiple_structure(
                new external_single_structure([
                    'name' => new external_value(PARAM_TEXT, 'Field type'),
                    'label' => new external_value(PARAM_TEXT, 'Field type name'),
                ])
            ),
        ]);
    }
}
