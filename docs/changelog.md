# Changelog

All notable changes to OpenWire will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- MkDocs documentation system
- GitHub Actions for automated documentation deployment
- Comprehensive getting started guides
- API reference documentation

### Changed
- Improved documentation structure and navigation

## [1.0.0] - 2025-01-15

### Added
- Initial release of OpenWire
- Component-based architecture for Magento 1
- Template directive system (`@click`, `#model`, etc.)
- Two-way data binding
- Drag and drop support
- Effect system for client-side actions
- Plugin architecture for extensibility
- Comprehensive test suite with Vitest
- Modern JavaScript build system with Vite
- Example components (Counter, TodoList, KanbanBoard, etc.)

### Features
- **Components**: Server-side state management with reactive updates
- **Templates**: Clean directive syntax inspired by Vue.js/Alpine.js
- **Events**: Automatic event binding and AJAX handling
- **Data Binding**: Real-time synchronization between client and server
- **Drag & Drop**: Built-in support for sortable lists and drag-drop interactions
- **Effects**: Server-triggered client-side actions (notifications, redirects, etc.)
- **Plugins**: Extensible plugin system for custom functionality
- **Testing**: Unit tests for both PHP and JavaScript components
- **Development**: Modern tooling with hot-reload and TypeScript-style documentation

### Supported Directives
- `@click` - Handle click events
- `@submit` - Handle form submissions
- `@input` - Handle input events
- `@change` - Handle change events
- `@drag` - Make elements draggable
- `@drop` - Handle drop events
- `@sortable` - Create sortable lists
- `#model` - Two-way data binding
- `#loading` - Loading state indicators
- `#ignore` - Ignore elements from OpenWire processing
- `:property` - One-way property binding

### Supported Effects
- `notify` - Display notifications
- `redirect` - Page redirections
- `registered` - Component registration confirmation

### Browser Support
- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- Internet Explorer 11 (with polyfills)

### Magento Compatibility
- Magento CE 1.7.x - 1.9.x
- Magento EE 1.12.x - 1.14.x

[Unreleased]: https://github.com/maco-studios/openwire/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/maco-studios/openwire/releases/tag/v1.0.0
