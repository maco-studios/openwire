/**
 * Plugin system for OpenWire
 */
import { log, error } from '../utils';

// Store for registered plugins
const plugins = new Map();

/**
 * Plugin class for extending OpenWire
 */
export class Plugin {
    /**
     * Create a new plugin
     *
     * @param {string} name - Plugin name
     * @param {Object} options - Plugin options
     */
    constructor(name, options = {}) {
        this.name = name;
        this.options = options;
        this.initialized = false;

        log(`Plugin ${name} created`);
    }

    /**
     * Initialize the plugin
     *
     * @param {OpenWire} openwire - OpenWire instance
     */
    init(openwire) {
        if (this.initialized) {
            return;
        }

        try {
            // Override in subclasses
            log(`Plugin ${this.name} initialized`);
            this.initialized = true;
        } catch (err) {
            error(`Error initializing plugin ${this.name}:`, err);
        }
    }

    /**
     * Clean up plugin resources
     */
    destroy() {
        if (!this.initialized) {
            return;
        }

        try {
            // Override in subclasses
            log(`Plugin ${this.name} destroyed`);
            this.initialized = false;
        } catch (err) {
            error(`Error destroying plugin ${this.name}:`, err);
        }
    }
}

/**
 * Register a plugin with OpenWire
 *
 * @param {Plugin} plugin - Plugin instance
 * @returns {boolean} Whether registration was successful
 */
export function registerPlugin(plugin) {
    if (!(plugin instanceof Plugin)) {
        error('Invalid plugin:', plugin);
        return false;
    }

    if (plugins.has(plugin.name)) {
        error(`Plugin ${plugin.name} is already registered`);
        return false;
    }

    plugins.set(plugin.name, plugin);
    log(`Plugin ${plugin.name} registered`);
    return true;
}

/**
 * Unregister a plugin
 *
 * @param {string} name - Plugin name
 * @returns {boolean} Whether unregistration was successful
 */
export function unregisterPlugin(name) {
    if (!plugins.has(name)) {
        error(`Plugin ${name} is not registered`);
        return false;
    }

    const plugin = plugins.get(name);
    plugin.destroy();
    plugins.delete(name);
    log(`Plugin ${name} unregistered`);
    return true;
}

/**
 * Initialize all registered plugins
 *
 * @param {OpenWire} openwire - OpenWire instance
 */
export function initializePlugins(openwire) {
    plugins.forEach(plugin => {
        plugin.init(openwire);
    });
}

/**
 * Get a plugin by name
 *
 * @param {string} name - Plugin name
 * @returns {Plugin|undefined} The plugin or undefined
 */
export function getPlugin(name) {
    return plugins.get(name);
}
