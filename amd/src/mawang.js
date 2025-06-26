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
 * TODO describe module mawang
 *
 * @module     assignsubmission_mawang/mawang
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Templates from 'core/templates';
import {getString} from 'core/str';
import Repository from './repository';
import $ from 'jquery';
import {debounce} from 'core/utils';

const DEBOUNCE_TIMER = 2000;

class Mawang {
    constructor() {
        this.rootElement = document.querySelector('.mawang-form');
        this.init();
    }

    async init() {
        const textareas = this.rootElement.querySelectorAll('textarea');
        // Create a new <div> with a word counter after each textarea, give it the id of the textarea + '-wordcounter'
        textareas.forEach(async(textarea) => {
            const areaid = textarea.dataset.fieldid + '-wordcounter';
            const statusid = textarea.dataset.fieldid + '-status';
            const count = textarea.value.split(/\s+/).filter((word) => word.length > 0).length;
            const {html, js} = await Templates.renderForPromise('assignsubmission_mawang/fieldactions',
                {areaid: areaid, statusid: statusid, count: count});
            Templates.appendNodeContents(textarea.parentNode, html, js);
        });
        this.bindEvents();

    }

    async bindEvents() {
        // Bind events here
        const textareas = this.rootElement.querySelectorAll('textarea');
        const saveDraftDebounce = debounce(this.saveDraft, DEBOUNCE_TIMER);

        textareas.forEach(async(textarea) => {
            textarea.addEventListener('input', async(event) => {
                const wordCounter = document.getElementById(event.target.dataset.fieldid + '-wordcounter');
                const count = event.target.value.split(/\s+/).filter((word) => word.length > 0).length;
                wordCounter.innerHTML = await getString('words', 'assignsubmission_mawang', count);
                saveDraftDebounce(event.target);
            });
        });
        document.addEventListener('click', async(event) => {
            const popOvers = document.querySelectorAll('[data-toggle="popover"]');
            const currentPopover = event.target.closest('[data-toggle="popover"]');
            if (popOvers.length > 0) {
                popOvers.forEach((popover) => {
                    if (popover !== currentPopover) {
                        $(popover).popover('hide');
                    }
                });
            }
        });
    }

    /**
     * Save a draft of the field.
     * @param {HTMLElement} field The field to save.
     */
    async saveDraft(field) {
        const statusdiv = document.getElementById(field.dataset.fieldid + '-status');
        statusdiv.classList.add('saving');

        const data = {
            assignmentid: parseInt(field.dataset.assignmentid),
            fieldid: parseInt(field.dataset.fieldid),
            value: field.value,
        };
        const response = await Repository.storeDraft(data);
        if (response) {
            statusdiv.classList.remove('saving');
        }
    }
}

/*
 * Initialise
 * @param {HTMLElement} element The element.
 * @param {String} courseid The courseid.
 */
const init = () => {
    new Mawang();
};

export default {
    init: init,
};