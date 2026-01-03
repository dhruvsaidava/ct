<?php
/**
 * Delete Handler
 * Handles deletion of people, team members, and teams
 */

require_once 'auth.php';
requireLogin();

require_once 'db.php';
require_once 'functions.php';

$message = '';
$messageType = '';
$redirect = 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
    
    switch ($action) {
        case 'person':
            if (isset($_GET['id'])) {
                $result = deletePerson((int)$_GET['id']);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                $redirect = 'index.php';
            }
            break;
            
        case 'team_member':
            if (isset($_GET['id'])) {
                $result = deleteTeamMember((int)$_GET['id']);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                $redirect = 'teams.php';
            }
            break;
            
        case 'team':
            if (isset($_GET['id'])) {
                $result = deleteTeam((int)$_GET['id']);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                $redirect = 'teams.php';
            }
            break;
            
        default:
            $message = 'Invalid action.';
            $messageType = 'error';
    }
}

// Redirect after 2 seconds
header("Refresh: 2; url={$redirect}");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete - Team Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Delete</h1>
        
        <div class="nav">
            <a href="index.php">People Entry</a>
            <a href="assign_owners.php">Assign Owners</a>
            <a href="teams.php">Team Dashboard</a>
        </div>
        
        <?php if ($message): ?>
            <div class="message message-<?php echo $messageType; ?>">
                <?php echo $message; ?>
                <br><small>Redirecting...</small>
            </div>
        <?php endif; ?>
        
        <p><a href="<?php echo $redirect; ?>" class="btn">Go Back</a></p>
    </div>
</body>
</html>

