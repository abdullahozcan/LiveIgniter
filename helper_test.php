<!DOCTYPE html>
<html>
<head>
    <title>LiveIgniter Helper Functions Test</title>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="<?= base_url('public/liveigniter.js') ?>"></script>
</head>
<body>
    <div data-component-id="test-component" style="max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;"<?= live_component_data(['testValue' => 'Hello World', 'counter' => 0, 'message' => 'Test message']) ?>>
        <h2>LiveIgniter Helper Functions Test</h2>
        
        <!-- Alpine.js reactive display -->
        <div style="margin: 10px 0; padding: 10px; background: #e7f3ff; border-radius: 4px;">
            <strong>Live Data:</strong> 
            <span x-text="testValue"></span> | 
            Counter: <span x-text="counter"></span> | 
            Loading: <span x-text="loading ? 'Yes' : 'No'"></span>
        </div>
        
        <!-- Test live_igniter function -->
        <div style="margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 4px;">
            <h3>1. live_igniter() Helper Function</h3>
            <p>Primary function for click events:</p>
            <button<?= live_igniter('testMethod') ?> 
                    style="background: #007bff; color: white; border: none; padding: 8px 16px; margin: 5px; border-radius: 4px; cursor: pointer;">
                Test Method
            </button>
            
            <button<?= live_igniter('methodWithParams', ['param1', 123, true]) ?>
                    style="background: #28a745; color: white; border: none; padding: 8px 16px; margin: 5px; border-radius: 4px; cursor: pointer;">
                Method with Parameters
            </button>
            
            <p><small>Generated HTML: <code><?= esc(live_igniter('increment')) ?></code></small></p>
        </div>

        <!-- Test live_model function -->
        <div style="margin: 20px 0; padding: 15px; background: #e9ecef; border-radius: 4px;">
            <h3>2. live_model() Helper Function</h3>
            <p>Two-way data binding:</p>
            <input<?= live_model('testProperty') ?> 
                   type="text" 
                   placeholder="Type something..."
                   style="width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px;">
            
            <p><small>Generated HTML: <code><?= esc(live_model('message')) ?></code></small></p>
        </div>

        <!-- Test live_loading function -->
        <div style="margin: 20px 0; padding: 15px; background: #fff3cd; border-radius: 4px;">
            <h3>3. live_loading() Helper Function</h3>
            <p>Loading states:</p>
            <div<?= live_loading('testMethod') ?> 
                 style="display: none; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; color: #0c5460;">
                ⏳ Loading test method...
            </div>
            
            <div<?= live_loading() ?> 
                 style="display: none; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;">
                ⏳ General loading state...
            </div>
            
            <p><small>Generated HTML: <code><?= esc(live_loading('save')) ?></code></small></p>
        </div>

        <!-- Test live_wire backward compatibility -->
        <div style="margin: 20px 0; padding: 15px; background: #d4edda; border-radius: 4px;">
            <h3>4. live_wire() Backward Compatibility</h3>
            <p>Deprecated but still works (alias to live_igniter):</p>
            <button<?= live_wire('oldMethod') ?>
                    style="background: #6c757d; color: white; border: none; padding: 8px 16px; margin: 5px; border-radius: 4px; cursor: pointer;">
                Old Method (live_wire)
            </button>
            
            <p><small>⚠️ Deprecated: Use <code>live_igniter()</code> instead</small></p>
        </div>

        <!-- Direct HTML usage example -->
        <div style="margin: 20px 0; padding: 15px; background: #e2e3e5; border-radius: 4px;">
            <h3>5. Direct HTML Usage</h3>
            <p>You can also use directives directly:</p>
            <button x-igniter-click="directMethod"
                    style="background: #dc3545; color: white; border: none; padding: 8px 16px; margin: 5px; border-radius: 4px; cursor: pointer;">
                Direct x-igniter-click
            </button>
            
            <input x-igniter-model="directProperty" 
                   type="text" 
                   placeholder="Direct model binding..."
                   style="width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px;">
        </div>

        <!-- Test live_data and live_component_data functions -->
        <div style="margin: 20px 0; padding: 15px; background: #e8f5e8; border-radius: 4px;">
            <h3>6. live_component_data() Helper Function</h3>
            <p>Automatically creates x-data from component properties:</p>
            
            <button x-igniter-click="increment" @click="counter++"
                    style="background: #28a745; color: white; border: none; padding: 8px 16px; margin: 5px; border-radius: 4px; cursor: pointer;">
                Increment Counter
            </button>
            
            <button @click="testValue = 'Updated!'"
                    style="background: #ffc107; color: black; border: none; padding: 8px 16px; margin: 5px; border-radius: 4px; cursor: pointer;">
                Update Test Value
            </button>
            
            <p><small>Current component data (live): 
                <code x-text="JSON.stringify({testValue, counter, message, loading})"></code>
            </small></p>
        </div>

        <!-- Output comparison -->
        <div style="margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 4px; font-family: monospace; font-size: 12px;">
            <h4>Generated Output Comparison:</h4>
            <ul style="list-style: none; padding: 0;">
                <li><strong>live_igniter('save'):</strong> <code><?= esc(live_igniter('save')) ?></code></li>
                <li><strong>live_model('name'):</strong> <code><?= esc(live_model('name')) ?></code></li>
                <li><strong>live_loading('save'):</strong> <code><?= esc(live_loading('save')) ?></code></li>
                <li><strong>live_wire('save'):</strong> <code><?= esc(live_wire('save')) ?></code> <small>(same as live_igniter)</small></li>
                <li><strong>live_component_data(...):</strong> <code><?= esc(live_component_data(['count' => 0, 'message' => 'test'])) ?></code></li>
            </ul>
        </div>
    </div>
</body>
</html>
