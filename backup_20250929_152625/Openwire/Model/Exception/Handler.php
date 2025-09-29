<?php

/**
 * Copyright (c) 2025 MACO
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/maco-studios/openwire
 */

class Maco_Openwire_Model_Exception_Handler
{
    public function handle(\Throwable $e)
    {
        Mage::logException($e);
        // Map some exceptions to user-friendly messages or codes later
        return ['message' => $e->getMessage()];
    }
}
