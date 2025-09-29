<?php

declare(strict_types=1);

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
 *
 * This factory reads directive configurations from config.xml and creates
 * directive instances dynamically based on the registered directives.
 */
class Maco_Openwire_Model_Template_Directive_Factory
{
    /**
     * @var array Cached directive models loaded from config
     */
    protected static $directiveModels = null;

    /**
     * @var array Cached directive instances
     */
    protected static $instances = [];

    /**
     * Load directive configurations from config.xml
     */
    protected static function loadDirectiveModels(): void
    {
        if (self::$directiveModels !== null) {
            return;
        }

        self::$directiveModels = [];

        // Load directives from config.xml
        $config = Mage::getConfig()->getNode('global/openwire/template/directives');

        if ($config) {
            foreach ($config->children() as $directiveName => $directiveConfig) {
                $class = (string) $directiveConfig->class;
                $pattern = (string) $directiveConfig->pattern;

                if ($class && $pattern) {
                    self::$directiveModels[$pattern] = $class;
                }
            }
        }

        // Fallback to hardcoded directives if config is empty
        if (empty(self::$directiveModels)) {
            self::$directiveModels = [
                '@click' => 'Maco_Openwire_Model_Template_Directive_Click',
                '@submit' => 'Maco_Openwire_Model_Template_Directive_Submit',
                '@input' => 'Maco_Openwire_Model_Template_Directive_Input',
                '@change' => 'Maco_Openwire_Model_Template_Directive_Change',
                '@drag' => 'Maco_Openwire_Model_Template_Directive_Drag',
                '@drop' => 'Maco_Openwire_Model_Template_Directive_Drop',
                '@sortable' => 'Maco_Openwire_Model_Template_Directive_Sortable',
                '#model' => 'Maco_Openwire_Model_Template_Directive_Model',
                '#loading' => 'Maco_Openwire_Model_Template_Directive_Loading',
                '#ignore' => 'Maco_Openwire_Model_Template_Directive_Ignore',
            ];
        }
    }

    /**
     * Get directive instance by pattern
     *
     * @param string $pattern Directive pattern (e.g., '@click', '#model')
     * @return Maco_Openwire_Model_Template_Directive_Interface
     * @throws InvalidArgumentException
     */
    public static function getDirective(string $pattern): Maco_Openwire_Model_Template_Directive_Interface
    {
        self::loadDirectiveModels();

        if (!isset(self::$directiveModels[$pattern])) {
            throw new InvalidArgumentException("Unknown directive pattern: {$pattern}");
        }

        if (!isset(self::$instances[$pattern])) {
            $className = self::$directiveModels[$pattern];

            // Try to instantiate using class name directly
            if (class_exists($className)) {
                self::$instances[$pattern] = new $className();
            } else {
                throw new InvalidArgumentException("Directive class not found: {$className}");
            }
        }

        return self::$instances[$pattern];
    }

    /**
     * Get all available directive patterns
     *
     * @return array
     */
    public static function getAvailablePatterns(): array
    {
        self::loadDirectiveModels();
        return array_keys(self::$directiveModels);
    }

    /**
     * Register a new directive at runtime
     *
     * @param string $pattern Directive pattern
     * @param string $className Full class name
     */
    public static function registerDirective(string $pattern, string $className): void
    {
        self::loadDirectiveModels();
        self::$directiveModels[$pattern] = $className;

        // Clear instance cache for this pattern
        unset(self::$instances[$pattern]);
    }

    /**
     * Check if a directive pattern is registered
     *
     * @param string $pattern Directive pattern
     * @return bool
     */
    public static function hasDirective(string $pattern): bool
    {
        self::loadDirectiveModels();
        return isset(self::$directiveModels[$pattern]);
    }

    /**
     * Clear all cached instances (useful for testing)
     */
    public static function clearCache(): void
    {
        self::$instances = [];
        self::$directiveModels = null;
    }
}
