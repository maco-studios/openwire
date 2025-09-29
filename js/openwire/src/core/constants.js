/**
 * Constants for OpenWire attribute prefixes and selectors
 *
 * Using constants makes it easier to:
 * 1. Change prefix globally if needed
 * 2. Keep selector strings consistent
 * 3. Enable autocompletion in editors
 */

// Base prefix for all OpenWire attributes
export const PREFIX = 'data-openwire';

// Component identification
export const ATTR = {
    // Core component identification
    COMPONENT: `${PREFIX}-component`,
    ID: `${PREFIX}-id`,
    NAME: `${PREFIX}-name`,
    STATE: `${PREFIX}-state`,
    INITIAL_DATA: `${PREFIX}-initial-data`,
    INITIALIZED: `${PREFIX}-initialized`,


    // Event handling
    CLICK: `${PREFIX}-click`,
    SUBMIT: `${PREFIX}-submit`,
    MODEL: `${PREFIX}-model`,
    MODEL_MODE: `${PREFIX}-model-mode`,
    BIND: `${PREFIX}-bind`,

    // Drag and drop
    DRAG: `${PREFIX}-drag`,
    DRAG_PARAMS: `${PREFIX}-drag-params`,
    DROP: `${PREFIX}-drop`,
    DROP_PARAMS: `${PREFIX}-drop-params`,
    SORTABLE: `${PREFIX}-sortable`,
    SORTABLE_PARAMS: `${PREFIX}-sortable-params`,
    SORTABLE_ITEM: 'data-sortable-item',
    SORTABLE_INDEX: 'data-sortable-index',

    // Special handling
    IGNORE: `${PREFIX}-ignore`,
    IGNORE_PLACEHOLDER: `${PREFIX}-ignore-placeholder`,
    PARAMS: `${PREFIX}-params`
};

// CSS class names
export const CLASS = {
    LOADING: 'openwire-loading',
    DRAGGING: 'openwire-dragging',
    DRAG_OVER: 'openwire-drag-over',
    SORTING: 'openwire-sorting',
    DRAG_PLACEHOLDER: 'openwire-drag-placeholder'
};

// CSS selectors (derived from attributes)
export const SELECTOR = {
    COMPONENT: `[${ATTR.COMPONENT}]`,
    CLICK: `[${ATTR.CLICK}]`,
    SUBMIT: `[${ATTR.SUBMIT}]`,
    MODEL: `[${ATTR.MODEL}]`,
    BIND: `[${ATTR.BIND}]`,
    DRAG: `[${ATTR.DRAG}]`,
    DROP: `[${ATTR.DROP}]`,
    SORTABLE: `[${ATTR.SORTABLE}]`,
    SORTABLE_ITEM: `[${ATTR.SORTABLE_ITEM}]`,
    IGNORE: `[${ATTR.IGNORE}]`
};

// API endpoints
export const API = {
    UPDATE: '/openwire/update/index',
    CALL: '/openwire/call/index'
};

// Timing constants
export const TIMING = {
    MODEL_DEBOUNCE: 300, // ms
    DEBOUNCE_MS: 300 // ms
};
