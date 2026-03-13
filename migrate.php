<?php
/**
 * Database Migration Script
 * Updates the database schema for OJT journal entries
 */

require_once 'config.php';

try {
    $pdo = getDbConnection();
    
    // Create new table structure for OJT entries
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS ojt_entries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            user_description TEXT,
            entry_date DATE NOT NULL,
            ai_enhanced_description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Create images table for multiple images per entry
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS entry_images (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            entry_id INTEGER NOT NULL,
            image_path TEXT NOT NULL,
            image_order INTEGER DEFAULT 0,
            ai_description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (entry_id) REFERENCES ojt_entries(id) ON DELETE CASCADE
        )
    ");
    
    // Create indexes
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_entry_date ON ojt_entries(entry_date)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_entry_images_entry_id ON entry_images(entry_id)");
    
    // Migrate existing data from journal_entries if any
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM journal_entries");
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        echo "Migrating {$result['count']} existing entries...\n";
        
        $oldEntries = $pdo->query("SELECT * FROM journal_entries ORDER BY created_at")->fetchAll();
        
        foreach ($oldEntries as $entry) {
            // Extract date from created_at
            $entryDate = date('Y-m-d', strtotime($entry['created_at']));
            
            // Create OJT entry with AI description as enhanced description
            $stmt = $pdo->prepare("
                INSERT INTO ojt_entries (title, user_description, entry_date, ai_enhanced_description, created_at)
                VALUES (:title, :user_desc, :entry_date, :ai_desc, :created_at)
            ");
            
            $stmt->execute([
                ':title' => 'OJT Activity - ' . $entryDate,
                ':user_desc' => null,
                ':entry_date' => $entryDate,
                ':ai_desc' => $entry['ai_description'],
                ':created_at' => $entry['created_at']
            ]);
            
            $newEntryId = $pdo->lastInsertId();
            
            // Add the image
            $stmt = $pdo->prepare("
                INSERT INTO entry_images (entry_id, image_path, image_order, ai_description, created_at)
                VALUES (:entry_id, :image_path, 0, :ai_desc, :created_at)
            ");
            
            $stmt->execute([
                ':entry_id' => $newEntryId,
                ':image_path' => $entry['image_path'],
                ':ai_desc' => $entry['ai_description'],
                ':created_at' => $entry['created_at']
            ]);
        }
        
        echo "Migration completed successfully!\n";
    }
    
    echo "Database schema updated successfully!\n";
    echo "\nNew tables created:\n";
    echo "- ojt_entries (main entries with title, description, date)\n";
    echo "- entry_images (multiple images per entry)\n";
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
