<?php

namespace LiveIgniter\Config;

use CodeIgniter\Config\BaseService;

/**
 * LiveIgniter Command Services
 * 
 * Register LiveIgniter commands with CodeIgniter
 */
class CommandServices extends BaseService
{
    /**
     * Register LiveIgniter commands
     */
    public static function registerCommands(): void
    {
        $commands = [
            'liveigniter:make' => \LiveIgniter\Commands\MakeComponent::class,
            'liveigniter:install' => \LiveIgniter\Commands\Install::class,
            'liveigniter:publish' => \LiveIgniter\Commands\Publish::class,
            'liveigniter:list' => \LiveIgniter\Commands\ListComponents::class,
            'liveigniter:clean' => \LiveIgniter\Commands\Clean::class,
        ];

        // Register commands with CodeIgniter's command runner
        $runner = service('commands');
        
        foreach ($commands as $name => $class) {
            if (class_exists($class)) {
                $runner->add($name, $class);
            }
        }
    }
}
