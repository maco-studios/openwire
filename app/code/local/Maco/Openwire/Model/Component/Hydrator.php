<?php

/**
 * Copyright (c) 2025 MACO
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/maco-studios/openwire
 */

class Maco_Openwire_Model_Component_Hydrator
{
    public function hydrate($instance, array $state = [])
    {
        if (!$instance) {
            return null;
        }

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
}
