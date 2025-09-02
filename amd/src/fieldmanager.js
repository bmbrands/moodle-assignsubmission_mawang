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
 * TODO describe module fieldmanager
 *
 * @module     assignsubmission_mawang/fieldmanager
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Templates from 'core/templates';

/**
 * The FieldManager class provides methods to manage fields in the Mawang repository.
 * It allows for fetching and updating field data.
 */
class FieldManager {

    /**
     * The fields data.
     * @type {Array}
     */
    fields = [];

    /**
     * The field types available in the Mawang repository.
     * @type {Array}
     */
    fieldtypes = [];

    /**
     * Initializes the FieldManager.
     */
    constructor() {
        const rootElement = document.querySelector('[data-region="mawang-fieldmanager"]');
        const form = document.querySelector('form[action="modedit.php"]');
        const fieldtypesInput = document.querySelector('[name="assignsubmission_mawang_fieldtypes"]');
        if (!rootElement || !form || !fieldtypesInput) {
            throw new Error('Required elements not found in the DOM.');
        }
        // Parse the fieldtypes from the input value.
        try {
            this.fieldtypes = JSON.parse(fieldtypesInput.value);
        } catch (e) {
            throw new Error('Failed to parse fieldtypes from input value: ' + e.message);
        }
        this.renderFields();

        rootElement.addEventListener('click', (e) => {
            let btn = e.target.closest('[data-action]');
            if (btn) {
                e.preventDefault();
                this.actions(btn);
            }
        });
        form.addEventListener('input', async(e) => {
            let input = e.target.closest('.mawang-fieldmanager input');
            if (input) {
                // If the input is a field name, we need to save the fields.
                await this.saveFields();
            }
            let select = e.target.closest('.mawang-fieldmanager select');
            if (select) {
                // If the select is a field type, we need to save the fields.
                await this.saveFields();
            }
            let requiredButton = e.target.closest('input[name="fieldrequired"]');
            if (requiredButton) {
                // If the required button is clicked, we need to save the fields.
                await this.saveFields();
            }
        });
    }

    /**
     * Actions to be performed when a button is clicked.
     * @param {HTMLElement} btn The button that was clicked.
     * @returns {Promise<void>} A promise that resolves when the action is complete.
     */
    async actions(btn) {
        const actionMap = {
            'add-field': this.addField,
            'save-fields': this.saveFields,
            'delete-field': this.deleteField,
            'add-tab': this.addTab,
            'remove-tab': this.removeTab,
        };
        const action = btn.dataset.action;
        if (actionMap[action]) {
            actionMap[action].call(this, btn);
        }
    }

    /**
     * Set the configuration for the field manager.
     * @param {Object} fields The configuration object.
     */
    setConfig(fields) {
        const config = document.querySelector('[name="assignsubmission_mawang_config"]');
        if (!config) {
            throw new Error('Configuration input not found.');
        }
        // Convert the fields array to a JSON string and set it as the value of the config input.
        config.value = JSON.stringify(fields);
    }

    /**
     * Get the configuration for the field manager.
     * @returns {Object} fields The configuration object.
     */
    getConfig() {
        const config = document.querySelector('[name="assignsubmission_mawang_config"]');
        if (!config) {
            throw new Error('Configuration input not found.');
        }
        if (!config.value) {
            this.addField(); // If no config value, add a new field.
            return;
        }
        // Parse the JSON string from the config input and return it as an object.
        const fields = JSON.parse(config.value);
        if (!fields || !Array.isArray(fields) || fields.length === 0) {
            this.addField();
        } else {
            this.fields = fields;
        }
    }

    /**
     * Fetch the field data from the UI
     */
    getFieldsFromUI() {
        const rootElement = document.querySelector('[data-region="mawang-fieldmanager"]');
        if (!rootElement) {
            throw new Error('FieldManager root element not found.');
        }
        const fieldCards = rootElement.querySelectorAll('[data-region="field"]');
        if (fieldCards.length === 0) {
            return []; // No fields found, return an empty array.
        }
        // Collect the fields data from the UI.
        const fields = [];
        fieldCards.forEach((card) => {
            const fieldid = card.dataset.fieldid;
            const nameInput = card.querySelector('input[name="fieldname"]');
            const typeSelect = card.querySelector('select[name="fieldtype"]');
            const requiredSelect = card.querySelector('select[name="fieldrequired"]');
            if (nameInput && typeSelect) {
                const field = {
                    id: fieldid ? parseInt(fieldid, 10) : null,
                    name: nameInput.value.trim(),
                    type: typeSelect.value,
                    deleted: card.dataset.deleted == 1 ? true : false,
                    required: requiredSelect.value == '1' ? true : false,
                };
                fields.push(field);
            } else {
                throw new Error('Field name or type input not found in the field card.');
            }
        });
        if (fields.length === 0) {
            throw new Error('No valid fields found in the UI.');
        }
        return fields;
    }

    /**
     * Saves the fields to the Mawang repository.
     * @returns {Promise<void>} A promise that resolves when the fields are saved.
     */
    async saveFields() {
        const rootElement = document.querySelector('[data-region="mawang-fieldmanager"]');
        if (!rootElement) {
            throw new Error('FieldManager root element not found.');
        }
        this.fields = this.getFieldsFromUI();
        this.setConfig(this.fields); // Set the configuration with the current fields.
        return; // No need to save fields if assignment ID is -1.
    }

    /**
     * Adds a new field to the Mawang repository.
     * @returns {Promise<void>} A promise that resolves when the field is added.
     */
    async addField() {
        await this.saveFields(); // Save existing fields before adding a new one.
        const newField = {
            name: '',
            type: 'textarea',
        };

        this.fields.push(newField);
        this.renderFields();
    }

    /**
     * Deletes a field from the Mawang repository.
     * @param {HTMLElement} btn The button that was clicked to delete the field.
     * @returns {Promise<void>} A promise that resolves when the field is deleted.
     */
    async deleteField(btn) {
        const fieldCard = btn.closest('[data-region="field"]');
        if (!fieldCard) {
            throw new Error('Field card not found.');
        }
        const fieldId = fieldCard.dataset.fieldid;
        if (!fieldId) {
            throw new Error('Field ID not found.');
        }

        // Remove the field from the fields array.
        this.fields = this.fields.filter(field => field.id !== parseInt(fieldId, 10));
        // Re-render the fields.
        await this.renderFields();
    }

    /**
     * Add a tab to separate the fields
     * @param {HTMLElement} btn The button that was clicked to add the tab.
     * @returns {Promise<void>} A promise that resolves when the tab is added.
     */
    async addTab(btn) {
        const fieldCard = btn.closest('[data-region="field"]');
        if (!fieldCard) {
            throw new Error('Field card not found.');
        }
        const fieldId = fieldCard.dataset.fieldid;
        if (!fieldId) {
            throw new Error('Field ID not found.');
        }

        const fieldIndex = this.fields.findIndex(field => field.id === parseInt(fieldId, 10));
        if (fieldIndex === -1) {
            throw new Error('Field not found in the fields array.');
        }
        const field = this.fields[fieldIndex];
        field.tabs = field.tabs || [];
        field.tabs.push({
            id: fieldId,
            name: 'New Tab',
        });
        await this.renderFields();
    }

    /**
     * Remove a tab from a field.
     * @param {HTMLElement} btn The button that was clicked to remove the tab.
     * @returns {Promise<void>} A promise that resolves when the tab is removed.
     */
    async removeTab(btn) {

        const fieldId = btn.dataset.fieldid;
        if (!fieldId) {
            throw new Error('Field ID not found.');
        }

        const fieldIndex = this.fields.findIndex(field => field.id === parseInt(fieldId, 10));
        if (fieldIndex === -1) {
            throw new Error('Field not found in the fields array.');
        }
        const field = this.fields[fieldIndex];
        field.tabs = [];
        await this.renderFields();
    }

    /**
     * Parse the fetched fields data, add the fieldtypes to each field and set the selected type.
     * @returns {Promise<void>} A promise that resolves when the fields are parsed.
     */
    async parseFields() {
        if (this.fields.length == 0) {
            this.getConfig();
        }
        let fieldcount = 0;
        // Create a copy of the fieldtypes to avoid modifying the original array.
        this.fields.forEach((field) => {
            field.id = ++fieldcount; // Assign a unique ID to each field.
            const fieldtypes = this.fieldtypes.map(type => structuredClone(type));
            // Add the selected attribute to the fieldtypes.
            fieldtypes.forEach((type) => {
                type.selected = type.name === field.type;
            });
            if (!fieldtypes.some(type => type.name === field.type)) {
                fieldtypes[0].selected = true;
            }
            field.fieldtypes = fieldtypes;
        });
    }


    /**
     * Renders the fields
     */
    async renderFields() {
        await this.parseFields();
        const rootElement = document.querySelector('[data-region="mawang-fieldmanager"] [data-region="fields-container"]');
        if (!rootElement) {
            throw new Error('FieldManager root element not found.');
        }

        const {html, js} = await Templates.renderForPromise('assignsubmission_mawang/fields', {
            fields: this.fields,
        });
        Templates.replaceNodeContents(rootElement, html, js);
    }
}

new FieldManager();
