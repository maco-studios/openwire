<?php

/**
 * Copyright (c) 2025 MACO
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/maco-studios/openwire
 */

abstract class Maco_Openwire_Model_Template_Directive_Abstract implements Maco_Openwire_Model_Template_Directive_Interface
{
    /**
     * @var string
     */
    protected $pattern;

    /**
     * @var Maco_Openwire_Model_Template_ParameterParser
     */
    protected $parameterParser;

    public function __construct()
    {
        $this->parameterParser = Mage::getModel('openwire/template_parameterParser');
    }

    /**
     * {@inheritdoc}
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * {@inheritdoc}
     */
    public function canHandle($pattern)
    {
        return $this->pattern === $pattern;
    }

    /**
     * Generate data attributes for the component
     *
     * @param string $componentId
     * @return string
     */
    protected function generateDataAttributes($componentId, array $attributes)
    {
        $result = '';
        foreach ($attributes as $key => $value) {
            if ($value !== null && $value !== '') {
                $result .= sprintf(' data-openwire-%s="%s"', $key, htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
        }
        return $result;
    }

    /**
     * Validate directive value
     *
     * @param string $value
     * @throws InvalidArgumentException
     */
    protected function validateValue($value)
    {
        if (empty($value)) {
            throw new InvalidArgumentException('Directive value cannot be empty');
        }
    }

    /**
     * Get component ID safely
     *
     * @param Maco_Openwire_Model_Component $component
     * @return string
     */
    protected function getComponentId($component)
    {
        $id = $component->getId();
        if (empty($id)) {
            throw new RuntimeException('Component must have an ID');
        }
        return $id;
    }
}
