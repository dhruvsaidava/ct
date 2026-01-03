<?php
/**
 * Database Connection and Schema Setup
 * SQLite database for Team Management Platform
 */

// Database file path
define('DB_PATH', __DIR__ . '/team_platform.db');

/**
 * Get database connection
 * @return PDO
 */
function getDB() {
    try {
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $db;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

/**
 * Initialize database tables if they don't exist
 */
function initDatabase() {
    $db = getDB();
    
    // Create People table
    $db->exec("
        CREATE TABLE IF NOT EXISTS people (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            entry_no TEXT,
            sr_no INTEGER UNIQUE NOT NULL,
            full_name TEXT NOT NULL,
            mobile_number TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Create Teams table
    $db->exec("
        CREATE TABLE IF NOT EXISTS teams (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            team_name TEXT NOT NULL,
            captain_sr_no INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (captain_sr_no) REFERENCES people(sr_no)
        )
    ");
    
    // Create Team Members table
    $db->exec("
        CREATE TABLE IF NOT EXISTS team_members (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            team_id INTEGER NOT NULL,
            member_sr_no INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (team_id) REFERENCES teams(id),
            FOREIGN KEY (member_sr_no) REFERENCES people(sr_no),
            UNIQUE(team_id, member_sr_no)
        )
    ");
    
    // Create indexes for better performance
    $db->exec("CREATE INDEX IF NOT EXISTS idx_people_sr_no ON people(sr_no)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_teams_captain ON teams(captain_sr_no)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_team_members_team ON team_members(team_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_team_members_member ON team_members(member_sr_no)");
}

// Initialize database on include
initDatabase();
?>

