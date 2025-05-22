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
;
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
     * Get the settings for mawang submission plugin
     *
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {
        global $PAGE;
        // Create a repeatable element to allow configuring the fields using a select for type and a text field for name.
        $fieldtypes = $this->get_valid_fieldtypes();
        $fieldtypes = array_merge(['' => get_string('selectfieldtype', 'assignsubmission_mawang')], $fieldtypes);

        // Show configured fields.
        $fields = $this->get_fields();
        $count = 0;
        foreach ($fields as $field) {
            $fieldtype = 'fieldtype_' . $field['id'];
            $fieldname = 'fieldname_' . $field['id'];
            $fieldremove = 'removefield_' . $field['id'];
            $fieldgroupname = 'group' . $field['id'];
            $removeurl = new moodle_url('/mod/assign/submission/mawang/action.php',
                [
                    'id' => $this->assignment->get_default_instance()->id,
                    'fieldid' => $field['id'],
                    'action' => 'deletefield',
                    'returnurl' => urlencode($PAGE->url)
                ]);
            $removelink = html_writer::link($removeurl, get_string('remove'), ['class' => 'removefield']);
            $fieldid = 'fieldid_' . $field['id'];
            $fieldgroup = [
                $mform->createElement('select', $fieldtype, get_string('fieldtype', 'assignsubmission_mawang'), $fieldtypes),
                $mform->createElement('text', $fieldname, get_string('fieldname', 'assignsubmission_mawang'), ['size' => '140']),
                $mform->createElement('html', $removelink),
                $mform->createElement('hidden', $fieldid, 0),
            ];
            $mform->setType($fieldid, PARAM_INT);
            $mform->setType($fieldname, PARAM_TEXT);
            $mform->setdefault($fieldtype, $field['type']);
            $mform->setdefault($fieldname, $field['name']);
            $mform->setdefault($fieldid, $field['id']);
            $mform->addGroup($fieldgroup, $fieldgroupname, get_string('fieldandname', 'assignsubmission_mawang', ++$count), ' ', false);
            $mform->hideif($fieldgroupname, 'assignsubmission_mawang_enabled', 'notchecked');
            $mform->hideIf($fieldtype, 'assignsubmission_mawang_enabled', 'notchecked');
            $mform->hideIf($fieldname, 'assignsubmission_mawang_enabled', 'notchecked');
        }

        $addurl = new moodle_url('/mod/assign/submission/mawang/action.php',
            [
                'id' => $this->assignment->get_default_instance()->id,
                'action' => 'addfield',
                'returnurl' => urlencode($PAGE->url)
            ]);
        $addlink = html_writer::link($addurl, get_string('addfield', 'assignsubmission_mawang'), ['class' => 'addfield']);

        // Add a button to add a new field.
        $mform->addElement('html', $addlink);
    }

    /**
     * Save the settings for mawang submission plugin
     *
     * @param stdClass $data
     * @return bool
     */
    public function save_settings(stdClass $data) {
        $fields = [];
        foreach ($data as $key => $value) {
            if (preg_match('/^fieldtype_(\d+)$/', $key, $matches)) {
                $fieldid = $matches[1];
                if (!empty($value)) {
                    $fields[$fieldid]['type'] = $value;
                }
            } else if (preg_match('/^fieldname_(\d+)$/', $key, $matches)) {
                $fieldid = $matches[1];
                if (!empty($value)) {
                    $fields[$fieldid]['name'] = $value;
                }
            } else if (preg_match('/^fieldid_(\d+)$/', $key, $matches)) {
                $fieldid = $matches[1];
                if (!empty($value)) {
                    $fields[$fieldid]['id'] = (int)$value;
                }
            } else if (preg_match('/^removefield_(\d+)$/', $key, $matches)) {
                $fieldid = $matches[1];
                if (!empty($value)) {
                    $fields[$fieldid]['remove'] = true;
                }
            }
        }

        // Save the fields.
        foreach ($fields as $field) {
            // If the field is marked for removal, delete it.
            if (isset($field['remove'])) {
                if (isset($field['id'])) {
                    field::get_record(['id' => $field['id']])->delete();
                }
                continue;
            }
            if (empty($field['type']) || empty($field['name'])) {
                continue;
            }
            $fieldobj = null;
            if (isset($field['id']) && $field['id'] > 0) {
                // Update the field.
                $fieldobj = field::get_record(['id' => $field['id']]);
                if (!$fieldobj) {
                    $fieldobj = new field();
                }
            } else {
                $fieldobj = new field();
            }
            $fieldobj->set('assignmentid', $data->instance);
            $fieldobj->set('type', $field['type']);
            $fieldobj->set('name', $field['name']);
            $fieldobj->save();
        }

        if (!empty($data->addfield)) {
            $fieldobj = new field();
            $fieldobj->set('assignmentid', $data->instance);
            $fieldobj->set('type', 'text');
            $fieldobj->set('name', get_string('newfield', 'assignsubmission_mawang'));
            $fieldobj->save();
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

        $repeatarray = array();
        $repeatarray[] = $mform->createElement('text', 'option', get_string('optionno', 'choice'));
        $repeatarray[] = $mform->createElement('text', 'limit', get_string('limitno', 'choice'));
        $repeatarray[] = $mform->createElement('hidden', 'optionid', 0);

        if ($this->_instance){
            $repeatno = $DB->count_records('choice_options', array('choiceid'=>$this->_instance));
            $repeatno += 2;
        } else {
            $repeatno = 5;
        }

        $repeateloptions = array();
        $repeateloptions['limit']['default'] = 0;
        $repeateloptions['limit']['hideif'] = array('limitanswers', 'eq', 0);
        $repeateloptions['limit']['rule'] = 'numeric';
        $repeateloptions['limit']['type'] = PARAM_INT;

        $repeateloptions['option']['helpbutton'] = array('choiceoptions', 'choice');
        $mform->setType('option', PARAM_CLEANHTML);

        $mform->setType('optionid', PARAM_INT);

        $this->repeat_elements($repeatarray, $repeatno,
                    $repeateloptions, 'option_repeats', 'option_add_fields', 3, null, true);

        // Make the first option required
        if ($mform->elementExists('option[0]')) {
            $mform->addRule('option[0]', get_string('atleastoneoption', 'choice'), 'required', null, 'client');
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
            'html' => get_string('fieldtype_html', 'assignsubmission_mawang'),
            'text' => get_string('fieldtype_text', 'assignsubmission_mawang'),
            'textarea' => get_string('fieldtype_textarea', 'assignsubmission_mawang'),
            'checkbox' => get_string('fieldtype_checkbox', 'assignsubmission_mawang'),
            'date_selector' => get_string('fieldtype_date', 'assignsubmission_mawang'),
            'date_time_selector' => get_string('fieldtype_datetime', 'assignsubmission_mawang'),
        ];

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
        global $OUTPUT;
        // Configured settings from JSON

        $fields = $this->get_fields();
        $mform->updateAttributes(['class' => 'mform full-width-labels mawang-form']);

        $validtypes = ['text', 'textarea', 'select', 'checkbox', 'radio', 'filepicker', 'date', 'datetime'];
        $defaultoptions = [
            'text' => ['size' => '140'],
            'textarea' => ['rows' => 10, 'cols' => 50],
            'select' => ['multiple' => false],
            'checkbox' => '',
            'radio' => ['group' => 1],
            'filepicker' => ['maxbytes' => 100, 'accepted_types' => '*'],
            'date' => ['startyear' => 2000, 'stopyear' => 2030],
            'datetime' => ['startyear' => 2000, 'stopyear' => 2030],
        ];

        // Add the fields to the form.
        foreach ($fields as $field) {
            $fieldname = 'mawang[' . $field['id'] . ']';
            $fieldtype = in_array($field['type'], $validtypes) ? $field['type'] : 'text';
            if ($fieldtype == 'html') {
                $mform->addElement('html', $field['name']);
                continue;
            }
            $mform->addElement($field['type'], $fieldname, $field['name'], $defaultoptions[$fieldtype]);
            $value = value::get_record(['submissionid' => $submission->id, 'fieldid' => $field['id']]);
            if (in_array($fieldtype, ['date_selector', 'date_time_selector'])) {
                $mform->setDefault($fieldname, $value ? intval($value->get('data')) : time());
            } else {
                $mform->setType($fieldname, PARAM_TEXT);
            }
        }

        $formjs = $OUTPUT->render_from_template('assignsubmission_mawang/mawang', []);
        $mform->addElement('html', $formjs);

        return true;
    }

        /**
     * Get a configuration value for this plugin
     *
     * @param mixed $setting The config key (string) or null
     * @return mixed string | false
     */
    private function get_fields() {

        $fields = field::get_records(['assignmentid' => $this->assignment->get_default_instance()->id]);
        $fieldarray = [];
        foreach ($fields as $field) {
            $fieldarray[] = [
                'id' => $field->get('id'),
                'type' => $field->get('type'),
                'name' => $field->get('name'),
            ];
        }
        if (empty($fieldarray)) {
            $fieldarray[] = [
                'id' => 0,
                'type' => 'text',
                'name' => get_string('newfield', 'assignsubmission_mawang'),
            ];
        }
        return $fieldarray;
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
        $values = value::get_records(['submissionid' => $submission->id]);
        if (!$currentsubmission) {
            $currentsubmission = new submission();
            $currentsubmission->set('assignment', $submission->assignment);
            $currentsubmission->set('submission', $submission->id);
            $currentsubmission->save();
        }
        foreach ($data->mawang as $fieldid => $value) {
            $set = false;
            foreach ($values as $field) {
                if ($field->get('fieldid') == $fieldid) {
                    $field->set('data', $value);
                    $field->save();
                    $set = true;
                }
            }
            if (!$set) {
                $field = new value();
                $field->set('submissionid', $submission->id);
                $field->set('fieldid', $fieldid);
                $field->set('data', $value);
                $field->save();
            }
        }
        return true;
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
        $values = value::get_records(['submissionid' => $submission->id]);
        $substrings = [];
        foreach ($values as $field) {
            $substrings[] = substr($field->get('data'), 0, 20);
            if (strlen($field->get('data')) > 20) {
                $substrings[] = '...';
            }
        }
        return $currentsubmission ? s(implode("\n", $substrings)) : '';
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
