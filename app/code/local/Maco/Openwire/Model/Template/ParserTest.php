<?php

/**
 * Copyright (c) 2025 MACO
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/maco-studios/openwire
 */

/**
 * Simple test class for the refactored Openwire Template Parser
 *
 * This demonstrates the improved design and functionality
 */
class Maco_Openwire_Model_Template_ParserTest
{
    /**
     * Test the refactored parser with various directives
     */
    public static function runTests()
    {
        echo "Running Openwire Template Parser Tests...\n\n";

        // Create a mock component
        $component = Mage::getBlockSingleton('openwire/component_abstract'));
        $component->setData('id', 'test_component_123');
        $component->setData('count', 5);
        $component->setData('name', 'Test User');

        $parser = Mage::getModel('openwire/template_parser');

        // Test 1: Basic component marker
        self::testComponentMarker($parser, $component);

        // Test 2: Click directive
        self::testClickDirective($parser, $component);

        // Test 3: Data binding
        self::testDataBinding($parser, $component);

        // Test 4: Model directive
        self::testModelDirective($parser, $component);

        // Test 5: Loading directive
        self::testLoadingDirective($parser, $component);

        // Test 6: Validation
        self::testValidation($parser, $component);

        echo "All tests completed!\n";
    }

    protected static function testComponentMarker($parser, $component)
    {
        echo "Test 1: Component Marker\n";
        $html = '<div ow class="test">Content</div>';
        $result = $parser->compile($html, $component);
        echo "Input:  {$html}\n";
        echo "Output: {$result}\n";
        echo "Expected: data-openwire-id attribute added\n\n";
    }

    protected static function testClickDirective($parser, $component)
    {
        echo "Test 2: Click Directive\n";
        $html = '<button @click="increment">Click me</button>';
        $result = $parser->compile($html, $component);
        echo "Input:  {$html}\n";
        echo "Output: {$result}\n";
        echo "Expected: data-openwire-click attribute added\n\n";
    }

    protected static function testDataBinding($parser, $component)
    {
        echo "Test 3: Data Binding\n";
        $html = '<span :count="5">Count: 5</span>';
        $result = $parser->compile($html, $component);
        echo "Input:  {$html}\n";
        echo "Output: {$result}\n";
        echo "Expected: data-openwire-bind attribute added\n\n";
    }

    protected static function testModelDirective($parser, $component)
    {
        echo "Test 4: Model Directive\n";
        $html = '<input #model="name" type="text">';
        $result = $parser->compile($html, $component);
        echo "Input:  {$html}\n";
        echo "Output: {$result}\n";
        echo "Expected: data-openwire-model attribute added\n\n";
    }

    protected static function testLoadingDirective($parser, $component)
    {
        echo "Test 5: Loading Directive\n";
        $html = '<div #loading>Loading...</div>';
        $result = $parser->compile($html, $component);
        echo "Input:  {$html}\n";
        echo "Output: {$result}\n";
        echo "Expected: data-openwire-loading attribute added\n\n";
    }

    protected static function testValidation($parser, $component)
    {
        echo "Test 6: Validation\n";

        // Test with invalid directive
        $html = '<button @invalid="test">Invalid</button>';
        try {
            $result = $parser->compile($html, $component);
            echo "Input:  {$html}\n";
            echo "Output: {$result}\n";
            echo "Expected: Should handle gracefully\n\n";
        } catch (Exception $e) {
            echo "Caught expected exception: " . $e->getMessage() . "\n\n";
        }
    }

    /**
     * Demonstrate custom directive registration
     */
    public static function demonstrateCustomDirective()
    {
        echo "Demonstrating Custom Directive Registration...\n";

        // Create a custom directive class
        $customDirectiveCode = '
        class Maco_Openwire_Model_Template_Directive_Custom extends Maco_Openwire_Model_Template_Directive_Abstract
        {
            protected $pattern = "@custom";

            public function compile($value, $component)
            {
                return " data-openwire-custom=\"{$value}\"";
            }
        }';

        echo "Custom directive code:\n{$customDirectiveCode}\n";

        $parser = Mage::getModel('openwire/template_parser');
        $component = Mage::getBlockSingleton('openwire/component_abstract'));
        $component->setData('id', 'test');

        // Register the custom directive
        $parser->registerDirective('@custom', 'openwire/template_directive_custom');

        echo "Custom directive '@custom' registered successfully!\n";
        echo "Available directives: " . implode(', ', $parser->getAvailableDirectives()) . "\n";
    }
}

// Uncomment to run tests
// Maco_Openwire_Model_Template_ParserTest::runTests();
// Maco_Openwire_Model_Template_ParserTest::demonstrateCustomDirective();
