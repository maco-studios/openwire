```markdown
# OpenWire Modular Approach Guide

This guide explains how to use OpenWire's modular architecture effectively when building and extending components.

## Understanding the Modular Architecture

OpenWire's modular architecture separates concerns into distinct modules, making the code more maintainable, testable, and extensible.

## Core Modules and Their Usage

### Component Module

The `Component` class is the foundation of OpenWire:

```javascript
import { Component } from 'openwire/core/component';

// Create a component instance
const component = new Component(element);

// Call a server method
component.callMethod('increment', [1])
  .then(response => {
    console.log('Method called successfully', response);
  });

// Update a property
component.updateProperty('counter', 5);
```

### DOM Module

The DOM module handles all DOM manipulation:

```javascript
import { updateDOM } from 'openwire/core/dom';

// Update the DOM content
updateDOM(element, '<div>New content</div>');
```

### API Module

The API module manages communication with the server:

```javascript
import { sendCall, sendUpdate, API } from 'openwire/core/api';

// Call a component method on the server
sendCall(API.UPDATE, {
  component: 'counter',
  id: 'counter-123',
  method: 'increment',
  params: [1]
}).then(response => {
  // Process response
});
```

### Events Module

The Events module manages event binding and handling:

```javascript
import { bindAllEvents } from 'openwire/events';

// Bind all events on an element
bindAllEvents(element, componentInstance);
```

## Using Constants

Always use the exported constants instead of hardcoded strings:

```javascript
import { ATTR } from 'openwire/core/constants';

// Good: Use constants
element.getAttribute(ATTR.COMPONENT_ID);

// Bad: Hardcode strings
element.getAttribute('data-openwire-id');
```

## Creating Custom Plugins

Extend OpenWire with custom plugins:

```javascript
import { registerPlugin } from 'openwire/plugins';

// Define a plugin
const myPlugin = {
  name: 'myPlugin',
  initialize(openwire) {
    // Plugin setup code
    console.log('My plugin initialized!');
  },
  beforeCall(payload) {
    // Modify or log call payload
    console.log('Before calling server', payload);
    return payload;
  },
  afterResponse(response) {
    // Process or log response
    console.log('After server response', response);
    return response;
  }
};

// Register the plugin
registerPlugin(myPlugin);
```

## Working with Effects

Effects handle server-side responses:

```javascript
import { registerEffect } from 'openwire/effects';

// Register a custom effect
registerEffect('show-modal', (component, params) => {
  const [modalId] = params;
  const modal = document.getElementById(modalId);
  modal.style.display = 'block';
});

// The server can now trigger this effect:
// return ['effects' => [['show-modal', 'login-modal']]];
```

## Testing Your Extensions

When testing your extensions to OpenWire:

```javascript
import { vi } from 'vitest';
import { Component } from 'openwire/core/component';

// Mock dependencies
vi.mock('openwire/core/api', () => ({
  sendCall: vi.fn(),
  sendUpdate: vi.fn()
}));

describe('My Custom Component', () => {
  it('should handle custom behavior', async () => {
    // Set up test
    const element = document.createElement('div');
    const component = new Component(element);

    // Test your custom behavior
    // ...
  });
});
```

## Advanced Patterns

### Composing Components

You can compose components for more complex UIs:

```javascript
// Parent component
class ShoppingCart extends Component {
  initialize() {
    // Find child components
    this.cartItems = Array.from(
      this.element.querySelectorAll('[data-openwire-component="cart-item"]')
    ).map(el => new CartItem(el));
  }

  updateQuantities() {
    // Work with child components
    this.cartItems.forEach(item => {
      item.updateDisplay();
    });
  }
}
```

### Creating Component Libraries

Group related components into libraries:

```javascript
// ecommerce-components.js
export { ProductCard } from './components/product-card';
export { CartItem } from './components/cart-item';
export { Checkout } from './components/checkout';
```

### Lazy Loading Components

Improve performance by lazy loading components:

```javascript
// Only load heavy components when needed
document.querySelector('#load-checkout').addEventListener('click', async () => {
  const { Checkout } = await import('./components/checkout.js');
  const element = document.querySelector('#checkout-container');
  new Checkout(element).initialize();
});
```

## Best Practices

1. **Keep components focused**: Each component should do one thing well
2. **Use dependency injection**: Makes testing and maintenance easier
3. **Document your components**: Add JSDoc comments to all public methods
4. **Follow naming conventions**: Consistent naming improves readability
5. **Write unit tests**: Ensure your components work as expected
6. **Minimize direct DOM manipulation**: Use the DOM module instead
7. **Avoid global state**: Use component state or props instead
8. **Use TypeScript definitions**: For better type checking and IDE support
9. **Handle errors gracefully**: Always catch and handle exceptions
10. **Optimize for performance**: Batch updates and minimize server calls

## Common Pitfalls to Avoid

1. Modifying the core OpenWire modules directly
2. Relying on internal implementation details
3. Not handling async operations properly
4. Forgetting to clean up event listeners
5. Overusing server calls for simple UI updates
```
