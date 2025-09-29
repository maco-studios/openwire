```markdown
# OpenWire JavaScript Architecture

This document details the JavaScript architecture of OpenWire, focusing on the modular design principles that guide our implementation.

## Module Structure

The JavaScript portion of OpenWire is organized into several key modules:

```
js/openwire/
├── src/
│   ├── core/
│   │   ├── api.js          # API communication layer
│   │   ├── component.js    # Core component class
│   │   ├── constants.js    # Shared constants and selectors
│   │   ├── dom.js          # DOM manipulation utilities
│   │   └── openwire.js     # Main initialization
│   ├── effects/
│   │   └── index.js        # Server-side effects handling
│   ├── events/
│   │   └── index.js        # Event binding system
│   ├── plugins/
│   │   └── index.js        # Plugin architecture
│   ├── styles/
│   │   └── index.js        # Style-related utilities
│   ├── utils/
│   │   └── index.js        # Shared utility functions
│   └── index.js            # Main entry point
└── tests/                  # Unit tests for each module
```

## Key Architectural Principles

### 1. Single Responsibility Principle

Each module has a clearly defined responsibility:

- **Component**: Manages component lifecycle, data, and server communication
- **DOM**: Handles all DOM-related operations
- **API**: Manages AJAX communication with the server
- **Events**: Binds and manages event listeners
- **Effects**: Processes server-side effects

### 2. Dependency Injection

Dependencies are explicitly injected to improve testability:

```javascript
// Example of dependency injection
function setupComponent(component, { dom, api, events }) {
  // Use injected dependencies
}
```

### 3. Plugin Architecture

The plugin system allows developers to extend OpenWire without modifying core code:

```javascript
// Example plugin registration
OpenWire.registerPlugin('debug', {
  initialize(openwire) {
    // Add debug functionality
  },
  beforeCall(payload) {
    // Modify or log call payload
  },
  afterResponse(response) {
    // Process or log response
  }
});
```

### 4. Event-Driven Communication

Components use events to communicate, reducing direct dependencies:

```javascript
// Example of event-based communication
component.emit('property:updated', { name: 'counter', value: 5 });
```

### 5. Immutable Data Flow

Data flows one way, making state changes predictable:

```javascript
// Example of immutable data flow
function processResponse(response) {
  // Create new state instead of modifying existing
  this.data = { ...this.data, ...response.data };
  return this.data;
}
```

## Testing Strategy

Our modular architecture facilitates comprehensive testing:

- **Unit Tests**: Each module is tested in isolation with mocked dependencies
- **Integration Tests**: Key module interactions are tested together
- **End-to-End Tests**: Complete component functionality is tested in a simulated browser environment

## Build Process

The modular code is bundled using Vite:

1. Development mode preserves module structure for easier debugging
2. Production mode optimizes and minifies the code
3. ESM and UMD builds are generated for maximum compatibility

## Performance Considerations

Our modular approach improves performance through:

1. **Tree Shaking**: Unused code is eliminated during build
2. **Code Splitting**: Only necessary code is loaded initially
3. **Selective Updates**: Only affected DOM parts are updated
4. **Debouncing**: Property updates are batched to reduce server calls

## Extending the Framework

Developers can extend OpenWire in several ways:

1. **Custom Components**: Create reusable components
2. **Plugins**: Add global functionality
3. **Custom Effects**: Create custom server-side effects
4. **Custom Event Handlers**: Add new event types

## Upgrade Path

When upgrading OpenWire, follow these steps:

1. Check the changelog for breaking changes
2. Update any custom plugins to use the latest API
3. Test custom components with the new version
4. Update any custom effects to handle new response formats

## Best Practices

When working with OpenWire's JavaScript modules:

1. **Don't modify core files**: Use extension points instead
2. **Follow the module pattern**: Keep related code together
3. **Write tests**: Ensure your extensions work as expected
4. **Document your changes**: Comment any custom code
5. **Use the API**: Don't rely on internal implementation details
```
