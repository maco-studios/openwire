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
 * Simple test component to verify array access fixes
 */
class Maco_Openwire_Model_Component_SimpleTest extends Maco_Openwire_Model_Component
{
    /**
     * Mount initial parameters
     */
    public function mount($params = [])
    {
        parent::mount($params);
        
        // Test with minimal data to ensure no null access
        $this->setData('message', 'Simple test component loaded successfully');
        $this->setData('items', []);
        
        return $this;
    }

    /**
     * Get template path
     */
    public function getTemplate()
    {
        return 'openwire/simple_test.phtml';
    }
}
