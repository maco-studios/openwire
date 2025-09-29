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
 * Component Hydrator - Hydrates component state from request payload
 *
 * This class is responsible for setting component state from initial_state
 * and updates passed in the request. It operates without session dependencies.
 */
class Maco_Openwire_Model_Component_Hydrator
{
    /**
     * Hydrate a component with state data
     *
     * @param Maco_Openwire_Block_Component_Abstract $component
     * @param array $state State data to apply
     * @return Maco_Openwire_Block_Component_Abstract The hydrated component
     */
    public function hydrate(Maco_Openwire_Block_Component_Abstract $component, array $state): Maco_Openwire_Block_Component_Abstract
    {
        // Apply each state property to the component
        foreach ($state as $key => $value) {
            $this->applyStateProperty($component, $key, $value);
        }

        // Call mount if available and not already called
        if (method_exists($component, 'mount') && !$component->getData('_mounted')) {
            $component->mount($state);
            $component->setData('_mounted', true);
        }

        return $component;
    }

    /**
     * Legacy hydrate method for backwards compatibility
     * @deprecated Use hydrate() with proper type hints
     */
    public function hydrateOld($instance, array $state = [])
    {
        if (!$instance) {
            return null;
        }

        if ($instance instanceof Maco_Openwire_Block_Component_Abstract) {
            return $this->hydrate($instance, $state);
        }

        // Legacy fallback
        if (method_exists($instance, 'setState')) {
            $instance->setState($state);
            return $instance;
        }

        if (method_exists($instance, 'setData')) {
            foreach ($state as $k => $v) {
                $instance->setData($k, $v);
            }
            return $instance;
        }

        // last resort: try to set public properties
        foreach ($state as $k => $v) {
            if (property_exists($instance, $k)) {
                $instance->{$k} = $v;
            }
        }

        return $instance;
    }

    /**
     * Apply updates to a component (for subsequent requests)
     *
     * @param Maco_Openwire_Block_Component_Abstract $component
     * @param array $updates Updates to apply
     * @return Maco_Openwire_Block_Component_Abstract
     */
    public function applyUpdates(Maco_Openwire_Block_Component_Abstract $component, array $updates): Maco_Openwire_Block_Component_Abstract
    {
        foreach ($updates as $key => $value) {
            $this->applyStateProperty($component, $key, $value);
        }

        return $component;
    }

    /**
     * Apply a single state property to a component
     *
     * @param Maco_Openwire_Block_Component_Abstract $component
     * @param string $key Property name
     * @param mixed $value Property value
     */
    private function applyStateProperty(Maco_Openwire_Block_Component_Abstract $component, string $key, $value): void
    {
        // Skip internal/private properties
        if (str_starts_with($key, '_')) {
            return;
        }

        // Use setter method if available
        $setter = 'set' . $this->camelize($key);
        if (method_exists($component, $setter)) {
            $component->$setter($value);
            return;
        }

        // Fall back to setData
        $component->setData($key, $value);
    }

    /**
     * Extract current state from a component
     *
     * @param Maco_Openwire_Block_Component_Abstract $component
     * @return array Current component state
     */
    public function extractState(Maco_Openwire_Block_Component_Abstract $component): array
    {
        $data = $component->getData();

        // Filter out internal/private properties
        $filteredData = [];
        foreach ($data as $key => $value) {
            if (!str_starts_with($key, '_') && $this->isSerializable($value)) {
                $filteredData[$key] = $value;
            }
        }

        return $filteredData;
    }

    /**
     * Check if a value can be safely serialized
     */
    private function isSerializable($value): bool
    {
        if (is_object($value)) {
            // Only allow specific object types
            return $value instanceof JsonSerializable ||
                   $value instanceof stdClass ||
                   method_exists($value, '__toString');
        }

        if (is_resource($value)) {
            return false;
        }

        if (is_array($value)) {
            // Check array contents recursively
            foreach ($value as $item) {
                if (!$this->isSerializable($item)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Convert snake_case to CamelCase
     */
    private function camelize(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }
}
