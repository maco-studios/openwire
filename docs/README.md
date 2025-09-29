# Documentation Development

This directory contains the MkDocs documentation for OpenWire.

## Setup

1. **Install Python dependencies**:
```bash
pip install -r requirements.txt
```

2. **Serve documentation locally**:
```bash
mkdocs serve
# or
npm run docs:serve
```

3. **Build static documentation**:
```bash
mkdocs build
# or
npm run docs:build
```

## Structure

```
docs/
├── index.md                    # Homepage
├── getting-started/           # Getting started guides
│   ├── installation.md
│   ├── quick-start.md
│   └── basic-usage.md
├── guide/                     # User guides
│   ├── components.md
│   ├── templates.md
│   ├── events.md
│   └── data-binding.md
├── developer/                 # Developer documentation
│   ├── architecture.md
│   ├── creating-components.md
│   └── extending.md
├── examples/                  # Example implementations
│   ├── counter.md
│   ├── todo-list.md
│   └── kanban-board.md
├── api/                       # API reference
│   ├── php-classes.md
│   ├── javascript-api.md
│   └── template-directives.md
├── contributing.md            # Contributing guidelines
└── changelog.md              # Version history
```

## Writing Guidelines

1. **Use clear, concise language**
2. **Include practical examples**
3. **Test all code snippets**
4. **Cross-reference related sections**
5. **Keep the user's perspective in mind**

## Deployment

Documentation is automatically deployed to GitHub Pages when changes are pushed to the `main` branch via the `.github/workflows/docs.yml` workflow.

The live documentation is available at: https://maco-studios.github.io/openwire/