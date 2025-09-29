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
 * Component Resolver - Resolves component classes without session dependencies
 *
 * This class is responsible for converting component aliases or class names
 * into actual component instances using Magento's createBlock system.
 * It operates without relying on session or registry state.
 */
class Maco_Openwire_Model_Component_Resolver
{
    /**
     * Resolve a component by model alias or class name. Returns an instance or null.
     *
     * @param string $classOrAlias Component identifier (e.g., 'openwire/component_counter' or 'MyClass')
     * @return Maco_Openwire_Block_Component_Abstract|null
     */
    public function resolve(string $classOrAlias): ?Maco_Openwire_Block_Component_Abstract
    {
        // Case 1: Block alias format (e.g., 'openwire/component_counter')
        if (strpos($classOrAlias, '/') !== false) {
            return $this->resolveByAlias($classOrAlias);
        }

        // Case 2: Try auto-resolve common patterns
        return $this->autoResolve($classOrAlias);
    }

    /**
     * Legacy method for backwards compatibility
     * @deprecated Use resolve() with proper return type checking
     */
    public function resolveOld($classOrAlias)
    {
        $result = $this->resolve($classOrAlias);
        return $result ?: false;
    }

    /**
     * Resolve component by Magento block alias
     */
    private function resolveByAlias(string $alias): ?Maco_Openwire_Block_Component_Abstract
    {
        try {
            // Magento will automatically resolve openwire/component_counter to Maco_Openwire_Block_Component_Counter
            // based on the block class naming convention and the registered openwire block class
            $instance = Mage::app()->getLayout()->createBlock($alias);

            if ($instance instanceof Maco_Openwire_Block_Component_Abstract) {
                return $instance;
            }

            return null;
        } catch (Exception $e) {
            Mage::log("Failed to resolve component alias '{$alias}': " . $e->getMessage());
            return null;
        }
    }

    /**
     * Auto-resolve common component patterns
     */
    private function autoResolve(string $identifier): ?Maco_Openwire_Block_Component_Abstract
    {
        // Try common patterns
        $patterns = [
            "openwire/component_{$identifier}",
            "openwire/component_" . strtolower($identifier),
            "openwire/component_" . ucfirst(strtolower($identifier))
        ];

        foreach ($patterns as $pattern) {
            $resolved = $this->resolveByAlias($pattern);
            if ($resolved) {
                return $resolved;
            }
        }

        return null;
    }

    /**
     * Check if a component class/alias can be resolved
     */
    public function canResolve(string $classOrAlias): bool
    {
        return $this->resolve($classOrAlias) !== null;
    }
}
