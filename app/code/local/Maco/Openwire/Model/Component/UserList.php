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
 * User List component - demonstrates nested components
 */
class Maco_Openwire_Model_Component_UserList extends Maco_Openwire_Model_Component
{
    /**
     * Mount initial parameters
     */
    public function mount($params = [])
    {
        parent::mount($params);

        // Set default values
        if (!isset($params['users'])) {
            $this->setData('users', [
                ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'online' => true],
                ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'online' => false],
                ['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com', 'online' => true],
            ]);
        }

        if (!isset($params['selectedUserId'])) {
            $this->setData('selectedUserId', null);
        }

        if (!isset($params['showOnlineOnly'])) {
            $this->setData('showOnlineOnly', false);
        }

        return $this;
    }

    /**
     * Add a new user
     */
    public function addUser($name, $email)
    {
        if (!empty($name) && !empty($email)) {
            $users = $this->getData('users');
            $newId = max(array_column($users, 'id')) + 1;

            $users[] = [
                'id' => $newId,
                'name' => $name,
                'email' => $email,
                'online' => false
            ];

            $this->setData('users', $users);
        }
        return $this;
    }

    /**
     * Remove a user
     */
    public function removeUser($userId)
    {
        $users = $this->getData('users');
        $users = array_filter($users, function($user) use ($userId) {
            return $user['id'] != $userId;
        });
        $this->setData('users', array_values($users));
        return $this;
    }

    /**
     * Select a user
     */
    public function selectUser($userId)
    {
        $this->setData('selectedUserId', $userId);
        return $this;
    }

    /**
     * Toggle online filter
     */
    public function toggleOnlineFilter()
    {
        $current = $this->getData('showOnlineOnly');
        $this->setData('showOnlineOnly', !$current);
        return $this;
    }

    /**
     * Get filtered users
     */
    public function getFilteredUsers()
    {
        $users = $this->getData('users');

        if ($this->getData('showOnlineOnly')) {
            $users = array_filter($users, function($user) {
                return $user['online'];
            });
        }

        return $users;
    }

    /**
     * Get selected user
     */
    public function getSelectedUser()
    {
        $selectedId = $this->getData('selectedUserId');
        if (!$selectedId) {
            return null;
        }

        $users = $this->getData('users');
        foreach ($users as $user) {
            if ($user['id'] == $selectedId) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Get user count
     */
    public function getUserCount()
    {
        return count($this->getFilteredUsers());
    }

    /**
     * Get template path
     */
    public function getTemplate()
    {
        return 'openwire/user_list.phtml';
    }
}
