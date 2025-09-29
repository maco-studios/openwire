/**
 * Event handlers for OpenWire components
 */
import { ATTR, SELECTOR } from '../core/constants';
import { safeJsonParse, log, error } from '../utils';
import { getSortableDropIndex } from '../core/dom';

/**
 * Bind click handlers to elements
 *
 * @param {HTMLElement} element - Container element
 * @param {Function} callMethod - Method to call server method
 * @returns {void}
 */
export function bindClickHandlers(element, callMethod) {
    element.querySelectorAll(SELECTOR.CLICK).forEach(el => {
        const method = el.getAttribute(ATTR.CLICK);
        const paramsJson = el.getAttribute(ATTR.PARAMS) || '[]';
        const params = safeJsonParse(paramsJson, []);

        el.addEventListener('click', e => {
            e.preventDefault();
            callMethod(method, params);
        });
    });
}

/**
 * Bind model handlers to form elements
 *
 * @param {HTMLElement} element - Container element
 * @param {Function} updateImmediate - Function to immediately update a property
 * @param {Function} updateDebounced - Function to debounce property updates
 * @returns {void}
 */
export function bindModelHandlers(element, updateImmediate, updateDebounced) {
    element.querySelectorAll(SELECTOR.MODEL).forEach(el => {
        const prop = el.getAttribute(ATTR.MODEL);
        const mode = el.getAttribute(ATTR.MODEL_MODE) || 'default';

        if (mode === 'lazy') {
            el.addEventListener('change', e => {
                updateImmediate(prop, e.target.value);
            });

            el.addEventListener('blur', e => {
                updateImmediate(prop, e.target.value);
            });
        } else {
            el.addEventListener('input', e => {
                updateDebounced(prop, e.target.value);
            });
        }
    });
}

/**
 * Bind form submit handlers
 *
 * @param {HTMLElement} element - Container element
 * @param {Function} callMethod - Method to call server method
 * @returns {void}
 */
export function bindFormHandlers(element, callMethod) {
    element.querySelectorAll(SELECTOR.SUBMIT).forEach(form => {
        form.addEventListener('submit', e => {
            e.preventDefault();

            const method = form.getAttribute(ATTR.SUBMIT);
            const paramsJson = form.getAttribute(ATTR.PARAMS) || '[]';
            const params = safeJsonParse(paramsJson, []);

            // Collect form data into an object
            const formData = new FormData(form);
            const payload = {};
            formData.forEach((value, key) => {
                payload[key] = value;
            });

            // Include payload as first param
            params.unshift(payload);
            callMethod(method, params);
        });
    });
}

/**
 * Bind drag handlers
 *
 * @param {HTMLElement} element - Container element
 * @param {string} componentId - Component ID
 * @returns {void}
 */
export function bindDragHandlers(element, componentId) {
    element.querySelectorAll(SELECTOR.DRAG).forEach(el => {
        const method = el.getAttribute(ATTR.DRAG);
        const paramsJson = el.getAttribute(ATTR.DRAG_PARAMS) || '[]';
        const params = safeJsonParse(paramsJson, []);

        log('Setting up drag handler for element:', el, 'method:', method);

        el.addEventListener('dragstart', e => {
            log('Drag start event triggered for:', el);
            el.classList.add('openwire-dragging');

            // Store drag data
            e.dataTransfer.setData('text/plain', JSON.stringify({
                method: method,
                params: params,
                componentId: componentId,
                elementId: el.id || el.getAttribute('data-id') || 'unknown'
            }));

            e.dataTransfer.effectAllowed = 'move';
        });

        el.addEventListener('dragend', e => {
            log('Drag end event triggered for:', el);
            el.classList.remove('openwire-dragging');
        });
    });
}

/**
 * Bind drop handlers
 *
 * @param {HTMLElement} element - Container element
 * @param {Function} callMethod - Method to call server method
 * @returns {void}
 */
export function bindDropHandlers(element, callMethod) {
    element.querySelectorAll(SELECTOR.DROP).forEach(el => {
        const method = el.getAttribute(ATTR.DROP);
        const paramsJson = el.getAttribute(ATTR.DROP_PARAMS) || '[]';
        const params = safeJsonParse(paramsJson, []);

        el.addEventListener('dragover', e => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            el.classList.add('openwire-drag-over');
        });

        el.addEventListener('dragleave', e => {
            el.classList.remove('openwire-drag-over');
        });

        el.addEventListener('drop', e => {
            e.preventDefault();
            el.classList.remove('openwire-drag-over');

            try {
                const dragData = JSON.parse(e.dataTransfer.getData('text/plain'));
                // Call the drop method with drag data
                const dropParams = params.slice();
                dropParams.unshift(dragData);
                callMethod(method, dropParams);
            } catch (err) {
                error('Error handling drop', err);
            }
        });
    });
}

/**
 * Bind sortable handlers
 *
 * @param {HTMLElement} element - Container element
 * @param {string} componentId - Component ID
 * @param {Function} callMethod - Method to call server method
 * @returns {void}
 */
export function bindSortableHandlers(element, componentId, callMethod) {
    element.querySelectorAll(SELECTOR.SORTABLE).forEach(el => {
        const method = el.getAttribute(ATTR.SORTABLE);
        const paramsJson = el.getAttribute(ATTR.SORTABLE_PARAMS) || '[]';
        const params = safeJsonParse(paramsJson, []);

        // Make children draggable
        const children = el.querySelectorAll(SELECTOR.SORTABLE_ITEM);
        children.forEach((child, index) => {
            child.draggable = true;
            child.setAttribute(ATTR.SORTABLE_INDEX, index);

            child.addEventListener('dragstart', e => {
                child.classList.add('openwire-sorting');
                e.dataTransfer.setData('text/plain', JSON.stringify({
                    method: method,
                    params: params,
                    componentId: componentId,
                    itemIndex: index,
                    itemId: child.id || child.getAttribute('data-id') || 'unknown'
                }));
            });

            child.addEventListener('dragend', e => {
                child.classList.remove('openwire-sorting');
            });
        });

        // Handle drops on sortable container
        el.addEventListener('dragover', e => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
        });

        el.addEventListener('drop', e => {
            e.preventDefault();

            try {
                const dragData = JSON.parse(e.dataTransfer.getData('text/plain'));
                const targetIndex = getSortableDropIndex(el, e);

                if (targetIndex !== null && targetIndex !== dragData.itemIndex) {
                    const sortParams = params.slice();
                    sortParams.unshift({
                        fromIndex: dragData.itemIndex,
                        toIndex: targetIndex,
                        itemId: dragData.itemId
                    });
                    callMethod(method, sortParams);
                }
            } catch (err) {
                error('Error handling sortable drop', err);
            }
        });
    });
}

/**
 * Bind all events to component elements
 *
 * @param {HTMLElement} element - Component element
 * @param {string} componentId - Component ID
 * @param {Object} handlers - Object with handler methods
 * @returns {void}
 */
export function bindAllEvents(element, componentId, handlers) {
    const { call, updateImmediate, updateDebounced } = handlers;

    bindClickHandlers(element, call);
    bindModelHandlers(element, updateImmediate, updateDebounced);
    bindFormHandlers(element, call);
    bindDragHandlers(element, componentId);
    bindDropHandlers(element, call);
    bindSortableHandlers(element, componentId, call);
}
