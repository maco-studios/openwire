/**
 * OpenWire Component class
 */
import { ATTR, PREFIX, API, TIMING } from '../core/constants';
import {
    safeJsonParse,
    generateUniqueId,
    getMagentoFormKey,
    debounce,
    log,
    error
} from '../utils';
import { sendUpdate, sendCall } from '../core/api';
import { updateDOM, toggleLoading } from '../core/dom';
import { handleEffects } from '../effects';
import { bindAllEvents } from '../events';

/**
 * Component class representing an OpenWire component instance
 */
export class Component {
    /**
     * Create a component instance
     *
     * @param {HTMLElement} element - The component root element
     */
    constructor(element) {
        // Basic properties
        this.element = element;
        this.id = element.getAttribute(ATTR.ID) || generateUniqueId();
        this.name = element.getAttribute(ATTR.NAME);

        // Initial data
        const initialDataStr = element.getAttribute(ATTR.INITIAL_DATA) || '{}';
        this.data = safeJsonParse(initialDataStr, {});

        // Events tracking
        this.pendingUpdates = {};
        this.updateQueue = [];
        this.processingQueue = false;

        // Create debounced update function
        this.debouncedUpdate = debounce((prop, value) => {
            this.updateProperty(prop, value);
        }, TIMING.DEBOUNCE_MS);

        // Set up event handlers
        this.setupEventHandlers();

        log('Component initialized:', this.id, this.name);
    }

    /**
     * Set up all event handlers for this component
     */
    setupEventHandlers() {
        // Create handlers object to pass to event binder
        const handlers = {
            call: this.callMethod.bind(this),
            updateImmediate: this.updateProperty.bind(this),
            updateDebounced: this.debouncedUpdate.bind(this)
        };

        bindAllEvents(this.element, this.id, handlers);
    }

    /**
     * Call a component method on the server
     *
     * @param {string} method - Method name to call
     * @param {Array} params - Parameters to pass to the method
     */
    callMethod(method, params = []) {
        log(`Calling method: ${method}`, params);

        // Show loading state
        this.showLoading();

        const payload = {
            id: this.id,
            calls: [{ method, params }],
            form_key: getMagentoFormKey()
        };

        // Add server class and initial state if component has them
        if (this.element.hasAttribute(ATTR.NAME)) {
            payload.server_class = this.element.getAttribute(ATTR.NAME);
        }

        const initialState = this.element.getAttribute(ATTR.STATE);
        if (initialState) {
            payload.initial_state = safeJsonParse(initialState, {});
        }

        sendCall(API.UPDATE, payload)
            .then(response => {
                this.processResponse(response);
            })
            .catch(err => {
                error(`Error calling ${method}:`, err);
                throw err;
            })
            .finally(() => {
                this.hideLoading();
            });
    }

    /**
     * Update a component property on the server
     *
     * @param {string} property - Property name to update
     * @param {any} value - New value for the property
     */
    updateProperty(property, value) {
        log(`Updating property: ${property}`, value);

        // Track this update
        this.pendingUpdates[property] = value;

        // Add to queue if not already there
        if (!this.updateQueue.includes(property)) {
            this.updateQueue.push(property);
        }

        // Process queue if not already processing
        if (!this.processingQueue) {
            this.processUpdateQueue();
        }
    }

    /**
     * Process pending property updates
     */
    processUpdateQueue() {
        this.processingQueue = true;

        if (this.updateQueue.length === 0) {
            this.processingQueue = false;
            return;
        }

        // Take the next property from queue
        const property = this.updateQueue.shift();
        const value = this.pendingUpdates[property];

        // Remove from pending updates
        delete this.pendingUpdates[property];

        const updates = {};
        updates[property] = value;

        const payload = {
            id: this.id,
            updates: updates,
            form_key: getMagentoFormKey()
        };

        // Add server class and initial state if component has them
        if (this.element.hasAttribute(ATTR.NAME)) {
            payload.server_class = this.element.getAttribute(ATTR.NAME);
        }

        const initialState = this.element.getAttribute(ATTR.STATE);
        if (initialState) {
            payload.initial_state = safeJsonParse(initialState, {});
        }

        sendUpdate(API.UPDATE, payload)
            .then(response => {
                this.processResponse(response);
            })
            .catch(err => {
                error(`Error updating ${property}:`, err);
                throw err;
            })
            .finally(() => {
                // Continue processing queue
                this.processUpdateQueue();
            });
    }

    /**
     * Process a response from the server
     *
     * @param {Object} response - Response from the server
     */
    processResponse(response) {
        if (!response || typeof response !== 'object') {
            error('Invalid response received:', response);
            return;
        }

        // Update component data
        if (response.data) {
            this.data = response.data;
        }

        // Update the DOM with new content if provided
        if (response.html) {
            updateDOM(this.element, response.html);

            // Re-initialize event handlers since DOM has changed
            this.setupEventHandlers();
        }

        // Handle effects
        if (response.effects && Array.isArray(response.effects)) {
            handleEffects(response.effects, this);
        }
    }

    /**
     * Get the current component data
     *
     * @returns {Object} Current component data
     */
    getData() {
        return this.data;
    }

    /**
     * Refresh the component by forcing a full re-render
     */
    refresh() {
        this.callMethod('$refresh');
    }

    /**
     * Show loading state
     */
    showLoading() {
        toggleLoading(this.element, true);
    }

    /**
     * Hide loading state
     */
    hideLoading() {
        toggleLoading(this.element, false);
    }
}
