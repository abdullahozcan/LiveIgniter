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

if (!function_exists('live_wire')) {
    /**
     * Generate wire directive attributes for HTML elements
     * 
     * @param string $method Method name to call
     * @param array $params Parameters to pass
     * @return string HTML attributes
     */
    function live_wire(string $method, array $params = []): string
    {
        $attributes = ['x-on:click' => "callLiveMethod('{$method}', " . json_encode($params) . ")"];
        
        $result = '';
        foreach ($attributes as $key => $value) {
            $result .= " {$key}=\"{$value}\"";
        }
        
        return $result;
    }
}

if (!function_exists('live_model')) {
    /**
     * Generate model binding directive for form inputs
     * 
     * @param string $property Property name to bind
     * @return string HTML attributes
     */
    function live_model(string $property): string
    {
        return " x-model=\"{$property}\" x-on:input=\"updateProperty('{$property}', \$event.target.value)\"";
    }
}

if (!function_exists('live_loading')) {
    /**
     * Generate loading state directive
     * 
     * @param string $target Target method or property
     * @return string HTML attributes
     */
    function live_loading(string $target = ''): string
    {
        $directive = $target ? "x-show=\"loading === '{$target}'\"" : "x-show=\"loading\"";
        return " {$directive}";
    }
}

if (!function_exists('live_offline')) {
    /**
     * Generate offline state directive
     * 
     * @return string HTML attributes
     */
    function live_offline(): string
    {
        return " x-show=\"offline\"";
    }
}

if (!function_exists('live_ignore')) {
    /**
     * Generate ignore directive to prevent LiveIgniter processing
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
     * Generate key directive for list items
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
     * Generate polling directive
     * 
     * @param int $interval Polling interval in milliseconds
     * @param string $method Method to call
     * @return string HTML attributes
     */
    function live_poll(int $interval, string $method = 'refresh'): string
    {
        return " x-init=\"setInterval(() => callLiveMethod('{$method}'), {$interval})\"";
    }
}

if (!function_exists('live_lazy')) {
    /**
     * Generate lazy loading directive
     * 
     * @param string $method Method to call when element becomes visible
     * @return string HTML attributes
     */
    function live_lazy(string $method): string
    {
        return " x-intersect=\"callLiveMethod('{$method}')\"";
    }
}
