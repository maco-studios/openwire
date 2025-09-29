/**
 * DOM manipulation utilities for OpenWire components
 */
import { ATTR, SELECTOR } from '../core/constants';
import { error } from '../utils';

/**
 * Update DOM with new HTML while preserving certain elements
 *
 * @param {HTMLElement} element - Container element to update
 * @param {string} html - New HTML content
 * @param {Object} state - Component state for data binding
 * @returns {void}
 */
export function updateDOM(element, html, state = {}) {
    // Create temporary DOM from new HTML
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    const newEl = doc.body.firstChild;

    if (!newEl) {
        error('Empty HTML response, cannot update DOM');
        return;
    }

    // Preserve focused input elements to prevent focus loss
    const focusedElement = document.activeElement;
    let focusedValue = '';
    let focusedProperty = '';

    if (focusedElement &&
        focusedElement.tagName === 'INPUT' &&
        element.contains(focusedElement)) {
        focusedValue = focusedElement.value;
        focusedProperty = focusedElement.getAttribute(ATTR.MODEL) || '';
    }

    // Preserve any elements marked to ignore inside the current element
    const ignores = Array.from(element.querySelectorAll(SELECTOR.IGNORE));
    const placeholders = [];

    ignores.forEach((el, idx) => {
        const ph = document.createElement('div');
        ph.setAttribute(ATTR.IGNORE_PLACEHOLDER, idx);
        el.parentNode.replaceChild(ph, el);
        placeholders.push({ idx, node: el });
    });

    // Replace content
    element.innerHTML = newEl.innerHTML;

    // Reattach ignored nodes back into placeholders
    placeholders.forEach(p => {
        const placeholder = element.querySelector(`[${ATTR.IGNORE_PLACEHOLDER}="${p.idx}"]`);
        if (placeholder) {
            placeholder.parentNode.replaceChild(p.node, placeholder);
        }
    });

    // Restore focus and value to the previously focused input
    if (focusedProperty) {
        const newFocusedElement = element.querySelector(`[${ATTR.MODEL}="${focusedProperty}"]`);
        if (newFocusedElement && newFocusedElement.tagName === 'INPUT') {
            newFocusedElement.value = focusedValue;
            newFocusedElement.focus();
            // Set cursor position to end of text
            const length = newFocusedElement.value.length;
            newFocusedElement.setSelectionRange(length, length);
        }
    }

    // Update data bindings
    updateDataBinding(element, state);
}

/**
 * Update data-bound elements based on component state
 *
 * @param {HTMLElement} element - Container element
 * @param {Object} state - Component state
 * @returns {void}
 */
export function updateDataBinding(element, state = {}) {
    // Find all elements with data binding attributes
    element.querySelectorAll(SELECTOR.BIND).forEach(el => {
        const bindAttr = el.getAttribute(ATTR.BIND);
        if (bindAttr && state[bindAttr] !== undefined) {
            // Update the element's value or text content based on its type
            if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT') {
                el.value = state[bindAttr];
            } else {
                el.textContent = state[bindAttr];
            }
        }
    });
}

/**
 * Calculate the drop index for sortable items
 *
 * @param {HTMLElement} container - Sortable container element
 * @param {Event} event - Drop event
 * @returns {number|null} Target index for dropping
 */
export function getSortableDropIndex(container, event) {
    const children = Array.from(container.querySelectorAll(SELECTOR.SORTABLE_ITEM));
    const mouseY = event.clientY;

    for (let i = 0; i < children.length; i++) {
        const child = children[i];
        const rect = child.getBoundingClientRect();
        const childMiddle = rect.top + (rect.height / 2);

        if (mouseY < childMiddle) {
            return i;
        }
    }

    return children.length;
}

/**
 * Toggle loading state of a component
 *
 * @param {HTMLElement} element - Component element
 * @param {boolean} isLoading - Whether component is in loading state
 * @returns {void}
 */
export function toggleLoading(element, isLoading) {
    if (isLoading) {
        element.classList.add('openwire-loading');
    } else {
        element.classList.remove('openwire-loading');
    }
}
