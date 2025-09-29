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
 * Parameter parser for Openwire template directives
 */
class Maco_Openwire_Model_Template_ParameterParser
{
    /**
     * Parse method calls like "method" or "method(param1, param2)"
     *
     * @param string $value
     * @return array
     * @throws InvalidArgumentException
     */
    public function parseMethodCall($value)
    {
        if (!preg_match('/^(\w+)(?:\(([^)]*)\))?$/', $value, $matches)) {
            throw new InvalidArgumentException("Invalid method call syntax: {$value}");
        }

        $method = $matches[1];
        $params = [];

        if (isset($matches[2]) && !empty($matches[2])) {
            $params = $this->parseParameters($matches[2]);
        }

        return ['method' => $method, 'params' => $params];
    }

    /**
     * Parse method parameters
     *
     * @param string $paramString
     * @return array
     */
    public function parseParameters($paramString)
    {
        $params = [];
        $paramString = trim($paramString);

        if (empty($paramString)) {
            return $params;
        }

        $paramArray = explode(',', $paramString);

        foreach ($paramArray as $param) {
            $params[] = $this->parseParameter(trim($param));
        }

        return $params;
    }

    /**
     * Parse a single parameter
     *
     * @param string $param
     * @return mixed
     */
    protected function parseParameter($param)
    {
        // String literals
        if (preg_match('/^["\'](.+)["\']$/', $param, $matches)) {
            return $matches[1];
        }

        // Boolean values
        if ($param === 'true') {
            return true;
        }
        if ($param === 'false') {
            return false;
        }

        // Numeric values
        if (is_numeric($param)) {
            return str_contains($param, '.') ? (float) $param : (int) $param;
        }

        // Default: return as string
        return $param;
    }

    /**
     * Parse modifiers like "property.lazy" or "property.class"
     *
     * @param string $value
     * @return array
     */
    public function parseModifiers($value)
    {
        $modifiers = [];

        if (str_contains($value, '.')) {
            $parts = explode('.', $value);
            $modifiers['property'] = $parts[0];
            $counter = count($parts);

            for ($i = 1; $i < $counter; $i++) {
                $modifiers[$parts[$i]] = true;
            }
        } else {
            $modifiers['property'] = $value;
        }

        return $modifiers;
    }
}
