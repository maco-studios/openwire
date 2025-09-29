import { describe, it, expect, vi, beforeEach } from 'vitest';
import {
    safeJsonParse,
    debounce,
    generateUniqueId,
    log,
    error,
    getMagentoFormKey
} from '../src/utils';

describe('Utils', () => {
    describe('safeJsonParse', () => {
        it('should parse valid JSON', () => {
            const jsonStr = '{"name":"test","value":123}';
            const result = safeJsonParse(jsonStr);
            expect(result).toEqual({ name: 'test', value: 123 });
        });

        it('should return fallback for invalid JSON', () => {
            const jsonStr = '{invalid:json}';
            const fallback = { default: true };
            const result = safeJsonParse(jsonStr, fallback);
            expect(result).toEqual(fallback);
        });

        it('should return empty object by default for invalid JSON', () => {
            const jsonStr = '{invalid:json}';
            const result = safeJsonParse(jsonStr);
            expect(result).toEqual({});
        });
    });

    describe('debounce', () => {
        beforeEach(() => {
            vi.useFakeTimers();
        });

        it('should debounce function calls', () => {
            const mockFn = vi.fn();
            const debouncedFn = debounce(mockFn, 100);

            // Call multiple times
            debouncedFn(1);
            debouncedFn(2);
            debouncedFn(3);

            // Check it hasn't been called yet
            expect(mockFn).not.toHaveBeenCalled();

            // Fast forward time
            vi.advanceTimersByTime(100);

            // Should be called with latest args
            expect(mockFn).toHaveBeenCalledTimes(1);
            expect(mockFn).toHaveBeenCalledWith(3);
        });
    });

    describe('generateUniqueId', () => {
        it('should generate a unique ID with prefix', () => {
            const id1 = generateUniqueId('test');
            const id2 = generateUniqueId('test');

            expect(id1).toMatch(/^test-/);
            expect(id2).toMatch(/^test-/);
            expect(id1).not.toEqual(id2);
        });

        it('should generate a unique ID without prefix', () => {
            const id1 = generateUniqueId();
            const id2 = generateUniqueId();

            expect(id1).toMatch(/^openwire-/);
            expect(id2).toMatch(/^openwire-/);
            expect(id1).not.toEqual(id2);
        });
    });

    describe('log and error', () => {
        beforeEach(() => {
            // Mock console methods
            vi.spyOn(console, 'log').mockImplementation(() => { });
            vi.spyOn(console, 'error').mockImplementation(() => { });
        });

        it('should log messages with OpenWire prefix', () => {
            log('test message');
            expect(console.log).toHaveBeenCalledWith('[OpenWire]', 'test message');
        });

        it('should log multiple arguments', () => {
            log('message', { data: 1 }, [1, 2, 3]);
            expect(console.log).toHaveBeenCalledWith('[OpenWire]', 'message', { data: 1 }, [1, 2, 3]);
        });

        it('should log error messages with OpenWire prefix', () => {
            error('error message');
            expect(console.error).toHaveBeenCalledWith('[OpenWire Error]', 'error message');
        });
    });

    describe('getMagentoFormKey', () => {
        it('should get form key from input', () => {
            // Clear global form key for this test
            window.FORM_KEY = undefined;
            // Setup mock DOM
            document.body.innerHTML = '<input name="form_key" value="test_form_key" type="hidden">';

            const formKey = getMagentoFormKey();
            expect(formKey).toBe('test_form_key');
        }); it('should return empty string when form key not found', () => {
            // Clear global form key for this test
            window.FORM_KEY = undefined;
            // Clear DOM
            document.body.innerHTML = '';

            const formKey = getMagentoFormKey();
            expect(formKey).toBe('');
        });
    });
});
