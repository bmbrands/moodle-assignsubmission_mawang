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

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use assignsubmission_mawang\local\persistent\draft;

/**
 * Class store_draft
 *
 * @package    assignsubmission_mawang
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class store_draft extends external_api {

    /**
     * Returns the parameters for the store_draft function.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'assignmentid' => new external_value(PARAM_INT, 'The assignment ID'),
            'fieldid' => new external_value(PARAM_INT, 'The field id'),
            'value' => new external_value(PARAM_RAW, 'The value of the field'),
        ]);
    }

    /**
     * Execute the store_draft function.
     *
     * @param int $assignmentid
     * @param int $fieldid
     * @param string $value
     * @return bool
     */
    public static function execute(int $assignmentid, int $fieldid, string $value): bool {
        global $USER;
        $params = self::validate_parameters(self::execute_parameters(), [
            'assignmentid' => $assignmentid,
            'fieldid' => $fieldid,
            'value' => $value,
        ]);
        $value = trim($params['value']);
        $draft = draft::get_record([
            'assignment' => $params['assignmentid'],
            'fieldid' => $params['fieldid'],
            'userid' => $USER->id,
        ]);
        if (!$draft) {
            $draft = new draft();
            $draft->set('assignment', $params['assignmentid']);
            $draft->set('fieldid', $params['fieldid']);
            $draft->set('userid', $USER->id);
        }
        $draft->set('data', $value);
        $draft->save();
        return true;
    }

    /**
     * Returns the description of the execute function.
     *
     * @return string
     */
    public static function execute_returns(): external_value {
        return new external_value(PARAM_BOOL, 'Status message indicating success or failure of the operation');
    }
}