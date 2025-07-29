/**
 * LiveIgniter JavaScript Library
 * 
 * Provides Alpine.js directives and functionality for LiveIgniter components
 */

// LiveIgniter Alpine.js plugin
window.LiveIgniter = {
    // Component state
    components: new Map(),
    
    // Global loading state
    loading: false,
    
    // Offline state
    offline: !navigator.onLine,
    
    // CSRF token
    csrfToken: null,
    
    // Base URL for AJAX requests
    baseUrl: window.location.origin,
    
    /**
     * Initialize LiveIgniter
     */
    init() {
        // Set up online/offline detection
        window.addEventListener('online', () => this.offline = false);
        window.addEventListener('offline', () => this.offline = true);
        
        // Get CSRF token from meta tag
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta) {
            this.csrfToken = csrfMeta.getAttribute('content');
        }
        
        // Initialize Alpine.js directives
        this.initAlpineDirectives();
        
        // Set up global error handling
        this.setupErrorHandling();
        
        console.log('LiveIgniter initialized');
    },
    
    /**
     * Initialize Alpine.js directives
     */
    initAlpineDirectives() {
        // x-live directive for component initialization
        Alpine.directive('live', (el, { expression }, { effect, cleanup }) => {
            const componentId = el.getAttribute('id') || this.generateId();
            el.setAttribute('id', componentId);
            
            // Initialize component state
            this.components.set(componentId, {
                element: el,
                loading: false,
                properties: this.extractProperties(el)
            });
            
            // Add component data to Alpine
            Alpine.bind(el, {
                'x-data'() {
                    return {
                        componentId,
                        loading: false,
                        offline: LiveIgniter.offline,
                        ...LiveIgniter.components.get(componentId).properties
                    };
                }
            });
        });
        
        // x-wire directive for method calls
        Alpine.directive('wire', (el, { expression }, { effect, cleanup }) => {
            const [method, ...params] = expression.split(':');
            
            el.addEventListener('click', (e) => {
                e.preventDefault();
                this.callMethod(this.getComponentId(el), method, params);
            });
        });
        
        // x-model-live directive for real-time model binding
        Alpine.directive('model-live', (el, { expression }, { effect, cleanup }) => {
            let timeout;
            
            el.addEventListener('input', (e) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    this.updateProperty(this.getComponentId(el), expression, e.target.value);
                }, 300); // Debounce for 300ms
            });
        });
    },
    
    /**
     * Call component method via AJAX
     */
    async callMethod(componentId, method, params = []) {
        const component = this.components.get(componentId);
        if (!component) {
            console.error(`Component ${componentId} not found`);
            return;
        }
        
        // Set loading state
        component.loading = true;
        this.loading = true;
        this.updateComponentState(componentId, { loading: true });
        
        try {
            const response = await fetch(`${this.baseUrl}/liveigniter/call`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(this.csrfToken && { 'X-CSRF-TOKEN': this.csrfToken })
                },
                body: JSON.stringify({
                    componentId,
                    method,
                    params
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                // Update component HTML
                component.element.outerHTML = data.data.html;
                
                // Update component properties
                this.updateComponentState(componentId, data.data.properties);
                
                // Process events
                if (data.events) {
                    this.processEvents(data.events);
                }
                
                // Dispatch success event
                this.dispatchEvent('liveigniter:updated', { componentId, data });
                
            } else {
                throw new Error(data.error || 'Unknown error occurred');
            }
            
        } catch (error) {
            console.error('LiveIgniter method call failed:', error);
            this.dispatchEvent('liveigniter:error', { componentId, error });
            
        } finally {
            // Clear loading state
            component.loading = false;
            this.loading = false;
            this.updateComponentState(componentId, { loading: false });
        }
    },
    
    /**
     * Update component property
     */
    async updateProperty(componentId, property, value) {
        const component = this.components.get(componentId);
        if (!component) return;
        
        // Update local state immediately
        component.properties[property] = value;
        this.updateComponentState(componentId, { [property]: value });
        
        // Sync with server
        await this.callMethod(componentId, '$set', [property, value]);
    },
    
    /**
     * Update component state in Alpine
     */
    updateComponentState(componentId, updates) {
        const component = this.components.get(componentId);
        if (!component) return;
        
        // Update properties
        Object.assign(component.properties, updates);
        
        // Trigger Alpine reactivity
        const element = component.element;
        if (element && element._x_dataStack) {
            const alpineData = element._x_dataStack[0];
            Object.assign(alpineData, updates);
        }
    },
    
    /**
     * Process server events
     */
    processEvents(events) {
        events.forEach(event => {
            this.dispatchEvent(`liveigniter:${event.name}`, {
                params: event.params,
                timestamp: event.timestamp
            });
        });
    },
    
    /**
     * Dispatch custom event
     */
    dispatchEvent(name, detail = {}) {
        const event = new CustomEvent(name, { detail });
        document.dispatchEvent(event);
    },
    
    /**
     * Get component ID from element
     */
    getComponentId(element) {
        // Traverse up to find component element
        while (element && !element.hasAttribute('id')) {
            element = element.parentElement;
        }
        return element ? element.getAttribute('id') : null;
    },
    
    /**
     * Extract properties from element attributes
     */
    extractProperties(element) {
        const properties = {};
        const attrs = element.attributes;
        
        for (let i = 0; i < attrs.length; i++) {
            const attr = attrs[i];
            if (attr.name.startsWith('data-')) {
                const prop = attr.name.replace('data-', '').replace(/-([a-z])/g, (g) => g[1].toUpperCase());
                properties[prop] = this.parseValue(attr.value);
            }
        }
        
        return properties;
    },
    
    /**
     * Parse attribute value
     */
    parseValue(value) {
        try {
            return JSON.parse(value);
        } catch {
            return value;
        }
    },
    
    /**
     * Generate unique ID
     */
    generateId() {
        return 'live-' + Math.random().toString(36).substr(2, 9);
    },
    
    /**
     * Setup global error handling
     */
    setupErrorHandling() {
        // Handle network errors
        window.addEventListener('error', (e) => {
            if (e.message.includes('NetworkError')) {
                this.offline = true;
            }
        });
        
        // Handle fetch errors
        window.addEventListener('unhandledrejection', (e) => {
            if (e.reason && e.reason.message && e.reason.message.includes('fetch')) {
                this.offline = true;
            }
        });
    }
};

// Global utility functions
window.callLiveMethod = function(method, params = []) {
    const componentId = this.$el ? LiveIgniter.getComponentId(this.$el) : null;
    if (componentId) {
        LiveIgniter.callMethod(componentId, method, params);
    }
}.bind(Alpine);

window.updateProperty = function(property, value) {
    const componentId = this.$el ? LiveIgniter.getComponentId(this.$el) : null;
    if (componentId) {
        LiveIgniter.updateProperty(componentId, property, value);
    }
}.bind(Alpine);

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    LiveIgniter.init();
});

// Make sure Alpine.js is available
if (typeof Alpine === 'undefined') {
    console.warn('Alpine.js is required for LiveIgniter to work properly. Please include Alpine.js before this script.');
}
