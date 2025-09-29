<?php

/**
 * Copyright (c) 2025 MACO
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/maco-studios/openwire
 */

class Maco_Openwire_Model_Response
{
    protected $html = '';
    protected $state = [];
    protected $effects = [];
    protected $errors = [];

    public function setHtml($html)
    {
        $this->html = $html;
        return $this;
    }

    public function setState(?array $state)
    {
        $this->state = $state ?? [];
        return $this;
    }

    public function addEffect($effect)
    {
        $this->effects[] = $effect;
        return $this;
    }

    public function setErrors(?array $errors)
    {
        $this->errors = $errors ?? [];
        return $this;
    }

    public function setEffects(?array $effects)
    {
        $this->effects = $effects ?? [];
        return $this;
    }

    public function toArray()
    {
        return [
            'html' => $this->html,
            'state' => $this->state,
            'effects' => $this->effects,
            'errors' => $this->errors,
        ];
    }
}
