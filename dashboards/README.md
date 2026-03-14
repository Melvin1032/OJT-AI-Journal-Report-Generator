# Dashboards Directory

This folder contains all dashboard interfaces for the OJT Journal Report Generator.

## Current Dashboards

### 📊 AI Agents Dashboard (`agents-dashboard.php`)
**Purpose:** Dedicated interface for AI Agent operations

**Features:**
- AI Agents control panel
- Real-time agent execution
- Result display and export
- Statistics tracking

**Access:** `http://localhost:8000/dashboards/agents-dashboard.php`

---

## Future Dashboards (Planned)

### 📈 Analytics Dashboard
- Entry statistics and trends
- Visual charts and graphs
- Progress tracking over time
- Export analytics reports

### 👤 Student Portfolio Dashboard
- Showcase of OJT work
- Skills visualization
- Achievement timeline
- Shareable portfolio page

### 📋 Admin Dashboard (if multi-user)
- User management
- System statistics
- Activity logs
- Configuration settings

---

## Adding a New Dashboard

1. **Create the file** in this directory
   ```php
   // dashboards/analytics-dashboard.php
   ```

2. **Use the common structure:**
   ```php
   <!DOCTYPE html>
   <html lang="en">
   <head>
       <meta charset="UTF-8">
       <title>Dashboard Name - OJT Journal</title>
       <link rel="stylesheet" href="../assets/css/style.css">
   </head>
   <body>
       <!-- Dashboard content -->
       <script src="../assets/js/utils.js"></script>
   </body>
   </html>
   ```

3. **Add navigation link** in `index.php` header:
   ```php
   <a href="dashboards/your-dashboard.php" class="btn">
       Your Dashboard
   </a>
   ```

4. **Update this README** with your new dashboard

---

## File Structure

```
dashboards/
├── README.md                     # This file
├── agents-dashboard.php          # AI Agents interface
├── analytics-dashboard.php       # (Future) Analytics
└── portfolio-dashboard.php       # (Future) Portfolio
```

---

## Common Resources

All dashboards share these resources:

- **Styles:** `../assets/css/style.css`
- **Scripts:** `../assets/js/`
- **API:** `../src/process.php`
- **Config:** `../config/config.php`

---

## Best Practices

1. **Consistent Navigation**
   - Include "Back to Journal" button
   - Use `../index.php` for the link

2. **Responsive Design**
   - Use CSS Grid and Flexbox
   - Test on mobile devices

3. **Performance**
   - Lazy load heavy content
   - Use caching where appropriate

4. **Accessibility**
   - Use semantic HTML
   - Add ARIA labels
   - Ensure keyboard navigation

5. **Security**
   - Validate all inputs
   - Use CSRF tokens
   - Sanitize outputs

---

## Access Control

Currently all dashboards are public (no authentication).

For future multi-user support, add authentication:
```php
<?php
require_once '../config/config.php';
// Add authentication check here
?>
```

---

**Maintained by:** Development Team  
**Last Updated:** 2024
