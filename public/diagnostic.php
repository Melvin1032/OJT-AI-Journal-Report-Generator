<?php
/**
 * System Diagnostic Tool
 * Run this page to check for common issues
 */

session_start();
require_once __DIR__ . '/../config/config.php';

// Simple authentication check
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Diagnostic</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
            background: #f8f9fa;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #333; margin-bottom: 0.5rem; }
        .status {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 8px;
            border-left: 4px solid;
        }
        .status.success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .status.error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .status.warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        .status.info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .badge-success {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }
        .badge-error {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 0.5rem 0.5rem 0.5rem 0;
        }
        .btn:hover { background: #5568d3; }
        pre {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 System Diagnostic</h1>
        <p>Checking for common issues...</p>

        <?php
        $issues = 0;
        $warnings = 0;

        // 1. Check PHP version
        echo '<h2>1. PHP Configuration</h2>';
        $phpVersion = phpversion();
        if (version_compare($phpVersion, '7.4.0', '>=')) {
            echo '<div class="status success">✓ PHP version: ' . $phpVersion . '</div>';
        } else {
            echo '<div class="status error">✗ PHP version too old: ' . $phpVersion . ' (requires 7.4+)</div>';
            $issues++;
        }

        // 2. Check SQLite extension
        echo '<h2>2. Database</h2>';
        if (extension_loaded('PDO') && extension_loaded('pdo_sqlite')) {
            echo '<div class="status success">✓ SQLite PDO extension is enabled</div>';
        } else {
            echo '<div class="status error">✗ SQLite PDO extension is NOT enabled</div>';
            $issues++;
        }

        // 3. Check database file
        if (file_exists(DB_PATH)) {
            echo '<div class="status success">✓ Database file exists: ' . DB_PATH . '</div>';
            
            // Check if writable
            if (is_writable(DB_PATH)) {
                echo '<div class="status success">✓ Database file is writable</div>';
            } else {
                echo '<div class="status error">✗ Database file is NOT writable</div>';
                $issues++;
            }
        } else {
            echo '<div class="status warning">⚠ Database file does not exist yet (will be created on first use)</div>';
            $warnings++;
        }

        // 4. Check database schema
        try {
            $pdo = getDbConnection();
            
            // Check tables
            $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
            $tables = array_column($stmt->fetchAll(), 'name');
            
            echo '<div class="status info">✓ Connected to database</div>';
            echo '<div class="status info">Tables: ' . implode(', ', $tables) . '</div>';
            
            // Check for user_id columns
            $requiredColumns = [
                'ojt_entries' => 'user_id',
                'entry_images' => 'user_id',
                'student_info' => 'user_id',
                'user_api_keys' => 'user_id'
            ];
            
            foreach ($requiredColumns as $table => $column) {
                if (in_array($table, $tables)) {
                    $stmt = $pdo->query("PRAGMA table_info($table)");
                    $columns = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');
                    
                    if (in_array($column, $columns)) {
                        echo '<div class="status success">✓ Table <strong>' . $table . '</strong> has <strong>' . $column . '</strong> column</div>';
                    } else {
                        echo '<div class="status error">✗ Table <strong>' . $table . '</strong> is missing <strong>' . $column . '</strong> column</div>';
                        echo '<p style="margin-left: 1rem;">Run migration: <a href="db/migrate.php">db/migrate.php</a></p>';
                        $issues++;
                    }
                } else {
                    echo '<div class="status warning">⚠ Table <strong>' . $table . '</strong> does not exist (will be created on first use)</div>';
                    $warnings++;
                }
            }
            
        } catch (PDOException $e) {
            echo '<div class="status error">✗ Database connection failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
            $issues++;
        }

        // 5. Check uploads directory
        echo '<h2>3. File Uploads</h2>';
        if (file_exists(UPLOAD_DIR)) {
            echo '<div class="status success">✓ Upload directory exists: ' . UPLOAD_DIR . '</div>';
            
            if (is_writable(UPLOAD_DIR)) {
                echo '<div class="status success">✓ Upload directory is writable</div>';
            } else {
                echo '<div class="status error">✗ Upload directory is NOT writable</div>';
                echo '<p style="margin-left: 1rem;">Fix: <code>chmod 755 ' . UPLOAD_DIR . '</code></p>';
                $issues++;
            }
        } else {
            echo '<div class="status error">✗ Upload directory does not exist: ' . UPLOAD_DIR . '</div>';
            echo '<p style="margin-left: 1rem;">Fix: Create the directory and set permissions</p>';
            $issues++;
        }

        // 6. Check API keys configuration
        echo '<h2>4. API Keys Configuration</h2>';
        echo '<div class="status info">';
        echo 'Logged in: ' . ($isLoggedIn ? '<strong>Yes</strong> (User ID: ' . $_SESSION['user_id'] . ')' : '<strong>No</strong>');
        echo '</div>';
        
        if ($isLoggedIn) {
            $userKeys = getUserApiKeys();
            if ($userKeys) {
                echo '<div class="status success">✓ User has API keys configured</div>';
                echo '<table>';
                echo '<tr><th>Service</th><th>Status</th></tr>';
                echo '<tr><td>OpenRouter</td><td>' . (empty($userKeys['openrouter']) ? '<span class="badge badge-error">Not Set</span>' : '<span class="badge badge-success">Configured</span>') . '</td></tr>';
                echo '<tr><td>Google Gemini</td><td>' . (empty($userKeys['gemini']) ? '<span class="badge badge-error">Not Set</span>' : '<span class="badge badge-success">Configured</span>') . '</td></tr>';
                echo '<tr><td>Groq</td><td>' . (empty($userKeys['groq']) ? '<span class="badge badge-error">Not Set</span>' : '<span class="badge badge-success">Configured</span>') . '</td></tr>';
                echo '</table>';
            } else {
                echo '<div class="status warning">⚠ User has NO API keys configured</div>';
                echo '<p style="margin-left: 1rem;">Go to <a href="setup.php">Setup Page</a> to configure API keys</p>';
                $warnings++;
            }
        } else {
            echo '<div class="status warning">⚠ Not logged in - cannot check user API keys</div>';
            echo '<p style="margin-left: 1rem;"><a href="login.php" class="btn">Login</a> <a href="register.php" class="btn">Register</a></p>';
            $warnings++;
        }

        // 7. Check global API keys (from .env)
        echo '<h2>5. Global API Keys (from .env)</h2>';
        if (defined('QWEN_API_KEY') && !empty(QWEN_API_KEY)) {
            echo '<div class="status success">✓ OpenRouter API key is configured globally</div>';
        } else {
            echo '<div class="status warning">⚠ No global OpenRouter API key (users must configure their own)</div>';
            $warnings++;
        }

        // 8. Session check
        echo '<h2>6. Session</h2>';
        echo '<div class="status info">';
        echo 'Session ID: ' . session_id() . '<br>';
        echo 'Session Status: ' . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . '<br>';
        if (isset($_SESSION['api_keys_configured'])) {
            echo 'API Keys Configured: ' . ($_SESSION['api_keys_configured'] ? 'Yes' : 'No');
        } else {
            echo 'API Keys Configured: Not set in session';
        }
        echo '</div>';

        // Summary
        echo '<h2>Summary</h2>';
        if ($issues === 0 && $warnings === 0) {
            echo '<div class="status success">';
            echo '<strong>✅ All checks passed!</strong><br>';
            echo 'Your system is properly configured.';
            echo '</div>';
        } elseif ($issues > 0) {
            echo '<div class="status error">';
            echo '<strong>❌ Found ' . $issues . ' critical issue(s)</strong><br>';
            echo 'Please fix the issues above before using the application.';
            echo '</div>';
        } else {
            echo '<div class="status warning">';
            echo '<strong>⚠ Found ' . $warnings . ' warning(s)</strong><br>';
            echo 'The application should work, but some features may be limited.';
            echo '</div>';
        }

        echo '<div style="margin-top: 2rem;">';
        echo '<a href="index.php" class="btn">Go to Dashboard</a>';
        echo '<a href="setup.php" class="btn">API Setup</a>';
        echo '<a href="db/migrate.php" class="btn">Run Migration</a>';
        echo '</div>';
        ?>
    </div>
</body>
</html>
