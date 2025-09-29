<?php

/**
 * Copyright (c) 2025 MACO
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/maco-studios/openwire
 */

class Maco_Openwire_UpdateController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $handler = Mage::getModel('openwire/exception_handler');
        try {
            // Read JSON body first so we can validate form_key inside the payload
            $raw = $this->getRequest()->getRawBody();
            $data = json_decode($raw, true);
            if (!is_array($data)) {
                throw new Exception('Invalid request payload');
            }

            $this->validateFormKey($data);

            $componentId = $data['id'] ?? null;
            $updates = $data['updates'] ?? [];
            $calls = $data['calls'] ?? [];

            $factory = Mage::getModel('openwire/component_factory');

            // Support anonymous server-backed components: client may provide server_class + initial_state
            if (empty($componentId) && !empty($data['server_class'])) {
                $serverClass = $data['server_class'];
                $initialState = $data['initial_state'] ?? [];
                $component = $factory->make($serverClass, (array) $initialState);
                // anonymous components are transient; we won't register them in the session by default
            } else {
                $registry = Mage::getModel('openwire/component_registry');
                $entry = $registry->load($componentId);
                if (!$entry) {
                    // Log available components for debugging
                    $allEntries = $registry->loadAll();
                    $availableIds = array_keys($allEntries);
                    Mage::log("Openwire: Component not found. ID: {$componentId}, Available IDs: " . implode(', ', $availableIds));
                    throw new Exception("Component not found: {$componentId}");
                }

                $component = $factory->make($entry['class'], $entry['state'] ?? []);

                // ensure id is set on component instance
                if (method_exists($component, 'setData')) {
                    $component->setData('id', $componentId);
                }
            }

            foreach ($updates as $prop => $val) {
                $component->update($prop, $val);
            }

            foreach ($calls as $call) {
                if (isset($call['method'])) {
                    $component->call($call['method'], $call['params'] ?? []);
                }
            }

            // persist state only for registered components
            if (!empty($componentId) && isset($registry)) {
                $registry->saveStateById($componentId, $component->getState());
            }

            $response = Mage::getModel('openwire/response');
            // allow components to request registration by returning an effect of type 'register'
            $effects = $component->getEffects();
            $registeredId = null;
            if (is_array($effects)) {
                foreach ($effects as &$fx) {
                    if (isset($fx['type']) && $fx['type'] === 'register') {
                        // register the component in the session registry
                        try {
                            $registry = Mage::getModel('openwire/component_registry');
                            $registeredId = $registry->registerComponent($component);
                            // set id on component
                            if ($registeredId && method_exists($component, 'setData')) {
                                $component->setData('id', $registeredId);
                            }
                            // replace the effect with a registered event that includes id
                            $fx['type'] = 'registered';
                            if (!isset($fx['data']) || !is_array($fx['data'])) {
                                $fx['data'] = [];
                            }
                            $fx['data']['id'] = $registeredId;
                            // persist state under the new id
                            $registry->saveStateById($registeredId, $component->getState());
                        } catch (Exception $e) {
                            // ignore registration failures for now
                        }
                    }
                }
            }

            $response->setHtml($component->render())
                ->setState($component->getState())
                ->setEffects(is_array($effects) ? $effects : [])
                ->setErrors($component->getErrors());

            $this->getResponse()->setHeader('Content-Type', 'application/json');
            $this->getResponse()->setBody(json_encode($response->toArray()));

        } catch (Exception $e) {
            $err = $handler->handle($e);
            $this->getResponse()->setHttpResponseCode(500);
            $this->getResponse()->setBody(json_encode(['error' => $err['message']]));
        }
    }

    /**
     * Validate CSRF form key. Accepts decoded JSON payload (from fetch) or
     * falls back to request param for traditional form POSTs.
     *
     * @param array|null $payload
     * @throws Exception
     */
    private function validateFormKey(array $payload = null)
    {
        $formKey = null;
        if (is_array($payload) && isset($payload['form_key'])) {
            $formKey = $payload['form_key'];
        } else {
            $formKey = $this->getRequest()->getParam('form_key');
        }

        if ($formKey !== Mage::getSingleton('core/session')->getFormKey()) {
            throw new Exception('Invalid form key');
        }
    }
}
