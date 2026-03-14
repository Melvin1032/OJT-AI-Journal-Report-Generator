/**
 * UI Components - Reusable across all pages
 * Handles: Modals, User dropdown
 * Note: Theme toggle is handled by individual pages
 */

// ==================== User Dropdown ====================
const userMenuBtn = document.getElementById('userMenuBtn');
const userDropdown = document.getElementById('userDropdown');

if (userMenuBtn && userDropdown) {
    userMenuBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const isShown = userDropdown.classList.contains('show');
        userDropdown.classList.toggle('show');
        userMenuBtn.setAttribute('aria-expanded', !isShown);
    });

    // Close menu when clicking outside
    document.addEventListener('click', () => {
        userDropdown.classList.remove('show');
        if (userMenuBtn) {
            userMenuBtn.setAttribute('aria-expanded', 'false');
        }
    });

    // Keyboard navigation
    if (userMenuBtn) {
        userMenuBtn.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                userMenuBtn.click();
            }
        });
    }
}

// ==================== Logout Modal ====================
let deleteCallback = null;

function showLogoutModal() {
    if (userDropdown) userDropdown.classList.remove('show');
    const modal = document.getElementById('logoutModal');
    if (modal) modal.classList.add('show');
}

function hideLogoutModal() {
    const modal = document.getElementById('logoutModal');
    if (modal) modal.classList.remove('show');
}

async function confirmLogout() {
    try {
        const response = await fetch('public/logout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content
            }
        });

        const data = await response.json();
        if (data.success) {
            window.location.href = 'login.php';
        }
    } catch (error) {
        console.error('Logout error:', error);
        alert('Logout failed. Please try again.');
    }
}

// ==================== Delete Modal ====================
function showDeleteModal(callback) {
    deleteCallback = callback;
    const modal = document.getElementById('deleteModal');
    if (modal) modal.classList.add('show');
}

function hideDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) modal.classList.remove('show');
    deleteCallback = null;
}

function confirmDelete() {
    if (deleteCallback) {
        deleteCallback();
    }
    hideDeleteModal();
}

// ==================== Modal Utilities ====================
// Close modals on overlay click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.classList.remove('show');
        }
    });
});

// Escape key to close modals
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.classList.remove('show');
        });
    }
});

// ==================== Export Functions ====================
// Make functions available globally for inline onclick handlers
window.showLogoutModal = showLogoutModal;
window.hideLogoutModal = hideLogoutModal;
window.confirmLogout = confirmLogout;
window.showDeleteModal = showDeleteModal;
window.hideDeleteModal = hideDeleteModal;
window.confirmDelete = confirmDelete;

console.log('✅ UI Components loaded');
