<!-- Reusable Modal Components - Include in any dashboard page -->
<!-- Usage: <?php include __DIR__ . '/../includes/modals.php'; ?> -->

<!-- Logout Modal -->
<div class="modal-overlay" id="logoutModal">
    <div class="modal-container">
        <div class="modal-icon warning">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="32" height="32">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
        </div>
        <h2 class="modal-title">Logout</h2>
        <p class="modal-message">Are you sure you want to logout? You will need to sign in again to access your journal.</p>
        <div class="modal-actions">
            <button class="modal-btn secondary" onclick="hideLogoutModal()">Cancel</button>
            <button class="modal-btn danger" onclick="confirmLogout()">Logout</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-container">
        <div class="modal-icon danger">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="32" height="32">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                <line x1="10" y1="11" x2="10" y2="17"/>
                <line x1="14" y1="11" x2="14" y2="17"/>
            </svg>
        </div>
        <h2 class="modal-title">Delete Entry</h2>
        <p class="modal-message">Are you sure you want to delete this entry? This action cannot be undone.</p>
        <div class="modal-actions">
            <button class="modal-btn secondary" onclick="hideDeleteModal()">Cancel</button>
            <button class="modal-btn danger" onclick="confirmDelete()">Delete</button>
        </div>
    </div>
</div>

<!-- Theme Toggle Button -->
<button class="theme-toggle-floating" id="themeToggle" aria-label="Toggle dark mode">
    <svg class="sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
        <circle cx="12" cy="12" r="5"/>
        <line x1="12" y1="1" x2="12" y2="3"/>
        <line x1="12" y1="21" x2="12" y2="23"/>
        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
        <line x1="1" y1="12" x2="3" y2="12"/>
        <line x1="21" y1="12" x2="23" y2="12"/>
        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
    </svg>
    <svg class="moon-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24" style="display: none;">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
    </svg>
</button>
