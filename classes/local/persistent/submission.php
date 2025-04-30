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

namespace assignsubmission_mawang\local\persistent;

use field;
use value;

use core\persistent;
use lang_string;

/**
 * Class submission
 *
 * @package    assignsubmission_mawang
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submission extends persistent {
    /**
     * Current table
     */
    const TABLE = 'assignsubmission_mawang';

    /**
     * Return the custom definition of the properties of this model.
     *
     * Each property MUST be listed here.
     *
     * @return array Where keys are the property names.
     */
    protected static function define_properties() {
        return [
            'assignment' => [
                'null' => NULL_NOT_ALLOWED,
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'assignsubmission_mawang', 'assignment'),
            ],
            'submission' => [
                'null' => NULL_NOT_ALLOWED,
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'assignsubmission_mawang', 'submission'),
            ],
        ];
    }

    /**
     * Get the fields for this submission.
     *
     * @return field[]
     */
    public function get_fields() {
        return fields::get_all_fields($this->get('assignment'));
    }

    /**
     * Gets the fiels and values for this submission.
     *
     * @return array
     */
    public function get_fields_and_values() {
        $fields = $this->get_fields();
        $values = values::get_all_values($this->get('submission'));

        $fieldsarray = [];
        foreach ($fields as $field) {
            $tempfield = $field->to_record();
            $tempfield->values = [];
            foreach ($values as $value) {
                if ($value->get('fieldid') == $field->get('id')) {
                    $tempfield->values[] = $value->get('data');
                }
            }
        }

        return $fieldsarray;
    }
}
