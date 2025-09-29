import { describe, it, expect, vi } from 'vitest';
import {
    PREFIX,
    ATTR,
    CLASS,
    SELECTOR
} from '../src/core/constants';

describe('Constants', () => {
    it('should export PREFIX constant', () => {
        expect(PREFIX).toBe('data-openwire');
    });

    it('should export ATTR constants', () => {
        expect(ATTR).toBeTypeOf('object');
        expect(ATTR.COMPONENT).toBe('data-openwire-component');
        expect(ATTR.ID).toBe('data-openwire-id');
        expect(ATTR.NAME).toBe('data-openwire-name');
        expect(ATTR.CLICK).toBe('data-openwire-click');
        expect(ATTR.MODEL).toBe('data-openwire-model');
    });

    it('should export CLASS constants', () => {
        expect(CLASS).toBeTypeOf('object');
        expect(CLASS.LOADING).toBe('openwire-loading');
    });

    it('should export SELECTOR constants', () => {
        expect(SELECTOR).toBeTypeOf('object');
        expect(SELECTOR.COMPONENT).toBe('[data-openwire-component]');
        expect(SELECTOR.CLICK).toBe('[data-openwire-click]');
        expect(SELECTOR.MODEL).toBe('[data-openwire-model]');
    });
});
