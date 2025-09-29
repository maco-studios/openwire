<?php

/**
 * Copyright (c) 2025 MACO
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/maco-studios/openwire
 */

class Maco_Openwire_Model_Component_Registry
{
    const SESSION_KEY = 'openwire_registry';

    /**
     * Use the SessionStore model for session-backed storage.
     * This keeps Registry focused and delegates persistence.
     *
     * @return Mage_Core_Model_Abstract|false
     */
    protected function getStore()
    {
        return Mage::getModel('openwire/sessionStore');
    }

    public function registerComponent($component)
    {
        /** @var Maco_Openwire_Model_SessionStore $store */
        $store = $this->getStore();
        $id = $component->getId() ?: uniqid('openwire_');
        $state = $component->getState();
        // ensure the id is part of serializable state for hydration later
        if (method_exists($component, 'getId')) {
            $state['id'] = $component->getId();
        }
        $entry = [
            'class' => $component::class,
            'state' => $state,
            'created_at' => time(),
        ];
        $store->save($id, $entry);
        return $id;
    }

    public function saveStateById($id, array $state)
    {
        /** @var Maco_Openwire_Model_SessionStore $store */
        $store = $this->getStore();
        $entry = $store->load($id);
        if (!$entry) {
            return false;
        }
        $entry['state'] = $state;
        $entry['updated_at'] = time();
        $store->save($id, $entry);
        return true;
    }

    public function load($id)
    {
        return $this->getStore()->load($id);
    }

    public function loadAll()
    {
        return $this->getStore()->loadAll();
    }

    public function remove($id)
    {
        return $this->getStore()->remove($id);
    }

    /**
     * Prune stale entries from the session-backed registry.
     * Delegates to the SessionStore::prune() method.
     *
     * @param int|null $olderThan Unix timestamp. If null uses default TTL behavior.
     * @return int Number of entries removed
     */
    public function prune($olderThan = null)
    {
        $store = $this->getStore();
        if (!$store) {
            return 0;
        }
        return $store->prune($olderThan);
    }
}
