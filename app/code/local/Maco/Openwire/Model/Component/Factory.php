<?php

/**
 * Copyright (c) 2025 MACO
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/maco-studios/openwire
 */

class Maco_Openwire_Model_Component_Factory
{
    public function make($classOrAlias, array $state = [])
    {
        // Accept model alias 'openwire/component_counter' or full class name
        $resolver = Mage::getModel('openwire/component_resolver');
        $hydrator = Mage::getModel('openwire/component_hydrator');

        $instance = $resolver->resolve($classOrAlias);
        if (!$instance) {
            throw new Exception('Unable to instantiate component: ' . $classOrAlias);
        }

        return $hydrator->hydrate($instance, $state);
    }
}
