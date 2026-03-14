<?php
/**
 * Clear All User Data
 * 
 * This script removes all user-generated data from the database:
 * - OJT entries
 * - Entry images
 * - Student information
 * 
 * The database structure remains intact.
 */

require_once __DIR__ . '/../config/config.php';

echo "=== Clear All User Data ===\n\n";

try {
    $pdo = getDbConnection();
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Delete entry images (cascade should handle this, but being explicit)
    $stmt = $pdo->prepare("DELETE FROM entry_images");
    $stmt->execute();
    $imagesDeleted = $stmt->rowCount();
    echo "✓ Deleted $imagesDeleted entry images\n";
    
    // Delete OJT entries
    $stmt = $pdo->prepare("DELETE FROM ojt_entries");
    $stmt->execute();
    $entriesDeleted = $stmt->rowCount();
    echo "✓ Deleted $entriesDeleted OJT entries\n";
    
    // Reset student info (keep the row but clear data)
    $stmt = $pdo->prepare("
        UPDATE student_info 
        SET student_name = '',
            company_name = '',
            company_address = '',
            student_role = '',
            introduction = '',
            purpose_role = '',
            conclusion = '',
            recommendations = '',
            updated_at = CURRENT_TIMESTAMP
        WHERE id = 1
    ");
    $stmt->execute();
    echo "✓ Cleared student information\n";
    
    // Commit transaction
    $pdo->commit();
    
    echo "\n=== Clear Complete ===\n";
    echo "All user data has been removed.\n";
    echo "The database structure remains intact.\n";
    
} catch (PDOException $e) {
    // Rollback on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
