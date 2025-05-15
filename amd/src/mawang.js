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
class Mawang {
    constructor() {
        this.rootElement = document.querySelector('.mawang-form');
        this.init();
    }

    init() {
        const textareas = this.rootElement.querySelectorAll('textarea');
        // Create a new <div> with a word counter after each textarea, give it the id of the textarea + '-wordcounter'
        textareas.forEach((textarea) => {
            const wordCounter = document.createElement('div');
            wordCounter.id = textarea.id + '-wordcounter';
            wordCounter.classList.add('mawang-wordcounter', 'small', 'px-2', 'py-1', 'mt-1', 'border', 'ml-auto');
            const count = textarea.value.split(/\s+/).filter((word) => word.length > 0).length;
            wordCounter.innerHTML = 'Word count:' + count;
            textarea.parentNode.insertBefore(wordCounter, textarea.nextSibling);
        });
        this.bindEvents();

    }

    bindEvents() {
        // Bind events here
        const textareas = this.rootElement.querySelectorAll('textarea');
        textareas.forEach((textarea) => {
            textarea.addEventListener('input', (event) => {
                const wordCounter = document.getElementById(event.target.id + '-wordcounter');
                const count = event.target.value.split(/\s+/).filter((word) => word.length > 0).length;
                wordCounter.innerHTML = 'Word count:' + count;
            });
        });
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