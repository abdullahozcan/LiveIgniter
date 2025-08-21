// LiveIgniter - Laravel Livewire benzeri sade ve modern yapı
window.LiveIgniter = {
    csrfToken: null,
    baseUrl: window.location.origin,
    ajaxUrl: '/liveigniter/call', // config'den veya meta'dan alınabilir
    components: new Map(),

    init() {
        // CSRF token
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta) {
            this.csrfToken = csrfMeta.getAttribute('content');
        }
        // x-live directive: her .live-component için id ve state kaydı
        document.addEventListener('alpine:init', () => {
            Alpine.directive('live', (el) => {
                el.classList.add('live-component'); // Otomatik ekle
                const componentId = el.getAttribute('id') || this.generateId();
                el.setAttribute('id', componentId);
                this.components.set(componentId, { element: el });
            });
            // x-igniter-click: method çağrısı
            Alpine.directive('igniter-click', (el, { expression }) => {
                el.addEventListener('click', async (e) => {
                    e.preventDefault();
                    let componentEl = el.closest('.live-component');
                    if (!componentEl) return;
                    let componentId = componentEl.id;
                    await window.LiveIgniter.callMethod(componentId, expression, []);
                });
            });
        });

        // Dinamik AJAX endpoint desteği
        const ajaxMeta = document.querySelector('meta[name="liveigniter-ajax-url"]');
        if (ajaxMeta) {
            this.ajaxUrl = ajaxMeta.getAttribute('content');
        }
    },

    async callMethod(componentId, method, params = []) {
        const response = await fetch(`${this.baseUrl}${this.ajaxUrl}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(this.csrfToken && { 'X-CSRF-TOKEN': this.csrfToken })
            },
            body: JSON.stringify({ componentId, method, params })
        });
        if (!response.ok) {
            alert('Sunucu hatası!');
            return;
        }
        const data = await response.json();
        if (data.success && data.data && data.data.html) {
            const oldEl = document.getElementById(componentId);
            const newEl = this.createElementFromHTML(data.data.html);
            oldEl.replaceWith(newEl);
            this.components.set(componentId, { element: newEl });
        } else {
            alert(data.error || 'Bilinmeyen hata!');
        }
    },

    createElementFromHTML(htmlString) {
        const div = document.createElement('div');
        div.innerHTML = htmlString.trim();
        return div.firstChild;
    },

    generateId() {
        return 'live-' + Math.random().toString(36).substr(2, 9);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    window.LiveIgniter.init();
});