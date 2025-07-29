<?php

namespace LiveIgniter\Events;

use CodeIgniter\Events\Events;

/**
 * LiveIgniter Event Registration
 * 
 * Registers LiveIgniter commands and other events
 */
class LiveIgniterEvents
{
    /**
     * Register LiveIgniter events
     */
    public static function register(): void
    {
        // Register commands when CodeIgniter starts
        Events::on('pre_system', static function () {
            if (class_exists('CodeIgniter\CLI\CLI') && is_cli()) {
                static::registerCommands();
            }
        });
    }

    /**
     * Register LiveIgniter commands
     */
    protected static function registerCommands(): void
    {
        $commands = service('commands');

        $liveIgniterCommands = [
            'liveigniter:make' => \LiveIgniter\Commands\MakeComponent::class,
            'liveigniter:install' => \LiveIgniter\Commands\Install::class,
            'liveigniter:publish' => \LiveIgniter\Commands\Publish::class,
            'liveigniter:list' => \LiveIgniter\Commands\ListComponents::class,
            'liveigniter:clean' => \LiveIgniter\Commands\Clean::class,
        ];

        foreach ($liveIgniterCommands as $name => $class) {
            if (class_exists($class)) {
                $commands->add($name, $class);
            }
        }
    }
}
