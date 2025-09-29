// Minimal Openwire front-end integration
var OpenwireComponent = (function () {
    function OpenwireComponent(element, id) {
        this.element = element;
        this.id = id;
        this.state = {};
        this.init();
    }
    OpenwireComponent.prototype.init = function () {
        this.bindEvents();
    };
    OpenwireComponent.prototype.bindEvents = function () {
        var self = this;

        // Click handlers
        this.element.querySelectorAll('[data-openwire-click]').forEach(function (el) {
            var method = el.getAttribute('data-openwire-click');
            var paramsJson = el.getAttribute('data-openwire-params') || '[]';
            var params = [];
            try { params = JSON.parse(paramsJson); } catch (e) { params = []; }
            el.addEventListener('click', function (e) {
                e.preventDefault();
                self.call(method, params);
            });
        });

        // Model bindings (support lazy mode)
        this.element.querySelectorAll('[data-openwire-model]').forEach(function (el) {
            var prop = el.getAttribute('data-openwire-model');
            var mode = el.getAttribute('data-openwire-model-mode') || 'default';
            if (mode === 'lazy') {
                el.addEventListener('change', function (e) {
                    self.update(prop, e.target.value);
                });
                el.addEventListener('blur', function (e) {
                    self.update(prop, e.target.value);
                });
            } else {
                el.addEventListener('input', function (e) {
                    self.update(prop, e.target.value);
                });
            }
        });

        // forms
        this.element.querySelectorAll('[data-openwire-submit]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var method = form.getAttribute('data-openwire-submit');
                var paramsJson = form.getAttribute('data-openwire-params') || '[]';
                var params = [];
                try { params = JSON.parse(paramsJson); } catch (e) { params = []; }

                // collect form data into an object and send as part of the call
                var formData = new FormData(form);
                var payload = {};
                formData.forEach(function (value, key) {
                    payload[key] = value;
                });

                // include payload as first param for the method
                params.unshift(payload);
                self.call(method, params);
            });
        });
    };
    OpenwireComponent.prototype.request = function (data) {
        data.id = this.id;
        if (this.serverClass) {
            data.server_class = this.serverClass;
        }
        if (this.initialState) {
            data.initial_state = this.initialState;
        }
        // Try common Magento form key locations: FORM_KEY (uppercase), formKey, or a hidden input
        var formKey = window.FORM_KEY || window.formKey || '';
        if (!formKey) {
            var fkInput = document.querySelector('input[name="form_key"]');
            if (fkInput) formKey = fkInput.value || '';
        }
        data.form_key = formKey;
        return fetch('/openwire/update/index', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        }).then(function (r) {
            if (!r.ok) throw new Error('Request failed');
            return r.json();
        });
    };
    OpenwireComponent.prototype.update = function (property, value) {
        var _this = this;
        this.showLoading();
        return this.request({ updates: (_a = {}, _a[property] = value, _a) }).then(function (res) {
            _this.updateDOM(res.html);
            _this.state = res.state || {};
            // apply any effects
            if (res.effects && Array.isArray(res.effects)) {
                res.effects.forEach(function (fx) {
                    if (fx.type === 'notify' && fx.data && fx.data.message) {
                        console.log('Openwire:', fx.data.message);
                    }
                });
            }
            _this.hideLoading();
        }).catch(function (err) {
            console.error('Openwire update error', err);
            _this.hideLoading();
        });
        var _a;
    };
    OpenwireComponent.prototype.call = function (method, params) {
        var _this = this;
        this.showLoading();
        return this.request({ calls: [{ method: method, params: params || [] }] }).then(function (res) {
            _this.updateDOM(res.html);
            _this.state = res.state || {};
            if (res.effects && Array.isArray(res.effects)) {
                _this.handleEffects(res.effects);
            }
            _this.hideLoading();
        }).catch(function (err) {
            console.error('Openwire call error', err);
            _this.hideLoading();
        });
    };
    OpenwireComponent.prototype.updateDOM = function (html) {
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');
        var newEl = doc.body.firstChild;
        if (!newEl) return;
        // Preserve any elements marked with data-openwire-ignore inside the current element.
        var ignores = Array.from(this.element.querySelectorAll('[data-openwire-ignore]'));
        var placeholders = [];
        ignores.forEach(function (el, idx) {
            var ph = document.createElement('div');
            ph.setAttribute('data-openwire-ignore-placeholder', idx);
            el.parentNode.replaceChild(ph, el);
            placeholders.push({ idx: idx, node: el });
        });

        // Replace content
        this.element.innerHTML = newEl.innerHTML;

        // Reattach ignored nodes back into placeholders
        placeholders.forEach(function (p) {
            var placeholder = this.element.querySelector('[data-openwire-ignore-placeholder="' + p.idx + '"]');
            if (placeholder) {
                placeholder.parentNode.replaceChild(p.node, placeholder);
            }
        }, this);

        // Update data-bound attributes
        this.updateDataBinding();

        this.bindEvents();
    };

    /**
     * Update data-bound attributes based on component state
     */
    OpenwireComponent.prototype.updateDataBinding = function () {
        var self = this;
        // Find all elements with data-openwire-bind attributes
        this.element.querySelectorAll('[data-openwire-bind]').forEach(function (el) {
            var bindAttr = el.getAttribute('data-openwire-bind');
            if (bindAttr && self.state[bindAttr] !== undefined) {
                // Update the element's value or text content based on its type
                if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT') {
                    el.value = self.state[bindAttr];
                } else {
                    el.textContent = self.state[bindAttr];
                }
            }
        });
    };

    /**
     * Centralized effect handling for responses.
     * Known effect types:
     * - notify: { data: { message } }
     * - redirect: { data: { url, target } }
     */
    OpenwireComponent.prototype.handleEffects = function (effects) {
        if (!effects || !Array.isArray(effects)) return;
        effects.forEach(function (fx) {
            try {
                switch (fx.type) {
                    case 'notify':
                        if (fx.data && fx.data.message) {
                            // Minimal: console and optional simple toast integration later
                            console.log('Openwire:', fx.data.message);
                        }
                        break;
                    case 'registered':
                        // server replied with a registration id for an anonymous component
                        if (fx.data && fx.data.id) {
                            try {
                                var id = fx.data.id;
                                // set DOM attribute and instance id so subsequent calls use the registry id
                                if (this && this.element) {
                                    this.element.setAttribute('data-openwire-id', id);
                                    this.id = id;
                                    window.openwireInstances = window.openwireInstances || {};
                                    window.openwireInstances[id] = this;
                                }
                            } catch (e) {
                                console.error('Openwire: failed to apply registered id', fx, e);
                            }
                        }
                        break;
                    case 'redirect':
                        if (fx.data && fx.data.url) {
                            var target = fx.data.target || '_self';
                            if (target === '_blank') {
                                window.open(fx.data.url, '_blank');
                            } else {
                                window.location.href = fx.data.url;
                            }
                        }
                        break;
                    // future effect types (eval, toast, focus, etc.) go here
                    default:
                        console.warn('Openwire: unknown effect', fx);
                }
            } catch (e) {
                console.error('Openwire: error processing effect', fx, e);
            }
        });
    };
    OpenwireComponent.prototype.showLoading = function () {
        this.element.classList.add('openwire-loading');
    };
    OpenwireComponent.prototype.hideLoading = function () {
        this.element.classList.remove('openwire-loading');
    };
    return OpenwireComponent;
}());

// helper to call from inline attributes
window.OpenwireComponent = window.OpenwireComponent || OpenwireComponent;

/**
 * Auto-initialize all components found in the DOM with a `data-openwire-id`.
 * Also expose a programmatic initializer for dynamic content.
 */
; (function () {
    function initNode(el) {
        if (!el) return;
        var id = el.getAttribute('data-openwire-id');
        // generate a temporary id for anonymous components if none provided
        if (!id) {
            id = 'anon_' + Date.now() + '_' + Math.floor(Math.random() * 10000);
            el.setAttribute('data-openwire-id', id);
        }
        window.openwireInstances = window.openwireInstances || {};
        if (!window.openwireInstances[id]) {
            try {
                var inst = new OpenwireComponent(el, id);
                // detect server-backed anonymous component class/state
                var cls = el.getAttribute('data-openwire-class');
                if (cls) {
                    inst.serverClass = cls;
                    var stateAttr = el.getAttribute('data-openwire-state');
                    try {
                        inst.initialState = stateAttr ? JSON.parse(stateAttr) : {};
                    } catch (e) {
                        inst.initialState = {};
                    }
                }
                window.openwireInstances[id] = inst;
            } catch (e) {
                console.error('Openwire: failed to init component', id, e);
            }
        }
    }

    function initAll(root) {
        root = root || document;
        var nodes = root.querySelectorAll('[data-openwire-id]');
        Array.prototype.forEach.call(nodes, function (el) {
            initNode(el);
        });
    }

    // expose helper
    window.Openwire = window.Openwire || {};
    window.Openwire.init = initAll;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initAll(document);
        });
    } else {
        // already ready
        initAll(document);
    }
})();
