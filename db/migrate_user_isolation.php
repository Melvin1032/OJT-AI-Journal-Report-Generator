<?php
/**
 * Migration: Add User Isolation to OJT Entries
 * 
 * This adds session_id to ojt_entries and entry_images tables
 * to properly isolate data between different users.
 * 
 * Run this ONCE on InfinityFree after uploading.
 */

require_once __DIR__ . '/../config/config.php';

echo "<h2>Migration: Add User Isolation</h2>";

try {
    $pdo = getDbConnection();
    
    echo "<h3>Step 1: Adding session_id to ojt_entries...</h3>";
    
    // Check if column already exists
    $stmt = $pdo->query("PRAGMA table_info(ojt_entries)");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('session_id', $columns)) {
        $pdo->exec("ALTER TABLE ojt_entries ADD COLUMN session_id TEXT DEFAULT NULL");
        echo "✓ Added session_id column to ojt_entries<br>";
        
        // Create index for faster lookups
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_ojt_entries_session_id ON ojt_entries(session_id)");
        echo "✓ Created index on session_id<br>";
    } else {
        echo "⊘ session_id column already exists<br>";
    }
    
    echo "<h3>Step 2: Adding session_id to entry_images...</h3>";
    
    $stmt = $pdo->query("PRAGMA table_info(entry_images)");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('session_id', $columns)) {
        $pdo->exec("ALTER TABLE entry_images ADD COLUMN session_id TEXT DEFAULT NULL");
        echo "✓ Added session_id column to entry_images<br>";
        
        // Create index
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_entry_images_session_id ON entry_images(session_id)");
        echo "✓ Created index on session_id<br>";
    } else {
        echo "⊘ session_id column already exists<br>";
    }
    
    echo "<h3>Step 3: Updating existing entries with current session...</h3>";
    
    $currentSession = session_id();
    
    // Update entries without session_id
    $stmt = $pdo->prepare("UPDATE ojt_entries SET session_id = ? WHERE session_id IS NULL");
    $stmt->execute([$currentSession]);
    $entriesUpdated = $stmt->rowCount();
    echo "✓ Updated {$entriesUpdated} entries with current session<br>";
    
    // Update images without session_id
    $stmt = $pdo->prepare("UPDATE entry_images SET session_id = ? WHERE session_id IS NULL");
    $stmt->execute([$currentSession]);
    $imagesUpdated = $stmt->rowCount();
    echo "✓ Updated {$imagesUpdated} images with current session<br>";
    
    echo "<h3>Step 4: Adding student_info session isolation...</h3>";
    
    $stmt = $pdo->query("PRAGMA table_info(student_info)");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('session_id', $columns)) {
        $pdo->exec("ALTER TABLE student_info ADD COLUMN session_id TEXT DEFAULT NULL");
        echo "✓ Added session_id column to student_info<br>";
        
        // Update existing student info
        $stmt = $pdo->prepare("UPDATE student_info SET session_id = ? WHERE session_id IS NULL");
        $stmt->execute([$currentSession]);
        echo "✓ Updated student_info with current session<br>";
    } else {
        echo "⊘ session_id column already exists<br>";
    }
    
    echo "<h3>✅ Migration Complete!</h3>";
    echo "<p><strong>Important:</strong> Delete this file after running for security.</p>";
    echo "<p><a href='index.php'>Go to Home</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'><strong>✗ Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    exit(1);
}
?>
