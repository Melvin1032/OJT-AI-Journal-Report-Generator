/**
 * OJT Journal - Enhanced Utilities
 * Toast notifications, image compression, lazy loading, and accessibility features
 */

// ============================================
// Toast Notification System
// ============================================

class ToastManager {
    constructor() {
        this.container = null;
        this.toasts = [];
        this.init();
    }
    
    init() {
        this.container = document.createElement('div');
        this.container.className = 'toast-container';
        this.container.setAttribute('role', 'region');
        this.container.setAttribute('aria-label', 'Notifications');
        document.body.appendChild(this.container);
        
        // Add styles if not already present
        if (!document.getElementById('toast-styles')) {
            this.addStyles();
        }
    }
    
    addStyles() {
        const style = document.createElement('style');
        style.id = 'toast-styles';
        style.textContent = `
            .toast-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 10px;
                max-width: 400px;
            }
            
            .toast {
                padding: 1rem 1.5rem;
                background: var(--bg-secondary);
                border-radius: var(--border-radius);
                box-shadow: var(--shadow-lg);
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                min-width: 300px;
                transform: translateX(400px);
                transition: transform 0.3s ease, opacity 0.3s ease;
                opacity: 0;
            }
            
            .toast.show {
                transform: translateX(0);
                opacity: 1;
            }
            
            .toast.toast-hiding {
                transform: translateX(400px);
                opacity: 0;
            }
            
            .toast-success {
                border-left: 4px solid var(--success-color);
            }
            
            .toast-error {
                border-left: 4px solid var(--error-color);
            }
            
            .toast-info {
                border-left: 4px solid var(--primary-color);
            }
            
            .toast-warning {
                border-left: 4px solid var(--warning-color);
            }
            
            .toast-message {
                flex: 1;
                color: var(--text-primary);
                font-size: 0.9rem;
            }
            
            .toast-close {
                background: none;
                border: none;
                color: var(--text-muted);
                cursor: pointer;
                padding: 0.25rem;
                font-size: 1.25rem;
                line-height: 1;
                transition: color 0.2s;
            }
            
            .toast-close:hover {
                color: var(--text-primary);
            }
            
            .toast-progress {
                position: absolute;
                bottom: 0;
                left: 0;
                height: 3px;
                background: rgba(255,255,255,0.3);
                border-radius: 0 0 var(--border-radius) var(--border-radius);
                animation: progress linear forwards;
            }
            
            @keyframes progress {
                from { width: 100%; }
                to { width: 0%; }
            }
            
            @media (max-width: 768px) {
                .toast-container {
                    top: 10px;
                    right: 10px;
                    left: 10px;
                    max-width: none;
                }
                
                .toast {
                    min-width: auto;
                    width: 100%;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    show(message, type = 'info', duration = 5000) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'polite');
        
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        
        toast.innerHTML = `
            <span class="toast-icon">${icons[type] || icons.info}</span>
            <span class="toast-message">${this.escapeHtml(message)}</span>
            <button class="toast-close" aria-label="Close notification">&times;</button>
            <div class="toast-progress" style="animation-duration: ${duration}ms"></div>
        `;
        
        this.container.appendChild(toast);
        
        // Animate in
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });
        
        // Close handler
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => this.hide(toast));
        
        // Auto-hide
        const hideTimeout = setTimeout(() => {
            this.hide(toast);
        }, duration);
        
        // Store reference
        this.toasts.push({ element: toast, timeout: hideTimeout });
        
        return toast;
    }
    
    hide(toast) {
        if (!toast || !toast.parentElement) return;
        
        toast.classList.add('toast-hiding');
        
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 300);
    }
    
    success(message, duration) {
        return this.show(message, 'success', duration);
    }
    
    error(message, duration) {
        return this.show(message, 'error', duration);
    }
    
    warning(message, duration) {
        return this.show(message, 'warning', duration);
    }
    
    info(message, duration) {
        return this.show(message, 'info', duration);
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    clearAll() {
        this.toasts.forEach(toast => {
            clearTimeout(toast.timeout);
            this.hide(toast.element);
        });
        this.toasts = [];
    }
}

// Global toast instance
const toast = new ToastManager();

// ============================================
// Image Compression Utility
// ============================================

class ImageCompressor {
    constructor(options = {}) {
        this.maxWidth = options.maxWidth || 1920;
        this.maxHeight = options.maxHeight || 1920;
        this.quality = options.quality || 0.8;
        this.maxSizeMB = options.maxSizeMB || 2;
    }
    
    async compress(file) {
        if (!file || !file.type.startsWith('image/')) {
            throw new Error('Invalid image file');
        }
        
        // Skip compression for already small files
        if (file.size < 500 * 1024) { // Less than 500KB
            return file;
        }
        
        try {
            const bitmap = await this.createImageBitmap(file);
            const canvas = document.createElement('canvas');
            
            // Calculate new dimensions
            let width = bitmap.width;
            let height = bitmap.height;
            
            if (width > this.maxWidth || height > this.maxHeight) {
                const ratio = Math.min(this.maxWidth / width, this.maxHeight / height);
                width = Math.floor(width * ratio);
                height = Math.floor(height * ratio);
            }
            
            canvas.width = width;
            canvas.height = height;
            
            const ctx = canvas.getContext('2d');
            ctx.drawImage(bitmap, 0, 0, width, height);
            
            // Convert to blob
            const blob = await new Promise((resolve, reject) => {
                canvas.toBlob(
                    resolve,
                    file.type,
                    this.quality
                );
            });
            
            // If still too large, reduce quality further
            if (blob.size > this.maxSizeMB * 1024 * 1024) {
                return await this.reduceQuality(canvas, file.type, blob.size);
            }
            
            // Create file with original name
            return new File([blob], file.name, {
                type: file.type,
                lastModified: Date.now()
            });
            
        } catch (error) {
            console.error('Image compression failed:', error);
            return file; // Return original on failure
        }
    }
    
    async reduceQuality(canvas, mimeType, currentSize) {
        let quality = this.quality - 0.1;
        
        while (quality > 0.3) {
            const blob = await new Promise((resolve) => {
                canvas.toBlob(resolve, mimeType, quality);
            });
            
            if (blob.size <= this.maxSizeMB * 1024 * 1024) {
                return blob;
            }
            
            quality -= 0.1;
        }
        
        // Return whatever we have at minimum quality
        return new Promise((resolve) => {
            canvas.toBlob(resolve, mimeType, 0.3);
        });
    }
    
    async compressMultiple(files) {
        const results = [];
        
        for (const file of files) {
            try {
                const compressed = await this.compress(file);
                const savings = ((1 - compressed.size / file.size) * 100).toFixed(1);
                results.push({
                    original: file,
                    compressed,
                    savings: `${savings}%`,
                    originalSize: this.formatFileSize(file.size),
                    newSize: this.formatFileSize(compressed.size)
                });
            } catch (error) {
                results.push({
                    original: file,
                    error: error.message
                });
            }
        }
        
        return results;
    }
    
    formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }
}

// Global compressor instance
const imageCompressor = new ImageCompressor({
    maxWidth: 1920,
    maxHeight: 1920,
    quality: 0.8,
    maxSizeMB: 2
});

// ============================================
// Lazy Loading Images
// ============================================

class LazyImageLoader {
    constructor(options = {}) {
        this.placeholderColor = options.placeholderColor || '#e0e0e0';
        this.transitionClass = options.transitionClass || 'fade-in';
        this.observer = null;
        this.init();
    }
    
    init() {
        if ('IntersectionObserver' in window) {
            this.observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadImage(entry.target);
                    }
                });
            }, {
                rootMargin: '50px 0px',
                threshold: 0.01
            });
        }
    }
    
    observe(container = document) {
        if (!this.observer) return;
        
        const images = container.querySelectorAll('img[data-src]');
        images.forEach(img => {
            if (!img.src || img.src === 'data:') {
                img.style.background = `linear-gradient(90deg, ${this.placeholderColor} 25%, #d0d0d0 50%, ${this.placeholderColor} 75%)`;
                img.style.backgroundSize = '200% 100%';
                img.style.animation = 'shimmer 1.5s infinite';
                this.observer.observe(img);
            }
        });
        
        // Add shimmer animation if not present
        if (!document.getElementById('lazy-load-styles')) {
            const style = document.createElement('style');
            style.id = 'lazy-load-styles';
            style.textContent = `
                @keyframes shimmer {
                    0% { background-position: 200% 0; }
                    100% { background-position: -200% 0; }
                }
                
                img.lazy-loaded {
                    background: none !important;
                    animation: none !important;
                }
                
                .fade-in {
                    transition: opacity 0.3s ease;
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    loadImage(img) {
        const src = img.dataset.src;
        if (!src) return;
        
        const image = new Image();
        image.src = src;
        
        image.onload = () => {
            img.src = src;
            img.classList.add('lazy-loaded');
            img.classList.add(this.transitionClass);
            img.removeAttribute('data-src');
            this.observer.unobserve(img);
        };
        
        image.onerror = () => {
            img.style.background = 'none';
            img.alt = 'Failed to load image';
            this.observer.unobserve(img);
        };
    }
    
    destroy() {
        if (this.observer) {
            this.observer.disconnect();
        }
    }
}

// Global lazy loader instance
const lazyLoader = new LazyImageLoader();

// ============================================
// CSRF Token Manager
// ============================================

class CSRFManager {
    constructor() {
        this.token = null;
        this.lastRefresh = 0;
        this.refreshInterval = 30 * 60 * 1000; // 30 minutes
    }
    
    async getToken() {
        // Return cached token if still valid
        if (this.token && (Date.now() - this.lastRefresh) < this.refreshInterval) {
            return this.token;
        }
        
        // Try to get from meta tag first
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        if (metaToken) {
            this.token = metaToken.getAttribute('content');
            this.lastRefresh = Date.now();
            return this.token;
        }
        
        // Fetch from server
        try {
            const response = await fetch('src/process.php?action=getCSRFToken');
            const data = await response.json();
            this.token = data.csrf_token;
            this.lastRefresh = Date.now();
            return this.token;
        } catch (error) {
            console.error('Failed to get CSRF token:', error);
            throw error;
        }
    }
    
    async getHeaders() {
        const token = await this.getToken();
        return {
            'X-CSRF-Token': token,
            'Content-Type': 'application/json'
        };
    }
}

// Global CSRF manager
const csrfManager = new CSRFManager();

// ============================================
// Accessibility Utilities
// ============================================

class AccessibilityUtils {
    static trapFocus(container) {
        const focusableElements = container.querySelectorAll(
            'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
        );
        
        if (focusableElements.length === 0) return;
        
        const firstFocusable = focusableElements[0];
        const lastFocusable = focusableElements[focusableElements.length - 1];
        
        container.addEventListener('keydown', (e) => {
            if (e.key !== 'Tab') return;
            
            if (e.shiftKey) {
                if (document.activeElement === firstFocusable) {
                    e.preventDefault();
                    lastFocusable.focus();
                }
            } else {
                if (document.activeElement === lastFocusable) {
                    e.preventDefault();
                    firstFocusable.focus();
                }
            }
        });
        
        // Focus first element
        firstFocusable.focus();
    }
    
    static announceToScreenReader(message, priority = 'polite') {
        let announcer = document.getElementById('sr-announcer');
        
        if (!announcer) {
            announcer = document.createElement('div');
            announcer.id = 'sr-announcer';
            announcer.setAttribute('role', 'status');
            announcer.setAttribute('aria-live', priority);
            announcer.setAttribute('aria-atomic', 'true');
            announcer.style.cssText = 'position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); border: 0;';
            document.body.appendChild(announcer);
        }
        
        announcer.textContent = message;
        
        // Clear after 3 seconds
        setTimeout(() => {
            announcer.textContent = '';
        }, 3000);
    }
    
    static addKeyboardCloseHandler(modal, escapeKey = true, enterKey = false) {
        modal.addEventListener('keydown', (e) => {
            if (escapeKey && e.key === 'Escape') {
                modal.dispatchEvent(new CustomEvent('close'));
            }
            if (enterKey && e.key === 'Enter') {
                modal.dispatchEvent(new CustomEvent('confirm'));
            }
        });
    }
}

// ============================================
// Search and Filter Utilities
// ============================================

class SearchFilter {
    constructor(containerSelector, options = {}) {
        this.container = document.querySelector(containerSelector);
        this.items = [];
        this.searchInput = null;
        this.filterSelect = null;
        this.sortSelect = null;
        this.options = {
            searchPlaceholder: options.searchPlaceholder || 'Search...',
            dateFilter: options.dateFilter !== false,
            sortOptions: options.sortOptions || [
                { value: 'date-desc', label: 'Newest First' },
                { value: 'date-asc', label: 'Oldest First' },
                { value: 'title', label: 'By Title' }
            ]
        };
    }
    
    init() {
        this.createFilterBar();
        this.bindEvents();
    }
    
    createFilterBar() {
        const filterBar = document.createElement('div');
        filterBar.className = 'filter-bar';
        filterBar.style.cssText = 'display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;';
        
        // Search input
        this.searchInput = document.createElement('input');
        this.searchInput.type = 'text';
        this.searchInput.className = 'search-input';
        this.searchInput.placeholder = this.options.searchPlaceholder;
        this.searchInput.style.cssText = 'flex: 1; min-width: 200px; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); background: var(--bg-secondary); color: var(--text-primary);';
        
        filterBar.appendChild(this.searchInput);
        
        // Date filter
        if (this.options.dateFilter) {
            this.filterSelect = document.createElement('select');
            this.filterSelect.className = 'date-filter';
            this.filterSelect.innerHTML = `
                <option value="all">All Dates</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
            `;
            this.filterSelect.style.cssText = 'padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); background: var(--bg-secondary); color: var(--text-primary);';
            
            filterBar.appendChild(this.filterSelect);
        }
        
        // Sort select
        this.sortSelect = document.createElement('select');
        this.sortSelect.className = 'sort-select';
        this.sortSelect.innerHTML = this.options.sortOptions
            .map(opt => `<option value="${opt.value}">${opt.label}</option>`)
            .join('');
        this.sortSelect.style.cssText = 'padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); background: var(--bg-secondary); color: var(--text-primary);';
        
        filterBar.appendChild(this.sortSelect);
        
        // Insert before container content
        this.container.insertBefore(filterBar, this.container.firstChild);
    }
    
    bindEvents() {
        if (this.searchInput) {
            this.searchInput.addEventListener('input', (e) => {
                this.filter(e.target.value);
            });
        }
        
        if (this.filterSelect) {
            this.filterSelect.addEventListener('change', () => {
                this.filter(this.searchInput?.value || '');
            });
        }
        
        if (this.sortSelect) {
            this.sortSelect.addEventListener('change', () => {
                this.sort();
            });
        }
    }
    
    filter(searchTerm = '') {
        const term = searchTerm.toLowerCase();
        const dateFilter = this.filterSelect?.value || 'all';
        const now = new Date();
        
        this.items.forEach(item => {
            const title = item.querySelector('.ojt-entry-title')?.textContent.toLowerCase() || '';
            const description = item.querySelector('.ojt-entry-description')?.textContent.toLowerCase() || '';
            const dateStr = item.dataset.entryDate;
            const date = dateStr ? new Date(dateStr) : null;
            
            // Search match
            const matchesSearch = !term || title.includes(term) || description.includes(term);
            
            // Date match
            let matchesDate = true;
            if (dateFilter !== 'all' && date) {
                const daysDiff = (now - date) / (1000 * 60 * 60 * 24);
                
                switch (dateFilter) {
                    case 'today':
                        matchesDate = date.toDateString() === now.toDateString();
                        break;
                    case 'week':
                        matchesDate = daysDiff <= 7;
                        break;
                    case 'month':
                        matchesDate = daysDiff <= 30;
                        break;
                }
            }
            
            item.style.display = (matchesSearch && matchesDate) ? '' : 'none';
        });
    }
    
    sort() {
        const sortBy = this.sortSelect?.value || 'date-desc';
        const itemsArray = Array.from(this.items);
        
        itemsArray.sort((a, b) => {
            switch (sortBy) {
                case 'date-asc':
                    return new Date(a.dataset.entryDate) - new Date(b.dataset.entryDate);
                case 'date-desc':
                    return new Date(b.dataset.entryDate) - new Date(a.dataset.entryDate);
                case 'title':
                    return (a.querySelector('.ojt-entry-title')?.textContent || '')
                        .localeCompare(b.querySelector('.ojt-entry-title')?.textContent || '');
                default:
                    return 0;
            }
        });
        
        itemsArray.forEach(item => this.container.appendChild(item));
    }
    
    setItems(items) {
        this.items = items;
    }
}

// Export utilities for use in main script
window.Toast = toast;
window.ImageCompressor = imageCompressor;
window.LazyLoader = lazyLoader;
window.CSRFManager = csrfManager;
window.Accessibility = AccessibilityUtils;
window.SearchFilter = SearchFilter;
