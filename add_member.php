<?php
/**
 * Add Member to Team
 * Handles adding a member to a team with validations
 */

require_once 'db.php';
require_once 'functions.php';

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $team_id = $_POST['team_id'] ?? 0;
    $member_sr_no = $_POST['member_sr_no'] ?? 0;
    
    if (empty($team_id) || empty($member_sr_no)) {
        $message = "Invalid request. Please provide team ID and member SR number.";
        $messageType = 'error';
    } else {
        // Get team info
        $team = getTeam($team_id);
        if (!$team) {
            $message = "Team not found.";
            $messageType = 'error';
        } else {
            // Check if person exists
            $person = getPersonBySR((int)$member_sr_no);
            if (!$person) {
                $message = "Person with SR No {$member_sr_no} not found.";
                $messageType = 'error';
            } else {
                // Check if person is already the captain
                if ($team['captain_sr_no'] == $member_sr_no) {
                    $message = "This person is already the captain of this team.";
                    $messageType = 'error';
                } else {
                    // Check if person is already in any team
                    if (isPersonInTeam((int)$member_sr_no)) {
                        $message = "This person is already assigned to a team.";
                        $messageType = 'error';
                    } else {
                        // Add member to team
                        try {
                            $db = getDB();
                            $stmt = $db->prepare("
                                INSERT INTO team_members (team_id, member_sr_no)
                                VALUES (?, ?)
                            ");
                            $stmt->execute([$team_id, $member_sr_no]);
                            $message = "Member added successfully!";
                            $messageType = 'success';
                        } catch (PDOException $e) {
                            if (strpos($e->getMessage(), 'UNIQUE constraint') !== false) {
                                $message = "This member is already in this team.";
                            } else {
                                $message = "Error: " . $e->getMessage();
                            }
                            $messageType = 'error';
                        }
                    }
                }
            }
        }
    }
}

// Redirect back to teams page after 2 seconds
if ($messageType === 'success') {
    header("Refresh: 2; url=teams.php");
} else {
    // For errors, show message and redirect
    header("Refresh: 3; url=teams.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Member - Team Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Add Member</h1>
        
        <div class="nav">
            <a href="index.php">People Entry</a>
            <a href="captains.php">Select Captains</a>
            <a href="teams.php">Team Dashboard</a>
        </div>
        
        <?php if ($message): ?>
            <div class="message message-<?php echo $messageType; ?>">
                <?php echo $message; ?>
                <br><small>Redirecting to Team Dashboard...</small>
            </div>
        <?php endif; ?>
        
        <p><a href="teams.php" class="btn">Back to Team Dashboard</a></p>
    </div>
</body>
</html>

