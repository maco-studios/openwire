# OpenWire Refactor — TODO

This TODO is derived from `ARCHITECTURE_REFACTOR_COMPLETE.md`. It's a prioritized, actionable checklist for finishing the refactor, with owners, targets, and acceptance checks.

Summary contract
- Inputs: JSON POST payloads with { id, calls[], updates, form_key, server_class?, initial_state? }.
- Outputs: JSON { html: string, state: object, effects: array }.
- Success: deterministic HTML given a state; no reliance on PHP session/registry; JS runtime includes `initial_state` when appropriate.

Top-level priorities (high → low)
1. Core server API (high)
2. Counter example component (high)
3. Resolver / Factory / Hydrator implementations + tests (high)
4. UpdateController stateless handling + tests (high)
5. JS runtime changes & JS tests (high)
6. Template compiler adjustments (medium)
7. Migration compatibility shim (medium)
8. Docs / site updates (medium)
9. CI tasks & smoke tests (high)

Actionable tasks

✅ Core: Server-side structure and contracts (owner: backend)
  ✅ Implement `app/code/local/Maco/Openwire/Block/Component/Abstract.php` (extends `Mage_Core_Block_Template`) — priority: high.
  ✅ Add `Block/Component/Counter.php` canonical example with minimal public API (`increment`, `decrement`, `reset`) and deterministic template — high.
  ✅ Create `Model/Component/Resolver.php` to resolve server component classes via `createBlock` without registry/session — high.
  ✅ Create `Model/Component/Factory.php` to instantiate and configure blocks from resolved classes — high.
  ✅ Create `Model/Component/Hydrator.php` that hydrates block state strictly from `initial_state`/`updates` in request — high.
  ⏸️ Optional: `Model/Component/Store.php` for persistent server tokens (only if needed) — medium.
  ✅ Files: `app/code/local/Maco/Openwire/Block/Component/*.php`, `app/code/local/Maco/Openwire/Model/Component/*.php`.

✅ Controller: stateless request handling (owner: backend)
  ✅ Implement `controllers/UpdateController.php` to accept JSON payloads, validate shape, call Factory/Hydrator, render block, and return normalized `Response` model — high.
  ✅ Return 400 for malformed payloads; return effects array for recoverable errors — high.
  ✅ Ensure no use of `Mage::getSingleton('core/session')` or `Mage::registry()` for component state — high.
  ✅ Files: `app/code/local/Maco/Openwire/controllers/UpdateController.php`, `Model/Response.php`.

🔄 JS runtime changes & tests (owner: frontend)
  ✅ Ensure `js/openwire/src` and bundled `openwire.js` include `initial_state` by default when sending calls/updates — high.
  ✅ Handle `effects` types: `registered`, `destroyed`, `notify`, `redirect` — high.
  ✅ Add a Vitest test that creates a payload for a method call and asserts `initial_state` is included and `registered`/`destroyed` effects are handled — high.
  🔄 Update existing JS runtime to implement new payload format — high.
  📋 Files: `js/openwire/src/*`, `js/openwire/tests/*`, update `openwire.js` bundle.

📋 Templates & Compiler (owner: backend/frontend)
  ✅ Implement deterministic template output with exact `data-openwire-*` attribute names per spec — high.
  ✅ Add transform step in `Model/Template/*` to compile templates into markup with `data-openwire-component`, `data-openwire-id`, `data-openwire-class`/`data-openwire-name`, `data-openwire-bind`, `data-openwire-ignore`, `data-openwire-model`, etc. — medium.
  ✅ Register template directives in config.xml for proper discovery — high.
  ✅ Create Layout model rewrite with `addComponents` functionality — high.
  ✅ Files: `app/code/local/Maco/Openwire/Model/Template/*`, `Model/Layout.php`, and block templates under `app/design/frontend/...`.

✅ Tests (owner: assigned devs)
  ✅ PHP unit tests (Pest/PHPUnit):
    ✅ `Model/Component/Resolver` — resolves blocks without session (unit) — high.
    ✅ `Model/Component/Factory` — instantiates block with provided state (unit) — high.
    ✅ `Model/Component/Hydrator` — hydrates state from `initial_state` and `updates` (unit) — high.
  ✅ JS Vitest tests:
    ✅ Runtime payload creation + effects handling (one happy path + registered/destroyed effects) — high.
  ✅ Smoke test:
    ✅ HTTP POST to `/openwire/update/index` with Counter payload; assert response shape and presence of `data-openwire-*` attributes in `html` — high.

📋 Migration & Compatibility shim (owner: backend)
  📋 Accept old session-backed requests and convert to stateless flow (emit `deprecation` effect) — medium.
  📋 Provide `registered` effect when handing server-generated token to client and include TTL/lifetime metadata — medium.

📋 Docs / Site updates (owner: docs)
  📋 Update `docs/getting-started/installation.md` and `developer/*` describing stateless contract, payload examples, and migration steps — medium.
  📋 Add example walkthrough for Counter component — medium.
  📋 Regenerate `site/` docs (mkdocs) — medium.

📋 CI / Quality gates (owner: devops)
  📋 Ensure `npm run build` passes (JS bundle) and `composer dump-autoload` / PHP autoload works — high.
  📋 Add CI steps: install composer deps, run php-cs-fixer, run rector (if used), run PHP unit tests, run `npm ci` and `npm run test` (Vitest) — high.
  📋 Add smoke HTTP integration step against a staging instance if available — medium.

Acceptance checklist (map to tasks)
- [ ] Server renders deterministic HTML given a state (Block + Template changes) — Done when templates + block render tests pass.
- [ ] `Resolver`, `Factory`, `Hydrator` exist and have unit tests asserting no session/registry usage — Done when unit tests pass.
- [ ] `UpdateController` accepts `initial_state` and returns JSON { html, state, effects } with correct error modes — Done when controller tests + smoke test pass.
- [ ] JS runtime includes `initial_state` and handles `registered`/`destroyed` effects — Done when Vitest tests pass and bundle updated.
- [ ] Docs updated and site regenerated — Done when `site/` contains new pages and `mkdocs build` succeeds.

Edge cases & notes
- Payload size growth: recommend sending diffs (partial `initial_state`) where possible.
- Form key resolution: client should check `window.FORM_KEY`, `window.formKey`, or `input[name="form_key"]` — document this.
- Backwards compatibility: add a deprecation effect when old session-backed flow is used.
- Security: do not embed server-only secrets into client state returned to the browser.

Estimates (rough, per task)
- Core PHP classes + Counter: 1–3 days
- Controller + Response model: 1–2 days
- Resolver/Factory/Hydrator tests: 1–2 days
- JS runtime change + tests: 1–2 days
- Template compiler tweaks: 1–3 days
- Docs + site build: 0.5–1 day
- CI updates + smoke test: 0.5–1 day

Quick next steps (what I can do now)
1. Create skeleton PHP classes for `Block/Component/Abstract.php`, `Counter.php`, and `Model/Component/{Resolver,Factory,Hydrator}` with minimal implementations and tests. (ask to proceed)
2. Add a Vitest JS test for runtime payload creation and `registered`/`destroyed` effect handling. (ask to proceed)
3. Draft the migration shim and small smoke test script. (ask to proceed)

If you want, I can now scaffold the PHP skeletons and the PHP tests (option 1) — pick which step to start and I'll implement it next.
