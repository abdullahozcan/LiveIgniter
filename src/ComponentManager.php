<?php

namespace LiveIgniter;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Session\SessionInterface;

/**
 * ComponentManager Class
 * 
 * Manages component lifecycle, method calls, and rendering
 */
class ComponentManager
{
    /**
     * Request instance
     */
    protected RequestInterface $request;
    
    /**
     * Response instance
     */
    protected ResponseInterface $response;
    
    /**
     * Session instance
     */
    protected SessionInterface $session;
    
    /**
     * Component instances registry
     */
    protected array $components = [];
    
    /**
     * Event listeners
     */
    protected array $listeners = [];
    
    /**
     * Emitted events queue
     */
    protected array $events = [];
    
    public function __construct()
    {
        $this->request = service('request');
        $this->response = service('response');
        $this->session = service('session');
    }
    
    /**
     * Create and register a component
     */
    public function create(string $componentClass, array $properties = []): LiveComponent
    {
        if (!class_exists($componentClass)) {
            throw new \InvalidArgumentException("Component class {$componentClass} does not exist");
        }
        
        if (!is_subclass_of($componentClass, LiveComponent::class)) {
            throw new \InvalidArgumentException("Component class must extend LiveComponent");
        }
        
        $component = new $componentClass();
        $component->setProperties($properties);
        
        $this->components[$component->componentId] = $component;
        
        return $component;
    }
    
    /**
     * Get component by ID
     */
    public function getComponent(string $componentId): ?LiveComponent
    {
        return $this->components[$componentId] ?? null;
    }
    
    /**
     * Handle AJAX component method call
     */
    public function handleAjaxCall(): ResponseInterface
    {
        $componentId = $this->request->getPost('componentId');
        $method = $this->request->getPost('method');
        $params = $this->request->getPost('params', []);
        
        if (!$componentId || !$method) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'Missing componentId or method'
            ]);
        }
        
        // Restore component state from session
        $component = $this->restoreComponent($componentId);
        
        if (!$component) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => 'Component not found'
            ]);
        }
        
        try {
            $result = $component->callMethod($method, $params);
            
            // Save component state to session
            $this->saveComponent($component);
            
            // Process any emitted events
            $this->processEvents();
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $result,
                'events' => $this->events
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Save component state to session
     */
    protected function saveComponent(LiveComponent $component): void
    {
        $componentData = [
            'class' => get_class($component),
            'properties' => $component->getProperties(),
            'componentId' => $component->componentId
        ];
        
        $this->session->set("liveigniter.components.{$component->componentId}", $componentData);
    }
    
    /**
     * Restore component from session
     */
    protected function restoreComponent(string $componentId): ?LiveComponent
    {
        $componentData = $this->session->get("liveigniter.components.{$componentId}");
        
        if (!$componentData) {
            return null;
        }
        
        $componentClass = $componentData['class'];
        
        if (!class_exists($componentClass)) {
            return null;
        }
        
        $component = new $componentClass();
        $component->setProperties($componentData['properties']);
        $component->componentId = $componentData['componentId'];
        
        $this->components[$componentId] = $component;
        
        return $component;
    }
    
    /**
     * Emit event to other components
     */
    public function emit(string $event, array $params = []): void
    {
        $this->events[] = [
            'name' => $event,
            'params' => $params,
            'timestamp' => time()
        ];
    }
    
    /**
     * Register event listener
     */
    public function listen(string $componentId, string $event, callable $callback): void
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }
        
        $this->listeners[$event][] = [
            'componentId' => $componentId,
            'callback' => $callback
        ];
    }
    
    /**
     * Process emitted events
     */
    protected function processEvents(): void
    {
        foreach ($this->events as $event) {
            $eventName = $event['name'];
            
            if (isset($this->listeners[$eventName])) {
                foreach ($this->listeners[$eventName] as $listener) {
                    $component = $this->getComponent($listener['componentId']);
                    
                    if ($component) {
                        call_user_func($listener['callback'], ...$event['params']);
                        $this->saveComponent($component);
                    }
                }
            }
        }
    }
    
    /**
     * Render component directive for views
     */
    public function renderDirective(string $componentClass, array $properties = []): string
    {
        $component = $this->create($componentClass, $properties);
        $this->saveComponent($component);
        
        return $component->render();
    }
    
    /**
     * Clean up expired component sessions
     */
    public function cleanup(int $maxAge = 3600): void
    {
        // Implementation for cleaning up old component sessions
        // This would typically be called via a scheduled task
    }
}
