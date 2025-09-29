<?php

/**
 * Copyright (c) 2025 MACO
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/maco-studios/openwire
 */

/**
 * Component helper for nested components and block integration
 */
class Maco_Openwire_Helper_Component extends Mage_Core_Helper_Abstract
{
    /**
     * Create and render a nested component
     *
     * @param string $componentAlias
     * @param array $params
     * @return string
     */
    public function renderNestedComponent($componentAlias, $params = [])
    {
        try {
            $component = Mage::getModel($componentAlias);
            if (!$component) {
                return $this->getErrorHtml("Component not found: {$componentAlias}");
            }

            if (method_exists($component, 'mount')) {
                $component->mount($params);
            }

            // Register the component in the registry so it can be found by AJAX requests
            if ($component instanceof Maco_Openwire_Model_Component) {
                $this->registerComponent($component);
            }

            if (method_exists($component, 'render')) {
                return $component->render();
            }

            return $this->getErrorHtml("Component does not support rendering: {$componentAlias}");

        } catch (Exception $e) {
            Mage::logException($e);
            return $this->getErrorHtml("Error rendering component: " . $e->getMessage());
        }
    }

    /**
     * Create a component instance without rendering
     *
     * @param string $componentAlias
     * @param array $params
     * @return Maco_Openwire_Model_Component|null
     */
    public function createComponent($componentAlias, $params = [])
    {
        try {
            $component = Mage::getModel($componentAlias);
            if (!$component || !($component instanceof Maco_Openwire_Model_Component)) {
                return null;
            }

            if (method_exists($component, 'mount')) {
                $component->mount($params);
            }

            return $component;

        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
    }

    /**
     * Render multiple nested components
     *
     * @param array $components Array of ['alias' => 'openwire/component_name', 'params' => [...]]
     * @return string
     */
    public function renderMultipleComponents($components)
    {
        $html = '';

        foreach ($components as $config) {
            if (isset($config['alias'])) {
                $params = $config['params'] ?? [];
                $html .= $this->renderNestedComponent($config['alias'], $params);
            }
        }

        return $html;
    }

    /**
     * Get component data for JavaScript
     *
     * @param Maco_Openwire_Model_Component $component
     * @return array
     */
    public function getComponentData($component)
    {
        if (!$component) {
            return [];
        }

        return [
            'id' => $component->getId(),
            'class' => $component::class,
            'state' => $component->getState(),
            'template' => method_exists($component, 'getTemplate') ? $component->getTemplate() : null
        ];
    }

    /**
     * Create a component with Magento block integration
     *
     * @param string $componentAlias
     * @param array $params
     * @param string $blockTemplate
     * @return string
     */
    public function renderComponentWithBlock($componentAlias, $params = [], $blockTemplate = null)
    {
        try {
            // Create the component
            $component = $this->createComponent($componentAlias, $params);
            if (!$component) {
                return $this->getErrorHtml("Failed to create component: {$componentAlias}");
            }

            // Create a Magento block
            $block = Mage::app()->getLayout()->createBlock('core/template');
            if ($blockTemplate) {
                $block->setTemplate($blockTemplate);
            }

            // Set component data on the block
            $block->setData('openwire_component', $component);
            $block->setData('component_data', $this->getComponentData($component));

            // Render the block
            return $block->toHtml();

        } catch (Exception $e) {
            Mage::logException($e);
            return $this->getErrorHtml("Error rendering component with block: " . $e->getMessage());
        }
    }

    /**
     * Get available component aliases
     *
     * @return array
     */
    public function getAvailableComponents()
    {
        return [
            'openwire/component_counter' => 'Counter Component',
            'openwire/component_userProfile' => 'User Profile Component',
            'openwire/component_userList' => 'User List Component',
            'openwire/component_productCard' => 'Product Card Component',
            'openwire/component_todoList' => 'Todo List Component',
            'openwire/component_example' => 'Example Component',
        ];
    }

    /**
     * Validate component parameters
     *
     * @param string $componentAlias
     * @param array $params
     * @return array Array of validation errors
     */
    public function validateComponentParams($componentAlias, $params)
    {
        $errors = [];

        // Basic validation
        if (empty($componentAlias)) {
            $errors[] = 'Component alias is required';
        }

        // Check if component exists
        $component = Mage::getModel($componentAlias);
        if (!$component) {
            $errors[] = "Component not found: {$componentAlias}";
            return $errors;
        }

        // Component-specific validation
        switch ($componentAlias) {
            case 'openwire/component_userProfile':
                if (isset($params['userId']) && !is_numeric($params['userId'])) {
                    $errors[] = 'User ID must be numeric';
                }
                if (isset($params['userEmail']) && !filter_var($params['userEmail'], FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Invalid email format';
                }
                break;

            case 'openwire/component_productCard':
                if (isset($params['productId']) && !is_numeric($params['productId'])) {
                    $errors[] = 'Product ID must be numeric';
                }
                break;

            case 'openwire/component_counter':
                if (isset($params['initialCount']) && !is_numeric($params['initialCount'])) {
                    $errors[] = 'Initial count must be numeric';
                }
                break;
        }

        return $errors;
    }

    /**
     * Register a component in the registry
     *
     * @param Maco_Openwire_Model_Component $component
     * @return string Component ID
     */
    protected function registerComponent($component)
    {
        try {
            $registry = Mage::getModel('openwire/component_registry');
            return $registry->registerComponent($component);
        } catch (Exception $e) {
            Mage::logException($e);
            return $component->getId();
        }
    }

    /**
     * Get error HTML
     *
     * @param string $message
     * @return string
     */
    protected function getErrorHtml($message)
    {
        return '<div class="openwire-error" style="color: red; padding: 10px; border: 1px solid red; background: #ffe6e6; border-radius: 4px;">' .
               htmlspecialchars($message) .
               '</div>';
    }

    /**
     * Get component documentation
     *
     * @param string $componentAlias
     * @return array
     */
    public function getComponentDocumentation($componentAlias)
    {
        $docs = [
            'openwire/component_counter' => [
                'description' => 'A simple counter component with increment functionality',
                'params' => [
                    'initialCount' => 'Starting count value (default: 0)',
                    'name' => 'Display name (default: Guest)'
                ],
                'methods' => [
                    'increment()' => 'Increments the counter by 1'
                ]
            ],
            'openwire/component_userProfile' => [
                'description' => 'User profile component with online status and name editing',
                'params' => [
                    'userId' => 'User ID (default: 0)',
                    'userName' => 'User display name (default: Guest)',
                    'userEmail' => 'User email address',
                    'avatar' => 'Avatar image URL',
                    'isOnline' => 'Online status (default: false)'
                ],
                'methods' => [
                    'toggleOnlineStatus()' => 'Toggles user online/offline status',
                    'updateUserName($name)' => 'Updates the user display name'
                ]
            ],
            'openwire/component_userList' => [
                'description' => 'User list component with filtering and user management',
                'params' => [
                    'users' => 'Array of user data',
                    'selectedUserId' => 'Currently selected user ID',
                    'showOnlineOnly' => 'Filter to show only online users'
                ],
                'methods' => [
                    'addUser($name, $email)' => 'Adds a new user to the list',
                    'removeUser($userId)' => 'Removes a user from the list',
                    'selectUser($userId)' => 'Selects a user',
                    'toggleOnlineFilter()' => 'Toggles online-only filter'
                ]
            ],
            'openwire/component_productCard' => [
                'description' => 'Product card component with Magento integration',
                'params' => [
                    'productId' => 'Magento product ID',
                    'showPrice' => 'Show/hide price (default: true)',
                    'showAddToCart' => 'Show/hide add to cart button (default: true)',
                    'quantity' => 'Default quantity (default: 1)',
                    'isWishlisted' => 'Wishlist status (default: false)'
                ],
                'methods' => [
                    'togglePriceVisibility()' => 'Toggles price display',
                    'toggleAddToCart()' => 'Toggles add to cart button',
                    'updateQuantity($qty)' => 'Updates product quantity',
                    'addToWishlist()' => 'Adds product to wishlist',
                    'removeFromWishlist()' => 'Removes product from wishlist'
                ]
            ]
        ];

        return $docs[$componentAlias] ?? [];
    }
}
