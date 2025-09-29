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
 * Click directive handler: @click="method"
 */
class Maco_Openwire_Model_Template_Directive_Click extends Maco_Openwire_Model_Template_Directive_Abstract
{
    protected $pattern = '@click';

    /**
     * {@inheritdoc}
     */
    public function compile($value, $component)
    {
        $this->validateValue($value);

        $methodCall = $this->parameterParser->parseMethodCall($value);
        $componentId = $this->getComponentId($component);

        return $this->generateDataAttributes($componentId, [
            'click' => $methodCall['method'],
            'component' => $componentId,
            'params' => json_encode($methodCall['params'])
        ]);
    }
}
