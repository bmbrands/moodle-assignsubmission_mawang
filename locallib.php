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

use assignsubmission_mawang\local\persistent\value;
use assignsubmission_mawang\local\persistent\draft;
use assignsubmission_mawang\local\api\mawang;
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
            $values = value::get_records(['submissionid' => $submissionid]);
            foreach ($values as $value) {
                $value->delete();
            }
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
        global $PAGE, $OUTPUT;
        // Add a button to add a new field.
        $issetup = $this->assignment->get_default_instance();
        $assignmentid = $issetup ? $this->assignment->get_default_instance()->id : -1;

        $fieldtypes = mawang::get_valid_fieldtypes();
        $fieldtypes = array_map(function($fieldtype) {
            return (object)[
                'name' => $fieldtype['name'],
                'label' => $fieldtype['label'],
            ];
        }, $fieldtypes);

        $fields = $this->get_fields();

        $fieldsmanager = $OUTPUT->render_from_template('assignsubmission_mawang/fieldmanager', [
            'fields' => [],
            'fieldtypes' => $fieldtypes,
            'assignmentid' => $assignmentid,
        ]);

        $mform->addElement('static', 'fieldmanager', '', $fieldsmanager);
        $mform->addElement('hidden', 'assignsubmission_mawang_config', json_encode($fields));
        $mform->setType('assignsubmission_mawang_config', PARAM_RAW);
        $mform->addElement('hidden', 'assignsubmission_mawang_fieldtypes', json_encode($fieldtypes));
        $mform->setType('assignsubmission_mawang_fieldtypes', PARAM_RAW);
        $mform->hideIf('fieldmanager', 'assignsubmission_mawang_enabled', 'notchecked');
        return;
    }

    /**
     * Save the settings for mawang submission plugin
     * @param stdClass $data The data from the form
     * @return bool
     */
    public function save_settings(stdClass $data) {
        $this->set_config('fields', $data->assignsubmission_mawang_config ?? '');
        return true;
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
        global $OUTPUT, $USER;
        // Configured settings from JSON

        $fields = $this->get_fields();
        $mform->updateAttributes(['class' => 'mform full-width-labels mawang-form']);

        $mform->addElement('hidden', 'instanceid', $this->assignment->get_instance()->id);
        $mform->setType('instanceid', PARAM_INT);

        $validtypes = ['text', 'textarea', 'date', 'html'];
        $defaultoptions = [
            'text' => ['size' => '140'],
            'textarea' => ['rows' => 10, 'cols' => 50],
            'date' => ['startyear' => 2000, 'stopyear' => 2030],
            'html' => [],
        ];

        // Add the fields to the form.
        foreach ($fields as $field) {
            $fieldname = 'mawang[' . $field['id'] . ']';
            $fieldtype = in_array($field['type'], $validtypes) ? $field['type'] : 'text';
            if ($fieldtype == 'html') {
                $mform->addElement('html', $field['name'],  $field['name']);
                continue;
            }
            $options = $defaultoptions[$fieldtype] ?? [];
            $options['data-fieldid'] = $field['id'];
            $options['data-assignmentid'] = $this->assignment->get_default_instance()->id;
            
            $mform->addElement($field['type'], $fieldname, $field['name'], $options);
            
            // Add required validation if field is marked as required
            if ($field['required']) {
                $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
            }
            $value = value::get_record(['submissionid' => $submission->id, 'fieldid' => $field['id']]);
            $draft = draft::get_record([
                'assignment' => $this->assignment->get_default_instance()->id,
                'fieldid' => $field['id'],
                'userid' => $USER->id,
            ]);
            $draftdata = $draft ? $draft->get('data') : '';
            if (in_array($field['type'], ['date_selector', 'date_time_selector'])) {
                $mform->setDefault($fieldname, $value ? intval($value->get('data')) : time());
            } else {
                $mform->setDefault($fieldname, $value ? $value->get('data') : $draftdata);
                $mform->setType($fieldname, PARAM_TEXT);
            }
        }

        $formjs = $OUTPUT->render_from_template('assignsubmission_mawang/mawang', []);
        $mform->addElement('html', $formjs);

        // Add submit button.
        $mform->addElement('submit', 'submitbutton', get_string('submit'));

        return true;
    }

        /**
     * Get a configuration value for this plugin
     *
     * @param mixed $setting The config key (string) or null
     * @return mixed string | false
     */
    private function get_fields() {

        $fields = $this->get_config('fields');
        if (empty($fields)) {
            return [];
        }
        $fields = json_decode($fields, true);
        if (empty($fields) || !is_array($fields)) {
            return [];
        }
        return array_map(function($field) {
            return [
                'id' => $field['id'] ?? 0,
                'assignmentid' => $field['assignmentid'] ?? -1,
                'name' => $field['name'] ?? '',
                'type' => $field['type'] ?? 'textarea',
                'required' => $field['required'] ?? false,
            ];
        }, $fields);
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
        $values = value::get_records(['submissionid' => $submission->id]);
        $assignmentid = $this->assignment->get_default_instance()->id;
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
                $field->set('assignment', $assignmentid);
                $field->set('fieldid', $fieldid);
                $field->set('data', $value);
                $field->save();
            }
        }
        // Delete the drafts for this submission.
        $drafts = draft::get_records([
            'assignment' => $this->assignment->get_default_instance()->id,
            'userid' => $data->userid,
        ]);
        foreach ($drafts as $draft) {
            $draft->delete();
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
        $currentsubmission = value::get_record(['submissionid' => $submission->id]);
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
        global $OUTPUT;
        $templatecontext = [];
        $fields = $this->get_fields();
        foreach ($fields as $field) {
            $value = value::get_record(['submissionid' => $submission->id, 'fieldid' => $field['id']]);
            $data = $value ? $value->get('data') : '';
            if (empty($data)) {
                continue; // Skip empty fields.
            }
            $istimestamp = is_numeric($data) && (int)$data > 0;
            $templatecontext['fields'][] = [
                'name' => $field['name'],
                'value' => $data,
                'type' => $field['type'],
                'datefield' => $istimestamp && in_array($field['type'], ['date_selector', 'date_time_selector']),
            ];
        }
        return $OUTPUT->render_from_template('assignsubmission_mawang/summary', $templatecontext);
    }

    /**
     * The assignment has been deleted - cleanup
     *
     * @return bool
     */
    public function delete_instance() {
        $assignmentid = $this->assignment->get_default_instance()->id;
        $values = value::get_records(['assignment' => $assignmentid]);
        foreach ($values as $value) {
            $value->delete();
        }
        $drafts = draft::get_records(['assignment' => $this->assignment->get_default_instance()->id]);
        foreach ($drafts as $draft) {
            $draft->delete();
        }

        return true;
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
