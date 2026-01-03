<?php
/**
 * Assign Owners to Teams Screen
 * Allows selecting owners and automatically creates teams
 */

require_once 'db.php';
require_once 'functions.php';

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    
    $ownerIds = $_POST['owner_ids'] ?? [];
    
    if (empty($ownerIds)) {
        $message = "Please select at least one owner.";
        $messageType = 'error';
    } else {
        // Validate owner IDs
        $validOwnerIds = [];
        $invalidOwnerIds = [];
        
        foreach ($ownerIds as $ownerId) {
            $owner = getOwner((int)$ownerId);
            if ($owner) {
                $validOwnerIds[] = (int)$ownerId;
            } else {
                $invalidOwnerIds[] = $ownerId;
            }
        }
        
        if (!empty($invalidOwnerIds)) {
            $message = "Invalid owner IDs: " . implode(', ', $invalidOwnerIds);
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
            $teamNames = ['Team A', 'Team B', 'Team C', 'Team D', 'Team E', 'Team F', 'Team G', 'Team H', 'Team I', 'Team J', 'Team K'];
            $createdCount = 0;
            $errors = [];
            
            foreach ($validOwnerIds as $index => $ownerId) {
                $teamName = $teamNames[$index] ?? "Team " . chr(65 + $index); // A, B, C, etc.
                
                try {
                    $stmt = $db->prepare("
                        INSERT INTO teams (team_name, owner_id)
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$teamName, $ownerId]);
                    $createdCount++;
                } catch (PDOException $e) {
                    $errors[] = "Error creating team for owner ID {$ownerId}: " . $e->getMessage();
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

// Get all owners
$allOwners = getAllOwners();

// Get all teams
$allTeams = getAllTeams();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Owners - Team Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <span>Assign Owners to Teams</span>
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
                        Select owners to assign to teams. Teams will be automatically created as Team A, Team B, etc.
                    </p>
                    
                    <?php if (empty($allOwners)): ?>
                        <div class="message message-info">
                            <span>No owners found. <a href="owners.php" style="color: var(--info); font-weight: 600;">Go to Owners Management</a> to add owners first.</span>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label>Select Owners *</label>
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 12px; margin-top: 12px;">
                                    <?php foreach ($allOwners as $owner): ?>
                                        <label style="display: flex; align-items: center; gap: 8px; padding: 12px; border: 1px solid var(--border); border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                                            <input type="checkbox" name="owner_ids[]" value="<?php echo $owner['id']; ?>"
                                                   style="width: 18px; height: 18px; cursor: pointer;">
                                            <span style="font-weight: 500;"><?php echo htmlspecialchars($owner['owner_name']); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-success" style="margin-top: 20px;">
                                <span>Create Teams</span>
                            </button>
                        </form>
                    <?php endif; ?>
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
                                        <strong>Owner</strong>
                                        <div style="margin-top: 8px; font-size: 16px; font-weight: 600;">
                                            <?php echo htmlspecialchars($team['owner_name']); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="no-members">No teams created yet. Select owners above to create teams.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

