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
 * Template parser for Openwire components.
 *
 * Supports the new ow directive syntax:
 * - <div ow> - marks an openwire component
 * - <div:ow> - alternative syntax for openwire component
 * - @click="method" - event handlers
 * - :property="value" - data binding
 * - #directive - special directives
 */
class Maco_Openwire_Model_Template_Parser
{
    /**
     * @var Maco_Openwire_Model_Template_ComponentMarker
     */
    protected $componentMarker;

    /**
     * @var Maco_Openwire_Model_Template_DataBinding
     */
    protected $dataBinding;

    /**
     * @var Maco_Openwire_Model_Template_Directive_Factory
     */
    protected $directiveFactory;

    /**
     * @var Maco_Openwire_Model_Template_Validator
     */
    protected $validator;

    /**
     * @var bool
     */
    protected $enableValidation = true;

    public function __construct()
    {
        $this->componentMarker = Mage::getModel('openwire/template_componentMarker');
        $this->dataBinding = Mage::getModel('openwire/template_dataBinding');
        $this->directiveFactory = Mage::getModel('openwire/template_directive_factory');
        $this->validator = Mage::getModel('openwire/template_validator');
    }

    /**
     * Compile template HTML with Openwire directives
     *
     * @param string $html
     * @param Maco_Openwire_Model_Component $component
     * @return string
     * @throws Exception
     */
    public function compile($html, $component)
    {
        try {
            // Validate template if validation is enabled
            if ($this->enableValidation) {
                if (!$this->validator->validate($html, $component)) {
                    $errors = $this->validator->getErrors();
                    $warnings = $this->validator->getWarnings();

                    // Log warnings
                    foreach ($warnings as $warning) {
                        Mage::log("Openwire Template Warning: {$warning}");
                    }

                    // Throw exception for errors
                    if (!empty($errors)) {
                        throw new Exception("Template validation failed: " . implode(', ', $errors));
                    }
                }
            }

            // First, handle ow component markers
            $compiled = $this->componentMarker->compile($html, $component);

            // Handle data binding with :property syntax
            $compiled = $this->dataBinding->compile($compiled, $component);

            // Then handle all directives
            $compiled = $this->compileDirectives($compiled, $component);

            return $compiled;
        } catch (Exception $e) {
            Mage::logException($e);
            throw new Exception("Template compilation failed: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Compile all directives in the HTML
     *
     * @param string $html
     * @param Maco_Openwire_Model_Component $component
     * @return string
     */
    protected function compileDirectives($html, $component)
    {
        $patterns = $this->directiveFactory->getAvailablePatterns();

        foreach ($patterns as $pattern) {
            $html = $this->compileDirective($html, $pattern, $component);
        }

        return $html;
    }

    /**
     * Compile a specific directive pattern
     *
     * @param string $html
     * @param string $pattern
     * @param Maco_Openwire_Model_Component $component
     * @return string
     */
    protected function compileDirective($html, $pattern, $component)
    {
        $escaped = preg_quote($pattern, '/');
        $regex = '/\s+' . $escaped . '(?:="([^"]+)")?/';

        return preg_replace_callback($regex, function ($matches) use ($pattern, $component) {
            $value = $matches[1] ?? '';

            try {
                $directive = $this->directiveFactory->getDirective($pattern);
                return $directive->compile($value, $component);
            } catch (Exception $e) {
                Mage::log("Failed to compile directive {$pattern}: " . $e->getMessage());
                return ''; // Skip invalid directives
            }
        }, $html);
    }

    /**
     * Enable or disable validation
     *
     * @param bool $enable
     * @return $this
     */
    public function setValidationEnabled($enable)
    {
        $this->enableValidation = (bool) $enable;
        return $this;
    }

    /**
     * Check if validation is enabled
     *
     * @return bool
     */
    public function isValidationEnabled()
    {
        return $this->enableValidation;
    }

    /**
     * Get the validator instance
     *
     * @return Maco_Openwire_Model_Template_Validator
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * Register a custom directive
     *
     * @param string $pattern
     * @param string $className
     * @return $this
     */
    public function registerDirective($pattern, $className)
    {
        $this->directiveFactory->registerDirective($pattern, $className);
        return $this;
    }

    /**
     * Get all available directive patterns
     *
     * @return array
     */
    public function getAvailableDirectives()
    {
        return $this->directiveFactory->getAvailablePatterns();
    }
}
