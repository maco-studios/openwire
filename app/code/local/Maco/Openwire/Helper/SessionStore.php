<?php

/**
 * Copyright (c) 2025 MACO
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/maco-studios/openwire
 */

class Maco_Openwire_Helper_SessionStore
{
    protected $key = 'openwire_registry';
    /**
     * Default TTL for entries (seconds). Null means no TTL.
     * Can be overridden by passing TTL to save if desired.
     */
    protected $defaultTtl = 86400; // 1 day

    protected function session()
    {
        return Mage::getSingleton('core/session');
    }

    public function loadAll()
    {
        $data = $this->session()->getData($this->key) ?: [];

        // Prune expired entries on load
        $now = time();
        $changed = false;
        foreach ($data as $id => $entry) {
            if (isset($entry['created_at']) || isset($entry['ttl'])) {
                $created = isset($entry['created_at']) ? (int) $entry['created_at'] : 0;
                $ttl = isset($entry['ttl']) ? (int) $entry['ttl'] : $this->defaultTtl;
                if ($ttl > 0 && ($created + $ttl) < $now) {
                    unset($data[$id]);
                    $changed = true;
                }
            }
        }

        if ($changed) {
            $this->saveAll($data);
        }

        return $data;
    }

    public function saveAll(array $data)
    {
        $this->session()->setData($this->key, $data);
        return $this;
    }

    public function load($id)
    {
        $all = $this->loadAll();
        return $all[$id] ?? null;
    }

    public function save($id, array $entry)
    {
        $all = $this->loadAll();

        // Ensure timestamps and TTL exist
        if (!isset($entry['created_at'])) {
            $entry['created_at'] = time();
        }
        if (!isset($entry['ttl'])) {
            $entry['ttl'] = $this->defaultTtl;
        }

        $all[$id] = $entry;
        $this->saveAll($all);
        return $this;
    }

    /**
     * Remove any entries older than provided $olderThan (seconds since epoch).
     * If $olderThan is null, use default TTL pruning behavior.
     *
     * @param int|null $olderThan
     * @return int Number of entries removed
     */
    public function prune($olderThan = null)
    {
        $all = $this->loadAll();
        $removed = 0;
        $now = time();

        foreach ($all as $id => $entry) {
            $created = isset($entry['created_at']) ? (int) $entry['created_at'] : 0;
            $ttl = isset($entry['ttl']) ? (int) $entry['ttl'] : $this->defaultTtl;

            $shouldRemove = false;
            if ($olderThan !== null) {
                if ($created < $olderThan) {
                    $shouldRemove = true;
                }
            } elseif ($ttl > 0 && ($created + $ttl) < $now) {
                $shouldRemove = true;
            }

            if ($shouldRemove) {
                unset($all[$id]);
                $removed++;
            }
        }

        if ($removed > 0) {
            $this->saveAll($all);
        }

        return $removed;
    }

    public function remove($id)
    {
        $all = $this->loadAll();
        if (isset($all[$id])) {
            unset($all[$id]);
            $this->saveAll($all);
            return true;
        }
        return false;
    }
}
