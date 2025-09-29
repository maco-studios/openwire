/**
 * OpenWire main class
 */
import { ATTR, SELECTOR, PREFIX } from './constants';
import { Component } from './component';
import { log, error } from '../utils';
import { registerEffectHandler } from '../effects';
import { initializePlugins } from '../plugins';

/**
 * Main OpenWire class
 */
export class OpenWire {
    /**
     * Create a new OpenWire instance
     */
    constructor() {
        this.components = new Map();
        this.initialized = false;

        // Bind methods
        this.init = this.init.bind(this);
        this.registerComponents = this.registerComponents.bind(this);
        this.registerEffectHandlers = this.registerEffectHandlers.bind(this);

        log('OpenWire created');
    }

    /**
     * Initialize OpenWire
     */
    init() {
        if (this.initialized) {
            log('OpenWire already initialized');
            return;
        }

        log('Initializing OpenWire');

        // Register standard effect handlers
        this.registerEffectHandlers();

        // Find and register all components
        this.registerComponents();

        // Set up mutation observer to detect new components
        this.setupMutationObserver();

        // Initialize all plugins
        initializePlugins(this);

        this.initialized = true;
        log('OpenWire initialization complete');
    }

    /**
     * Register all OpenWire components in the document
     */
    registerComponents() {
        const components = document.querySelectorAll(SELECTOR.COMPONENT);

        log(`Found ${components.length} OpenWire components`);

        components.forEach(element => {
            this.registerComponent(element);
        });
    }

    /**
     * Register a single OpenWire component
     *
     * @param {HTMLElement} element - Component root element
     * @returns {Component|null} The registered component or null
     */
    registerComponent(element) {
        try {
            const id = element.getAttribute(ATTR.ID);

            // Skip if already registered
            if (id && this.components.has(id)) {
                log(`Component ${id} already registered`);
                return this.components.get(id);
            }

            const component = new Component(element);
            this.components.set(component.id, component);

            // Mark as initialized
            element.setAttribute(ATTR.INITIALIZED, 'true');

            log(`Registered component: ${component.id}`);
            return component;
        } catch (err) {
            error('Error registering component:', err);
            return null;
        }
    }

    /**
     * Register standard effect handlers
     */
    registerEffectHandlers() {
        // Redirect effect handler
        registerEffectHandler('redirect', (params) => {
            if (params.url) {
                log('Redirecting to:', params.url);
                window.location.href = params.url;
            }
        });

        // Alert effect handler
        registerEffectHandler('alert', (params) => {
            if (params.message) {
                alert(params.message);
            }
        });

        // Reload page effect handler
        registerEffectHandler('reload', () => {
            window.location.reload();
        });

        // Focus effect handler
        registerEffectHandler('focus', (params) => {
            if (params.selector) {
                const element = document.querySelector(params.selector);
                if (element) {
                    element.focus();
                }
            }
        });
    }

    /**
     * Set up mutation observer to detect new components
     */
    setupMutationObserver() {
        const observer = new MutationObserver(mutations => {
            let shouldRegister = false;

            mutations.forEach(mutation => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === 1) { // Element node
                            // Check if node is a component
                            if (node.hasAttribute && node.hasAttribute(ATTR.COMPONENT)) {
                                this.registerComponent(node);
                            }

                            // Check if node contains components
                            if (node.querySelectorAll) {
                                const components = node.querySelectorAll(SELECTOR.COMPONENT);
                                if (components.length > 0) {
                                    shouldRegister = true;
                                }
                            }
                        }
                    });
                }
            });

            if (shouldRegister) {
                this.registerComponents();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        log('Mutation observer set up');
    }

    /**
     * Get a component by ID
     *
     * @param {string} id - Component ID
     * @returns {Component|undefined} The component or undefined
     */
    getComponent(id) {
        return this.components.get(id);
    }

    /**
     * Get all registered components
     *
     * @returns {Map} Map of all components
     */
    getComponents() {
        return this.components;
    }

    /**
     * Register a custom effect handler
     *
     * @param {string} name - Effect name
     * @param {Function} handler - Effect handler function
     */
    registerEffect(name, handler) {
        registerEffectHandler(name, handler);
    }
}
