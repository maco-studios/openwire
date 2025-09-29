```markdown
# OpenWire JavaScript Documentation

Welcome to the OpenWire JavaScript documentation. This section provides comprehensive information about OpenWire's JavaScript architecture and usage.

## Contents

- [JavaScript Architecture](./architecture.md): Detailed overview of OpenWire's modular JavaScript architecture
- [Modular Approach Guide](./modular-approach.md): Guide on how to use and extend OpenWire's modular system

## Quick Start

To begin working with OpenWire's JavaScript modules:

1. Import the necessary modules:
   ```javascript
   import { Component } from 'openwire/core/component';
   import { ATTR, EVENTS } from 'openwire/core/constants';
   ```

2. Create a component instance:
   ```javascript
   const element = document.querySelector('[data-openwire-component="my-component"]');
   const component = new Component(element);
   ```

3. Interact with the component:
   ```javascript
   // Call a server method
   component.callMethod('increment', [1]);

   // Update a property
   component.updateProperty('counter', 5);
   ```

## Additional Resources

- [Main Documentation](../README.md): Return to the main documentation
- [Developer Guide](../developer-guide.md): General developer guide for OpenWire
- [Usage Guide](../usage-guide.md): Guide for using OpenWire components in Magento 1

For specific code examples and implementation details, refer to the modular approach guide.
```
