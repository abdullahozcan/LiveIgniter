<?php

namespace LiveIgniter;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\View\RendererInterface;

/**
 * LiveComponent Base Class
 * 
 * Base class for all LiveIgniter reactive components
 */
abstract class LiveComponent
{
    /**
     * Component properties
     */
    protected array $properties = [];
    
    /**
     * Component ID
     */
    protected string $componentId;
    
    /**
     * View renderer
     */
    protected RendererInterface $renderer;
    
    /**
     * Component manager instance
     */
    protected ComponentManager $manager;
    
    public function __construct()
    {
        $this->componentId = $this->generateComponentId();
        $this->renderer = service('renderer');
        $this->manager = service('liveigniter.manager');
        
        $this->mount();
    }
    
    /**
     * Component initialization
     */
    public function mount(): void
    {
        // Override in child components
    }
    
    /**
     * Render the component
     */
    public function render(): string
    {
        $viewName = $this->getViewName();
        $data = array_merge($this->properties, [
            'componentId' => $this->componentId,
            'component' => $this
        ]);
        
        return $this->renderer->setData($data)->render($viewName);
    }
    
    /**
     * Get component view name
     */
    protected function getViewName(): string
    {
        $className = get_class($this);
        $shortName = substr($className, strrpos($className, '\\') + 1);
        
        return 'components/' . strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $shortName));
    }
    
    /**
     * Generate unique component ID
     */
    protected function generateComponentId(): string
    {
        return 'live-' . uniqid();
    }
    
    /**
     * Set component property
     */
    public function __set(string $name, $value): void
    {
        $this->properties[$name] = $value;
    }
    
    /**
     * Get component property
     */
    public function __get(string $name)
    {
        return $this->properties[$name] ?? null;
    }
    
    /**
     * Check if property exists
     */
    public function __isset(string $name): bool
    {
        return isset($this->properties[$name]);
    }
    
    /**
     * Get all properties
     */
    public function getProperties(): array
    {
        return $this->properties;
    }
    
    /**
     * Set multiple properties
     */
    public function setProperties(array $properties): self
    {
        $this->properties = array_merge($this->properties, $properties);
        return $this;
    }
    
    /**
     * Call component method (for AJAX requests)
     */
    public function callMethod(string $method, array $params = []): array
    {
        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException("Method {$method} does not exist");
        }
        
        // Call the method
        $result = call_user_func_array([$this, $method], $params);
        
        // Return updated component state
        return [
            'html' => $this->render(),
            'properties' => $this->properties,
            'result' => $result
        ];
    }
    
    /**
     * Emit event to other components
     */
    protected function emit(string $event, ...$params): void
    {
        $this->manager->emit($event, $params);
    }
    
    /**
     * Listen to events from other components
     */
    protected function listen(string $event, callable $callback): void
    {
        $this->manager->listen($this->componentId, $event, $callback);
    }
}
