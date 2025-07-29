/**
 * LiveIgniter JavaScript Library
 * 
 * Provides Alpine.js directives and functionality for LiveIgniter components
 */

// Wait for Alpine.js to be available
function waitForAlpine(callback) {
    if (typeof Alpine !== 'undefined') {
        callback();
    } else {
        setTimeout(() => waitForAlpine(callback), 50);
    }
}

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
        
        // Emit initialized event
        document.dispatchEvent(new CustomEvent('liveigniter:initialized'));
        
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
        
        // Register all igniter directives
        this.registerIgniterDirectives();
        
        // Legacy x-wire directive (backwards compatibility)
        Alpine.directive('wire', (el, { expression }, { effect, cleanup }) => {
            const [method, ...params] = this.parseMethodCall(expression);
            
            el.addEventListener('click', (e) => {
                e.preventDefault();
                this.callMethod(this.getComponentId(el), method, params);
            });
        });
    },
    
    /**
     * Register all igniter: directives
     */
    registerIgniterDirectives() {
        const self = this;
        
        // x-igniter-click (for igniter:click attribute)
        Alpine.directive('igniter-click', (el, { expression }, { effect, cleanup }) => {
            const [method, ...params] = self.parseMethodCall(expression);
            
            el.addEventListener('click', (e) => {
                e.preventDefault();
                self.callMethod(self.getComponentId(el), method, params);
            });
        });
        
        // x-igniter-submit (for igniter:submit attribute)
        Alpine.directive('igniter-submit', (el, { expression }, { effect, cleanup }) => {
            const [method, ...params] = self.parseMethodCall(expression);
            
            el.addEventListener('submit', (e) => {
                e.preventDefault();
                const formData = new FormData(el);
                const data = Object.fromEntries(formData);
                self.callMethod(self.getComponentId(el), method, [...params, data]);
            });
        });
        
        // x-igniter-change (for igniter:change attribute)
        Alpine.directive('igniter-change', (el, { expression }, { effect, cleanup }) => {
            const [method, ...params] = self.parseMethodCall(expression);
            
            el.addEventListener('change', (e) => {
                self.callMethod(self.getComponentId(el), method, [...params, e.target.value]);
            });
        });
        
        // x-igniter-input (for igniter:input attribute)
        Alpine.directive('igniter-input', (el, { expression }, { effect, cleanup }) => {
            const [method, ...params] = self.parseMethodCall(expression);
            let timeout;
            
            el.addEventListener('input', (e) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    self.callMethod(self.getComponentId(el), method, [...params, e.target.value]);
                }, 300);
            });
        });
        
        // x-igniter-model (for two-way data binding)
        Alpine.directive('igniter-model', (el, { expression }, { effect, cleanup }) => {
            const property = expression;
            
            // Update component property when input changes
            el.addEventListener('input', (e) => {
                self.callMethod(self.getComponentId(el), 'updateProperty', [property, e.target.value]);
            });
            
            // Optional: Listen for component updates to sync back to input
            // This would require additional implementation in the component system
        });
        
        // x-igniter-loading (for loading states)
        Alpine.directive('igniter-loading', (el, { expression }, { effect, cleanup }) => {
            const targetMethod = expression || 'any';
            
            effect(() => {
                const component = self.components.get(self.getComponentId(el));
                if (component) {
                    const isLoading = targetMethod === 'any' ? 
                        component.loading : 
                        component.loadingMethods && component.loadingMethods[targetMethod];
                    
                    el.style.display = isLoading ? 'block' : 'none';
                }
            });
        });
        
        // igniter:keydown with modifiers support
        Alpine.directive('igniter:keydown', (el, { expression, modifiers }, { effect, cleanup }) => {
            const [method, ...params] = self.parseMethodCall(expression);
            
            el.addEventListener('keydown', (e) => {
                let shouldCall = false;
                
                if (modifiers.length === 0) {
                    shouldCall = true;
                } else {
                    for (const modifier of modifiers) {
                        if (modifier === 'enter' && e.key === 'Enter') shouldCall = true;
                        if (modifier === 'escape' && e.key === 'Escape') shouldCall = true;
                        if (modifier === 'space' && e.key === ' ') shouldCall = true;
                        if (modifier === 'ctrl' && e.ctrlKey) shouldCall = true;
                        if (modifier === 'shift' && e.shiftKey) shouldCall = true;
                        if (modifier === 'alt' && e.altKey) shouldCall = true;
                        if (modifier === 'tab' && e.key === 'Tab') shouldCall = true;
                        if (modifier === 'delete' && e.key === 'Delete') shouldCall = true;
                        if (modifier === 'backspace' && e.key === 'Backspace') shouldCall = true;
                        // Support for specific keys like a, b, c, etc.
                        if (modifier.length === 1 && e.key.toLowerCase() === modifier.toLowerCase()) shouldCall = true;
                    }
                }
                
                if (shouldCall) {
                    e.preventDefault();
                    self.callMethod(self.getComponentId(el), method, [...params, e.key]);
                }
            });
        });
        
        // igniter:model for two-way binding
        Alpine.directive('igniter:model', (el, { expression }, { effect, cleanup }) => {
            const property = expression;
            let timeout;
            
            // Update element when property changes
            effect(() => {
                const component = self.components.get(self.getComponentId(el));
                if (component && component.properties[property] !== undefined) {
                    if (el.type === 'checkbox') {
                        el.checked = !!component.properties[property];
                    } else if (el.type === 'radio') {
                        el.checked = el.value === component.properties[property];
                    } else {
                        el.value = component.properties[property];
                    }
                }
            });
            
            // Update property when element changes
            const updateProperty = (e) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    let value;
                    if (el.type === 'checkbox') {
                        value = el.checked;
                    } else if (el.type === 'radio') {
                        value = el.checked ? el.value : null;
                    } else {
                        value = e.target.value;
                    }
                    self.updateProperty(self.getComponentId(el), property, value);
                }, 150);
            };
            
            el.addEventListener('input', updateProperty);
            el.addEventListener('change', updateProperty);
        });
        
        // igniter:loading
        Alpine.directive('igniter:loading', (el, { expression }, { effect, cleanup }) => {
            const targetMethod = expression || 'any';
            
            effect(() => {
                const component = self.components.get(self.getComponentId(el));
                if (component) {
                    const isLoading = targetMethod === 'any' ? 
                        component.loading : 
                        component.loadingMethods && component.loadingMethods[targetMethod];
                    
                    el.style.display = isLoading ? 'block' : 'none';
                }
            });
        });
        
        // igniter:target (for loading states)
        Alpine.directive('igniter:target', (el, { expression }, { effect, cleanup }) => {
            const targetMethods = expression.split(',').map(m => m.trim());
            
            effect(() => {
                const component = self.components.get(self.getComponentId(el));
                if (component && component.loadingMethods) {
                    const isLoading = targetMethods.some(method => component.loadingMethods[method]);
                    el.disabled = isLoading;
                    
                    if (isLoading) {
                        el.classList.add('loading');
                    } else {
                        el.classList.remove('loading');
                    }
                }
            });
        });
        
        // igniter:poll
        Alpine.directive('igniter:poll', (el, { expression }, { effect, cleanup }) => {
            const [interval, method] = expression.split(':');
            const intervalMs = parseInt(interval) * 1000;
            const methodName = method || 'refresh';
            
            const pollInterval = setInterval(() => {
                if (!self.offline) {
                    self.callMethod(self.getComponentId(el), methodName);
                }
            }, intervalMs);
            
            cleanup(() => clearInterval(pollInterval));
        });
        
        // igniter:lazy
        Alpine.directive('igniter:lazy', (el, { expression }, { effect, cleanup }) => {
            const method = expression || 'load';
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        self.callMethod(self.getComponentId(el), method);
                        observer.unobserve(el);
                    }
                });
            });
            
            observer.observe(el);
            cleanup(() => observer.unobserve(el));
        });
        
        // igniter:confirm
        Alpine.directive('igniter:confirm', (el, { expression, modifiers }, { effect, cleanup }) => {
            const originalEvent = modifiers[0] || 'click';
            const confirmMessage = expression || 'Are you sure?';
            
            // Find the original igniter directive
            const originalDirective = el.getAttribute(`igniter:${originalEvent}`);
            
            if (originalDirective) {
                el.addEventListener(originalEvent, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (confirm(confirmMessage)) {
                        const [method, ...params] = self.parseMethodCall(originalDirective);
                        self.callMethod(self.getComponentId(el), method, params);
                    }
                });
                
                // Remove the original directive to prevent double handling
                el.removeAttribute(`igniter:${originalEvent}`);
            }
        });
        
        // igniter:dirty (show when form is modified)
        Alpine.directive('igniter:dirty', (el, { expression }, { effect, cleanup }) => {
            const targetInputs = expression ? 
                el.querySelectorAll(expression) : 
                el.querySelectorAll('input, textarea, select');
            
            let isDirty = false;
            const originalValues = new Map();
            
            // Store original values
            targetInputs.forEach(input => {
                originalValues.set(input, input.value);
            });
            
            // Listen for changes
            targetInputs.forEach(input => {
                const checkDirty = () => {
                    isDirty = Array.from(targetInputs).some(inp => inp.value !== originalValues.get(inp));
                    el.style.display = isDirty ? 'block' : 'none';
                };
                
                input.addEventListener('input', checkDirty);
                input.addEventListener('change', checkDirty);
            });
            
            el.style.display = 'none';
        });
        
        // igniter:init
        Alpine.directive('igniter:init', (el, { expression }, { effect, cleanup }) => {
            if (expression) {
                // Execute the expression when component initializes
                try {
                    new Function('$el', '$component', expression)(el, self.getComponentId(el));
                } catch (error) {
                    console.error('Error in igniter:init:', error);
                }
            }
        });
        
        // igniter:offline
        Alpine.directive('igniter:offline', (el, { expression }, { effect, cleanup }) => {
            effect(() => {
                el.style.display = self.offline ? 'block' : 'none';
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
        
        // Set method-specific loading state
        if (!component.loadingMethods) {
            component.loadingMethods = {};
        }
        component.loadingMethods[method] = true;
        
        this.updateComponentState(componentId, { 
            loading: true,
            loadingMethods: component.loadingMethods
        });

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
                const newElement = this.createElementFromHTML(data.data.html);
                component.element.replaceWith(newElement);
                
                // Update component reference
                component.element = newElement;
                this.components.set(componentId, component);

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
            component.loadingMethods[method] = false;
            
            this.updateComponentState(componentId, { 
                loading: false,
                loadingMethods: component.loadingMethods
            });
        }
    },
    
    /**
     * Parse method call expression
     */
    parseMethodCall(expression) {
        // Handle method calls like: method, method(param1, param2), method('string', 123)
        const match = expression.match(/^(\w+)(?:\((.*)\))?$/);
        
        if (!match) {
            return [expression];
        }
        
        const method = match[1];
        const paramsString = match[2];
        
        if (!paramsString) {
            return [method];
        }
        
        // Parse parameters (simple implementation)
        const params = paramsString.split(',').map(param => {
            param = param.trim();
            
            // String parameter
            if (param.startsWith("'") && param.endsWith("'")) {
                return param.slice(1, -1);
            }
            if (param.startsWith('"') && param.endsWith('"')) {
                return param.slice(1, -1);
            }
            
            // Number parameter
            if (!isNaN(param)) {
                return parseFloat(param);
            }
            
            // Boolean parameter
            if (param === 'true') return true;
            if (param === 'false') return false;
            if (param === 'null') return null;
            
            // Variable reference (simplified)
            if (param.startsWith('$')) {
                return param; // Will be resolved on server
            }
            
            return param;
        });
        
        return [method, ...params];
    },

    /**
     * Create element from HTML string
     */
    createElementFromHTML(htmlString) {
        const div = document.createElement('div');
        div.innerHTML = htmlString.trim();
        return div.firstChild;
    },
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

// Global utility functions (will be bound when Alpine is ready)
let callLiveMethod, updateProperty;

// Initialize when both DOM and Alpine.js are ready
document.addEventListener('DOMContentLoaded', () => {
    waitForAlpine(() => {
        // Bind utility functions to Alpine context
        callLiveMethod = function(method, params = []) {
            const componentId = this.$el ? LiveIgniter.getComponentId(this.$el) : null;
            if (componentId) {
                LiveIgniter.callMethod(componentId, method, params);
            }
        }.bind(Alpine);

        updateProperty = function(property, value) {
            const componentId = this.$el ? LiveIgniter.getComponentId(this.$el) : null;
            if (componentId) {
                LiveIgniter.updateProperty(componentId, property, value);
            }
        }.bind(Alpine);

        // Make functions globally available
        window.callLiveMethod = callLiveMethod;
        window.updateProperty = updateProperty;

        // Initialize LiveIgniter
        LiveIgniter.init();
        
        console.log('LiveIgniter initialized successfully with Alpine.js');
    });
});
