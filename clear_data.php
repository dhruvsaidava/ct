<?php
/**
 * Clear All Data
 * Deletes all records from all tables
 */

require_once 'db.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear'])) {
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
        
        $message = "All data has been cleared successfully!";
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = "Error clearing data: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Get current counts
$db = getDB();
$peopleCount = $db->query("SELECT COUNT(*) as count FROM people")->fetch()['count'];
$teamsCount = $db->query("SELECT COUNT(*) as count FROM teams")->fetch()['count'];
$teamMembersCount = $db->query("SELECT COUNT(*) as count FROM team_members")->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clear All Data - Team Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <span>Clear All Data</span>
                </div>
            </div>
            
            <div class="container">
                <?php if ($message): ?>
                    <div class="message message-<?php echo $messageType; ?>">
                        <span><?php echo $message; ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-card-value"><?php echo $peopleCount; ?></div>
                                <div class="stat-card-label">People</div>
                            </div>
                            <div class="stat-card-icon"></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-card-value"><?php echo $teamsCount; ?></div>
                                <div class="stat-card-label">Teams</div>
                            </div>
                            <div class="stat-card-icon"></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-card-value"><?php echo $teamMembersCount; ?></div>
                                <div class="stat-card-label">Team Members</div>
                            </div>
                            <div class="stat-card-icon"></div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <span>Clear All Data</span>
                        </div>
                    </div>
                    
                    <p class="help-text" style="color: #dc3545; font-weight: bold;">
                        ⚠️ WARNING: This will permanently delete ALL data from the database!<br>
                        This includes all people, teams, and team members.<br>
                        This action cannot be undone.
                    </p>
                    
                    <form method="POST" action="" onsubmit="return confirm('Are you absolutely sure you want to delete ALL data? This action cannot be undone!');">
                        <button type="submit" name="clear" class="btn" style="background-color: #dc3545; color: white;">
                            <span>Clear All Data</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

