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
 * Interface for Openwire template directives
 */
interface Maco_Openwire_Model_Template_Directive_Interface
{
    /**
     * Get the directive pattern (e.g., '@click', '#model')
     *
     * @return string
     */
    public function getPattern();

    /**
     * Compile the directive into data attributes
     *
     * @param string $value The directive value
     * @param Maco_Openwire_Model_Component $component The component instance
     * @return string The compiled data attributes
     */
    public function compile($value, $component);

    /**
     * Check if this directive can handle the given pattern
     *
     * @param string $pattern
     * @return bool
     */
    public function canHandle($pattern);
}
