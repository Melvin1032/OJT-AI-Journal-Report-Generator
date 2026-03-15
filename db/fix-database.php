<?php
/**
 * Emergency Database Fix
 * Fixes "no such column: session_id" and missing user_id columns
 * 
 * Visit: http://your-domain.com/db/fix-database.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Fix - OJT Journal</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:system-ui,-apple-system,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#667eea,#764ba2);padding:1rem}
        .container{max-width:800px;width:100%;background:white;border-radius:16px;padding:2rem;box-shadow:0 10px 40px rgba(0,0,0,0.2)}
        h1{color:#1a202c;margin-bottom:1rem}
        .step{padding:1rem;margin:1rem 0;border-radius:8px;border-left:4px solid #ccc}
        .step.success{background:#d4edda;border-color:#28a745;color:#155724}
        .step.error{background:#f8d7da;border-color:#dc3545;color:#721c24}
        .step.info{background:#d1ecf1;border-color:#17a2b8;color:#0c5460}
        .btn{display:inline-block;padding:0.75rem 1.5rem;background:#667eea;color:white;text-decoration:none;border-radius:8px;margin:0.5rem 0.5rem 0.5rem 0;border:none;cursor:pointer}
        .btn:hover{background:#5568d3}
        code{background:#f8f9fa;padding:0.2rem 0.4rem;border-radius:4px;font-family:monospace}
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Database Fix</h1>
        <p>Fixing database schema issues...</p>

        <?php
        try {
            // Find database
            $paths = [
                __DIR__ . '/../storage/db/journal.db',
                __DIR__ . '/../db/journal.db',
                '../storage/db/journal.db',
                '../../storage/db/journal.db',
            ];

            $dbPath = null;
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    $dbPath = $path;
                    break;
                }
            }

            if (!$dbPath) {
                throw new Exception('❌ Database file not found! Check if storage/db/journal.db exists.');
            }

            echo '<div class="step info">✓ Found database: <code>' . htmlspecialchars(basename($dbPath)) . '</code></div>';

            // Connect
            $pdo = new PDO('sqlite:' . $dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo '<div class="step success">✓ Connected to database</div>';

            // Fix user_api_keys table
            $stmt = $pdo->query("PRAGMA table_info(user_api_keys)");
            $cols = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');

            echo '<h2 style="margin-top:1.5rem">Checking user_api_keys table...</h2>';

            if (!in_array('session_id', $cols)) {
                $pdo->exec("ALTER TABLE user_api_keys ADD COLUMN session_id TEXT DEFAULT NULL");
                echo '<div class="step success">✓ Added session_id column</div>';
            } else {
                echo '<div class="step success">✓ session_id column exists</div>';
            }

            if (!in_array('user_id', $cols)) {
                $pdo->exec("ALTER TABLE user_api_keys ADD COLUMN user_id INTEGER DEFAULT NULL");
                echo '<div class="step success">✓ Added user_id column</div>';
            } else {
                echo '<div class="step success">✓ user_id column exists</div>';
            }

            // Create indexes
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_user_api_keys_session_id ON user_api_keys(session_id)");
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_user_api_keys_user_id ON user_api_keys(user_id)");
            echo '<div class="step success">✓ Created indexes</div>';

            // Fix other tables
            $tables = ['ojt_entries', 'entry_images', 'student_info'];
            echo '<h2 style="margin-top:1.5rem">Checking other tables...</h2>';

            foreach ($tables as $table) {
                $stmt = $pdo->query("PRAGMA table_info($table)");
                $cols = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');

                if (!in_array('user_id', $cols)) {
                    $pdo->exec("ALTER TABLE $table ADD COLUMN user_id INTEGER DEFAULT NULL");
                    echo "<div class=\"step success\">✓ Added user_id to $table</div>";
                } else {
                    echo "<div class=\"step success\">✓ user_id exists in $table</div>";
                }
            }

            // Create users table
            $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                email TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            echo '<div class="step success">✓ Users table exists</div>';

            // Create indexes
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_ojt_entries_user_id ON ojt_entries(user_id)");
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_entry_images_user_id ON entry_images(user_id)");
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_student_info_user_id ON student_info(user_id)");
            echo '<div class="step success">✓ Created user_id indexes</div>';

            echo '<div class="step success" style="margin-top:1.5rem">';
            echo '<strong>✅ Database fix completed successfully!</strong><br><br>';
            echo 'All required columns and tables are now in place.';
            echo '</div>';

            echo '<div style="margin-top:2rem">';
            echo '<a href="../index.php" class="btn">Go to Dashboard →</a>';
            echo '<a href="../login.php" class="btn">Go to Login</a>';
            echo '</div>';

        } catch (PDOException $e) {
            echo '<div class="step error">';
            echo '<strong>❌ Database Error:</strong><br>';
            echo '<code>' . htmlspecialchars($e->getMessage()) . '</code>';
            echo '</div>';
            echo '<div class="step info">';
            echo '<strong>Try this:</strong><br>';
            echo '1. Make sure storage/db/ folder exists<br>';
            echo '2. Set permissions: chmod 755 storage/db/<br>';
            echo '3. Refresh this page';
            echo '</div>';
        } catch (Exception $e) {
            echo '<div class="step error">';
            echo '<strong>❌ Error:</strong><br>';
            echo '<code>' . htmlspecialchars($e->getMessage()) . '</code>';
            echo '</div>';
        }
        ?>

        <h2 style="margin-top:2rem">What This Script Does</h2>
        <ul style="margin:1rem 0 1rem 1.5rem;line-height:1.8">
            <li>Adds <code>session_id</code> column to user_api_keys table</li>
            <li>Adds <code>user_id</code> column to all tables</li>
            <li>Creates <code>users</code> table</li>
            <li>Creates indexes for better performance</li>
        </ul>

        <div class="step info" style="margin-top:1.5rem">
            <strong>ℹ Note:</strong> This script is safe to run multiple times. It only adds columns that don't exist.
        </div>
    </div>
</body>
</html>
