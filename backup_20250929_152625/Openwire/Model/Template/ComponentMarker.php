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
 * Handles Openwire component markers in templates
 */
class Maco_Openwire_Model_Template_ComponentMarker
{
    /**
     * Compile component markers in HTML
     *
     * @param string $html
     * @param Maco_Openwire_Model_Component $component
     * @return string
     */
    public function compile($html, $component)
    {
        $componentId = $component->getId();
        if (empty($componentId)) {
            throw new RuntimeException('Component must have an ID');
        }

        // Handle <div ow> syntax
        $html = $this->compileOwSyntax($html, $componentId);

        // Handle <div:ow> syntax
        $html = $this->compileColonOwSyntax($html, $componentId);

        return $html;
    }

    /**
     * Handle <div ow> syntax
     *
     * @param string $html
     * @param string $componentId
     * @return string
     */
    protected function compileOwSyntax($html, $componentId)
    {
        return preg_replace_callback(
            '/<(\w+)([^>]*?)\s+ow([^>]*?)>/',
            function ($matches) use ($componentId) {
                $tag = $matches[1];
                $beforeOw = $matches[2];
                $afterOw = $matches[3];

                // Remove ow attribute and add component attributes
                $attributes = trim($beforeOw . $afterOw);
                return "<{$tag}{$attributes} data-openwire-component data-openwire-id=\"{$componentId}\">";
            },
            $html
        );
    }

    /**
     * Handle <div:ow> syntax
     *
     * @param string $html
     * @param string $componentId
     * @return string
     */
    protected function compileColonOwSyntax($html, $componentId)
    {
        return preg_replace_callback(
            '/<(\w+):ow([^>]*?)>/',
            function ($matches) use ($componentId) {
                $tag = $matches[1];
                $attributes = $matches[2];
                return "<{$tag}{$attributes} data-openwire-component data-openwire-id=\"{$componentId}\">";
            },
            $html
        );
    }
}
