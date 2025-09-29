<?php

/**
 * Copyright (c) 2025 MACO
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/maco-studios/openwire
 */

class Maco_Openwire_Model_Component_Counter extends Maco_Openwire_Model_Component
{
    /**
     * Mount into data-first model. Supported params: initialCount, name
     */
    public function mount($params = [])
    {
        parent::mount($params);
        if (isset($params['initialCount'])) {
            $this->setData('count', (int) $params['initialCount']);
        } elseif ($this->getData('count') === null) {
            $this->setData('count', 0);
        }
        if (isset($params['name'])) {
            $this->setData('name', $params['name']);
        } elseif ($this->getData('name') === null) {
            $this->setData('name', 'Guest');
        }
        return $this;
    }

    public function increment()
    {
        $current = (int) $this->getData('count');
        $current++;
        $this->setData('count', $current);
        return $this;
    }

    public function getCount()
    {
        return (int) $this->getData('count');
    }

    public function getName()
    {
        return (string) $this->getData('name');
    }

    public function getTemplate()
    {
        return 'openwire/counter.phtml';
    }
}
