/**
 * Effects handler for OpenWire component responses
 */
import { log, warn, error } from '../utils';

/**
 * Handle effects from server response
 *
 * @param {Array<Object>} effects - Array of effect objects
 * @param {Object} context - Component context
 * @returns {void}
 */
export function handleEffects(effects, context) {
    if (!effects || !Array.isArray(effects)) return;

    effects.forEach(fx => {
        try {
            // Call the appropriate effect handler
            const handler = EFFECT_HANDLERS[fx.type];
            if (handler) {
                handler(fx, context);
            } else {
                warn('Unknown effect type:', fx.type);
            }
        } catch (e) {
            error('Error processing effect', fx, e);
        }
    });
}

/**
 * Registry of effect handlers
 * Extension point: Add custom effect handlers here
 */
export const EFFECT_HANDLERS = {
    /**
     * Notification effect
     * @param {Object} fx - Effect object with data.message
     */
    notify(fx) {
        if (fx.data && fx.data.message) {
            log(fx.data.message);
        }
    },

    /**
     * Component registration effect
     * @param {Object} fx - Effect object with data.id
     * @param {Object} context - Component context
     */
    registered(fx, context) {
        if (!fx.data || !fx.data.id) return;

        try {
            const id = fx.data.id;
            // Set DOM attribute and instance id so subsequent calls use the registry id
            if (context && context.element) {
                context.element.setAttribute('data-openwire-id', id);
                context.id = id;

                // Register in global instance registry
                window.openwireInstances = window.openwireInstances || {};
                window.openwireInstances[id] = context;
            }
        } catch (e) {
            error('Failed to apply registered id', fx, e);
        }
    },

    /**
     * Page redirect effect
     * @param {Object} fx - Effect object with data.url and optional data.target
     */
    redirect(fx) {
        if (!fx.data || !fx.data.url) return;

        const target = fx.data.target || '_self';
        if (target === '_blank') {
            window.open(fx.data.url, '_blank');
        } else {
            window.location.href = fx.data.url;
        }
    }
};

/**
 * Register a custom effect handler
 *
 * @param {string} effectType - Effect type name
 * @param {Function} handler - Handler function (fx, context) => void
 * @returns {void}
 */
export function registerEffectHandler(effectType, handler) {
    if (typeof handler !== 'function') {
        error('Effect handler must be a function');
        return;
    }

    EFFECT_HANDLERS[effectType] = handler;
    log(`Registered custom effect handler for "${effectType}"`);
}
