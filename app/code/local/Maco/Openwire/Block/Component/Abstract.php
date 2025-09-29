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
 * Simple, clean component base class for Openwire.
 *
 * This class provides a minimal, data-first approach where:
 * - Public properties represent component state
 * - getData/setData is the single source of truth
 * - Components are Block classes that integrate with Magento's template system
 */
class Maco_Openwire_Block_Component_Abstract extends Mage_Core_Block_Template
{
    public function __construct($data = [])
    {
        parent::__construct($data);
        if (!$this->getData('id')) {
            $this->setData('id', uniqid('openwire_'));
        }
    }

    /**
     * Mount initial parameters into component state
     */
    public function mount($params = [])
    {
        foreach ((array) $params as $k => $v) {
            $this->setData($k, $v);
        }
        return $this;
    }

    /**
     * Render the component template and return compiled HTML
     */
    public function render()
    {
        $template = $this->getTemplate();
        if (!$template) {
            throw new Exception('Component template not defined');
        }

        $block = Mage::app()->getLayout()->createBlock('core/template');
        $block->setTemplate($template);
        $block->setData('openwire', $this);

        // Make component methods available directly in template scope
        $this->exposeMethodsToTemplate($block);

        $html = $block->toHtml();

        $parser = Mage::getModel('openwire/template_parser');
        if ($parser) {
            $html = $parser->compile($html, $this);
        }

        return $html;
    }

    /**
     * Expose component methods directly to template for cleaner syntax
     */
    protected function exposeMethodsToTemplate($block)
    {
        // Get all public methods from this component
        $reflection = new ReflectionClass($this);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $methodName = $method->getName();

            // Skip magic methods and internal methods
            if (
                str_starts_with($methodName, '__') ||
                in_array($methodName, ['render', 'mount', 'update', 'call', 'getState', 'setState', 'getId', 'getTemplate', 'jsonSerialize'])
            ) {
                continue;
            }

            // Create a closure that calls the method on this component
            $block->setData($methodName, function () use ($methodName) {
                return $this->{$methodName}();
            });
        }

        // Also expose data properties directly
        $data = $this->getData();
        foreach ($data as $key => $value) {
            if (!is_object($value) && !is_array($value)) {
                $block->setData($key, $value);
            }
        }
    }

    /**
     * Update a single property on the component
     */
    public function update($property, $value)
    {
        // protect some internal keys
        if (in_array($property, ['id', 'state'])) {
            throw new Exception("Cannot update property: {$property}");
        }
        $this->setData($property, $value);
        return $this;
    }

    /**
     * Call a method on the component
     */
    public function call($method, $params = [])
    {
        if (!method_exists($this, $method)) {
            throw new Exception("Method {$method} not found on component " . static::class);
        }
        return call_user_func_array([$this, $method], (array) $params);
    }

    /**
     * Get component state for serialization
     */
    public function getState()
    {
        return $this->getData();
    }

    /**
     * Set component state from serialized data
     */
    public function setState($state)
    {
        if (!is_array($state)) {
            return $this;
        }
        $this->setData($state);
        return $this;
    }

    /**
     * Get component ID
     */
    public function getId()
    {
        return $this->getData('id');
    }

    /**
     * Serialize component state to JSON
     */
    public function jsonSerialize()
    {
        return $this->getData();
    }

    /**
     * Get template path for this component
     */
    public function getTemplate()
    {
        $className = static::class;
        $templateName = strtolower(str_replace('maco_openwire_model_', '', $className));
        return "openwire/{$templateName}.phtml";
    }
}
