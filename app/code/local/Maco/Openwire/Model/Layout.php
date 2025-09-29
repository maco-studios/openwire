<?php

declare(strict_types=1);

/**
 * Copyright (c) 2025 MACO
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/maco-studios/openwire
 */

/**
 * Extended Layout model with OpenWire component support
 *
 * This class extends Magento's core layout model to add OpenWire component
 * functionality while maintaining full backward compatibility.
 */
class Maco_Openwire_Model_Layout extends Mage_Core_Model_Layout
{
    /**
     * @var array Registered OpenWire components
     */
    protected $_openwireComponents = [];

    /**
     * @var Maco_Openwire_Model_Component_Factory
     */
    protected $_componentFactory;

    /**
     * Initialize OpenWire extensions
     */
    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->_componentFactory = Mage::getModel('openwire/component_factory');
    }

    /**
     * Add OpenWire components to the layout
     *
     * @param array $components Array of component configurations
     * @return $this
     *
     * Example usage:
     * $layout->addComponents([
     *     'counter1' => [
     *         'type' => 'openwire/component_counter',
     *         'name' => 'main_counter',
     *         'state' => ['count' => 0, 'name' => 'Main Counter']
     *     ],
     *     'counter2' => [
     *         'type' => 'openwire/component_counter',
     *         'name' => 'sidebar_counter',
     *         'state' => ['count' => 5, 'name' => 'Sidebar Counter']
     *     ]
     * ]);
     */
    public function addComponents(array $components): self
    {
        foreach ($components as $alias => $config) {
            $this->addComponent($alias, $config);
        }

        return $this;
    }

    /**
     * Add a single OpenWire component to the layout
     *
     * @param string $alias Component alias for referencing
     * @param array $config Component configuration
     * @return $this
     */
    public function addComponent(string $alias, array $config): self
    {
        try {
            $componentType = $config['type'] ?? null;
            $componentName = $config['name'] ?? $alias;
            $initialState = $config['state'] ?? [];

            if (!$componentType) {
                throw new Exception("Component type is required for alias '{$alias}'");
            }

            // Create the component using our factory
            $component = $this->_componentFactory->create($componentType, $initialState);

            // Set component name/alias
            $component->setData('name', $componentName);
            $component->setData('alias', $alias);

            // Store component reference
            $this->_openwireComponents[$alias] = $component;

            // Also add as a regular block for template access
            $this->setBlock($alias, $component);

        } catch (Exception $e) {
            Mage::log("Failed to add OpenWire component '{$alias}': " . $e->getMessage());
        }

        return $this;
    }

    /**
     * Get an OpenWire component by alias
     *
     * @param string $alias Component alias
     * @return Maco_Openwire_Block_Component_Abstract|null
     */
    public function getComponent(string $alias): ?Maco_Openwire_Block_Component_Abstract
    {
        return $this->_openwireComponents[$alias] ?? null;
    }

    /**
     * Get all registered OpenWire components
     *
     * @return array
     */
    public function getComponents(): array
    {
        return $this->_openwireComponents;
    }

    /**
     * Check if a component is registered
     *
     * @param string $alias Component alias
     * @return bool
     */
    public function hasComponent(string $alias): bool
    {
        return isset($this->_openwireComponents[$alias]);
    }

    /**
     * Remove a component from the layout
     *
     * @param string $alias Component alias
     * @return $this
     */
    public function removeComponent(string $alias): self
    {
        unset($this->_openwireComponents[$alias]);
        $this->unsetBlock($alias);

        return $this;
    }

    /**
     * Render all components and return their HTML
     *
     * @return array Array of alias => html mappings
     */
    public function renderComponents(): array
    {
        $renderedComponents = [];

        foreach ($this->_openwireComponents as $alias => $component) {
            try {
                $renderedComponents[$alias] = $component->render();
            } catch (Exception $e) {
                Mage::log("Failed to render component '{$alias}': " . $e->getMessage());
                $renderedComponents[$alias] = "<!-- Component '{$alias}' failed to render -->";
            }
        }

        return $renderedComponents;
    }

    /**
     * Enhanced createBlock method with OpenWire component support
     *
     * @param string $type Block type/alias
     * @param string $name Block name
     * @param array $attributes Block attributes
     * @return Mage_Core_Block_Abstract|Maco_Openwire_Block_Component_Abstract
     */
    public function createBlock($type, $name = '', $attributes = [])
    {
        // Check if it's an OpenWire component
        if (strpos($type, 'openwire/component_') === 0) {
            try {
                $component = $this->_componentFactory->create($type, $attributes);

                if ($name) {
                    $component->setData('name', $name);
                    $this->setBlock($name, $component);
                }

                return $component;
            } catch (Exception $e) {
                Mage::log("Failed to create OpenWire component '{$type}': " . $e->getMessage());
                // Fall back to parent implementation
            }
        }

        // Use parent implementation for regular blocks
        return parent::createBlock($type, $name, $attributes);
    }

    /**
     * Get component factory instance
     *
     * @return Maco_Openwire_Model_Component_Factory
     */
    public function getComponentFactory(): Maco_Openwire_Model_Component_Factory
    {
        return $this->_componentFactory;
    }
}
