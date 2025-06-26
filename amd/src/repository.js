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
 * TODO describe module repository
 *
 * @module     assignsubmission_mawang/repository
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Notification from 'core/notification';

/**
 * The Repository class provides methods to interact with the Mawang repository.
 * It allows for fetching and updating repository data.
 */
export default class Repository {
    /**
     * Fetches the repository data.
     *
     * @param {Object} data The data to fetch.
     * @returns {Promise<Object>} A promise that resolves with the repository data.
     */
    static getFields(data) {
        return Ajax.call([{
            methodname: 'assignsubmission_mawang_get_fields',
            args: data
        }])[0].then((response) => {
            if (response.error) {
                Notification.exception(response.error);
            }
            return response;
        });
    }

    /**
     * Updates the repository data.
     *
     * @param {Object} data The data to update.
     * @returns {Promise<Object>} A promise that resolves with the updated data.
     */
    static updateFields(data) {
        return Ajax.call([{
            methodname: 'assignsubmission_mawang_update_fields',
            args: data
        }])[0].then((response) => {
            if (response.error) {
                Notification.exception(response.error);
            }
            return response;
        });
    }

    /**
     * Store the draft data.
     * @param {Object} data The data to store.
     * @returns {Promise<Object>} A promise that resolves with the stored data.
     */
    static storeDraft(data) {
        return Ajax.call([{
            methodname: 'assignsubmission_mawang_store_draft',
            args: data
        }])[0].then((response) => {
            if (response.error) {
                Notification.exception(response.error);
            }
            return response;
        });
    }
}
