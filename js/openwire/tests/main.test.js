import { describe, it, expect } from 'vitest';
import { hello } from '../main.js';

describe('OpenWire main', () => {
    it('hello() returns expected string', () => {
        expect(hello()).toBe('hello from openwire');
    });
});
