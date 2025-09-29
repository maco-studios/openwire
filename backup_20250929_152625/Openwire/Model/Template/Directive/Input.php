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
 * Input directive handler: @input="property"
 */
class Maco_Openwire_Model_Template_Directive_Input extends Maco_Openwire_Model_Template_Directive_Abstract
{
    protected $pattern = '@input';

    /**
     * {@inheritdoc}
     */
    public function compile($value, $component)
    {
        $this->validateValue($value);

        $componentId = $this->getComponentId($component);

        return $this->generateDataAttributes($componentId, [
            'model' => $value,
            'component' => $componentId,
            'model-mode' => 'default'
        ]);
    }
}
