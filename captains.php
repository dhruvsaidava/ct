<?php
/**
 * Captain Selection Screen
 * Allows selecting captains by SR numbers and automatically creates teams
 */

require_once 'db.php';
require_once 'functions.php';

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    
    $captainSRs = $_POST['captain_sr_nos'] ?? '';
    
    if (empty($captainSRs)) {
        $message = "Please enter at least one captain SR number.";
        $messageType = 'error';
    } else {
        // Parse comma-separated SR numbers
        $srNumbers = array_map('trim', explode(',', $captainSRs));
        $srNumbers = array_filter($srNumbers, function($sr) {
            return !empty($sr) && is_numeric($sr);
        });
        
        if (empty($srNumbers)) {
            $message = "Please enter valid SR numbers.";
            $messageType = 'error';
        } else {
            // Check if all SR numbers exist
            $invalidSRs = [];
            $validSRs = [];
            
            foreach ($srNumbers as $sr) {
                $person = getPersonBySR((int)$sr);
                if ($person) {
                    $validSRs[] = (int)$sr;
                } else {
                    $invalidSRs[] = $sr;
                }
            }
            
            if (!empty($invalidSRs)) {
                $message = "Invalid SR numbers: " . implode(', ', $invalidSRs);
                $messageType = 'error';
            } else {
                // Check if teams already exist
                $existingTeams = getAllTeams();
                if (!empty($existingTeams)) {
                    // Clear existing teams
                    $db->exec("DELETE FROM team_members");
                    $db->exec("DELETE FROM teams");
                }
                
                // Create teams
                $teamNames = ['Team A', 'Team B', 'Team C', 'Team D', 'Team E', 'Team F', 'Team G', 'Team H'];
                $createdCount = 0;
                $errors = [];
                
                foreach ($validSRs as $index => $sr) {
                    $teamName = $teamNames[$index] ?? "Team " . chr(65 + $index); // A, B, C, etc.
                    
                    try {
                        $stmt = $db->prepare("
                            INSERT INTO teams (team_name, captain_sr_no)
                            VALUES (?, ?)
                        ");
                        $stmt->execute([$teamName, $sr]);
                        $createdCount++;
                    } catch (PDOException $e) {
                        $errors[] = "Error creating team for SR {$sr}: " . $e->getMessage();
                    }
                }
                
                if ($createdCount > 0) {
                    $message = "Successfully created {$createdCount} team(s)!";
                    $messageType = 'success';
                }
                if (!empty($errors)) {
                    $message .= '<br><small>' . implode('<br>', $errors) . '</small>';
                }
            }
        }
    }
}

// Get all teams
$allTeams = getAllTeams();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Captains - Team Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <span>Select Captains</span>
                </div>
            </div>
            
            <div class="container">
                <?php if ($message): ?>
                    <div class="message message-<?php echo $messageType; ?>">
                        <span><?php echo $message; ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <span>Create Teams</span>
                        </div>
                    </div>
                    
                    <p class="help-text">
                        Enter captain SR numbers separated by commas. Teams will be automatically created as Team A, Team B, etc.
                    </p>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="captain_sr_nos">Captain SR Numbers *</label>
                            <input type="text" id="captain_sr_nos" name="captain_sr_nos" required
                                   placeholder="1, 5, 12, 20"
                                   value="<?php echo htmlspecialchars($_POST['captain_sr_nos'] ?? ''); ?>">
                            <p class="help-text">Example: 1,5,12,20 (comma-separated)</p>
                        </div>
                        
                        <button type="submit" class="btn btn-success">
                            <span>Create Teams</span>
                        </button>
                    </form>
                </div>
                
                <?php if (!empty($allTeams)): ?>
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <span>Current Teams (<?php echo count($allTeams); ?>)</span>
                            </div>
                        </div>
                        
                        <div class="teams-grid">
                            <?php foreach ($allTeams as $team): ?>
                                <div class="team-card">
                                    <div class="team-card-header">
                                        <h3><?php echo htmlspecialchars($team['team_name']); ?></h3>
                                    </div>
                                    <div class="captain-info">
                                        <strong>Captain</strong>
                                        <div style="margin-top: 8px; font-size: 16px; font-weight: 600;">
                                            <?php echo htmlspecialchars($team['captain_name']); ?>
                                        </div>
                                        <small style="color: var(--gray);"><?php echo htmlspecialchars($team['captain_mobile']); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="no-members">No teams created yet. Enter captain SR numbers above to create teams.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

