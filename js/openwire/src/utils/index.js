/**
 * General utility functions for OpenWire
 */

/**
 * Safely parse JSON or return fallback
 * @param {string} json - JSON string to parse
 * @param {*} fallback - Value to return if parsing fails
 * @returns {*} Parsed object or fallback value
 */
export function safeJsonParse(json, fallback = {}) {
    try {
        return JSON.parse(json);
    } catch (e) {
        return fallback;
    }
}

/**
 * Generate a unique ID for anonymous components
 * @param {string} prefix - Optional prefix for the ID
 * @returns {string} Unique ID
 */
export function generateUniqueId(prefix = 'openwire') {
    return `${prefix}-${Date.now()}_${Math.floor(Math.random() * 10000)}`;
}

/**
 * Get Magento form key from various sources
 * @returns {string} Form key or empty string
 */
export function getMagentoFormKey() {
    // Try common Magento form key locations: FORM_KEY (uppercase), formKey, or a hidden input
    let formKey = window.FORM_KEY || window.formKey || '';
    if (!formKey) {
        const fkInput = document.querySelector('input[name="form_key"]');
        if (fkInput) formKey = fkInput.value || '';
    }
    return formKey;
}

/**
 * Debug log with prefix
 * @param {...any} args - Arguments to log
 */
export function log(...args) {
    console.log('[OpenWire]', ...args);
}

/**
 * Debug error with prefix
 * @param {...any} args - Arguments to log as error
 */
export function error(...args) {
    console.error('[OpenWire Error]', ...args);
}

/**
 * Debug warning with prefix
 * @param {...any} args - Arguments to log as warning
 */
export function warn(...args) {
    console.warn('[OpenWire]', ...args);
}

/**
 * Debounce function to limit the rate at which a function fires
 * @param {Function} func - Function to debounce
 * @param {number} wait - Milliseconds to wait
 * @returns {Function} Debounced function
 */
export function debounce(func, wait) {
    let timeout;
    return function (...args) {
        const context = this;
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            func.apply(context, args);
        }, wait);
    };
}
