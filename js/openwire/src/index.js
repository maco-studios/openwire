/**
 * OpenWire main entry point
 */
import { OpenWire } from './core/openwire';
import { registerEffectHandler } from './effects';
import { PREFIX, ATTR, CLASS, SELECTOR } from './core/constants';
import { Plugin, registerPlugin, unregisterPlugin } from './plugins';

// Create global OpenWire instance
const openwire = new OpenWire();

// Initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    openwire.init();
});

// Export OpenWire instance and public API
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

// Export as global for non-module environments
window.OpenWire = openwire;
window.OpenWirePlugin = Plugin;
window.registerOpenWirePlugin = registerPlugin;
window.unregisterOpenWirePlugin = unregisterPlugin;
