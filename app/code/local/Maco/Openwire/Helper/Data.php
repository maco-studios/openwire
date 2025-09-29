<?php

/**
 * Copyright (c) 2025 MACO
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/maco-studios/openwire
 */

class Maco_Openwire_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function renderComponent($component)
    {
        return $component->render();
    }

    public function mountComponent($class, $params = [])
    {
        $factory = Mage::getModel('openwire/component_factory');
        $component = $factory->make($class, []);
        if (method_exists($component, 'mount')) {
            $component->mount($params);
        }

        // register serializable state in registry
        $registry = Mage::getModel('openwire/component_registry');
        $id = $registry->registerComponent($component);

        // set the id on the instance so template rendering uses it
        if (method_exists($component, 'setData')) {
            $component->setData('id', $id);
        }

        return $component;
    }

    public function wireDirective($directive, $value)
    {
        // simple helper proxy — extend as needed
        return sprintf('%s="%s"', $directive, $value);
    }
}
