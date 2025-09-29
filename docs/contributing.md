# Contributing to OpenWire

Thank you for your interest in contributing to OpenWire! This document provides guidelines and information for contributors.

## 🚀 Getting Started

### Prerequisites

- **PHP 7.4+** (for development, though OpenWire supports PHP 5.6+ for compatibility)
- **Node.js 16+** and npm
- **Composer**
- **Git**
- **Magento 1.9.x** for testing

### Development Setup

1. **Fork and clone the repository**:

```bash
git clone https://github.com/your-username/openwire.git
cd openwire
```

2. **Install PHP dependencies**:

```bash
composer install
```

3. **Install JavaScript dependencies**:

```bash
npm install
```

4. **Set up your development environment**:

```bash
# Build JavaScript for development
npm run dev

# Or start the development server
npm run dev -- --watch
```

## 🏗️ Project Structure

```
openwire/
├── app/code/local/Maco/Openwire/     # PHP backend code
│   ├── controllers/                   # Magento controllers
│   ├── etc/                          # Module configuration
│   ├── Helper/                       # Helper classes
│   ├── Model/                        # Component models and logic
│   └── ...
├── app/design/                       # Template files
├── js/openwire/                      # JavaScript source code
│   ├── src/                          # Source modules
│   ├── tests/                        # JavaScript tests
│   └── main.js                       # Entry point
├── docs/                             # Documentation
├── examples/                         # Example components
└── ...
```

## 🧪 Testing

### Running PHP Tests

We use Pest for PHP testing:

```bash
# Run all PHP tests
composer test

# Run specific test file
vendor/bin/pest tests/ComponentTest.php

# Run with coverage
composer test:coverage
```

### Running JavaScript Tests

We use Vitest for JavaScript testing:

```bash
# Run all JavaScript tests
npm test

# Run tests in watch mode
npm run test:watch

# Run with coverage
npm run test:coverage
```

### Manual Testing

1. Set up a Magento 1 instance
2. Install OpenWire using the development version
3. Create test components to verify functionality
4. Test in multiple browsers

## 📝 Code Standards

### PHP Standards

We follow PSR-12 coding standards with some Magento-specific adaptations:

```bash
# Check code style
composer cs

# Fix code style automatically
composer cs:fix
```

**Key guidelines:**

- Use proper DocBlocks for all classes and methods
- Follow Magento 1 naming conventions (e.g., `ClassName_MethodName`)
- Maintain backward compatibility with PHP 5.6
- Use type hints where possible (PHP 7+)

### JavaScript Standards

- Use ES6+ features (they're transpiled for compatibility)
- Follow JSDoc conventions for documentation
- Use descriptive variable and function names
- Prefer functional programming patterns

### Documentation Standards

- Use clear, concise language
- Include code examples for complex concepts
- Test all code examples before committing
- Follow the existing documentation structure

## 🐛 Reporting Issues

### Bug Reports

When reporting bugs, please include:

1. **Environment details**:
   - Magento version
   - PHP version
   - Browser and version
   - OpenWire version

2. **Steps to reproduce**:
   - Clear, numbered steps
   - Expected vs actual behavior
   - Screenshots if relevant

3. **Code samples**:
   - Minimal component code that reproduces the issue
   - Template code
   - Any error messages

### Feature Requests

For feature requests, please:

1. Check if the feature already exists or is planned
2. Describe the use case and benefits
3. Provide examples of how it would be used
4. Consider backward compatibility

## 🔧 Development Workflow

### Creating a Pull Request

1. **Create a feature branch**:

```bash
git checkout -b feature/your-feature-name
# or
git checkout -b bugfix/issue-description
```

2. **Make your changes**:
   - Write code following our standards
   - Add tests for new functionality
   - Update documentation if needed

3. **Test your changes**:

```bash
# Run all tests
composer test
npm test

# Check code style
composer cs
```

4. **Commit your changes**:

```bash
git add .
git commit -m "feat: add new component feature"
```

Follow [Conventional Commits](https://www.conventionalcommits.org/) format:

- `feat:` - New features
- `fix:` - Bug fixes
- `docs:` - Documentation changes
- `style:` - Code style changes
- `refactor:` - Code refactoring
- `test:` - Test changes
- `chore:` - Build/tooling changes

5. **Push and create PR**:

```bash
git push origin feature/your-feature-name
```

Then create a pull request on GitHub.

### Pull Request Guidelines

- **Clear title and description**
- **Reference related issues** (e.g., "Fixes #123")
- **Include tests** for new functionality
- **Update documentation** if needed
- **Ensure CI passes** before requesting review

## 🏗️ Architecture Guidelines

### Component Design

When creating new components:

1. **Single Responsibility**: Each component should have one clear purpose
2. **Data-First**: Use `setData()`/`getData()` for state management
3. **Template Separation**: Keep logic in PHP, presentation in templates
4. **Testability**: Write testable code with clear inputs/outputs

### JavaScript Modules

When adding JavaScript functionality:

1. **Modular Design**: Create focused, single-purpose modules
2. **No External Dependencies**: Keep the library dependency-free
3. **Browser Compatibility**: Support IE11+ and modern browsers
4. **Performance**: Optimize for minimal bundle size

### Template Directives

When adding new directives:

1. **Follow Conventions**: Use `@` for events, `#` for special directives
2. **Clear Syntax**: Make directives intuitive and readable
3. **Error Handling**: Provide clear error messages for invalid usage
4. **Documentation**: Include comprehensive examples

## 📚 Adding Documentation

### Documentation Structure

```
docs/
├── index.md                    # Home page
├── getting-started/           # Getting started guides
├── guide/                     # User guides
├── developer/                 # Developer documentation
├── examples/                  # Example implementations
├── api/                       # API reference
└── contributing.md            # This file
```

### Writing Guidelines

1. **Start with user needs**: What problem does this solve?
2. **Provide examples**: Show, don't just tell
3. **Test your examples**: Ensure all code works
4. **Use consistent formatting**: Follow existing patterns
5. **Include navigation**: Help users find related content

### Building Documentation Locally

```bash
# Install MkDocs
pip install mkdocs mkdocs-material mkdocs-awesome-pages-plugin

# Serve documentation locally
mkdocs serve

# Build static documentation
mkdocs build
```

## 🎯 Areas for Contribution

### High Priority

- **Additional Components**: More example components (charts, forms, etc.)
- **Testing**: Increase test coverage
- **Documentation**: More tutorials and examples
- **Performance**: Optimization and benchmarking

### Medium Priority

- **Browser Compatibility**: IE11 support improvements
- **Accessibility**: ARIA support and keyboard navigation
- **Developer Tools**: Better debugging utilities
- **Integration**: Other Magento modules

### Low Priority

- **Themes**: Alternative UI themes
- **Localization**: Multi-language support
- **Build Tools**: Webpack support, etc.

## 💬 Getting Help

- **GitHub Discussions**: Ask questions and share ideas
- **Issues**: Report bugs and request features
- **Discord**: Join our community chat (coming soon)
- **Email**: Contact maintainers directly for sensitive issues

## 📄 License

By contributing to OpenWire, you agree that your contributions will be licensed under the same license as the project.

## 🙏 Recognition

Contributors will be:

- Listed in the project README
- Credited in release notes
- Invited to the contributors team (for significant contributions)

Thank you for helping make OpenWire better!