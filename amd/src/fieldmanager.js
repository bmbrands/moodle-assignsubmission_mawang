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

import Repository from './repository';
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
        this.renderFields();
        const rootElement = document.querySelector('[data-region="mawang-fieldmanager"]');
        rootElement.addEventListener('click', (e) => {
            let btn = e.target.closest('[data-action]');
            if (btn) {
                e.preventDefault();
                this.actions(btn);
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
        };
        const action = btn.dataset.action;
        if (actionMap[action]) {
            actionMap[action].call(this, btn);
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
            throw new Error('No fields containers found.');
        }
        // Collect the fields data from the UI.
        const fields = [];
        fieldCards.forEach((card) => {
            const fieldid = card.dataset.fieldid;
            const nameInput = card.querySelector('input[name="fieldname"]');
            const typeSelect = card.querySelector('select[name="fieldtype"]');
            if (nameInput && typeSelect) {
                const field = {
                    id: fieldid ? parseInt(fieldid, 10) : null,
                    name: nameInput.value.trim(),
                    type: typeSelect.value,
                    deleted: card.dataset.deleted == 1 ? true : false,
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
        const args = {
            assignmentid: rootElement.dataset.assignmentid,
            fields: this.getFieldsFromUI(),
        };
        const response = await Repository.updateFields(args);
        if (response) {
            this.fields = response.fields || [];
            this.fieldtypes = response.fieldtypes || [];
            await this.renderFields();
        } else {
            throw new Error('Failed to save fields.');
        }
    }

    /**
     * Adds a new field to the Mawang repository.
     * @returns {Promise<void>} A promise that resolves when the field is added.
     */
    async addField() {
        const newField = {
            name: '',
            type: 'text',
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
        this.fields.forEach((field) => {
            if (field.id && field.id.toString() === fieldId) {
                field.deleted = true; // Mark the field for deletion.
            }
        });
        // Re-render the fields.
        await this.renderFields();
    }

    /**
     * Fetches the fields from the Mawang repository.
     *
     * @returns {Promise<Object>} A promise that resolves with the fields data.
     */
    async getFields() {
        const rootElement = document.querySelector('[data-region="mawang-fieldmanager"]');
        if (!rootElement) {
            throw new Error('FieldManager root element not found.');
        }

        const args = {
            assignmentid: rootElement.dataset.assignmentid,
        };
        const response = await Repository.getFields(args);
        if (response) {
            this.fields = response.fields || [];
            this.fieldtypes = response.fieldtypes || [];
        }
    }

    /**
     * Parse the fetched fields data, add the fieldtypes to each field and set the selected type.
     * @returns {Promise<void>} A promise that resolves when the fields are parsed.
     */
    async parseFields() {
        if (this.fields.length == 0) {
            await this.getFields();
        }
        // Create a copy of the fieldtypes to avoid modifying the original array.
        this.fields.forEach((field) => {
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
