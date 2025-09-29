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
 * Template validator for Openwire components
 */
class Maco_Openwire_Model_Template_Validator
{
    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var array
     */
    protected $warnings = [];

    /**
     * Validate template HTML
     *
     * @param string $html
     * @param Maco_Openwire_Model_Component $component
     * @return bool
     */
    public function validate($html, $component)
    {
        $this->errors = [];
        $this->warnings = [];

        $this->validateComponentId($component);
        $this->validateDirectives($html);
        $this->validateDataBinding($html, $component);

        return empty($this->errors);
    }

    /**
     * Get validation errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get validation warnings
     *
     * @return array
     */
    public function getWarnings()
    {
        return $this->warnings;
    }

    /**
     * Validate component has an ID
     *
     * @param Maco_Openwire_Model_Component $component
     */
    protected function validateComponentId($component)
    {
        if (empty($component->getId())) {
            $this->errors[] = 'Component must have an ID';
        }
    }

    /**
     * Validate directives in HTML
     *
     * @param string $html
     */
    protected function validateDirectives($html)
    {
        $factory = Mage::getModel('openwire/template_directive_factory');
        $availablePatterns = $factory->getAvailablePatterns();

        // Find all directive patterns in HTML
        foreach ($availablePatterns as $pattern) {
            $escaped = preg_quote($pattern, '/');
            $regex = '/\s+' . $escaped . '(?:="([^"]+)")?/';

            if (preg_match_all($regex, $html, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $value = $match[1] ?? '';
                    $this->validateDirectiveValue($pattern, $value);
                }
            }
        }
    }

    /**
     * Validate directive value
     *
     * @param string $pattern
     * @param string $value
     */
    protected function validateDirectiveValue($pattern, $value)
    {
        try {
            $directive = Mage::getModel('openwire/template_directive_factory')->getDirective($pattern);

            // Check if directive requires a value
            if (empty($value) && !in_array($pattern, ['#loading', '#ignore'])) {
                $this->warnings[] = "Directive {$pattern} should have a value";
            }

            // Validate method calls for event directives
            if (str_starts_with($pattern, '@') && !empty($value)) {
                $this->validateMethodCall($value);
            }

        } catch (Exception $e) {
            $this->errors[] = "Invalid directive {$pattern}: " . $e->getMessage();
        }
    }

    /**
     * Validate method call syntax
     *
     * @param string $value
     */
    protected function validateMethodCall($value)
    {
        if (!preg_match('/^(\w+)(?:\(([^)]*)\))?$/', $value)) {
            $this->errors[] = "Invalid method call syntax: {$value}";
        }
    }

    /**
     * Validate data binding
     *
     * @param string $html
     * @param Maco_Openwire_Model_Component $component
     */
    protected function validateDataBinding($html, $component)
    {
        if (preg_match_all('/\s+:(\w+)="([^"]+)"/', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $property = $match[1];

                // Check if property exists in component
                if (!$component->hasData($property)) {
                    $this->warnings[] = "Property '{$property}' not found in component data";
                }
            }
        }
    }
}
