# 🎨 UI Components Guide

## Reusable Components for All Pages

This guide explains how to add the modern header, modals, and theme toggle to all dashboard pages.

---

## 📁 Component Files Created

### 1. **JavaScript Components**
- **File:** `assets/js/ui-components.js`
- **Purpose:** Handles theme toggle, modals, and user dropdown
- **Include in:** All pages

### 2. **Modal Components**
- **File:** `includes/modals.php`
- **Purpose:** Logout and delete confirmation modals
- **Include in:** All authenticated pages

### 3. **Dashboard Header**
- **File:** `includes/dashboard-header.php`
- **Purpose:** Modern navigation header
- **Include in:** All dashboard pages

---

## 🚀 How to Add to Any Dashboard Page

### Step 1: Add the Header

At the top of your dashboard page (after `<body>`):

```php
<?php
session_start();
require_once 'config/config.php';
requireAuth();

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Your head content -->
    <style>
        /* Include the CSS from index.php header styles */
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/dashboard-header.php'; ?>
    
    <!-- Your page content -->
</body>
</html>
```

### Step 2: Add Modals and Theme Toggle

Before the closing `</body>` tag:

```php
    <!-- Your scripts -->
    <script src="assets/js/ui-components.js"></script>
    
    <?php include __DIR__ . '/../includes/modals.php'; ?>
</body>
</html>
```

---

## 📋 Complete Example

Here's a complete dashboard page template:

```php
<?php
session_start();
require_once 'config/config.php';
requireAuth();

$currentUser = getCurrentUser();
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $csrfToken; ?>">
    <title>Dashboard - OJT Journal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        /* Copy header styles from index.php */
        .modern-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #764ba2 100%);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        /* ... rest of header styles ... */
    </style>
</head>
<body>
    <!-- Include Header -->
    <?php include __DIR__ . '/../includes/dashboard-header.php'; ?>

    <!-- Your Dashboard Content -->
    <div class="container" style="padding-top: 2rem;">
        <h1>Your Dashboard Content</h1>
        <!-- Your content here -->
    </div>

    <!-- Include Modals and Theme Toggle -->
    <?php include __DIR__ . '/../includes/modals.php'; ?>

    <!-- Scripts -->
    <script src="assets/js/ui-components.js"></script>
</body>
</html>
```

---

## 🎯 Features Included

### ✅ Modern Header
- Gradient background
- Sticky navigation
- User dropdown menu
- Responsive design
- Active page highlighting

### ✅ Modals
- **Logout Modal:** Confirmation before logout
- **Delete Modal:** Confirmation before deleting entries
- Click outside or ESC to close
- Smooth animations

### ✅ Theme Toggle
- Floating button (bottom-right)
- Light/Dark mode
- Saves preference to localStorage
- Smooth transitions

### ✅ User Dropdown
- Shows username and email
- Settings link
- Logout button
- Click outside to close

---

## 🎨 Styling

The components use CSS variables, so they automatically adapt to your theme:

```css
var(--bg-secondary)      /* Background colors */
var(--text-primary)       /* Text colors */
var(--primary-color)      /* Primary accent */
var(--error-color)        /* Error/danger colors */
var(--border-color)       /* Border colors */
```

---

## 📱 Responsive Design

All components are mobile-responsive:
- Header wraps on small screens
- Navigation icons only (no text) on mobile
- User details hidden on mobile
- Modals are full-width on small screens

---

## 🔧 Customization

### Change Header Color
Edit the gradient in `includes/dashboard-header.php` or your CSS:

```css
.modern-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
```

### Change Modal Text
Edit `includes/modals.php` - just update the text content.

### Add More Navigation Links
Edit `includes/dashboard-header.php` and add more `.nav-link` elements.

---

## 📄 Files to Update

To apply to all dashboards, update these pages:

1. ✅ `index.php` - **Already updated**
2. 🔄 `dashboards/agents-dashboard.php` - Needs update
3. 🔄 `settings.php` - Needs update
4. 🔄 Any other dashboard pages

---

## 💡 Tips

1. **Always include `ui-components.js`** - It handles all interactivity
2. **Include modals on every authenticated page** - For consistent UX
3. **Use the header include** - Maintains consistency across pages
4. **Test on mobile** - All components are responsive

---

## 🐛 Troubleshooting

### Modals not showing?
- Check if `ui-components.js` is loaded
- Verify modal HTML is included
- Check browser console for errors

### Theme toggle not working?
- Ensure button has `id="themeToggle"`
- Check if localStorage is enabled
- Verify CSS for dark mode is present

### User dropdown not opening?
- Check if `userMenuBtn` and `userDropdown` IDs exist
- Verify `ui-components.js` is loaded
- Check for JavaScript errors

---

## 📞 Need Help?

If you encounter issues:
1. Check browser console for errors
2. Verify all includes are correct
3. Ensure CSS variables are defined
4. Test in different browsers

---

**Last Updated:** March 14, 2026  
**Version:** 1.0.0
