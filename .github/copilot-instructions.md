## Quick orientation for coding agents

This repository is a Magento 1 module (OpenWire) that provides a server-backed component system plus a companion JavaScript runtime.
Keep answers and edits focused on how PHP components and the JS runtime integrate, the project's conventions, and the developer workflows below.

U need to follow the TODO.md file for tasks and priorities, and after interactions, update the TODO.md file to reflect progress.

### Best practices and coding rules
- do not use inline comments and if u found any, remove them.
- utilize __() and sprintf for all user-facing strings, e.g. __('Hello %s', $name)
- Error handling: use try/catch blocks to handle exceptions gracefully and return meaningful error messages in JSON responses.
- Logging: use Mage::log() for logging important events and errors, ensuring logs are informative and structured.
- Security: validate and sanitize all inputs rigorously to prevent security vulnerabilities such as SQL injection and XSS.
- YAGNI Principle: "You Aren't Gonna Need It" - removing unused code and features is a priority.
- Follow modern PHP practices (PHP 8.3+):
- All typed PHP 8.3 code should use the new `readonly` property feature where applicable, property promotion in constructors, and union types.
- Follow PSR-12 coding style for PHP (use `php-cs-fixer` with the provided config).
- Use strict types in all PHP files: `declare(strict_types=1);`
- Avoid using `<?php echo ?>` in templates; prefer `<?= ?>` for brevity and clarity.
- Classes with minimum content
- High use of Single Responsibility Principle (SRP) for PHP classes.
- Dependency inversion via constructor injection where possible.
- Avoid static methods and properties in PHP classes unless absolutely necessary.

### Make things as simple as possible, do not create unnecessary complexity
- Favor simple solutions over complex ones. If a problem can be solved in multiple ways, choose the simplest one.
- Use php core code instead of magento patterns, example: use of Varien_Object instead of default php arrays.
- Create abstraction to use new technologies like psr/log.

### Big picture / key locations
- PHP module: `app/code/local/Maco/Openwire/` — component classes, controllers and rendering logic.
- JS runtime and source: `js/openwire/src/` and entry `js/openwire/main.js` (bundled output present as `openwire.js`).
- Tests: JavaScript tests in `js/openwire/tests/` (run with Vitest). See `vitest.config.js`.
- Docs and site: `docs/` and generated `site/` (mkdocs-powered). Project README at `README.md`.

### How client/server communicate (important)
- Client sends JSON POSTs to `/openwire/update/index` (see `js/openwire/*` and `openwire.js`).
- Typical payload fields: `id`, `calls: [{method, params}]`, `updates`, `form_key`, optional `server_class`, `initial_state`.
- Form key resolution: JS looks for `window.FORM_KEY`, `window.formKey`, or `input[name="form_key"]` before sending.
- Server response shape used by the JS runtime: `{ html, state, effects }` where `effects` may include `notify`, `redirect`, `registered`, etc.

### DOM / attribute conventions (copy these exactly)
- Attribute prefix: `data-openwire-*` (examples below are used throughout the JS):
  - `data-openwire-id` — component instance id
  - `data-openwire-class` / `data-openwire-name` — server class for anonymous components
  - `data-openwire-click` — method name to call on click
  - `data-openwire-submit` — form submit handler
  - `data-openwire-model` & `data-openwire-model-mode` (`lazy` supported) — input binding
  - `data-openwire-bind` — render content from state
  - `data-openwire-ignore` — preserve DOM nodes across updates
  - sortable/drag/drop attributes: `data-openwire-sortable`, `data-openwire-drag`, `data-openwire-drop`

Example JS payload (constructed by the runtime):
```json
{ "id": "comp123", "calls": [{"method":"increment","params":[]}], "form_key": "..." }
```

### Common patterns and effects
- Ignore placeholders: server HTML replacements preserve nodes marked with `data-openwire-ignore`.
- Effects: server can return `effects[]` used for client-side behavior (see `openwire.js`): `notify`, `redirect`, `registered` (used to assign ids to anonymous server components).
- Plugin API: `registerOpenWirePlugin(...)`, `registerEffectHandler(...)` and exported symbols in `js/openwire/src/index.js`.

### Developer workflows (how to build, test, and debug)
- JavaScript: `npm install` then `npm run dev` (vite dev) or `npm run build` for production bundles; `npm run test` runs Vitest tests.
- Docs: uses MkDocs (Python) — `npm run docs:serve` / `npm run docs:build` are configured in `package.json`.
- PHP tools: use Composer and vendor tools (see `composer.json`) — composer dev scripts include `rector` and `php-cs-fixer` invocations: run `composer install` then `vendor/bin/rector` / `vendor/bin/php-cs-fixer` as needed.
- Installing into a Magento instance: copy `app/`, `app/design/` and `js/` into your Magento installation (examples in `README.md`).
