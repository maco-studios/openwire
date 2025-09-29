# Quick Start

This guide will help you create your first OpenWire component in just a few minutes.

## Step 1: Create a Component Class

Let's create a simple counter component that demonstrates the core concepts of OpenWire.

Create the following file:

```php title="app/code/local/Demo/Openwire/Model/Component/Counter.php"
<?php

class Demo_Openwire_Model_Component_Counter extends Maco_Openwire_Model_Component
{
    /**
     * Initialize component with default values
     */
    public function mount($params = [])
    {
        parent::mount($params);

        // Set initial count from params or default to 0
        $initialCount = isset($params['initial_count']) ? (int)$params['initial_count'] : 0;
        $this->setData('count', $initialCount);

        // Set component name
        $name = isset($params['name']) ? $params['name'] : 'Counter';
        $this->setData('name', $name);

        return $this;
    }

    /**
     * Increment the counter
     */
    public function increment()
    {
        $currentCount = (int) $this->getData('count');
        $this->setData('count', $currentCount + 1);

        return $this;
    }

    /**
     * Decrement the counter
     */
    public function decrement()
    {
        $currentCount = (int) $this->getData('count');
        $this->setData('count', max(0, $currentCount - 1)); // Don't go below 0

        return $this;
    }

    /**
     * Reset counter to zero
     */
    public function reset()
    {
        $this->setData('count', 0);

        // Return an effect to show a notification
        return [
            'effects' => [
                [
                    'type' => 'notify',
                    'data' => [
                        'message' => 'Counter has been reset!'
                    ]
                ]
            ]
        ];
    }

    /**
     * Get the template path for this component
     */
    public function getTemplate()
    {
        return 'demo/openwire/counter.phtml';
    }
}
```

## Step 2: Create the Template

Create the template file that will render your component:

```html title="app/design/frontend/base/default/template/demo/openwire/counter.phtml"
<div ow class="openwire-counter">
    <div class="counter-header">
        <h3><?php echo htmlspecialchars($this->getData('name')) ?></h3>
        <div class="counter-display">
            <span class="count-value"><?php echo (int) $this->getData('count') ?></span>
        </div>
    </div>

    <div class="counter-controls">
        <button @click="decrement" class="btn btn-decrement">
            - Decrease
        </button>

        <button @click="increment" class="btn btn-increment">
            + Increase
        </button>

        <button @click="reset" class="btn btn-reset">
            🔄 Reset
        </button>
    </div>

    <div #loading class="loading-indicator" style="display: none;">
        <span>Updating...</span>
    </div>
</div>

<style>
.openwire-counter {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    max-width: 300px;
    margin: 20px auto;
    text-align: center;
    font-family: Arial, sans-serif;
}

.counter-header h3 {
    margin: 0 0 15px 0;
    color: #333;
}

.counter-display {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 50%;
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.count-value {
    font-size: 24px;
    font-weight: bold;
    color: #007bff;
}

.counter-controls {
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.2s;
}

.btn-increment {
    background: #28a745;
    color: white;
}

.btn-decrement {
    background: #dc3545;
    color: white;
}

.btn-reset {
    background: #6c757d;
    color: white;
}

.btn:hover {
    opacity: 0.8;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.loading-indicator {
    margin-top: 15px;
    color: #6c757d;
    font-style: italic;
}

/* Loading state styling */
.openwire-loading {
    opacity: 0.7;
    pointer-events: none;
}

.openwire-loading .loading-indicator {
    display: block !important;
}
</style>
```

## Step 3: Register the Module

Create the module declaration file:

```xml title="app/etc/modules/Demo_Openwire.xml"
<?xml version="1.0"?>
<config>
    <modules>
        <Demo_Openwire>
            <active>true</active>
            <codePool>local</codePool>
            <depends>
                <Maco_Openwire/>
            </depends>
        </Demo_Openwire>
    </modules>
</config>
```

Create the module configuration:

```xml title="app/code/local/Demo/Openwire/etc/config.xml"
<?xml version="1.0"?>
<config>
    <modules>
        <Demo_Openwire>
            <version>1.0.0</version>
        </Demo_Openwire>
    </modules>

    <global>
        <models>
            <demo_openwire>
                <class>Demo_Openwire_Model</class>
            </demo_openwire>
        </models>

        <blocks>
            <demo_openwire>
                <class>Demo_Openwire_Block</class>
            </demo_openwire>
        </blocks>
    </global>
</config>
```

## Step 4: Create a Block Helper

Create a block to easily render your component:

```php title="app/code/local/Demo/Openwire/Block/Counter.php"
<?php

class Demo_Openwire_Block_Counter extends Mage_Core_Block_Template
{
    /**
     * Render the OpenWire counter component
     */
    public function renderCounter($params = [])
    {
        // Create component factory
        $factory = Mage::getModel('openwire/component_factory');

        // Create the counter component
        $component = $factory->make('demo_openwire/component_counter', $params);

        // Render the component
        return $component->render();
    }

    /**
     * Get default parameters for the counter
     */
    public function getDefaultParams()
    {
        return [
            'initial_count' => 0,
            'name' => 'My Counter'
        ];
    }
}
```

## Step 5: Use the Component

Now you can use your component in any template or CMS page:

### Option A: In a Template File

```php title="In any .phtml template file"
<?php
// Create the component with custom parameters
$counterParams = [
    'initial_count' => 5,
    'name' => 'Product Views'
];

echo $this->getLayout()
    ->createBlock('demo_openwire/counter')
    ->renderCounter($counterParams);
?>
```

### Option B: In a CMS Page/Block

Add this to a CMS page or static block:

```html
{{block type="demo_openwire/counter" template="demo/openwire/counter.phtml"}}
```

### Option C: Via Layout XML

```xml title="Add to your layout XML"
<reference name="content">
    <block type="demo_openwire/counter" name="demo.counter" template="demo/openwire/counter.phtml">
        <action method="setData">
            <key>initial_count</key>
            <value>10</value>
        </action>
        <action method="setData">
            <key>name</key>
            <value>Page Counter</value>
        </action>
    </block>
</reference>
```

## Step 6: Include OpenWire JavaScript

Make sure OpenWire JavaScript is included in your layout:

```xml title="app/design/frontend/base/default/layout/demo_openwire.xml"
<?xml version="1.0"?>
<layout version="0.1.0">
    <default>
        <reference name="head">
            <action method="addJs">
                <script>openwire/dist/openwire.js</script>
            </action>
        </reference>
    </default>
</layout>
```

## Step 7: Clear Cache and Test

1. **Clear Magento cache**:
```bash
rm -rf var/cache/*
```

2. **Refresh your page** and you should see your interactive counter!

## Understanding What Happened

Let's break down the key concepts:

### 🧩 Component Structure

- **`mount()`**: Initializes the component with parameters
- **Public methods**: Can be called from the frontend (e.g., `increment()`, `decrement()`)
- **Data properties**: Component state stored via `setData()`/`getData()`
- **Template**: HTML with OpenWire directives

### ⚡ Template Directives

- **`ow`**: Marks the root element as an OpenWire component
- **`@click="method"`**: Binds click events to component methods
- **`#loading`**: Element shown during AJAX requests

### 🔄 Reactive Updates

When you click a button:

1. OpenWire sends an AJAX request to the server
2. The server method executes and updates component state
3. The server re-renders the template with new data
4. OpenWire updates the DOM with the new HTML

## Next Steps

Now that you have a working component, explore more features:

- **[Data Binding](../guide/data-binding.md)**: Learn about two-way data binding with form inputs
- **[Events](../guide/events.md)**: Discover all available event types
- **[Effects](../guide/effects.md)**: Add client-side effects like notifications and redirects
- **[Examples](../examples/todo-list.md)**: See more complex component examples

!!! tip "Pro Tip"
    Try modifying the counter to accept a step value and create an `incrementBy($step)` method!
