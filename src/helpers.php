<?php

use LiveIgniter\ComponentManager;

if (!function_exists('liveigniter')) {
    /**
     * Get LiveIgniter component manager instance
     */
    function liveigniter(): ComponentManager
    {
        try {
            // Try CodeIgniter's service container first
            if (function_exists('service')) {
                return service('liveigniterManager');
            }
            
            // Fallback to our custom services
            return \LiveIgniter\Config\Services::liveigniterManager();
        } catch (\Exception $e) {
            // Final fallback to creating a new instance
            return new ComponentManager();
        }
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

if (!function_exists('live_data')) {
    /**
     * Generate x-data attribute with component properties
     * Automatically extracts all public properties from the current component context
     * 
     * @param array $additionalData Additional data to merge
     * @return string HTML x-data attribute
     */
    function live_data(array $additionalData = []): string
    {
        // Get all defined variables in the current template context
        $templateVars = get_defined_vars();
        
        // Filter out system variables and keep only component data
        $componentData = [];
        $systemVars = ['_SERVER', '_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_ENV', 'GLOBALS', 'componentId'];
        
        foreach ($templateVars as $key => $value) {
            if (!in_array($key, $systemVars) && !str_starts_with($key, '_') && !str_starts_with($key, '__')) {
                // Convert objects to arrays, handle different data types
                if (is_object($value)) {
                    continue; // Skip objects for now
                } elseif (is_array($value)) {
                    $componentData[$key] = $value;
                } elseif (is_scalar($value) || is_null($value)) {
                    $componentData[$key] = $value;
                }
            }
        }
        
        // Merge with additional data
        $componentData = array_merge($componentData, $additionalData);
        
        // Add LiveIgniter specific properties
        $componentData['loading'] = false;
        $componentData['offline'] = false;
        
        // Convert to JSON and escape for HTML
        $jsonData = json_encode($componentData, JSON_HEX_QUOT | JSON_HEX_APOS);
        
        return " x-data='{$jsonData}'";
    }
}

if (!function_exists('live_component_data')) {
    /**
     * Extract component properties as JSON for x-data
     * More explicit version that takes variables as parameters
     * 
     * @param array $properties Associative array of properties
     * @param array $additionalData Additional data to merge
     * @return string HTML x-data attribute
     */
    function live_component_data(array $properties, array $additionalData = []): string
    {
        // Merge properties with additional data
        $data = array_merge($properties, $additionalData);
        
        // Add LiveIgniter specific properties
        $data['loading'] = false;
        $data['offline'] = false;
        
        // Convert to JSON and escape for HTML
        $jsonData = json_encode($data, JSON_HEX_QUOT | JSON_HEX_APOS);
        
        return " x-data='{$jsonData}'";
    }
}

// New Alpine.js x-igniter-* directive helpers
if (!function_exists('live_igniter')) {
    /**
     * Generate igniter directive attributes for HTML elements
     * Use x-igniter-click="method" instead of igniter:click="method"
     * 
     * @param string $method Method name to call
     * @param array $params Parameters to pass
     * @return string HTML attributes
     */
    function live_igniter(string $method, array $params = []): string
    {
        $methodCall = $method;
        if (!empty($params)) {
            $methodCall .= '(' . implode(', ', array_map(function($param) {
                return is_string($param) ? "'{$param}'" : $param;
            }, $params)) . ')';
        }
        
        return " x-igniter-click=\"{$methodCall}\"";
    }
}

// Backwards compatibility - keep live_wire as alias
if (!function_exists('live_wire')) {
    /**
     * Alias for live_igniter (backwards compatibility)
     * @deprecated Use live_igniter() instead
     */
    function live_wire(string $method, array $params = []): string
    {
        return live_igniter($method, $params);
    }
}

if (!function_exists('live_model')) {
    /**
     * Generate model binding directive for form inputs
     * Use x-igniter-model="property" instead of igniter:model="property"
     * 
     * @param string $property Property name to bind
     * @return string HTML attributes
     */
    function live_model(string $property): string
    {
        return " x-igniter-model=\"{$property}\"";
    }
}

if (!function_exists('live_loading')) {
    /**
     * Generate loading state directive
     * Use x-igniter-loading="method" instead of igniter:loading="method"
     * 
     * @param string $target Target method or property
     * @return string HTML attributes
     */
    function live_loading(string $target = 'any'): string
    {
        return " x-igniter-loading=\"{$target}\"";
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
