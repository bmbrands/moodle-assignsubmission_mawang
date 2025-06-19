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
 * External functions and service declaration for Simple Form
 *
 * Documentation: {@link https://moodledev.io/docs/apis/subsystems/external/description}
 *
 * @package    assignsubmission_mawang
 * @category   webservice
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'assignsubmission_mawang_get_fields' => [
        'classname'   => \assignsubmission_mawang\external\get_fields::class,
        'methodname'  => 'execute',
        'description' => 'Get the fields for a specific assignment.',
        'type'        => 'read',
        'ajax'        => true,
    ],
    'assignsubmission_mawang_update_fields' => [
        'classname'   => \assignsubmission_mawang\external\update_fields::class,
        'methodname'  => 'execute',
        'description' => 'Save the fields for a specific assignment.',
        'type'        => 'write',
        'ajax'        => true,
    ],
];

