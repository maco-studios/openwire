<?php

/**
 * Copyright (c) 2025 MACO
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/maco-studios/openwire
 */

class Maco_Openwire_Model_Component_Resolver
{
    /**
     * Resolve a component by model alias or class name. Returns an instance or false.
     */
    public function resolve($classOrAlias)
    {
        // Case 1: Direct block alias
        if (strpos($classOrAlias, '/') !== false) {
            // For component blocks, convert model aliases to block aliases
            if (strpos($classOrAlias, 'openwire/component_') === 0) {
                $blockAlias = str_replace('openwire/component_', 'openwire/component_', $classOrAlias);
                return Mage::app()->getLayout()->createBlock($blockAlias);
            }
            return Mage::app()->getLayout()->createBlock($classOrAlias);
        }

        // Case 2: Try as component alias (for backwards compatibility)
        if (strpos($classOrAlias, 'component_') === false) {
            $alias = 'openwire/component_' . strtolower($classOrAlias);
            $instance = Mage::app()->getLayout()->createBlock($alias);
            if ($instance) {
                return $instance;
            }
        }

        // Case 3: Direct component alias
        $instance = Mage::app()->getLayout()->createBlock('openwire/component_' . $classOrAlias);
        if ($instance) {
            return $instance;
        }

        // Case 4: Try some common variations
        $variations = [
            'openwire/component_' . strtolower($classOrAlias),
            'openwire/component_' . ucfirst(strtolower($classOrAlias))
        ];

        foreach ($variations as $aliasAttempt) {
            $instance = Mage::app()->getLayout()->createBlock($aliasAttempt);
            if ($instance) {
                return $instance;
            }
        }

        return false;
    }
}
