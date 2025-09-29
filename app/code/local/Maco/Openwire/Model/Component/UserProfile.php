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
 * User Profile component - can be used as a child component
 */
class Maco_Openwire_Model_Component_UserProfile extends Maco_Openwire_Model_Component
{
    /**
     * Mount initial parameters
     */
    public function mount($params = [])
    {
        parent::mount($params);

        // Set default values
        if (!isset($params['userId'])) {
            $this->setData('userId', 0);
        }
        if (!isset($params['userName'])) {
            $this->setData('userName', 'Guest');
        }
        if (!isset($params['userEmail'])) {
            $this->setData('userEmail', '');
        }
        if (!isset($params['avatar'])) {
            $this->setData('avatar', '/skin/frontend/base/default/images/avatar-default.png');
        }
        if (!isset($params['isOnline'])) {
            $this->setData('isOnline', false);
        }

        return $this;
    }

    /**
     * Toggle online status
     */
    public function toggleOnlineStatus()
    {
        $currentStatus = $this->getData('isOnline');
        $this->setData('isOnline', !$currentStatus);
        return $this;
    }

    /**
     * Update user name
     */
    public function updateUserName($newName)
    {
        if (!empty($newName)) {
            $this->setData('userName', $newName);
        }
        return $this;
    }

    /**
     * Get user display name
     */
    public function getDisplayName()
    {
        $name = $this->getData('userName');
        $isOnline = $this->getData('isOnline');
        return $name . ($isOnline ? ' (Online)' : ' (Offline)');
    }

    /**
     * Get online status class
     */
    public function getOnlineStatusClass()
    {
        return $this->getData('isOnline') ? 'online' : 'offline';
    }

    /**
     * Get template path
     */
    public function getTemplate()
    {
        return 'openwire/user_profile.phtml';
    }
}
