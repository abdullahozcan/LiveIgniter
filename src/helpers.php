<?php

use LiveIgniter\ComponentManager;

if (!function_exists('liveigniter')) {
    /**
     * Get LiveIgniter component manager instance
     */
    function liveigniter(): ComponentManager
    {
        return \Config\Services::liveigniterManager();
    }
}

if (!function_exists('live_component')) {
    /**
     * Render a LiveIgniter component
     * 
     * @param string $componentClass Component class name
     * @param array $properties Initial properties
     * @return string Rendered component HTML
     */
    function live_component(string $componentClass, array $properties = []): string
    {
        return liveigniter()->renderDirective($componentClass, $properties);
    }
}

if (!function_exists('live_emit')) {
    /**
     * Emit an event to other components
     * 
     * @param string $event Event name
     * @param mixed ...$params Event parameters
     */
    function live_emit(string $event, ...$params): void
    {
        liveigniter()->emit($event, $params);
    }
}

// Backwards compatibility - these will generate the old-style attributes
if (!function_exists('live_wire')) {
    /**
     * Generate wire directive attributes for HTML elements (legacy)
     * Use igniter:click="method" directly instead
     * 
     * @param string $method Method name to call
     * @param array $params Parameters to pass
     * @return string HTML attributes
     */
    function live_wire(string $method, array $params = []): string
    {
        $methodCall = $method;
        if (!empty($params)) {
            $methodCall .= '(' . implode(', ', array_map(function($param) {
                return is_string($param) ? "'{$param}'" : $param;
            }, $params)) . ')';
        }
        
        return " igniter:click=\"{$methodCall}\"";
    }
}

if (!function_exists('live_model')) {
    /**
     * Generate model binding directive for form inputs (legacy)
     * Use igniter:model="property" directly instead
     * 
     * @param string $property Property name to bind
     * @return string HTML attributes
     */
    function live_model(string $property): string
    {
        return " igniter:model=\"{$property}\"";
    }
}

if (!function_exists('live_loading')) {
    /**
     * Generate loading state directive (legacy)
     * Use igniter:loading="method" directly instead
     * 
     * @param string $target Target method or property
     * @return string HTML attributes
     */
    function live_loading(string $target = 'any'): string
    {
        return " igniter:loading=\"{$target}\"";
    }
}

if (!function_exists('live_offline')) {
    /**
     * Generate offline state directive (legacy)
     * Use igniter:offline directly instead
     * 
     * @return string HTML attributes
     */
    function live_offline(): string
    {
        return " igniter:offline";
    }
}

if (!function_exists('live_ignore')) {
    /**
     * Generate ignore directive to prevent LiveIgniter processing (legacy)
     * 
     * @return string HTML attributes
     */
    function live_ignore(): string
    {
        return " x-ignore";
    }
}

if (!function_exists('live_key')) {
    /**
     * Generate key directive for list items (legacy)
     * 
     * @param mixed $key Unique key for the item
     * @return string HTML attributes
     */
    function live_key($key): string
    {
        return " x-key=\"{$key}\"";
    }
}

if (!function_exists('live_poll')) {
    /**
     * Generate polling directive (legacy)
     * Use igniter:poll="30:refresh" directly instead
     * 
     * @param int $interval Polling interval in seconds
     * @param string $method Method to call
     * @return string HTML attributes
     */
    function live_poll(int $interval, string $method = 'refresh'): string
    {
        return " igniter:poll=\"{$interval}:{$method}\"";
    }
}

if (!function_exists('live_lazy')) {
    /**
     * Generate lazy loading directive (legacy)
     * Use igniter:lazy="method" directly instead
     * 
     * @param string $method Method to call when element becomes visible
     * @return string HTML attributes
     */
    function live_lazy(string $method): string
    {
        return " igniter:lazy=\"{$method}\"";
    }
}
