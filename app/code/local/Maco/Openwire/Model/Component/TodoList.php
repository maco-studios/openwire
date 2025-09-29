<?php

/**
 * Copyright (c) 2025 MACO
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/maco-studios/openwire
 */

class Maco_Openwire_Model_Component_TodoList extends Maco_Openwire_Model_Component
{
    /**
     * Ensure items is an array on mount
     */
    public function mount($params = [])
    {
        parent::mount($params);
        $items = $this->getData('items');
        if (!is_array($items)) {
            $this->setData('items', []);
        }
        return $this;
    }

    public function addItem($item)
    {
        // support being called with either a string or an array payload
        if (is_array($item)) {
            if (isset($item['item'])) {
                $value = (string) $item['item'];
            } else {
                // try to use the first scalar value found
                $value = '';
                foreach ($item as $v) {
                    if (!is_array($v)) {
                        $value = (string) $v;
                        break;
                    }
                }
            }
        } else {
            $value = (string) $item;
        }

        $items = $this->getData('items') ?: [];
        $items[] = $value;
        $this->setData('items', $items);
        return $this;
    }

    public function removeItem($index)
    {
        $items = $this->getData('items') ?: [];
        if (!isset($items[$index]))
            return $this;
        array_splice($items, $index, 1);
        $this->setData('items', $items);
        return $this;
    }

    public function clear()
    {
        $this->setData('items', []);
        return $this;
    }

    public function getTemplate()
    {
        return 'openwire/todo.phtml';
    }
}
