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
 * Drop directive handler: @drop="method" or @drop="method(data)"
 */
class Maco_Openwire_Model_Template_Directive_Drop extends Maco_Openwire_Model_Template_Directive_Abstract
{
    protected $pattern = '@drop';

    /**
     * {@inheritdoc}
     */
    public function compile($value, $component)
    {
        $this->validateValue($value);

        $methodCall = $this->parameterParser->parseMethodCall($value);
        $componentId = $this->getComponentId($component);

        return $this->generateDataAttributes($componentId, [
            'drop' => $methodCall['method'],
            'component' => $componentId,
            'drop-params' => json_encode($methodCall['params'])
        ]);
    }
}
