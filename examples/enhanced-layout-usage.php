<?php

/**
 * Example: Using the Enhanced Layout with OpenWire Components
 *
 * This example demonstrates how to use the new Layout model rewrite
 * with registered directives and component support.
 */

// Example 1: Adding components via Layout
class ExampleController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $layout = $this->getLayout();

        // Add multiple components at once
        $layout->addComponents([
            'header_counter' => [
                'type' => 'openwire/component_counter',
                'name' => 'Header Counter',
                'state' => [
                    'count' => 0,
                    'name' => 'Page Views',
                    'step' => 1
                ]
            ],
            'sidebar_counter' => [
                'type' => 'openwire/component_counter',
                'name' => 'Sidebar Counter',
                'state' => [
                    'count' => 10,
                    'name' => 'User Interactions',
                    'step' => 5
                ]
            ]
        ]);

        // Add a single component
        $layout->addComponent('footer_counter', [
            'type' => 'openwire/component_counter',
            'state' => ['count' => 100, 'name' => 'Total Visits']
        ]);

        // Access components
        $headerCounter = $layout->getComponent('header_counter');
        if ($headerCounter) {
            $headerCounter->increment(); // Modify component state
        }

        // Render all components
        $renderedComponents = $layout->renderComponents();

        // Pass to view
        $this->getResponse()->setBody(json_encode([
            'components' => array_keys($layout->getComponents()),
            'rendered' => $renderedComponents
        ]));
    }
}

// Example 2: Using directives in templates
?>
<!-- Template: example_component.phtml -->
<div data-openwire-component="openwire/component_counter" data-openwire-id="example_counter">
    <h3><?= $this->getName() ?></h3>

    <!-- Click directive (registered via config.xml) -->
    <button @click="increment">
        Count: <span #bind="count"><?= $this->getCount() ?></span>
    </button>

    <!-- Submit directive -->
    <form @submit="setCount">
        <input type="number" #model="count" value="<?= $this->getCount() ?>" />
        <button type="submit">Set Count</button>
    </form>

    <!-- Conditional loading state -->
    <div #loading="updating" style="display: none;">
        Updating...
    </div>

    <!-- Ignore directive - preserve content across updates -->
    <div #ignore>
        This content will not be replaced during component updates.
        <script>
            // Client-side only scripts are preserved
            console.log('This script runs only once');
        </script>
    </div>
</div>

<?php
// Example 3: Using directive factory programmatically
$directiveFactory = new Maco_Openwire_Model_Template_Directive_Factory();

// Get available directive patterns (loaded from config.xml)
$patterns = $directiveFactory::getAvailablePatterns();
// Returns: ['@click', '@submit', '@input', '@change', '@drag', '@drop', '@sortable', '#model', '#loading', '#ignore']

// Get specific directive
$clickDirective = $directiveFactory::getDirective('@click');
$compiled = $clickDirective->compile('increment', $component);
// Returns: 'data-openwire-click="increment"'

// Register new directive at runtime
$directiveFactory::registerDirective('@hover', 'MyModule_Model_Template_Directive_Hover');

// Example 4: Component creation through enhanced layout
$layout = Mage::app()->getLayout();

// Create component via enhanced createBlock method
$counter = $layout->createBlock('openwire/component_counter', 'my_counter', [
    'count' => 5,
    'name' => 'Dynamic Counter'
]);

echo $counter->render();
?>
