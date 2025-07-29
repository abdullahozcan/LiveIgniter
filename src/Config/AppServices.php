<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use LiveIgniter\ComponentManager;

/**
 * Services Configuration File.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or services that
 * extend the system services.
 */
class Services extends \CodeIgniter\Config\Services
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
}
