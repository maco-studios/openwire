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
 * Example component demonstrating the new ow directive syntax
 */
class Maco_Openwire_Model_Component_Example extends Maco_Openwire_Model_Component
{
    public function mount($params = [])
    {
        parent::mount($params);

        // Set default values
        if ($this->getData('title') === null) {
            $this->setData('title', 'Example Component');
        }
        if ($this->getData('count') === null) {
            $this->setData('count', 0);
        }
        if ($this->getData('message') === null) {
            $this->setData('message', 'Hello from Openwire!');
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

    public function decrement()
    {
        $current = (int) $this->getData('count');
        $current--;
        $this->setData('count', $current);
        return $this;
    }

    public function updateMessage($newMessage)
    {
        $this->setData('message', $newMessage);
        return $this;
    }

    public function getTitle()
    {
        return (string) $this->getData('title');
    }

    public function getCount()
    {
        return (int) $this->getData('count');
    }

    public function getMessage()
    {
        return (string) $this->getData('message');
    }
}
