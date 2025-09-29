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
 * Loading directive handler: #loading or #loading="condition"
 */
class Maco_Openwire_Model_Template_Directive_Loading extends Maco_Openwire_Model_Template_Directive_Abstract
{
    protected $pattern = '#loading';

    /**
     * {@inheritdoc}
     */
    public function compile($value, $component)
    {
        $componentId = $this->getComponentId($component);

        $attributes = ['component' => $componentId];

        if (!empty($value)) {
            $attributes['loading'] = $value;
        }

        return $this->generateDataAttributes($componentId, $attributes);
    }

    /**
     * Override validation - loading directive can have empty value
     */
    protected function validateValue($value)
    {
        // Loading directive can have empty value
    }
}
