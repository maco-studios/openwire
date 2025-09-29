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
 * Draggable Card component
 */
class Maco_Openwire_Model_Component_DraggableCard extends Maco_Openwire_Model_Component
{
    /**
     * Mount initial parameters
     */
    public function mount($params = [])
    {
        parent::mount($params);

        // Load product data if productId is provided
        if (isset($params['productId']) && $params['productId']) {
            $this->loadProductData($params['productId']);
        } else {
            // Use provided data or defaults
            $this->setData('cardId', isset($params['cardId']) ? (int)$params['cardId'] : null);
            $this->setData('title', isset($params['title']) ? (string)$params['title'] : 'Untitled Card');
            $this->setData('content', isset($params['content']) ? (string)$params['content'] : '');
            $this->setData('color', isset($params['color']) ? (string)$params['color'] : 'blue');
        }

        $this->setData('isDragging', false);
        $this->setData('position', isset($params['position']) ? (array)$params['position'] : ['x' => 0, 'y' => 0]);

        return $this;
    }

    /**
     * Load product data from catalog
     */
    protected function loadProductData($productId)
    {
        try {
            $product = Mage::getModel('catalog/product')->load($productId);
            if ($product->getId()) {
                $this->setData('cardId', $product->getId());
                $this->setData('title', $product->getName());
                $this->setData('content', $product->getShortDescription() ?: 'No description available');
                $this->setData('color', $this->getProductColor($product));
                $this->setData('product', $product);
                $this->setData('price', $product->getPrice());
                $this->setData('sku', $product->getSku());
                $this->setData('image', $product->getImageUrl());
            }
        } catch (Exception $e) {
            Mage::logException($e);
            // Fallback to defaults if product can't be loaded
            $this->setData('cardId', $productId);
            $this->setData('title', 'Product #' . $productId);
            $this->setData('content', 'Product not found');
            $this->setData('color', 'red');
        }
    }

    /**
     * Determine card color based on product price
     */
    protected function getProductColor($product)
    {
        $price = (float)$product->getPrice();
        if ($price > 200) {
            return 'purple'; // High-end products
        } elseif ($price > 100) {
            return 'blue'; // Mid-range products
        } elseif ($price > 50) {
            return 'green'; // Affordable products
        } else {
            return 'orange'; // Budget products
        }
    }

    /**
     * Handle drag start
     */
    public function onDragStart($dragData)
    {
        $this->setData('isDragging', true);
        $this->addEffect([
            'type' => 'notify',
            'data' => ['message' => 'Started dragging: ' . $this->getData('title')]
        ]);
        return $this;
    }

    /**
     * Handle drag end
     */
    public function onDragEnd($dragData)
    {
        $this->setData('isDragging', false);
        return $this;
    }

    /**
     * Update card position
     */
    public function updatePosition($x, $y)
    {
        $this->setData('position', ['x' => (int)$x, 'y' => (int)$y]);
        return $this;
    }

    /**
     * Update card title
     */
    public function updateTitle($newTitle)
    {
        $this->setData('title', (string)$newTitle);
        return $this;
    }

    /**
     * Update card content
     */
    public function updateContent($newContent)
    {
        $this->setData('content', (string)$newContent);
        return $this;
    }

    /**
     * Change card color
     */
    public function changeColor($color)
    {
        $this->setData('color', (string)$color);
        return $this;
    }

    /**
     * Get available colors
     */
    public function getAvailableColors()
    {
        return [
            'blue' => '#2196F3',
            'green' => '#4CAF50',
            'orange' => '#FF9800',
            'red' => '#F44336',
            'purple' => '#9C27B0',
            'teal' => '#009688'
        ];
    }

    /**
     * Get color style
     */
    public function getColorStyle()
    {
        $colors = $this->getAvailableColors();
        $color = $this->getData('color');
        return $colors[$color] ?? $colors['blue'];
    }

    /**
     * Get template path
     */
    public function getTemplate()
    {
        return 'openwire/draggable_card.phtml';
    }
}
