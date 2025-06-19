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
 * Provides the information to backup Mawang submissions
 *
 * @package    assignsubmission_mawang
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_assignsubmission_mawang_subplugin extends backup_subplugin {

    /**
     * Returns the subplugin information to attach to submission element
     *
     * @return backup_subplugin_element
     */
    protected function define_submission_subplugin_structure() {

        // Create XML elements.
        $subplugin = $this->get_subplugin_element();
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());

        $userinfo = $this->get_setting_value('userinfo');

        /*   <FIELDS>
    <TABLE NAME="assignsubmission_mawang" COMMENT="Info about Mawang submission">
      <FIELDS>
        <FIELD NAME="id" TYPE="int" LENGTH="10" NOTNULL="true" SEQUENCE="true"/>
        <FIELD NAME="assignment" TYPE="int" LENGTH="10" NOTNULL="true" DEFAULT="0" SEQUENCE="false" COMMENT="The assignment instance this submission relates to."/>
        <FIELD NAME="submission" TYPE="int" LENGTH="10" NOTNULL="true" DEFAULT="0" SEQUENCE="false" COMMENT="The submission this submission relates to."/>
        <FIELD NAME="usermodified" TYPE="int" LENGTH="10" NOTNULL="true" DEFAULT="0" SEQUENCE="false"/>
        <FIELD NAME="timecreated" TYPE="int" LENGTH="10" NOTNULL="true" DEFAULT="0" SEQUENCE="false"/>
        <FIELD NAME="timemodified" TYPE="int" LENGTH="10" NOTNULL="true" DEFAULT="0" SEQUENCE="false"/>
      </FIELDS>
      <KEYS>
      </FIELDS> */

        // TODO: make sure the names of the elements are correct, "value" is just an example of a field name.
        $subpluginelement = new backup_nested_element('assignsubmission_mawang', null, ['assignment', 'submission']);

        // Connect XML elements into the tree.
        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginelement);

        // Set source to populate the data.
        $subpluginelement->set_source_table('assignsubmission_mawang', ['submission' => backup::VAR_PARENTID]);

        /*     <TABLE NAME="assignsubmission_mawang_field" COMMENT="Configuration for the form fields">
      <FIELDS>
        <FIELD NAME="id" TYPE="int" LENGTH="10" NOTNULL="true" SEQUENCE="true"/>
        <FIELD NAME="assignmentid" TYPE="int" LENGTH="10" NOTNULL="false" SEQUENCE="false"/>
        <FIELD NAME="type" TYPE="char" LENGTH="255" NOTNULL="false" SEQUENCE="false"/>
        <FIELD NAME="name" TYPE="char" LENGTH="255" NOTNULL="false" SEQUENCE="false"/>
        <FIELD NAME="options" TYPE="text" NOTNULL="false" SEQUENCE="false"/>
        <FIELD NAME="usermodified" TYPE="int" LENGTH="10" NOTNULL="true" DEFAULT="0" SEQUENCE="false"/>
        <FIELD NAME="timecreated" TYPE="int" LENGTH="10" NOTNULL="true" DEFAULT="0" SEQUENCE="false"/>
        <FIELD NAME="timemodified" TYPE="int" LENGTH="10" NOTNULL="true" DEFAULT="0" SEQUENCE="false"/>
      </FIELDS>*/
        $assignmentid = backup::VAR_ACTIVITYID;
        $fields = new backup_nested_element('fields');
        $field = new backup_nested_element('field', ['id'], [
            'assignmentid', 'type', 'name', 'options', 'usermodified', 'timecreated', 'timemodified'
        ]);
        $subpluginwrapper->add_child($fields);
        $fields->add_child($field);
        $field->set_source_table('assignsubmission_mawang_field', ['assignmentid' => backup::VAR_ACTIVITYID]);
        return $subplugin;
    }
}
