<?php

namespace App\LiveComponents;

use LiveIgniter\LiveComponent;

/**
 * Example Counter Component
 * 
 * A simple counter component to demonstrate LiveIgniter functionality
 */
class Counter extends LiveComponent
{
    public int $count = 0;
    public string $message = 'Hello from LiveIgniter!';
    public string $tempMessage = '';
    public bool $additionalContentLoaded = false;
    
    public function mount(): void
    {
        // Component initialization
        $this->count = 0;
    }
    
    public function increment(): void
    {
        $this->count++;
        
        // Emit event when count reaches 10
        if ($this->count === 10) {
            $this->emit('counter.milestone', $this->count);
        }
    }
    
    public function decrement(): void
    {
        if ($this->count > 0) {
            $this->count--;
        }
    }
    
    public function reset(): void
    {
        $this->count = 0;
        $this->message = 'Counter reset!';
        $this->additionalContentLoaded = false;
    }
    
    public function setMessage(?string $message = null): void
    {
        // Use tempMessage if no parameter provided
        $newMessage = $message ?? $this->tempMessage;
        
        if (!empty($newMessage)) {
            $this->message = $newMessage;
            $this->tempMessage = ''; // Clear the temp message
        }
    }
    
    public function updateProperty(string $property, $value): void
    {
        // Update component property dynamically
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }
    
    public function refresh(): void
    {
        // Refresh component data - could fetch from database
        $this->message = 'Refreshed at ' . date('H:i:s');
    }
    
    public function loadAdditionalContent(): void
    {
        // Simulate loading additional content
        if (!$this->additionalContentLoaded) {
            $this->additionalContentLoaded = true;
            $this->message = 'Additional content loaded!';
        }
    }
    
    public function updateMessage(string $newMessage): void
    {
        $this->message = $newMessage;
    }
    
    /**
     * Handle form submission
     */
    public function handleFormSubmit(array $formData): void
    {
        if (isset($formData['message'])) {
            $this->setMessage($formData['message']);
        }
    }
    
    /**
     * Handle keyboard shortcut
     */
    public function handleKeyPress(string $key): void
    {
        switch ($key) {
            case 'Enter':
                $this->increment();
                break;
            case 'Escape':
                $this->reset();
                break;
            case ' ': // Space
                $this->increment();
                break;
        }
    }
}
