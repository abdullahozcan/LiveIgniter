<div id="<?= $componentId ?>" class="live-component counter-component" x-data="{
    count: <?= $count ?>,
    message: '<?= esc($message) ?>',
    loading: false,
    tempMessage: ''
}">
    <div class="card">
        <div class="card-header">
            <h3>LiveIgniter Counter Example</h3>
        </div>
        
        <div class="card-body">
            <div class="message mb-3">
                <p x-text="message" class="alert alert-info"></p>
            </div>
            
            <div class="counter-display mb-4 text-center">
                <h1 x-text="count" class="display-4 text-primary"></h1>
                <p class="text-muted">Current Count</p>
            </div>
            
            <div class="counter-controls d-flex justify-content-center gap-2">
                <button 
                    igniter:click="decrement"
                    igniter:target="decrement"
                    class="btn btn-danger"
                >
                    <span igniter:loading="decrement">
                        <i class="spinner-border spinner-border-sm me-1"></i>
                    </span>
                    <span igniter:loading.remove="decrement">-</span>
                </button>
                
                <button 
                    igniter:click="reset"
                    igniter:confirm="Are you sure you want to reset the counter?"
                    igniter:target="reset"
                    class="btn btn-secondary"
                >
                    <span igniter:loading="reset">
                        <i class="spinner-border spinner-border-sm me-1"></i>
                    </span>
                    <span igniter:loading.remove="reset">Reset</span>
                </button>
                
                <button 
                    igniter:click="increment"
                    igniter:target="increment"
                    class="btn btn-success"
                >
                    <span igniter:loading="increment">
                        <i class="spinner-border spinner-border-sm me-1"></i>
                    </span>
                    <span igniter:loading.remove="increment">+</span>
                </button>
            </div>
            
            <div class="mt-4">
                <label for="message-input" class="form-label">Custom Message:</label>
                <div class="input-group">
                    <input 
                        type="text" 
                        id="message-input"
                        class="form-control"
                        placeholder="Enter a message..."
                        igniter:model="tempMessage"
                        igniter:keydown.enter="setMessage"
                    >
                    <button 
                        igniter:click="setMessage"
                        class="btn btn-outline-primary"
                        x-text="tempMessage ? 'Update Message' : 'Enter message first'"
                        :disabled="!tempMessage"
                    >
                    </button>
                </div>
                
                <!-- Dirty indicator -->
                <div igniter:dirty="input" class="text-muted mt-1">
                    <small><i class="fas fa-edit"></i> Message modified</small>
                </div>
            </div>
            
            <!-- Auto-refresh every 30 seconds -->
            <div igniter:poll="30:refresh" class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-sync-alt"></i> Auto-refreshing every 30 seconds
                </small>
            </div>
            
            <!-- Lazy load additional content -->
            <div igniter:lazy="loadAdditionalContent" class="mt-3">
                <div class="alert alert-info">
                    <i class="fas fa-eye"></i> This content loads when visible
                </div>
            </div>
            
            <!-- Offline indicator -->
            <div igniter:offline class="alert alert-warning mt-3">
                <i class="fas fa-wifi-slash me-2"></i>
                You are currently offline. Changes will sync when connection is restored.
            </div>
            
            <!-- Milestone celebration -->
            <div x-show="count >= 10" class="alert alert-success mt-3">
                <i class="fas fa-trophy me-2"></i>
                Congratulations! You've reached the milestone of 10!
            </div>
            
            <!-- Keyboard shortcuts info -->
            <div class="mt-3">
                <small class="text-muted">
                    <strong>Keyboard shortcuts:</strong><br>
                    • Enter in message input: Update message<br>
                    • Space: Quick increment (when focused on increment button)<br>
                    • Escape: Reset counter
                </small>
            </div>
        </div>
        
        <div class="card-footer text-muted text-center">
            <small>
                Component ID: <?= $componentId ?> | 
                LiveIgniter Example Component
            </small>
        </div>
    </div>
</div>

<!-- Additional keyboard shortcuts -->
<div 
    igniter:keydown.space="increment"
    igniter:keydown.escape="reset"
    tabindex="0"
    style="position: absolute; opacity: 0; pointer-events: none;"
></div>

<style>
.counter-component {
    max-width: 500px;
    margin: 2rem auto;
}

.counter-display h1 {
    font-size: 4rem;
    font-weight: bold;
}

.counter-controls button {
    min-width: 80px;
    height: 50px;
    font-size: 1.2rem;
    border-radius: 25px;
}

.counter-controls button.loading {
    opacity: 0.7;
    cursor: not-allowed;
}

.card {
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.live-component[x-cloak] {
    display: none !important;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.counter-display h1:hover {
    animation: pulse 0.5s ease-in-out;
}

/* Loading state styles */
.loading .spinner-border {
    display: inline-block !important;
}

.btn.loading {
    position: relative;
}

.btn.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.2);
    border-radius: inherit;
}
</style>
