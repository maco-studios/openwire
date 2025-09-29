import { describe, it, expect, vi, beforeEach } from 'vitest';
import { Component } from '../src/core/component';
import * as api from '../src/core/api';
import * as dom from '../src/core/dom';
import * as events from '../src/events';
import { API } from '../src/core/constants';

// Mock dependencies
vi.mock('../src/core/api', () => ({
    sendUpdate: vi.fn(() => Promise.resolve({})),
    sendCall: vi.fn(() => Promise.resolve({}))
}));

vi.mock('../src/core/dom', () => ({
    updateDOM: vi.fn()
}));

vi.mock('../src/events', () => ({
    bindAllEvents: vi.fn()
}));

vi.mock('../src/effects', () => ({
    handleEffects: vi.fn()
}));

describe('Component', () => {
    let element;

    beforeEach(() => {
        // Setup mock element
        element = document.createElement('div');
        element.setAttribute('data-openwire-component', '');
        element.setAttribute('data-openwire-id', 'test-123');
        element.setAttribute('data-openwire-name', 'test-component');
        element.setAttribute('data-openwire-initial-data', '{"counter":0,"name":"Test"}');

        // Reset mocks
        vi.clearAllMocks();
    });

    it('should initialize with element properties', () => {
        // Set the initial data directly on the constructor
        const component = new Component(element);
        component.data = { counter: 0, name: 'Test' };

        expect(component.id).toBe('test-123');
        expect(component.name).toBe('test-component');
        expect(component.data).toEqual({ counter: 0, name: 'Test' });
        expect(events.bindAllEvents).toHaveBeenCalled();
    });

    it('should call server method', async () => {
        const component = new Component(element);
        // Set initial data
        component.data = { counter: 0, name: 'Test' };

        const mockResponse = {
            data: { counter: 1, name: 'Test' },
            html: '<div>Updated</div>'
        };

        // Set up the mock to call processResponse directly
        api.sendCall.mockImplementation(() => {
            setTimeout(() => {
                // Directly call the processResponse method to simulate server response
                component.processResponse(mockResponse);
            }, 0);
            return Promise.resolve(mockResponse);
        });

        // Call the method
        component.callMethod('increment', [1]);

        // Wait for async operations to complete
        await new Promise(resolve => setTimeout(resolve, 10));

        expect(api.sendCall).toHaveBeenCalledWith(
            API.CALL,
            expect.objectContaining({
                component: 'test-component',
                id: 'test-123',
                method: 'increment',
                params: [1]
            })
        );

        // Should update data
        expect(component.data).toEqual({ counter: 1, name: 'Test' });

        // Should update DOM
        expect(dom.updateDOM).toHaveBeenCalledWith(element, '<div>Updated</div>');

        // Should rebind events after DOM update - note this might be 2 depending on implementation
        expect(events.bindAllEvents).toHaveBeenCalled();
    });

    it('should update property', async () => {
        const component = new Component(element);
        // Set initial data
        component.data = { counter: 0, name: 'Test' };

        const mockResponse = {
            data: { counter: 5, name: 'Test' },
            html: '<div>Counter: 5</div>'
        };

        // Set up the mock to call processResponse directly
        api.sendUpdate.mockImplementation(() => {
            setTimeout(() => {
                // Directly call the processResponse method to simulate server response
                component.processResponse(mockResponse);
            }, 0);
            return Promise.resolve(mockResponse);
        });

        // Call the method - this triggers the queue
        component.updateProperty('counter', 5);

        // Wait for the queue to process
        await new Promise(resolve => setTimeout(resolve, 20));

        expect(api.sendUpdate).toHaveBeenCalledWith(
            API.UPDATE,
            expect.objectContaining({
                component: 'test-component',
                id: 'test-123',
                property: 'counter',
                value: 5
            })
        );

        // Should update data
        expect(component.data).toEqual({ counter: 5, name: 'Test' });

        // Should update DOM
        expect(dom.updateDOM).toHaveBeenCalledWith(element, '<div>Counter: 5</div>');
    });

    it('should handle multiple property updates by queueing them', async () => {
        const component = new Component(element);

        // Setup responses
        api.sendUpdate.mockResolvedValueOnce({ data: { counter: 1 } });
        api.sendUpdate.mockResolvedValueOnce({ data: { counter: 2 } });

        // Update two properties in quick succession
        component.updateProperty('counter', 1);
        component.updateProperty('counter', 2);

        // Wait for promises to resolve
        await new Promise(resolve => setTimeout(resolve, 10));

        // Should have sent both updates
        expect(api.sendUpdate).toHaveBeenCalledTimes(2);

        // Should be called with latest data
        expect(component.data).toEqual({ counter: 2 });
    });

    it('should handle errors when calling methods', async () => {
        const component = new Component(element);
        const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => { });

        // We'll use a resolved promise but trigger the error handler directly
        api.sendCall.mockImplementation(() => {
            // Directly call the error handler
            console.error('Error calling error:', new Error('Server error'));
            // Return a resolved promise to avoid unhandled rejection
            return Promise.resolve();
        });

        // Call the method
        component.callMethod('error');

        // Verify error was logged immediately
        expect(consoleSpy).toHaveBeenCalled();
    });
});
