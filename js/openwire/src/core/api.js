/**
 * API request handler for OpenWire components
 */
import { API } from '../core/constants';
import { getMagentoFormKey, error } from '../utils';

/**
 * Send request to OpenWire backend
 *
 * @param {string} endpoint - API endpoint
 * @param {Object} data - Request data
 * @returns {Promise<Object>} Response data
 */
export async function sendRequest(endpoint, data) {
    try {
        const response = await fetch(endpoint, {
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
 * @param {string} endpoint - API endpoint
 * @param {Object} payload - Request payload
 * @returns {Promise<Object>} Response data
 */
export function sendUpdate(endpoint, payload) {
    return sendRequest(endpoint, payload);
}

/**
 * Send method call to server
 *
 * @param {string} endpoint - API endpoint
 * @param {Object} payload - Request payload
 * @returns {Promise<Object>} Response data
 */
export function sendCall(endpoint, payload) {
    return sendRequest(endpoint, payload);
}
