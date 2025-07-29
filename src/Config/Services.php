<?php

namespace LiveIgniter\Config;

use CodeIgniter\Config\BaseService;
use LiveIgniter\ComponentManager;

/**
 * LiveIgniter Services
 */
class Services extends BaseService
{
    /**
     * Get LiveIgniter Component Manager instance
     */
    public static function liveigniterManager(bool $getShared = true): ComponentManager
    {
        if ($getShared) {
            return static::getSharedInstance('liveigniterManager');
        }
        
        return new ComponentManager();
    }
    
    /**
     * Get component manager (alias)
     */
    public static function componentManager(bool $getShared = true): ComponentManager
    {
        return static::liveigniterManager($getShared);
    }
}
