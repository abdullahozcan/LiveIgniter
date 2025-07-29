<?php

namespace LiveIgniter\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * LiveIgniter Configuration
 */
class LiveIgniter extends BaseConfig
{
    /**
     * Session key prefix for components
     */
    public string $sessionPrefix = 'liveigniter.components.';
    
    /**
     * Maximum component session lifetime in seconds
     */
    public int $sessionLifetime = 3600;
    
    /**
     * Enable debug mode
     */
    public bool $debug = false;
    
    /**
     * Assets URL path
     */
    public string $assetsUrl = '/liveigniter/assets/';
    
    /**
     * AJAX endpoint URL
     */
    public string $ajaxUrl = '/liveigniter/call';
    
    /**
     * Default component view path
     */
    public string $viewPath = 'components/';
    
    /**
     * Component method call timeout in seconds
     */
    public int $timeout = 30;
    
    /**
     * Enable component caching
     */
    public bool $enableCache = true;
    
    /**
     * Cache TTL in seconds
     */
    public int $cacheTtl = 300;
    
    /**
     * Allowed component methods patterns
     */
    public array $allowedMethods = [
        '/^[a-zA-Z_][a-zA-Z0-9_]*$/', // Standard method names
        '/^on[A-Z][a-zA-Z0-9]*$/',   // Event handlers (onSomething)
        '/^handle[A-Z][a-zA-Z0-9]*$/' // Action handlers (handleSomething)
    ];
    
    /**
     * Blocked component methods
     */
    public array $blockedMethods = [
        '__construct',
        '__destruct',
        '__call',
        '__callStatic',
        '__get',
        '__set',
        '__isset',
        '__unset',
        '__sleep',
        '__wakeup',
        '__toString',
        '__invoke',
        '__set_state',
        '__clone',
        '__debugInfo'
    ];
    
    /**
     * Rate limiting settings
     */
    public array $rateLimit = [
        'enabled' => true,
        'max_requests' => 60,
        'time_window' => 60 // seconds
    ];
    
    /**
     * Security settings
     */
    public array $security = [
        'csrf_protection' => true,
        'validate_origin' => true,
        'allowed_origins' => [], // Empty means same origin only
        'sanitize_input' => true
    ];
}
