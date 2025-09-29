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
 * Input test component to verify focus preservation
 */
class Maco_Openwire_Model_Component_InputTest extends Maco_Openwire_Model_Component
{
    /**
     * Mount initial parameters
     */
    public function mount($params = [])
    {
        parent::mount($params);

        // Initialize input values
        $this->setData('realTimeInput', '');
        $this->setData('lazyInput', '');
        $this->setData('textareaInput', '');

        return $this;
    }

    /**
     * Clear all input values
     */
    public function clearAll()
    {
        $this->setData('realTimeInput', '');
        $this->setData('lazyInput', '');
        $this->setData('textareaInput', '');

        $this->addEffect([
            'type' => 'notify',
            'data' => ['message' => 'All inputs cleared']
        ]);

        return $this;
    }

    /**
     * Set test values for demonstration
     */
    public function setTestValues()
    {
        $this->setData('realTimeInput', 'Real-time test value');
        $this->setData('lazyInput', 'Lazy test value');
        $this->setData('textareaInput', 'This is a test textarea value that demonstrates how the component handles longer text content.');

        $this->addEffect([
            'type' => 'notify',
            'data' => ['message' => 'Test values set']
        ]);

        return $this;
    }

    /**
     * Get template path
     */
    public function getTemplate()
    {
        return 'openwire/input_test.phtml';
    }
}
