<?php
/**
 * Team Detail Page
 * Shows team members with auto-refresh every 5 seconds
 * Accessible via clean URLs: /a, /b, /c, etc.
 */

require_once 'db.php';
require_once 'functions.php';

// Get team identifier from URL
$team_slug = $_GET['team'] ?? '';

if (empty($team_slug)) {
    header("Location: teams.php");
    exit;
}

// Convert slug to team name (e.g., "a" -> "Team A")
$team_name = 'Team ' . strtoupper($team_slug);

// Get team info
$team = getTeamByName($team_name);

if (!$team) {
    // Team not found, redirect to teams page
    header("Location: teams.php");
    exit;
}

// Get team members
$members = getTeamMembers($team['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="5">
    <title><?php echo htmlspecialchars($team['team_name']); ?> - Team Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .members-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 8px;
            margin: 12px 0;
        }
        .member-card {
            background: var(--white);
            border: 1px solid var(--gray-border);
            border-radius: 3px;
            padding: 10px;
            border-left: 2px solid var(--orange);
            font-size: 12px;
        }
        .member-card strong {
            display: block;
            color: var(--black);
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .member-card .sr-no {
            color: var(--gray);
            font-size: 11px;
            margin-bottom: 4px;
        }
        .member-card .mobile {
            color: var(--orange);
            font-size: 11px;
        }
        .refresh-indicator {
            position: fixed;
            top: 10px;
            right: 10px;
            background: var(--orange);
            color: var(--white);
            padding: 6px 12px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
            z-index: 1000;
            border: 2px solid var(--orange);
        }
        .team-header-card {
            background: var(--white);
            color: var(--black);
            border-radius: 3px;
            padding: 12px;
            margin-bottom: 12px;
            border: 2px solid var(--orange);
        }
        .team-header-card h1 {
            color: var(--black);
            margin: 0 0 8px 0;
            font-size: 16px;
            font-weight: 600;
        }
        .captain-badge {
            background: var(--white);
            padding: 8px;
            border-radius: 2px;
            margin-top: 8px;
            border: 2px solid var(--orange);
            border-left: 4px solid var(--orange);
        }
        .captain-badge strong {
            display: block;
            margin-bottom: 4px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
                <div class="top-bar">
                <div class="page-title">
                    <span><?php echo htmlspecialchars($team['team_name']); ?></span>
                </div>
                <a href="teams.php" class="btn btn-secondary btn-small">
                    <span>Back to Dashboard</span>
                </a>
            </div>
            
            <div class="container">
                <div class="refresh-indicator">
            Auto-refreshing every 5 seconds
        </div>
                
                <div class="team-header-card">
                    <h1><?php echo htmlspecialchars($team['team_name']); ?></h1>
                    <div class="captain-badge">
                        <strong>Owner</strong>
                        <div style="font-size: 13px; font-weight: 600; margin-top: 4px;">
                            <?php echo htmlspecialchars($team['owner_name']); ?>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <span>Team Members <span class="badge badge-info"><?php echo count($members); ?></span></span>
                        </div>
                    </div>
                    
                    <?php if (empty($members)): ?>
                        <div class="no-members">No members added to this team yet.</div>
                    <?php else: ?>
                        <div class="members-grid">
                            <?php foreach ($members as $member): ?>
                                <div class="member-card">
                                    <strong><?php echo htmlspecialchars($member['full_name']); ?></strong>
                                    <div class="sr-no">SR No: <?php echo htmlspecialchars($member['sr_no']); ?></div>
                                    <div class="mobile"><?php echo htmlspecialchars($member['mobile_number']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div style="text-align: center; color: var(--gray); font-size: 11px; margin-top: 12px; padding: 8px; background: var(--gray-light); border-radius: 3px;">
                    Page auto-refreshes every 5 seconds
                </div>
            </div>
        </div>
    </div>
</body>
</html>

