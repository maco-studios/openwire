/**
 * Vitest setup file
 * This file runs before tests to set up the test environment
 */

// Import any global polyfills needed for the tests

// Add any global mocks here
if (typeof window !== 'undefined') {
    // Add mock Magento form key
    window.FORM_KEY = 'test_form_key_global';
}

// Define any global helper functions for tests
