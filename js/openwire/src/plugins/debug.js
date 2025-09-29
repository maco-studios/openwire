/**
 * Debug Plugin for OpenWire
 *
 * Adds debug tools and visual inspection for OpenWire components
 */
import { Plugin, registerPlugin } from '../plugins';
import { ATTR, SELECTOR } from '../core/constants';
import { log } from '../utils';

/**
 * Debug Plugin for OpenWire
 */
export class DebugPlugin extends Plugin {
    /**
     * Create a new debug plugin
     *
     * @param {Object} options - Plugin options
     */
    constructor(options = {}) {
        super('debug', options);

        // Default options
        this.options = {
            enableOverlay: true,
            logEvents: true,
            ...options
        };

        // Plugin state
        this.styles = null;
        this.toolbar = null;
        this.debugMode = false;
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

        super.init(openwire);

        // Store reference to OpenWire
        this.openwire = openwire;

        // Add keyboard shortcut for toggle (Ctrl+Shift+D)
        document.addEventListener('keydown', this.handleKeydown.bind(this));

        // Create toolbar
        if (this.options.enableOverlay) {
            this.createDebugTools();
        }

        log('Debug plugin initialized');
    }

    /**
     * Create debug toolbar and styles
     */
    createDebugTools() {
        // Create styles
        this.styles = document.createElement('style');
        this.styles.textContent = `
      .openwire-debug-toolbar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #333;
        color: #fff;
        z-index: 9999;
        padding: 8px;
        font-family: monospace;
        font-size: 12px;
        display: none;
        box-shadow: 0 -2px 5px rgba(0,0,0,0.2);
      }

      .openwire-debug-toolbar.active {
        display: block;
      }

      .openwire-debug-toolbar button {
        background: #555;
        border: none;
        color: #fff;
        padding: 4px 8px;
        margin-right: 8px;
        cursor: pointer;
        border-radius: 3px;
      }

      .openwire-debug-toolbar button:hover {
        background: #777;
      }

      [data-openwire-component].openwire-debug {
        outline: 2px solid #f00 !important;
        position: relative;
      }

      [data-openwire-component].openwire-debug::before {
        content: attr(data-openwire-name);
        position: absolute;
        top: -18px;
        left: 0;
        background: #f00;
        color: #fff;
        padding: 2px 6px;
        font-size: 10px;
        font-family: monospace;
        z-index: 9990;
      }
    `;
        document.head.appendChild(this.styles);

        // Create toolbar
        this.toolbar = document.createElement('div');
        this.toolbar.className = 'openwire-debug-toolbar';

        const toggleBtn = document.createElement('button');
        toggleBtn.textContent = 'Toggle Highlighting';
        toggleBtn.addEventListener('click', this.toggleDebugHighlighting.bind(this));

        const countSpan = document.createElement('span');
        countSpan.textContent = `OpenWire Components: ${this.openwire.components.size}`;

        const refreshBtn = document.createElement('button');
        refreshBtn.textContent = 'Refresh Count';
        refreshBtn.addEventListener('click', () => {
            countSpan.textContent = `OpenWire Components: ${this.openwire.components.size}`;
        });

        this.toolbar.appendChild(toggleBtn);
        this.toolbar.appendChild(refreshBtn);
        this.toolbar.appendChild(countSpan);

        document.body.appendChild(this.toolbar);
    }

    /**
     * Handle keyboard shortcut
     *
     * @param {KeyboardEvent} e - Keyboard event
     */
    handleKeydown(e) {
        // Ctrl+Shift+D to toggle debug mode
        if (e.ctrlKey && e.shiftKey && e.key === 'D') {
            e.preventDefault();
            this.toggleDebugMode();
        }
    }

    /**
     * Toggle debug mode on/off
     */
    toggleDebugMode() {
        this.debugMode = !this.debugMode;

        if (this.toolbar) {
            this.toolbar.classList.toggle('active', this.debugMode);
        }

        if (this.debugMode) {
            this.enableDebugMode();
        } else {
            this.disableDebugMode();
        }

        log(`Debug mode ${this.debugMode ? 'enabled' : 'disabled'}`);
    }

    /**
     * Enable debug mode
     */
    enableDebugMode() {
        if (this.options.enableOverlay) {
            this.toggleDebugHighlighting(true);
        }
    }

    /**
     * Disable debug mode
     */
    disableDebugMode() {
        if (this.options.enableOverlay) {
            this.toggleDebugHighlighting(false);
        }
    }

    /**
     * Toggle component highlighting
     *
     * @param {boolean|Event} forceState - Force a specific state or toggle if event
     */
    toggleDebugHighlighting(forceState) {
        const state = typeof forceState === 'boolean' ? forceState : undefined;

        document.querySelectorAll(SELECTOR.COMPONENT).forEach(el => {
            if (state === undefined) {
                el.classList.toggle('openwire-debug');
            } else {
                el.classList.toggle('openwire-debug', state);
            }
        });
    }

    /**
     * Clean up plugin resources
     */
    destroy() {
        if (!this.initialized) {
            return;
        }

        // Remove debug highlighting
        this.toggleDebugHighlighting(false);

        // Remove event listeners
        document.removeEventListener('keydown', this.handleKeydown.bind(this));

        // Remove toolbar and styles
        if (this.toolbar) {
            document.body.removeChild(this.toolbar);
            this.toolbar = null;
        }

        if (this.styles) {
            document.head.removeChild(this.styles);
            this.styles = null;
        }

        this.openwire = null;
        super.destroy();
    }
}

// Register the plugin
registerPlugin(new DebugPlugin());

// Export the plugin class
export default DebugPlugin;
