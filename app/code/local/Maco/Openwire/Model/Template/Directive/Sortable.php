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
 * Sortable directive handler: @sortable="method" or @sortable="method(data)"
 */
class Maco_Openwire_Model_Template_Directive_Sortable extends Maco_Openwire_Model_Template_Directive_Abstract
{
    protected $pattern = '@sortable';

    /**
     * {@inheritdoc}
     */
    public function compile($value, $component)
    {
        $this->validateValue($value);

        $methodCall = $this->parameterParser->parseMethodCall($value);
        $componentId = $this->getComponentId($component);

        return $this->generateDataAttributes($componentId, [
            'sortable' => $methodCall['method'],
            'component' => $componentId,
            'sortable-params' => json_encode($methodCall['params'])
        ]);
    }
}
