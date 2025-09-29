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
 * Model directive handler: #model="property" or #model="property.lazy"
 */
class Maco_Openwire_Model_Template_Directive_Model extends Maco_Openwire_Model_Template_Directive_Abstract
{
    protected $pattern = '#model';

    /**
     * {@inheritdoc}
     */
    public function compile($value, $component)
    {
        $this->validateValue($value);

        $modifiers = $this->parameterParser->parseModifiers($value);
        $componentId = $this->getComponentId($component);
        $mode = isset($modifiers['lazy']) ? 'lazy' : 'default';

        return $this->generateDataAttributes($componentId, [
            'model' => $modifiers['property'],
            'component' => $componentId,
            'model-mode' => $mode
        ]);
    }
}
