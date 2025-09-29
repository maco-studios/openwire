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
 * Drag test component to debug drag and drop functionality
 */
class Maco_Openwire_Model_Component_DragTest extends Maco_Openwire_Model_Component
{
    /**
     * Mount initial parameters
     */
    public function mount($params = [])
    {
        parent::mount($params);

        $this->setData('message', 'Drag test component loaded');
        $this->setData('dragCount', 0);
        $this->setData('dropCount', 0);

        return $this;
    }

    /**
     * Handle drag start
     */
    public function onDragStart($dragData)
    {
        $dragCount = $this->getData('dragCount') + 1;
        $this->setData('dragCount', $dragCount);

        $this->addEffect([
            'type' => 'notify',
            'data' => ['message' => "Drag started #{$dragCount}"]
        ]);

        return $this;
    }

    /**
     * Handle drop
     */
    public function onDrop($dragData)
    {
        $dropCount = $this->getData('dropCount') + 1;
        $this->setData('dropCount', $dropCount);

        $this->addEffect([
            'type' => 'notify',
            'data' => ['message' => "Drop event #{$dropCount}"]
        ]);

        return $this;
    }

    /**
     * Handle sortable reorder
     */
    public function reorderItems($sortData)
    {
        $this->addEffect([
            'type' => 'notify',
            'data' => ['message' => "Reordered items: {$sortData['fromIndex']} -> {$sortData['toIndex']}"]
        ]);

        return $this;
    }

    /**
     * Get template path
     */
    public function getTemplate()
    {
        return 'openwire/drag_test.phtml';
    }
}
