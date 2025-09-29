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
 * Handles data binding in templates
 */
class Maco_Openwire_Model_Template_DataBinding
{
    /**
     * Compile data binding attributes in HTML
     *
     * @param string $html
     * @param Maco_Openwire_Model_Component $component
     * @return string
     */
    public function compile($html, $component)
    {
        return preg_replace_callback(
            '/\s+:(\w+)="([^"]+)"/',
            function ($matches) use ($component) {
                return $this->compileBinding($matches[1], $matches[2], $component);
            },
            $html
        );
    }

    /**
     * Compile a single binding
     *
     * @param string $property
     * @param string $value
     * @param Maco_Openwire_Model_Component $component
     * @return string
     */
    protected function compileBinding($property, $value, $component)
    {
        // Get the actual value from component data
        $actualValue = $component->getData($property);

        if ($actualValue !== null) {
            $displayValue = htmlspecialchars($actualValue, ENT_QUOTES, 'UTF-8');
        } else {
            // If property doesn't exist in component, keep original value
            $displayValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        return sprintf(
            ' %s="%s" data-openwire-bind="%s"',
            $property,
            $displayValue,
            $property
        );
    }
}
