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
 * Debug helper for Openwire components
 */
class Maco_Openwire_Helper_Debug extends Mage_Core_Helper_Abstract
{
    /**
     * Get registry debug information
     *
     * @return array
     */
    public function getRegistryDebugInfo()
    {
        try {
            $registry = Mage::getModel('openwire/component_registry');
            $allEntries = $registry->loadAll();

            $debugInfo = [
                'total_components' => count($allEntries),
                'components' => []
            ];

            foreach ($allEntries as $id => $entry) {
                $debugInfo['components'][] = [
                    'id' => $id,
                    'class' => $entry['class'] ?? 'unknown',
                    'created_at' => $entry['created_at'] ?? 'unknown',
                    'updated_at' => $entry['updated_at'] ?? 'not_updated',
                    'state_keys' => array_keys($entry['state'] ?? [])
                ];
            }

            return $debugInfo;

        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }

    /**
     * Log registry state
     */
    public function logRegistryState()
    {
        $debugInfo = $this->getRegistryDebugInfo();
        Mage::log('Openwire Registry Debug: ' . json_encode($debugInfo, JSON_PRETTY_PRINT));
    }

    /**
     * Get component by ID for debugging
     *
     * @param string $componentId
     * @return array
     */
    public function getComponentDebugInfo($componentId)
    {
        try {
            $registry = Mage::getModel('openwire/component_registry');
            $entry = $registry->load($componentId);

            if (!$entry) {
                return [
                    'found' => false,
                    'error' => 'Component not found in registry',
                    'available_ids' => array_keys($registry->loadAll())
                ];
            }

            return [
                'found' => true,
                'id' => $componentId,
                'class' => $entry['class'] ?? 'unknown',
                'state' => $entry['state'] ?? [],
                'created_at' => $entry['created_at'] ?? 'unknown',
                'updated_at' => $entry['updated_at'] ?? 'not_updated'
            ];

        } catch (Exception $e) {
            return [
                'found' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Test component registration
     *
     * @param string $componentAlias
     * @param array $params
     * @return array
     */
    public function testComponentRegistration($componentAlias, $params = [])
    {
        try {
            // Create component
            $component = Mage::getModel($componentAlias);
            if (!$component) {
                return ['error' => "Component not found: {$componentAlias}"];
            }

            // Mount parameters
            if (method_exists($component, 'mount')) {
                $component->mount($params);
            }

            // Register component
            $registry = Mage::getModel('openwire/component_registry');
            $id = $registry->registerComponent($component);

            // Verify registration
            $entry = $registry->load($id);

            return [
                'success' => true,
                'component_id' => $id,
                'component_class' => $component::class,
                'registered' => !empty($entry),
                'entry' => $entry
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }
}
