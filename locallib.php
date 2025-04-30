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

use assignsubmission_mawang\local\persistent\submission;
use assignsubmission_mawang\local\persistent\field;
use assignsubmission_mawang\local\persistent\value;
/**
 * Main class for Mawang submission plugin
 *
 * @package    assignsubmission_mawang
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_submission_mawang extends assign_submission_plugin {

    /**
     * Should return the name of this plugin type.
     *
     * @return string - the name
     */
    public function get_name() {
        return get_string('pluginname', 'assignsubmission_mawang');
    }

    /**
     * Remove all data stored in this plugin that is associated with the given submission.
     *
     * @param stdClass $submission record from assign_submission table
     * @return boolean
     */
    public function remove(stdClass $submission) {

        $submissionid = $submission ? $submission->id : 0;
        if ($submissionid) {
            submission::get_record($submissionid)->delete();
        }
        return true;
    }
    /**
     * Moodle Example form elements.
     *
     * These are all valid form elements that can be used in a form.
     */
    public function definition() {
        $mform = $this->_form;

        $required = optional_param('required', false, PARAM_BOOL);
        $help = optional_param('help', false, PARAM_BOOL);
        $mixed = optional_param('mixed', false, PARAM_BOOL);

        // Text.
        $mform->addElement('text', 'textelement', 'Text');
        $mform->setType('textelement', 'text');
        if ($required) {
            $mform->addRule('textelement', null, 'required', null, 'client');
        }
        if ($help && !$mixed) {
            $mform->addHelpButton('textelement', 'summary');
        }
        $mform->setAdvanced('textelement', true);

        // Text with a long label.
        $mform->addElement('text', 'textelementtwo', 'Text element with a long label that can span multiple lines.
            The next field has no label. ');
        $mform->setType('textelementtwo', 'text');
        if ($required) {
            $mform->addRule('textelementtwo', 'This element is required', 'required', null, 'client');
        }
        if ($help) {
            $mform->addHelpButton('textelementtwo', 'summary');
        }
        $mform->setAdvanced('textelementtwo', true);

        // Text without label.
        $mform->addElement('text', 'textelementhree', '', '');
        $mform->setType('textelementhree', 'text');
        if ($required && !$mixed) {
            $mform->addRule('textelementhree', 'This element is required', 'required', null, 'client');
        }
        if ($help && !$mixed) {
            $mform->addHelpButton('textelementhree', 'summary');
        }
        $mform->setAdvanced('textelementhree', true);

        // Button.
        $mform->addElement('button', 'buttonelement', 'Button');
        if ($required) {
            $mform->addRule('buttonelement', 'This element is required', 'required', null, 'client');
        }
        if ($help) {
            $mform->addHelpButton('buttonelement', 'summary');
        }
        $mform->setAdvanced('buttonelement', true);

        // Date.
        $mform->addElement('date_selector', 'date', 'Date selector');
        if ($required && !$mixed) {
            $mform->addRule('date', 'This element is required', 'required', null, 'client');
        }
        if ($help) {
            $mform->addHelpButton('date', 'summary');
        }
        $mform->setAdvanced('date', true);

        // Date time.
        $mform->addElement('date_time_selector', 'datetimesel', 'Date time selector');
        if ($required) {
            $mform->addRule('datetimesel', 'This element is required', 'required', null, 'client');
        }
        if ($help && !$mixed) {
            $mform->addHelpButton('datetimesel', 'summary');
        }
        $mform->setAdvanced('datetimesel', true);

        // Duration (does not support required form fields).
        $mform->addElement('duration', 'duration', 'Duration');
        if ($help) {
            $mform->addHelpButton('duration', 'summary');
        }

        // Editor.
        $mform->addElement('editor', 'editor', 'Editor');
        $mform->setType('editor', PARAM_RAW);
        if ($required) {
            $mform->addRule('editor', 'This element is required', 'required', null, 'client');
        }
        if ($help && !$mixed) {
            $mform->addHelpButton('editor', 'summary');
        }
        $mform->setAdvanced('editor', true);

        // Filepicker.
        $mform->addElement('filepicker', 'userfile', 'Filepicker', null, ['maxbytes' => 100, 'accepted_types' => '*']);
        if ($required) {
            $mform->addRule('userfile', 'This element is required', 'required', null, 'client');
        }
        if ($help) {
            $mform->addHelpButton('userfile', 'summary');
        }
        $mform->setAdvanced('userfile', true);

        // Html.
        $mform->addElement('html', '<div class="text-success h2 ">The HTML only formfield</div>');

        // Passwords.
        $mform->addElement('passwordunmask', 'passwordunmask', 'Passwordunmask');
        if ($required && !$mixed) {
            $mform->addRule('passwordunmask', 'This element is required', 'required', null, 'client');
        }
        if ($help && !$mixed) {
            $mform->addHelpButton('passwordunmask', 'summary');
        }
        $mform->setAdvanced('passwordunmask', true);

        // Radio.
        $mform->addElement('radio', 'radio', 'Radio', 'Radio label', 'choice_value');
        if ($required) {
            $mform->addRule('radio', 'This element is required', 'required', null, 'client');
        }
        if ($help && !$mixed) {
            $mform->addHelpButton('radio', 'summary');
        }
        $mform->setAdvanced('radio', true);

        // Checkbox.
        $mform->addElement('checkbox', 'checkbox', 'Checkbox', 'Checkbox Text');
        if ($required) {
            $mform->addRule('checkbox', 'This element is required', 'required', null, 'client');
        }
        if ($help) {
            $mform->addHelpButton('checkbox', 'summary');
        }
        $mform->setAdvanced('checkbox', true);

        // Select.
        $mform->addElement('select', 'auth', 'Select', ['cow', 'crow', 'dog', 'cat']);
        if ($required && !$mixed) {
            $mform->addRule('auth', 'This element is required', 'required', null, 'client');
        }
        if ($help) {
            $mform->addHelpButton('auth', 'summary');
        }
        $mform->setAdvanced('auth', true);

        // Yes No.
        $mform->addElement('selectyesno', 'selectyesno', 'Selectyesno');
        if ($required && !$mixed) {
            $mform->addRule('selectyesno', 'This element is required', 'required', null, 'client');
        }
        if ($help) {
            $mform->addHelpButton('selectyesno', 'summary');
        }
        $mform->setAdvanced('selectyesno', true);

        // Static.
        $mform->addElement('static', 'static', 'Static', 'static description');

        // Float.
        $mform->addElement('float', 'float', 'Floating number');
        if ($required) {
            $mform->addRule('float', 'This element is required', 'required', null, 'client');
        }
        if ($help) {
            $mform->addHelpButton('float', 'summary');
        }
        $mform->setAdvanced('float', true);

        // Textarea.
        $mform->addElement('textarea', 'textarea', 'Text area', 'wrap="virtual" rows="20" cols="50"');
        if ($required && !$mixed) {
            $mform->addRule('textarea', 'This element is required', 'required', null, 'client');
        }
        if ($help && !$mixed) {
            $mform->addHelpButton('textarea', 'summary');
        }
        $mform->setAdvanced('textarea', true);

        // Recaptcha. (does not support required).
        $mform->addElement('recaptcha', 'recaptcha', 'Recaptcha');
        if ($help) {
            $mform->addHelpButton('recaptcha', 'summary');
        }

        // Tags.
        $mform->addElement('tags', 'tags', 'Tags', ['itemtype' => 'course_modules', 'component' => 'core']);
        if ($required && !$mixed) {
            $mform->addRule('tags', 'This element is required', 'required', null, 'client');
        }
        if ($help && !$mixed) {
            $mform->addHelpButton('tags', 'summary');
        }
        $mform->setAdvanced('tags', true);

        // Filetypes. (does not support required).
        $mform->addElement('filetypes', 'filetypes', 'Allowedfiletypes', ['onlytypes' => ['document', 'image'],
            'allowunknown' => true]);
        if ($help) {
            $mform->addHelpButton('filetypes', 'summary');
        }
        $mform->setAdvanced('filetypes', true);

        // Advanced checkbox.
        $mform->addElement('advcheckbox', 'advcheckbox', 'Advanced checkbox', 'Advanced checkbox name', ['group' => 1],
            [0, 1]);
        if ($required) {
            $mform->addRule('advcheckbox', 'This element is required', 'required', null, 'client');
        }
        if ($help) {
            $mform->addHelpButton('advcheckbox', 'summary');
        }
        $mform->setAdvanced('advcheckbox', true);

        // Autocomplete.
        $searchareas = \core_search\manager::get_search_areas_list(true);
        $areanames = [];
        foreach ($searchareas as $areaid => $searcharea) {
            $areanames[$areaid] = $searcharea->get_visible_name();
        }
        $options = [
            'multiple' => true,
            'noselectionstring' => get_string('allareas', 'search'),
        ];
        $mform->addElement('autocomplete', 'autocomplete', get_string('searcharea', 'search'), $areanames, $options);
        if ($required) {
            $mform->addRule('autocomplete', 'This element is required', 'required', null, 'client');
        }
        if ($help && !$mixed) {
            $mform->addHelpButton('autocomplete', 'summary');
        }
        $mform->setAdvanced('autocomplete', true);

        // Group.
        $radiogrp = [
            $mform->createElement('text', 'rtext', 'Text'),
            $mform->createElement('radio', 'rradio', 'Radio label', 'After one ', 1),
            $mform->createElement('checkbox', 'rchecbox', 'Checkbox label', 'After two ', 2)
        ];
        $mform->setType('rtext', PARAM_RAW);
        $mform->addGroup($radiogrp, 'group', 'Group', ' ', false);
        if ($required) {
            $mform->addRule('group', 'This element is required', 'required', null, 'client');
        }
        if ($help) {
            $mform->addHelpButton('group', 'summary');
        }
        $mform->setAdvanced('group', true);

        $group = $mform->getElement('group');

        // Group of groups.
        $group = [];
        $group[] = $mform->createElement('select', 'profilefield', '', [0 => 'Username', 1 => 'Email']);
        $elements = [];
        $elements[] = $mform->createElement('select', 'operator', null, [0 => 'equal', 1 => 'not equal']);
        $elements[] = $mform->createElement('text', 'value', null);
        $elements[] = $mform->createElement('static', 'desc', 'Just a static text', 'Just a static text');
        $mform->setType('value', PARAM_RAW);
        $group[] = $mform->createElement('group', 'fieldvalue', '', $elements, '', false);
        $mform->addGroup($group, 'fieldsgroup', 'Group containing another group', '', false);
        if ($required) {
            $mform->addRule('fieldsgroup', 'This element is required', 'required', null, 'client');
        }
        if ($help) {
            $mform->addHelpButton('fieldsgroup', 'summary');
        }

        $validfieldtypes = [
            'text' => get_string('text'),
            'textarea' => get_string('textarea'),
            'select' => get_string('select'),
            'checkbox' => get_string('checkbox'),
            'radio' => get_string('radio'),
            'filepicker' => get_string('filepicker'),
            'date' => get_string('date'),
            'datetime' => get_string('datetime'),
        ];

        $this->add_action_buttons();
    }
    /**
     * Get all the valid assignment field types
     *
     * @return array
     */
    public function get_valid_fieldtypes() {
        return [
            'text' => get_string('fieldtype_text', 'assignsubmission_mawang'),
            'textarea' => get_string('fieldtype_textarea', 'assignsubmission_mawang'),
            'select' => get_string('fieldtype_select', 'assignsubmission_mawang'),
            'checkbox' => get_string('fieldtype_checkbox', 'assignsubmission_mawang'),
            'radio' => get_string('fieldtype_radio', 'assignsubmission_mawang'),
            'filepicker' => get_string('fieldtype_filepicker', 'assignsubmission_mawang'),
            'date' => get_string('fieldtype_date', 'assignsubmission_mawang'),
            'datetime' => get_string('fieldtype_datetime', 'assignsubmission_mawang'),
        ];
    }

    /**
     * Add form elements for settings
     *
     * @param null|stdClass $submission record from assign_submission table or null if it is a new submission
     * @param MoodleQuickForm $mform
     * @param stdClass $data form data that can be modified
     * @return true if elements were added to the form
     */
    public function get_form_elements($submission, MoodleQuickForm $mform, stdClass $data) {
        // Configured settings from JSON

        $fields = [
            [
                'id' => 7,
                'type' => 'textarea',
                'name' => '1. First thing you would teach following on from the SOW extract.',
                'options' => [
                    'required' => true,
                    'maxwords' => 100,
                ],
                'value' => '',
            ],
            [
                'id' => 8,
                'type' => 'textarea',
                'name' => '2. Second thing you would teach following on from the SOW extract.',
                'options' => [
                    'required' => true,
                    'maxwords' => 100,
                ],
                'value' => '',
            ],
            [
                'id' => 9,
                'type' => 'textarea',
                'name' => '3. Third thing you would teach following on from the SOW extract.',
                'options' => [
                    'required' => true,
                    'maxwords' => 100,
                ],
                'value' => '',
            ],
            [
                'id' => 10,
                'type' => 'checkbox',
                'name' => 'Have you read the SOW extract?',
                'options' => [],
                'value' => '',
            ],
        ];

        $validtypes = ['text', 'textarea', 'select', 'checkbox', 'radio', 'filepicker', 'date', 'datetime'];

        // Add the fields to the form.
        foreach ($fields as $field) {
            $fieldname = 'mawang[' . $field['id'] . ']';
            $fieldtype = in_array($field['type'], $validtypes) ? $field['type'] : 'text';
            $mform->addElement($field['type'], $fieldname, $field['name'], $field['options']);
        }

        return true;
    }

    /**
     * Save data to the database and trigger plagiarism plugin,
     * if enabled, to scan the uploaded content via events trigger
     *
     * @param stdClass $submission record from assign_submission table
     * @param stdClass $data data from the form
     * @return bool
     */
    public function save(stdClass $submission, stdClass $data) {
        $currentsubmission = submission::get_record(['id' => $submission->id]);
        $values = field::get_records(['submissionid' => $submission->id]);
        if (!$currentsubmission) {
            $currentsubmission = new submission();
            $currentsubmission->set('assignment', $submission->assignment);
            $currentsubmission->set('submission', $submission->id);
            $currentsubmission->save();
        }
        foreach ($data->mawang as $fieldid => $value) {
            foreach ($values as $field) {
                $set = false;
                if ($field->get('fieldid') == $fieldid) {
                    $field->set('data', $value);
                    $field->save();
                    $set = true;
                }
                if (!$set) {
                    $field = new field();
                    $field->set('submissionid', $submission->id);
                    $field->set('fieldid', $fieldid);
                    $field->set('data', $value);
                    $field->save();
                }
            }
        }
    }

    /**
     * Determine if a submission is empty
     *
     * This is distinct from is_empty in that it is intended to be used to
     * determine if a submission made before saving is empty.
     *
     * @param stdClass $data data from the form
     * @return bool
     */
    public function submission_is_empty(stdClass $data) {
        return count($data->mawang) === 0;
    }

    /**
     * Is this assignment plugin empty? (ie no submission or feedback)
     *
     * @param stdClass $submission record from assign_submission
     * @return bool
     */
    public function is_empty(stdClass $submission) {
        $currentsubmission = submission::get_record(['id' => $submission->id]);
        return !$currentsubmission;
    }

    /**
     * Display value in the submission status table
     *
     * @param stdClass $submission record from assign_submission table
     * @param bool $showviewlink Modifed to return whether or not to show a link to the full submission/feedback
     * @return string
     */
    public function view_summary(stdClass $submission, &$showviewlink) {
        global $DB;
        $currentsubmission = submission::get_record(['id' => $submission->id]);
        return $currentsubmission ? s($currentsubmission->value) : '';
    }

    /**
     * Return a description of external params suitable for uploading an feedback comment from a webservice.
     *
     * Used in WebService mod_assign_save_submission
     *
     * @return array
     */
    public function get_external_parameters() {
        global $CFG;
        require_once($CFG->dirroot . '/lib/externallib.php');

        return ['mawang' => new external_value(PARAM_RAW, 'The value for this submission.')];
    }
}
