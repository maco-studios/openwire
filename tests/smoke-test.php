<?php

/**
 * OpenWire Smoke Test - HTTP Integration Test
 *
 * This script tests the /openwire/update/index endpoint with a Counter payload
 * to verify the stateless architecture works end-to-end.
 */

// Basic configuration for testing
$baseUrl = 'http://localhost/magento'; // Adjust for your Magento installation
$endpoint = '/openwire/update/index';

/**
 * Simple HTTP POST function
 */
function httpPost($url, $data, $headers = []) {
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", $headers),
            'content' => $data
        ]
    ]);

    $response = @file_get_contents($url, false, $context);
    $statusCode = 200;

    if (isset($http_response_header)) {
        $statusLine = $http_response_header[0];
        if (preg_match('/HTTP\/\d+\.\d+ (\d+)/', $statusLine, $matches)) {
            $statusCode = (int) $matches[1];
        }
    }

    return ['body' => $response, 'status' => $statusCode];
}

/**
 * Test counter component increment
 */
function testCounterIncrement($baseUrl) {
    echo "Testing Counter Component Increment...\n";

    $payload = [
        'id' => 'test_counter_' . uniqid(),
        'server_class' => 'openwire/component_counter',
        'initial_state' => [
            'count' => 5,
            'name' => 'Test Counter',
            'step' => 1
        ],
        'calls' => [
            ['method' => 'increment', 'params' => []]
        ],
        'updates' => [],
        'form_key' => 'test_form_key_123' // In real test, get from session
    ];

    $response = httpPost(
        $baseUrl . '/openwire/update/index',
        json_encode($payload),
        [
            'Content-Type: application/json',
            'X-Requested-With: XMLHttpRequest'
        ]
    );

    echo "Response Status: {$response['status']}\n";

    if ($response['status'] === 200) {
        $data = json_decode($response['body'], true);

        if ($data && isset($data['success']) && $data['success']) {
            echo "✓ Success response received\n";

            // Check required response fields
            $requiredFields = ['html', 'state', 'effects'];
            foreach ($requiredFields as $field) {
                if (isset($data[$field])) {
                    echo "✓ Response contains '{$field}' field\n";
                } else {
                    echo "✗ Missing '{$field}' field in response\n";
                }
            }

            // Check state update
            if (isset($data['state']['count']) && $data['state']['count'] == 6) {
                echo "✓ Counter state incremented correctly (5 → 6)\n";
            } else {
                echo "✗ Counter state not updated correctly\n";
                echo "   Expected count: 6, Got: " . ($data['state']['count'] ?? 'null') . "\n";
            }

            // Check for data-openwire-* attributes in HTML
            if (isset($data['html']) && strpos($data['html'], 'data-openwire-') !== false) {
                echo "✓ HTML contains data-openwire-* attributes\n";
            } else {
                echo "✗ HTML missing data-openwire-* attributes\n";
            }

            // Check effects
            if (isset($data['effects']) && is_array($data['effects'])) {
                echo "✓ Effects array present (" . count($data['effects']) . " effects)\n";

                foreach ($data['effects'] as $effect) {
                    if (isset($effect['type'])) {
                        echo "  - Effect: {$effect['type']}\n";
                    }
                }
            }

        } else {
            echo "✗ Error response: " . ($data['error'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "✗ HTTP Error {$response['status']}\n";
        echo "Response: " . substr($response['body'], 0, 200) . "...\n";
    }

    echo "\n";
}

/**
 * Test invalid component class
 */
function testInvalidComponent($baseUrl) {
    echo "Testing Invalid Component Handling...\n";

    $payload = [
        'server_class' => 'invalid/component_doesnotexist',
        'initial_state' => [],
        'calls' => [['method' => 'someMethod', 'params' => []]],
        'form_key' => 'test_form_key_123'
    ];

    $response = httpPost(
        $baseUrl . '/openwire/update/index',
        json_encode($payload),
        ['Content-Type: application/json']
    );

    if ($response['status'] === 400) {
        echo "✓ Correctly returned 400 error for invalid component\n";

        $data = json_decode($response['body'], true);
        if ($data && isset($data['error'])) {
            echo "✓ Error message provided: {$data['error']}\n";
        }
    } else {
        echo "✗ Expected 400 error, got {$response['status']}\n";
    }

    echo "\n";
}

/**
 * Run all smoke tests
 */
function runSmokeTests($baseUrl) {
    echo "=== OpenWire Smoke Tests ===\n";
    echo "Base URL: {$baseUrl}\n";
    echo "Endpoint: /openwire/update/index\n\n";

    // Test valid counter component
    testCounterIncrement($baseUrl);

    // Test error handling
    testInvalidComponent($baseUrl);

    echo "=== Tests Complete ===\n";
}

// Run if called directly
if (php_sapi_name() === 'cli') {
    $baseUrl = $argv[1] ?? $baseUrl;
    runSmokeTests($baseUrl);
} else {
    // If run via web, provide simple interface
    echo "<pre>";
    $baseUrl = $_GET['base_url'] ?? $baseUrl;
    runSmokeTests($baseUrl);
    echo "</pre>";
}
