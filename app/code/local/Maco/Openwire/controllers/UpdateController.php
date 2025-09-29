<?php

declare(strict_types=1);

/**
 * Stateless OpenWire Update Controller
 * Handles component updates without session dependencies
 *
 * Accepts JSON payloads with format:
 * {
 *   "id": "component_id",
 *   "calls": [{"method": "methodName", "params": []}],
 *   "updates": {"prop": "value"},
 *   "form_key": "csrf_token",
 *   "server_class": "openwire/component_counter", // optional
 *   "initial_state": {"count": 0} // optional
 * }
 *
 * Returns JSON response:
 * {
 *   "success": true,
 *   "html": "<div>...</div>",
 *   "state": {"count": 1},
 *   "effects": [{"type": "notify", "data": {"message": "Updated!"}}]
 * }
 */
class Maco_Openwire_UpdateController extends Mage_Core_Controller_Front_Action
{
    private readonly Maco_Openwire_Model_Component_Factory $factory;
    private readonly Maco_Openwire_Model_Component_Hydrator $hydrator;

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = [])
    {
        parent::__construct($request, $response, $invokeArgs);
        $this->factory = Mage::getModel('openwire/component_factory');
        $this->hydrator = Mage::getModel('openwire/component_hydrator');
    }

    public function indexAction()
    {
        try {
            // Parse and validate request
            $payload = $this->parseRequest();
            $this->validateFormKey($payload);

            // Create response builder
            $response = Mage::getModel('openwire/response');

            // Determine component class
            $componentClass = $this->determineComponentClass($payload);
            if (!$componentClass) {
                throw new Exception('Component class must be specified for stateless operation');
            }

            // Merge initial state with updates
            $initialState = $payload['initial_state'] ?? [];
            $updates = $payload['updates'] ?? [];
            $fullState = array_merge($initialState, $updates);

            // Create and hydrate component
            $component = $this->factory->create($componentClass, $fullState);

            // Process method calls
            $this->processCalls($component, $payload['calls'] ?? []);

            // Render component
            $html = $this->renderComponent($component);
            $state = $this->hydrator->extractState($component);

            // Build response
            $response->setHtml($html);
            $response->setState($state);

            // Add component effects if available
            if (method_exists($component, 'getEffects')) {
                $effects = $component->getEffects();
                if (is_array($effects)) {
                    $response->addEffects($effects);
                }
            }

            // Add registered effect for new components
            if (!isset($payload['id']) || empty($payload['id'])) {
                $response->addRegistered($component->getId());
            }

            $this->sendJsonResponse($response->toArray());

        } catch (Exception $e) {
            Mage::log("OpenWire Error: " . $e->getMessage());
            $this->sendErrorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Parse and validate the JSON request payload
     */
    private function parseRequest(): array
    {
        $raw = $this->getRequest()->getRawBody();
        if (empty($raw)) {
            throw new Exception('Empty request body');
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            throw new Exception('Invalid JSON payload');
        }

        return $data;
    }

    /**
     * Validate the form key for CSRF protection
     */
    private function validateFormKey(array $payload): void
    {
        if (!isset($payload['form_key'])) {
            throw new Exception('Form key required');
        }

        $sessionFormKey = Mage::getSingleton('core/session')->getFormKey();
        if ($payload['form_key'] !== $sessionFormKey) {
            throw new Exception('Invalid form key');
        }
    }

    /**
     * Determine the component class from the payload
     */
    private function determineComponentClass(array $payload): ?string
    {
        // Check for explicit server_class
        if (isset($payload['server_class']) && !empty($payload['server_class'])) {
            return $payload['server_class'];
        }

        // Check for legacy component field
        if (isset($payload['component']) && !empty($payload['component'])) {
            return $payload['component'];
        }

        // For existing components, we'd need to resolve from ID
        // but in stateless mode, we require explicit class specification
        return null;
    }

    /**
     * Process method calls on the component
     */
    private function processCalls(Maco_Openwire_Block_Component_Abstract $component, array $calls): void
    {
        foreach ($calls as $call) {
            if (!isset($call['method'])) {
                continue;
            }

            $method = $call['method'];
            $params = $call['params'] ?? [];

            if (!method_exists($component, $method)) {
                throw new Exception("Method '{$method}' not found on component");
            }

            // Call the method with parameters
            try {
                call_user_func_array([$component, $method], $params);
            } catch (Exception $e) {
                throw new Exception("Error calling method '{$method}': " . $e->getMessage());
            }
        }
    }

    /**
     * Render the component to HTML
     */
    private function renderComponent(Maco_Openwire_Block_Component_Abstract $component): string
    {
        try {
            if (method_exists($component, 'render')) {
                return $component->render();
            }

            // Fallback to toHtml for standard Magento blocks
            return $component->toHtml();
        } catch (Exception $e) {
            throw new Exception("Error rendering component: " . $e->getMessage());
        }
    }

    /**
     * Send JSON response
     */
    private function sendJsonResponse(array $data): void
    {
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->setBody(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Send error response
     */
    private function sendErrorResponse(string $message, int $code = 400): void
    {
        $response = Maco_Openwire_Model_Response::error($message);

        $this->getResponse()
            ->setHttpResponseCode($code)
            ->setHeader('Content-Type', 'application/json')
            ->setBody($response->toJson());
    }

    /**
     * Legacy method for backwards compatibility
     * @deprecated Use the new stateless flow
     */
    protected function _getComponentManager()
    {
        // Return a compatibility wrapper if needed
        return Mage::getModel('openwire/component_manager');
    }
}
