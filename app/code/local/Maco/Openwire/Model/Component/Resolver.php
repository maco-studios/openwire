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
        // alias form: module/model
        if (str_contains($classOrAlias, '/')) {
            return Mage::getModel($classOrAlias);
        }

        // full class name mapping for this module
        if (str_starts_with($classOrAlias, 'Maco_Openwire_Model_')) {
            $modelPart = substr($classOrAlias, strlen('Maco_Openwire_Model_'));
            $alias = 'openwire/' . strtolower($modelPart);
            $instance = Mage::getModel($alias);
            if ($instance) {
                return $instance;
            }
        }

        // try openwire alias
        $instance = Mage::getModel('openwire/' . $classOrAlias);
        if ($instance) {
            return $instance;
        }

        // try resolving class name to a model alias (strip namespace parts)
        if (class_exists($classOrAlias)) {
            // attempt to find a model alias by converting class name
            $short = preg_replace('/^.*_Model_/', '', $classOrAlias);
            $aliasAttempt = 'openwire/' . strtolower($short);
            $instance = Mage::getModel($aliasAttempt);
            if ($instance) {
                return $instance;
            }

            // fallback to direct instantiation only as last resort
            return new $classOrAlias();
        }

        return false;
    }
}
