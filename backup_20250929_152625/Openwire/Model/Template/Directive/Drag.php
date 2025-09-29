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
 * Drag directive handler: @drag="method" or @drag="method(data)"
 */
class Maco_Openwire_Model_Template_Directive_Drag extends Maco_Openwire_Model_Template_Directive_Abstract
{
    protected $pattern = '@drag';

    /**
     * {@inheritdoc}
     */
    public function compile($value, $component)
    {
        $this->validateValue($value);

        $methodCall = $this->parameterParser->parseMethodCall($value);
        $componentId = $this->getComponentId($component);

        // Generate data attributes for Openwire functionality
        $dataAttributes = $this->generateDataAttributes($componentId, [
            'drag' => $methodCall['method'],
            'component' => $componentId,
            'drag-params' => json_encode($methodCall['params'])
        ]);

        // Add the draggable HTML attribute
        return $dataAttributes . ' draggable="true"';
    }
}
