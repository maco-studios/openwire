<?php
class Maco_Openwire_TestController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        try {
            // Try to instantiate the counter component via factory if available
            $counter = null;
            if (class_exists('Maco_Openwire_Model_Component_Factory')) {
                $factory = Mage::getModel('openwire/component_factory');
                if ($factory) {
                    $counter = $factory->make('openwire/component_counter', ['initialCount' => 3, 'name' => 'Tester']);
                }
            }

            if (!$counter) {
                // Fallback to direct model instantiation
                if (class_exists('Maco_Openwire_Model_Component_Counter')) {
                    $counter = new Maco_Openwire_Model_Component_Counter(['initialCount' => 3, 'name' => 'Tester']);
                    if (method_exists($counter, 'mount')) {
                        $counter->mount(['initialCount' => 3, 'name' => 'Tester']);
                    }
                }
            }

            if (!$counter) {
                throw new Exception('Counter component not available');
            }

            // Register component in the registry so update requests can find it by id
            try {
                $registry = Mage::getModel('openwire/component_registry');
                if ($registry) {
                    $cid = $registry->registerComponent($counter);
                    if ($cid && method_exists($counter, 'setData')) {
                        $counter->setData('id', $cid);
                    }
                }
            } catch (Exception $e) {
                // non-fatal: continue without registry registration
            }

            // Render component HTML and send a small page for manual testing
            $html = $counter->render();
            $formKey = '';
            try {
                $formKey = Mage::getSingleton('core/session')->getFormKey();
            } catch (Exception $e) {
                $formKey = '';
            }

            $full = '<!doctype html><html><head><meta charset="utf-8"><title>Openwire Counter Test</title>' .
                '<script>window.FORM_KEY = "' . htmlspecialchars($formKey, ENT_QUOTES, 'UTF-8') . '"; window.formKey = window.FORM_KEY;</script>' .
                '<script src="/js/openwire/dist/openwire.js"></script>' .
                '</head><body>' . $html . '</body></html>';

            $this->getResponse()->setHeader('Content-Type', 'text/html');
            $this->getResponse()->setBody($full);

        } catch (Exception $e) {
            $this->getResponse()->setBody('<pre>Error: ' . htmlspecialchars($e->getMessage()) . '</pre>');
        }
    }

    public function statefulAction()
    {
        try {
            $todo = null;
            $store = Mage::helper('openwire/sessionStore');

            // Try to find an existing saved TodoList in the session-backed registry
            $existingId = null;
            if ($store) {
                $all = $store->loadAll();
                foreach ($all as $id => $entry) {
                    if (isset($entry['class']) && $entry['class'] === 'Maco_Openwire_Model_Component_TodoList') {
                        $existingId = $id;
                        break;
                    }
                }
            }

            // If we found an existing entry, hydrate using the saved state
            if ($existingId) {
                $entry = $store->load($existingId);
                $factory = Mage::getModel('openwire/component_factory');
                if ($factory) {
                    try {
                        $todo = $factory->make($entry['class'], isset($entry['state']) ? $entry['state'] : []);
                    } catch (Exception $e) {
                        $todo = null;
                    }
                }
                if (!$todo) {
                    // fallback to direct instantiation and set state
                    if (class_exists('Maco_Openwire_Model_Component_TodoList')) {
                        $todo = new Maco_Openwire_Model_Component_TodoList();
                        if (method_exists($todo, 'setState') && isset($entry['state'])) {
                            $todo->setState($entry['state']);
                        }
                    }
                }
                if ($todo && method_exists($todo, 'setData')) {
                    $todo->setData('id', $existingId);
                }
            }

            // Otherwise create a fresh one
            if (!$todo) {
                if (class_exists('Maco_Openwire_Model_Component_Factory')) {
                    $factory = Mage::getModel('openwire/component_factory');
                    if ($factory) {
                        try {
                            $todo = $factory->make('Maco_Openwire_Model_Component_TodoList', ['items' => []]);
                        } catch (Exception $e) {
                            $todo = null;
                        }
                    }
                }

                if (!$todo) {
                    if (class_exists('Maco_Openwire_Model_Component_TodoList')) {
                        $todo = new Maco_Openwire_Model_Component_TodoList(['items' => []]);
                        if (method_exists($todo, 'mount')) {
                            $todo->mount(['items' => []]);
                        }
                    }
                }
            }

            if (!$todo) {
                throw new Exception('TodoList component not available');
            }

            try {
                $registry = Mage::getModel('openwire/component_registry');
                if ($registry) {
                    $cid = $registry->registerComponent($todo);
                    if ($cid && method_exists($todo, 'setData')) {
                        $todo->setData('id', $cid);
                    }
                }
            } catch (Exception $e) {
            }

            $html = $todo->render();
            $formKey = '';
            try {
                $formKey = Mage::getSingleton('core/session')->getFormKey();
            } catch (Exception $e) {
                $formKey = '';
            }

            $full = '<!doctype html><html><head><meta charset="utf-8"><title>Openwire Stateful Test</title>' .
                '<script>window.FORM_KEY = "' . htmlspecialchars($formKey, ENT_QUOTES, 'UTF-8') . '"; window.formKey = window.FORM_KEY;</script>' .
                '<script src="/js/openwire/dist/openwire.js"></script>' .
                '</head><body>' . $html . '</body></html>';

            $this->getResponse()->setHeader('Content-Type', 'text/html');
            $this->getResponse()->setBody($full);

        } catch (Exception $e) {
            $this->getResponse()->setBody('<pre>Error: ' . htmlspecialchars($e->getMessage()) . '</pre>');
        }
    }
}
