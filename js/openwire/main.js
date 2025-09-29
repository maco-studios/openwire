/**
 * OpenWire main entry point
 * This file is the main entry for the OpenWire library
 */
import { openwire, registerEffectHandler, Plugin, registerPlugin, unregisterPlugin, PREFIX, ATTR, CLASS, SELECTOR } from './src/index.js';

console.log('OpenWire JS loaded');

// Export all public APIs
export {
    openwire,
    registerEffectHandler,
    Plugin,
    registerPlugin,
    unregisterPlugin,
    PREFIX,
    ATTR,
    CLASS,
    SELECTOR
};
