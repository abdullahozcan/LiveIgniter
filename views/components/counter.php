<div id="<?= $componentId ?>" class="live-component counter-component" x-data="{
    count: <?= $count ?>,
    message: '<?= esc($message) ?>',
    loading: false
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
                    <?= live_wire('decrement') ?>
                    class="btn btn-danger"
                    :disabled="count <= 0 || loading"
                >
                    <span <?= live_loading('decrement') ?>>
                        <i class="spinner-border spinner-border-sm me-1"></i>
                    </span>
                    <span x-show="!loading || loading !== 'decrement'">-</span>
                </button>
                
                <button 
                    <?= live_wire('reset') ?>
                    class="btn btn-secondary"
                    :disabled="loading"
                >
                    <span <?= live_loading('reset') ?>>
                        <i class="spinner-border spinner-border-sm me-1"></i>
                    </span>
                    <span x-show="!loading || loading !== 'reset'">Reset</span>
                </button>
                
                <button 
                    <?= live_wire('increment') ?>
                    class="btn btn-success"
                    :disabled="loading"
                >
                    <span <?= live_loading('increment') ?>>
                        <i class="spinner-border spinner-border-sm me-1"></i>
                    </span>
                    <span x-show="!loading || loading !== 'increment'">+</span>
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
                        <?= live_model('tempMessage') ?>
                    >
                    <button 
                        <?= live_wire('setMessage', ['$event.target.previousElementSibling.value']) ?>
                        class="btn btn-outline-primary"
                    >
                        Update Message
                    </button>
                </div>
            </div>
            
            <!-- Offline indicator -->
            <div <?= live_offline() ?> class="alert alert-warning mt-3">
                <i class="fas fa-wifi-slash me-2"></i>
                You are currently offline. Changes will sync when connection is restored.
            </div>
            
            <!-- Milestone celebration -->
            <div x-show="count >= 10" class="alert alert-success mt-3">
                <i class="fas fa-trophy me-2"></i>
                Congratulations! You've reached the milestone of 10!
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
</style>
