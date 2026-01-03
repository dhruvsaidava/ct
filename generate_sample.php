<?php
/**
 * Generate Sample Data
 * Creates 55 sample members for testing
 */

require_once 'db.php';
require_once 'functions.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $result = generateSampleData();
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';
}

// Get current count
$db = getDB();
$currentCount = $db->query("SELECT COUNT(*) as count FROM people")->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Sample Data - Team Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <span>Generate Sample Data</span>
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
                                <div class="stat-card-value"><?php echo $currentCount; ?></div>
                                <div class="stat-card-label">Current People in Database</div>
                            </div>
                            <div class="stat-card-icon"></div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <span>Generate 55 Sample Members</span>
                        </div>
                    </div>
                    
                    <p class="help-text">
                        This will create 55 sample people with random names and phone numbers.<br>
                        SR numbers will be 1-55. If SR numbers already exist, they will be skipped.
                    </p>
                    
                    <form method="POST" action="" onsubmit="return confirm('Generate 55 sample members? Existing SR numbers will be skipped.');">
                        <button type="submit" name="generate" class="btn btn-success">
                            <span>Generate Sample Data</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

