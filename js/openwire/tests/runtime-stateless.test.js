import { describe, it, expect, beforeEach, vi } from 'vitest';

/**
 * Test for JavaScript runtime payload creation and effects handling
 * This ensures the new stateless architecture works properly on the client side
 */
describe('OpenWire Runtime - Stateless Payload', () => {
    let mockElement;
    let mockFetch;

    beforeEach(() => {
        // Reset DOM
        document.body.innerHTML = '';

        // Mock fetch
        mockFetch = vi.fn();
        global.fetch = mockFetch;

        // Mock window.FORM_KEY
        window.FORM_KEY = 'test_form_key_123';

        // Create a mock component element
        mockElement = document.createElement('div');
        mockElement.setAttribute('data-openwire-id', 'counter_123');
        mockElement.setAttribute('data-openwire-class', 'openwire/component_counter');
        mockElement.innerHTML = `
            <div>
                <span data-openwire-bind="count">5</span>
                <button data-openwire-click="increment">+</button>
            </div>
        `;
        document.body.appendChild(mockElement);
    });

    it('should include initial_state in payload when making method calls', async () => {
        // Mock successful server response
        mockFetch.mockResolvedValueOnce({
            ok: true,
            json: async () => ({
                success: true,
                html: '<div>Updated HTML</div>',
                state: { count: 6, name: 'Counter' },
                effects: []
            })
        });

        // Import OpenWire runtime after mocks are set up
        const { Component } = await import('../src/core/component.js');

        // Create component instance
        const component = new Component(mockElement);

        // Set initial state
        const initialState = { count: 5, name: 'Counter', step: 1 };
        component.state = initialState;

        // Trigger method call
        await component.call('increment', []);

        // Verify fetch was called with correct payload
        expect(mockFetch).toHaveBeenCalledWith('/openwire/update/index', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                id: 'counter_123',
                server_class: 'openwire/component_counter',
                initial_state: initialState,
                calls: [{ method: 'increment', params: [] }],
                updates: {},
                form_key: 'test_form_key_123'
            })
        });
    });

    it('should handle registered effect for new components', async () => {
        // Mock response with registered effect
        mockFetch.mockResolvedValueOnce({
            ok: true,
            json: async () => ({
                success: true,
                html: '<div data-openwire-id="counter_456">New Component</div>',
                state: { count: 0 },
                effects: [
                    {
                        type: 'registered',
                        data: { id: 'counter_456', server_id: 'some_server_token' }
                    }
                ]
            })
        });

        const { Component } = await import('../src/core/component.js');
        const { registerEffectHandler } = await import('../src/effects/index.js');

        // Mock registered effect handler
        const registeredHandler = vi.fn();
        registerEffectHandler('registered', registeredHandler);

        // Create anonymous component (no id initially)
        const anonymousElement = document.createElement('div');
        anonymousElement.setAttribute('data-openwire-class', 'openwire/component_counter');

        const component = new Component(anonymousElement);

        // Trigger method call
        await component.call('increment', []);

        // Verify registered effect was handled
        expect(registeredHandler).toHaveBeenCalledWith({
            id: 'counter_456',
            server_id: 'some_server_token'
        });
    });

    it('should handle destroyed effect for component cleanup', async () => {
        // Mock response with destroyed effect
        mockFetch.mockResolvedValueOnce({
            ok: true,
            json: async () => ({
                success: true,
                html: '',
                state: {},
                effects: [
                    {
                        type: 'destroyed',
                        data: { id: 'counter_123' }
                    }
                ]
            })
        });

        const { Component } = await import('../src/core/component.js');
        const { registerEffectHandler } = await import('../src/effects/index.js');

        // Mock destroyed effect handler
        const destroyedHandler = vi.fn();
        registerEffectHandler('destroyed', destroyedHandler);

        const component = new Component(mockElement);

        // Trigger method call that results in component destruction
        await component.call('destroy', []);

        // Verify destroyed effect was handled
        expect(destroyedHandler).toHaveBeenCalledWith({
            id: 'counter_123'
        });
    });

    it('should handle notify effects', async () => {
        // Mock response with notify effect
        mockFetch.mockResolvedValueOnce({
            ok: true,
            json: async () => ({
                success: true,
                html: '<div>Updated</div>',
                state: { count: 6 },
                effects: [
                    {
                        type: 'notify',
                        data: {
                            message: 'Counter incremented to 6',
                            type: 'success'
                        }
                    }
                ]
            })
        });

        const { Component } = await import('../src/core/component.js');
        const { registerEffectHandler } = await import('../src/effects/index.js');

        // Mock notify effect handler
        const notifyHandler = vi.fn();
        registerEffectHandler('notify', notifyHandler);

        const component = new Component(mockElement);

        // Trigger method call
        await component.call('increment', []);

        // Verify notify effect was handled
        expect(notifyHandler).toHaveBeenCalledWith({
            message: 'Counter incremented to 6',
            type: 'success'
        });
    });

    it('should resolve form key from multiple sources', async () => {
        // Test form key resolution priority
        delete window.FORM_KEY;
        delete window.formKey;

        // Add form key in hidden input
        const formKeyInput = document.createElement('input');
        formKeyInput.type = 'hidden';
        formKeyInput.name = 'form_key';
        formKeyInput.value = 'input_form_key_456';
        document.body.appendChild(formKeyInput);

        mockFetch.mockResolvedValueOnce({
            ok: true,
            json: async () => ({ success: true, html: '', state: {}, effects: [] })
        });

        const { Component } = await import('../src/core/component.js');
        const component = new Component(mockElement);

        await component.call('increment', []);

        // Verify form key was found from input
        const callArgs = mockFetch.mock.calls[0];
        const payload = JSON.parse(callArgs[1].body);
        expect(payload.form_key).toBe('input_form_key_456');
    });

    it('should handle error responses gracefully', async () => {
        // Mock error response
        mockFetch.mockResolvedValueOnce({
            ok: false,
            status: 400,
            json: async () => ({
                success: false,
                error: 'Invalid component class'
            })
        });

        const { Component } = await import('../src/core/component.js');
        const component = new Component(mockElement);

        // Should not throw, but handle error gracefully
        await expect(component.call('increment', [])).resolves.toBeUndefined();

        // Error should be logged or handled appropriately
        // (specific error handling depends on implementation)
    });
});
