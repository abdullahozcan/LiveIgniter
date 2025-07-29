<!DOCTYPE html>
<html>
<head>
    <title>LiveIgniter Debug Test</title>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="<?= base_url('public/liveigniter.js') ?>"></script>
</head>
<body>
    <div x-data="{ message: 'Not clicked yet' }">
        <h1>LiveIgniter Debug Test</h1>
        
        <!-- Alpine.js Status Check -->
        <div x-text="'Alpine.js Status: ' + (typeof Alpine !== 'undefined' ? 'Loaded' : 'Not Loaded')"></div>
        <div x-text="'LiveIgniter Status: ' + (typeof LiveIgniter !== 'undefined' ? 'Loaded' : 'Not Loaded')"></div>
        
        <!-- Test x-igniter-click directive -->
        <div data-component-id="test-component">
            <button x-igniter-click="testMethod" class="test-button">
                Test x-igniter-click (Should work)
            </button>
        </div>
        
        <!-- Test regular Alpine.js directive -->
        <button @click="message = 'Alpine.js clicked!'" class="alpine-button">
            Test Alpine.js @click
        </button>
        
        <div x-text="message"></div>
        
        <!-- Manual Debug -->
        <button onclick="debugDirectives()">Debug Directives</button>
        
        <script>
            function debugDirectives() {
                console.log('Alpine:', typeof Alpine !== 'undefined' ? Alpine : 'Not loaded');
                console.log('LiveIgniter:', typeof LiveIgniter !== 'undefined' ? LiveIgniter : 'Not loaded');
                
                if (typeof Alpine !== 'undefined' && Alpine.directives) {
                    console.log('Alpine Directives:', Object.keys(Alpine.directives));
                }
                
                // Test if our directive is registered
                const testBtn = document.querySelector('.test-button');
                console.log('Test Button:', testBtn);
                console.log('Button Attributes:', testBtn.attributes);
            }
            
            // Listen for Alpine events
            document.addEventListener('alpine:init', () => {
                console.log('Alpine initialized');
            });
            
            // Listen for LiveIgniter events  
            document.addEventListener('liveigniter:ready', () => {
                console.log('LiveIgniter ready');
            });
        </script>
    </div>
</body>
</html>
        setTimeout(() => {
            testAlpine();
            testLiveIgniter();
            checkComponent();
        }, 1000);
        
        // Listen for LiveIgniter events
        document.addEventListener('liveigniter:initialized', () => {
            console.log('LiveIgniter initialized event fired');
        });
        
        document.addEventListener('liveigniter:component:updated', (e) => {
            console.log('Component updated:', e.detail);
        });
    </script>
</body>
</html>
