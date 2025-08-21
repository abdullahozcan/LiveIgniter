<div id="<?= $componentId ?>" data-component-id="<?= $componentId ?>"<?= live_component_data(compact('count', 'message', 'tempMessage', 'additionalContentLoaded')) ?>>
    <div class="card" style="max-width: 400px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
        <h3>LiveIgniter Counter Example</h3>
        
        <!-- Display counter value with Alpine reactivity -->
        <div style="text-align: center; margin: 20px 0;">
            <span x-text="count" style="font-size: 2em; font-weight: bold; color: #333;">
                <?= $count ?>
            </span>
        </div>
        
        <!-- Action buttons using live_igniter helper -->
        <div style="text-align: center; margin: 15px 0;">
            <button<?= live_igniter('increment') ?>
                    style="background: #28a745; color: white; border: none; padding: 8px 16px; margin: 0 5px; border-radius: 4px; cursor: pointer;">
                +1
            </button>
            
            <button<?= live_igniter('decrement') ?>
                    style="background: #dc3545; color: white; border: none; padding: 8px 16px; margin: 0 5px; border-radius: 4px; cursor: pointer;">
                -1
            </button>
            
            <button<?= live_igniter('reset') ?>
                    style="background: #6c757d; color: white; border: none; padding: 8px 16px; margin: 0 5px; border-radius: 4px; cursor: pointer;">
                Reset
            </button>
        </div>
        
        <!-- Message display with Alpine reactivity -->
        <div style="margin: 15px 0; padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong>Message:</strong> <span x-text="message"><?= esc($message) ?></span>
        </div>
        
        <!-- Input with x-igniter-model (two-way binding) -->
        <div style="margin: 15px 0;">
            <label>Update message:</label>
            <input type="text" 
                   x-igniter-model="tempMessage"
                   x-model="tempMessage"
                   value="<?= esc($tempMessage) ?>"
                   style="width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px;"
                   placeholder="Type a new message...">
            
            <button<?= live_igniter('setMessage') ?>
                    x-bind:disabled="!tempMessage"
                    style="background: #007bff; color: white; border: none; padding: 8px 16px; margin: 5px 0; border-radius: 4px; cursor: pointer; width: 100%;">
                <span x-text="tempMessage ? 'Update Message' : 'Type a message first'">Update Message</span>
            </button>
        </div>
        
        <!-- Conditional content with Alpine reactivity -->
        <div x-show="count >= 5" x-transition style="margin: 15px 0; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">
            🎉 Great! You've reached <span x-text="count"></span> clicks!
        </div>
        
        <!-- Load additional content button with Alpine reactivity -->
        <div x-show="!additionalContentLoaded">
            <button<?= live_igniter('loadAdditionalContent') ?>
                    style="background: #17a2b8; color: white; border: none; padding: 8px 16px; margin: 5px 0; border-radius: 4px; cursor: pointer; width: 100%;">
                Load Additional Content
            </button>
        </div>
        
        <div x-show="additionalContentLoaded" x-transition style="margin: 15px 0; padding: 10px; background: #bee5eb; border: 1px solid #86cfda; border-radius: 4px; color: #0c5460;">
            <h4>Additional Content Loaded!</h4>
            <p>This content was loaded dynamically using LiveIgniter.</p>
            <ul>
                <li>Current count: <span x-text="count"></span></li>
                <li>Component ID: <?= $componentId ?></li>
                <li>Server time: <?= date('Y-m-d H:i:s') ?></li>
            </ul>
        </div>
        
        <!-- Debug info with Alpine reactivity -->
        <details style="margin-top: 20px;">
            <summary style="cursor: pointer; color: #666;">Debug Info</summary>
            <div style="margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 4px; font-family: monospace; font-size: 12px;">
                <strong>Component Properties (Live):</strong><br>
                - count: <span x-text="count"></span><br>
                - message: <span x-text="message"></span><br>
                - tempMessage: <span x-text="tempMessage"></span><br>
                - additionalContentLoaded: <span x-text="additionalContentLoaded"></span><br>
                - loading: <span x-text="loading"></span><br>
                - componentId: <?= $componentId ?>
            </div>
        </details>
    </div>
</div>
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
