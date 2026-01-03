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
    
    // Create Users table for portal managers
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            full_name TEXT NOT NULL,
            email TEXT,
            is_active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_login DATETIME
        )
    ");
    
    // Create Owners table
    $db->exec("
        CREATE TABLE IF NOT EXISTS owners (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            owner_name TEXT NOT NULL UNIQUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
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
    
    // Check if teams table exists with old structure (captain_sr_no)
    $tableInfo = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='teams'")->fetch();
    if ($tableInfo) {
        // Check if old column exists
        $columns = $db->query("PRAGMA table_info(teams)")->fetchAll();
        $hasCaptainColumn = false;
        foreach ($columns as $col) {
            if ($col['name'] === 'captain_sr_no') {
                $hasCaptainColumn = true;
                break;
            }
        }
        
        // Migrate from captain_sr_no to owner_id if needed
        if ($hasCaptainColumn) {
            // Drop old teams and team_members tables (data will be lost, but user wants to clear anyway)
            $db->exec("DROP TABLE IF EXISTS team_members");
            $db->exec("DROP TABLE IF EXISTS teams");
        }
    }
    
    // Create Teams table with owner_id
    $db->exec("
        CREATE TABLE IF NOT EXISTS teams (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            team_name TEXT NOT NULL,
            owner_id INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (owner_id) REFERENCES owners(id)
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
    $db->exec("CREATE INDEX IF NOT EXISTS idx_teams_owner ON teams(owner_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_team_members_team ON team_members(team_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_team_members_member ON team_members(member_sr_no)");
    
    // Seed default owners if table is empty
    seedDefaultOwners($db);
    
    // Seed default admin user if table is empty
    seedDefaultAdmin($db);
}

/**
 * Seed default owners
 */
function seedDefaultOwners($db) {
    $defaultOwners = [
        'Nileshbhai Dharampur',
        'Navnitbhai Vasudev bhai',
        'Bharatbhai Motka',
        'Dharmendrabhai Motka',
        'Dhruv Motka',
        'Dhruv zalodiya',
        'Shrey Pareshbhai Motka',
        'Nyalkaran Group',
        'Vipul B Zalodiya',
        'Priteshbhai Patel',
        'Dilip Metal'
    ];
    
    $count = $db->query("SELECT COUNT(*) as count FROM owners")->fetch()['count'];
    if ($count == 0) {
        $stmt = $db->prepare("INSERT INTO owners (owner_name) VALUES (?)");
        foreach ($defaultOwners as $ownerName) {
            try {
                $stmt->execute([$ownerName]);
            } catch (PDOException $e) {
                // Ignore duplicates
            }
        }
    }
}

/**
 * Seed default admin user
 */
function seedDefaultAdmin($db) {
    $count = $db->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
    if ($count == 0) {
        // Default admin: username: admin, password: admin123
        $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("
            INSERT INTO users (username, password_hash, full_name, email, is_active)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute(['admin', $passwordHash, 'Administrator', 'admin@example.com', 1]);
    }
}

// Initialize database on include
initDatabase();
?>

