# OpenWire Developer Documentation

## Overview

OpenWire is a lightweight component system for Magento 1 that enables developers to create interactive components with server-side rendering and client-side interactions. This document explains how to extend OpenWire with custom functionality.

## Architecture

OpenWire is built with modularity in mind, allowing for extensions at multiple levels:

1. **Core** - The central system that manages components and their lifecycle
2. **Components** - Individual instances of OpenWire components in the DOM
3. **Effects** - Server responses that trigger client-side effects
4. **Events** - User interactions that trigger component updates
5. **Plugins** - Custom extensions to the OpenWire system

## Extension Points

### Custom Effect Handlers

Effect handlers allow you to define custom actions that can be triggered from server responses.

```javascript
import { registerEffectHandler } from 'openwire';

// Register a custom effect handler
registerEffectHandler('myEffect', (params, component) => {
  // params - Effect parameters from server
  // component - Component instance that triggered the effect
  console.log('My effect triggered with params:', params);

  // Implement your custom effect logic here
  // For example:
  if (params.elementId) {
    const element = document.getElementById(params.elementId);
    if (element) {
      element.classList.add(params.cssClass);
    }
  }
});
```

From the server side, you can trigger this effect by returning:

```php
return [
    'effects' => [
        [
            'type' => 'myEffect',
            'params' => [
                'elementId' => 'product-123',
                'cssClass' => 'highlight'
            ]
        ]
    ]
];
```

### Creating Plugins

Plugins provide a way to extend OpenWire with more complex functionality.

```javascript
import { Plugin, registerPlugin } from 'openwire';

// Create a custom plugin
class MyPlugin extends Plugin {
  constructor(options = {}) {
    super('myPlugin', options);

    // Default options merged with user options
    this.options = {
      featureEnabled: true,
      ...options
    };
  }

  init(openwire) {
    // Don't forget to call parent init
    super.init(openwire);

    // Store reference to OpenWire instance
    this.openwire = openwire;

    // Initialize your plugin features
    if (this.options.featureEnabled) {
      this.setupFeature();
    }

    console.log('My plugin initialized');
  }

  setupFeature() {
    // Implement your feature setup
    document.addEventListener('click', this.handleClick.bind(this));
  }

  handleClick(event) {
    // Custom click handler logic
  }

  destroy() {
    // Clean up resources
    document.removeEventListener('click', this.handleClick.bind(this));

    // Don't forget to call parent destroy
    super.destroy();
  }
}

// Register the plugin
registerPlugin(new MyPlugin({
  featureEnabled: true
}));
```

### Working with Components

You can interact with OpenWire components programmatically:

```javascript
import { openwire } from 'openwire';

// Get a component by ID
const component = openwire.getComponent('my-component-id');

// Call a component method
component.callMethod('increment', [1]);

// Update a component property
component.updateProperty('counter', 5);

// Get component data
const data = component.getData();
console.log('Component data:', data);

// Force refresh a component
component.refresh();
```

### Creating Custom Components

On the server side, you can create custom OpenWire components by extending the base component class:

```php
namespace Maco\Openwire\Component;

class Counter extends \Maco\Openwire\Component
{
    public $count = 0;

    public function increment($amount = 1)
    {
        $this->count += $amount;
    }

    public function decrement($amount = 1)
    {
        $this->count -= $amount;
    }

    public function render()
    {
        return <<<HTML
        <div data-openwire-component data-openwire-id="{$this->id}" data-openwire-name="counter">
            <p>Count: {$this->count}</p>
            <button data-openwire-click="increment">+</button>
            <button data-openwire-click="decrement">-</button>
        </div>
        HTML;
    }
}
```

## Constants and Selectors

OpenWire provides a set of constants for working with attributes and selectors:

```javascript
import { PREFIX, ATTR, CLASS, SELECTOR } from 'openwire';

console.log('OpenWire prefix:', PREFIX); // 'data-openwire'

// Use the constants instead of hardcoding selectors
document.querySelectorAll(SELECTOR.COMPONENT).forEach(component => {
  const id = component.getAttribute(ATTR.ID);
  console.log('Component ID:', id);
});
```

## Best Practices

1. **Use constants instead of hardcoded strings**
   Always use the exported constants for attributes and selectors to ensure compatibility.

2. **Register event handlers properly**
   When adding custom event handlers, make sure to properly clean them up in destroy methods.

3. **Keep components small and focused**
   Design components to do one thing well rather than creating monolithic components.

4. **Use plugins for cross-cutting concerns**
   If functionality spans multiple components, consider creating a plugin.

5. **Test thoroughly**
   Use the testing tools to ensure your extensions work correctly across browsers.

## Example: Creating a Toast Notification Plugin

Here's a complete example of a toast notification plugin:

```javascript
import { Plugin, registerPlugin } from 'openwire';

class ToastPlugin extends Plugin {
  constructor(options = {}) {
    super('toast', options);

    this.options = {
      duration: 3000,
      position: 'top-right',
      ...options
    };

    this.container = null;
  }

  init(openwire) {
    super.init(openwire);

    // Create toast container
    this.container = document.createElement('div');
    this.container.className = 'openwire-toasts';
    this.container.style.position = 'fixed';
    this.container.style.zIndex = '9999';

    // Position based on options
    switch (this.options.position) {
      case 'top-right':
        this.container.style.top = '20px';
        this.container.style.right = '20px';
        break;
      case 'top-left':
        this.container.style.top = '20px';
        this.container.style.left = '20px';
        break;
      // Add other positions as needed
    }

    document.body.appendChild(this.container);

    // Register toast effect handler
    this.openwire.registerEffect('toast', (params) => {
      this.show(params.message, params.type);
    });
  }

  show(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `openwire-toast openwire-toast-${type}`;
    toast.textContent = message;

    toast.style.background = '#333';
    toast.style.color = '#fff';
    toast.style.padding = '10px 20px';
    toast.style.borderRadius = '4px';
    toast.style.marginBottom = '10px';
    toast.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';

    if (type === 'success') {
      toast.style.background = '#4caf50';
    } else if (type === 'error') {
      toast.style.background = '#f44336';
    } else if (type === 'warning') {
      toast.style.background = '#ff9800';
    }

    this.container.appendChild(toast);

    // Remove after duration
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transition = 'opacity 0.3s ease';

      setTimeout(() => {
        if (toast.parentNode) {
          this.container.removeChild(toast);
        }
      }, 300);
    }, this.options.duration);
  }

  destroy() {
    if (this.container) {
      document.body.removeChild(this.container);
      this.container = null;
    }

    super.destroy();
  }
}

// Register the plugin
registerPlugin(new ToastPlugin({
  duration: 5000,
  position: 'top-right'
}));
```

From the server, you could trigger a toast like this:

```php
return [
    'effects' => [
        [
            'type' => 'toast',
            'params' => [
                'message' => 'Product added to cart',
                'type' => 'success'
            ]
        ]
    ]
];
```
