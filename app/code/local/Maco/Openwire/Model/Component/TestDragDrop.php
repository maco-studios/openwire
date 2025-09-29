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
 * Test component to verify drag and drop functionality works without errors
 */
class Maco_Openwire_Model_Component_TestDragDrop extends Maco_Openwire_Model_Component
{
    /**
     * Mount initial parameters
     */
    public function mount($params = [])
    {
        parent::mount($params);

        // Initialize with safe defaults
        $this->setData('testItems', [
            ['id' => 1, 'name' => 'Test Item 1', 'value' => 'A'],
            ['id' => 2, 'name' => 'Test Item 2', 'value' => 'B'],
            ['id' => 3, 'name' => 'Test Item 3', 'value' => 'C'],
        ]);

        $this->setData('message', 'Drag and drop test component loaded successfully');

        return $this;
    }

    /**
     * Test reordering items
     */
    public function reorderItems($dragData)
    {
        $items = $this->getData('testItems') ?: [];
        $fromIndex = isset($dragData['fromIndex']) ? (int)$dragData['fromIndex'] : 0;
        $toIndex = isset($dragData['toIndex']) ? (int)$dragData['toIndex'] : 0;

        if ($fromIndex !== $toIndex && $fromIndex >= 0 && $toIndex >= 0 && $toIndex < count($items) && $fromIndex < count($items)) {
            $movedItem = array_splice($items, $fromIndex, 1)[0];
            array_splice($items, $toIndex, 0, [$movedItem]);
            $this->setData('testItems', $items);

            $this->setData('message', "Moved item from position {$fromIndex} to {$toIndex}");
        }

        return $this;
    }

    /**
     * Test drag start
     */
    public function onDragStart($dragData)
    {
        $this->setData('message', 'Drag started: ' . ($dragData['itemId'] ?? 'unknown'));
        return $this;
    }

    /**
     * Test drop
     */
    public function onDrop($dragData)
    {
        $this->setData('message', 'Drop received: ' . ($dragData['itemId'] ?? 'unknown'));
        return $this;
    }

    /**
     * Get test items
     */
    public function getTestItems()
    {
        return $this->getData('testItems') ?: [];
    }

    /**
     * Get template path
     */
    public function getTemplate()
    {
        return 'openwire/test_drag_drop.phtml';
    }
}
