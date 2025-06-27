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

namespace assignsubmission_mawang\local\api;

/**
 * Class mawang
 *
 * @package    assignsubmission_mawang
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mawang {

    /**
     * Get all the valid assignment field types
     *
     * @return array
     */
    public static function get_valid_fieldtypes() {
        return [
            [
                'name' => 'html',
                'label' => get_string('fieldtype_html', 'assignsubmission_mawang'),
            ],
            [
                'name' => 'text',
                'label' => get_string('fieldtype_text', 'assignsubmission_mawang'),
            ],
            [
                'name' => 'textarea',
                'label' => get_string('fieldtype_textarea', 'assignsubmission_mawang'),
            ],
            [
                'name' => 'date_selector',
                'label' => get_string('fieldtype_date', 'assignsubmission_mawang'),
            ],
        ];
    }
}
