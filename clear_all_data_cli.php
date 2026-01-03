<?php
/**
 * Clear All Data - CLI Script
 * Deletes all records from all tables
 */

require_once 'db.php';

try {
    $db = getDB();
    
    // Disable foreign key constraints temporarily
    $db->exec("PRAGMA foreign_keys = OFF");
    
    // Delete in order: team_members -> teams -> people
    $db->exec("DELETE FROM team_members");
    $db->exec("DELETE FROM teams");
    $db->exec("DELETE FROM people");
    
    // Re-enable foreign key constraints
    $db->exec("PRAGMA foreign_keys = ON");
    
    echo "✓ All data cleared successfully!\n";
    echo "  - Team members deleted\n";
    echo "  - Teams deleted\n";
    echo "  - People deleted\n";
} catch (PDOException $e) {
    echo "✗ Error clearing data: " . $e->getMessage() . "\n";
    exit(1);
}
?>

