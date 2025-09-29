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
 * Ignore directive handler: #ignore
 */
class Maco_Openwire_Model_Template_Directive_Ignore extends Maco_Openwire_Model_Template_Directive_Abstract
{
    protected $pattern = '#ignore';

    /**
     * {@inheritdoc}
     */
    public function compile($value, $component)
    {
        return ' data-openwire-ignore';
    }

    /**
     * Override validation - ignore directive doesn't need value
     */
    protected function validateValue($value)
    {
        // Ignore directive doesn't require a value
    }
}
