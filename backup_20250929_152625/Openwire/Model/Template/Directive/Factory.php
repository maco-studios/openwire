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
 * Factory for creating directive instances
 */
class Maco_Openwire_Model_Template_Directive_Factory
{
    /**
     * @var array
     */
    protected static $directiveModels = [
        '@click' => 'openwire/template_directive_click',
        '@submit' => 'openwire/template_directive_submit',
        '@input' => 'openwire/template_directive_input',
        '@change' => 'openwire/template_directive_change',
        '@drag' => 'openwire/template_directive_drag',
        '@drop' => 'openwire/template_directive_drop',
        '@sortable' => 'openwire/template_directive_sortable',
        '#model' => 'openwire/template_directive_model',
        '#loading' => 'openwire/template_directive_loading',
        '#ignore' => 'openwire/template_directive_ignore',
    ];

    /**
     * @var array
     */
    protected static $instances = [];

    /**
     * Get directive instance by pattern
     *
     * @param string $pattern
     * @return Maco_Openwire_Model_Template_Directive_Interface
     * @throws InvalidArgumentException
     */
    public static function getDirective($pattern)
    {
        if (!isset(self::$directiveModels[$pattern])) {
            throw new InvalidArgumentException("Unknown directive pattern: {$pattern}");
        }

        if (!isset(self::$instances[$pattern])) {
            $modelAlias = self::$directiveModels[$pattern];
            self::$instances[$pattern] = Mage::getModel($modelAlias);
        }

        return self::$instances[$pattern];
    }

    /**
     * Get all available directive patterns
     *
     * @return array
     */
    public static function getAvailablePatterns()
    {
        return array_keys(self::$directiveModels);
    }

    /**
     * Register a new directive
     *
     * @param string $pattern
     * @param string $modelAlias
     */
    public static function registerDirective($pattern, $modelAlias)
    {
        self::$directiveModels[$pattern] = $modelAlias;
        // Clear instance cache for this pattern
        unset(self::$instances[$pattern]);
    }

    /**
     * Check if a directive pattern is registered
     *
     * @param string $pattern
     * @return bool
     */
    public static function hasDirective($pattern)
    {
        return isset(self::$directiveModels[$pattern]);
    }
}
