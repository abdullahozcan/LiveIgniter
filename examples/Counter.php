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
    }
    
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
