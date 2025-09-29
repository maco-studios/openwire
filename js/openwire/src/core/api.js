/**
 * API request handler for OpenWire components
 */
import { API } from '../core/constants';
import { getMagentoFormKey, error } from '../utils';

/**
 * Send request to OpenWire backend
 *
 * @param {Object} data - Request data
 * @param {string} componentId - Component ID
 * @param {string} [serverClass] - Optional server class
 * @param {Object} [initialState] - Optional initial state
 * @returns {Promise<Object>} Response data
 */
export async function sendRequest(data, componentId, serverClass, initialState) {
    // Add component identification
    data.id = componentId;

    // Add server class if available
    if (serverClass) {
        data.server_class = serverClass;
    }

    // Add initial state if available
    if (initialState) {
        data.initial_state = initialState;
    }

    // Add Magento form key
    data.form_key = getMagentoFormKey();

    try {
        const response = await fetch(API.UPDATE, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            throw new Error(`Request failed with status: ${response.status}`);
        }

        return await response.json();
    } catch (err) {
        error('API request failed:', err);
        throw err;
    }
}

/**
 * Send property update to server
 *
 * @param {string} property - Property name to update
 * @param {*} value - New property value
 * @param {string} componentId - Component ID
 * @param {string} [serverClass] - Optional server class
 * @param {Object} [initialState] - Optional initial state
 * @returns {Promise<Object>} Response data
 */
export function sendUpdate(property, value, componentId, serverClass, initialState) {
    const updates = {};
    updates[property] = value;
    return sendRequest({ updates }, componentId, serverClass, initialState);
}

/**
 * Send method call to server
 *
 * @param {string} method - Method name to call
 * @param {Array} params - Method parameters
 * @param {string} componentId - Component ID
 * @param {string} [serverClass] - Optional server class
 * @param {Object} [initialState] - Optional initial state
 * @returns {Promise<Object>} Response data
 */
export function sendCall(method, params, componentId, serverClass, initialState) {
    return sendRequest({ calls: [{ method, params: params || [] }] }, componentId, serverClass, initialState);
}
