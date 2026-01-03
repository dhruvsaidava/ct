<?php
/**
 * Team Dashboard Screen
 * Shows all teams with captains and members, allows adding members
 */

require_once 'db.php';
require_once 'functions.php';

// Get all teams
$allTeams = getAllTeams();

// Get statistics
$db = getDB();
$totalPeople = $db->query("SELECT COUNT(*) as count FROM people")->fetch()['count'];
$totalTeams = count($allTeams);
$totalMembers = $db->query("SELECT COUNT(*) as count FROM team_members")->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Dashboard - Team Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <span>Team Dashboard</span>
                </div>
            </div>
            
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-card-value"><?php echo $totalPeople; ?></div>
                                <div class="stat-card-label">Total People</div>
                            </div>
                            <div class="stat-card-icon"></div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-card-value"><?php echo $totalTeams; ?></div>
                                <div class="stat-card-label">Teams</div>
                            </div>
                            <div class="stat-card-icon"></div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-card-value"><?php echo $totalMembers; ?></div>
                                <div class="stat-card-label">Team Members</div>
                            </div>
                            <div class="stat-card-icon"></div>
                        </div>
                    </div>
                </div>
                
                <?php if (empty($allTeams)): ?>
                    <div class="card">
                        <div class="message message-info">
                            <span>No teams created yet. <a href="captains.php" style="color: var(--info); font-weight: 600;">Go to Select Captains</a> to create teams.</span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="teams-grid">
                <?php foreach ($allTeams as $team): ?>
                    <?php 
                    $members = getTeamMembers($team['id']);
                    ?>
                        <div class="team-card">
                            <div class="team-card-header">
                                <h3>
                                    <a href="<?php echo getTeamSlug($team['team_name']); ?>">
                                        <?php echo htmlspecialchars($team['team_name']); ?>
                                    </a>
                                </h3>
                            </div>
                            
                            <div class="captain-info">
                                <strong>Captain</strong>
                                <div style="margin-top: 8px; font-size: 16px; font-weight: 600;">
                                    <?php echo htmlspecialchars($team['captain_name']); ?>
                                </div>
                                <small style="color: var(--gray);"><?php echo htmlspecialchars($team['captain_mobile']); ?></small>
                            </div>
                            
                            <div class="members-list">
                                <strong style="display: block; margin-bottom: 12px; color: var(--dark);">
                                    Members <span class="badge badge-info"><?php echo count($members); ?></span>
                                </strong>
                                <?php if (empty($members)): ?>
                                    <div class="no-members">No members yet</div>
                                <?php else: ?>
                                    <?php foreach ($members as $member): ?>
                                        <div class="member-item">
                                            <div>
                                                <strong><?php echo htmlspecialchars($member['full_name']); ?></strong>
                                                <small>SR: <?php echo htmlspecialchars($member['sr_no']); ?> | 
                                                       <?php echo htmlspecialchars($member['mobile_number']); ?></small>
                                            </div>
                                            <a href="delete.php?action=team_member&id=<?php echo $member['id']; ?>" 
                                               class="btn btn-danger btn-small"
                                               onclick="return confirm('Remove <?php echo htmlspecialchars($member['full_name']); ?> from this team?');">
                                                <span>X</span>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="add-member-form">
                                <form method="POST" action="add_member.php">
                                    <input type="hidden" name="team_id" value="<?php echo $team['id']; ?>">
                                    <input type="number" name="member_sr_no" placeholder="Enter SR No" required 
                                           min="1">
                                    <button type="submit" class="btn btn-success btn-small">
                                        <span>Add Member</span>
                                    </button>
                                </form>
                            </div>
                            
                            <div class="action-buttons">
                                <a href="<?php echo getTeamSlug($team['team_name']); ?>" 
                                   class="btn btn-small">
                                    <span>View Team Page</span>
                                </a>
                                <a href="delete.php?action=team&id=<?php echo $team['id']; ?>" 
                                   class="btn btn-danger btn-small"
                                   onclick="return confirm('Delete <?php echo htmlspecialchars($team['team_name']); ?>?\n\nThis will remove the team and all its members.');">
                                    <span>Delete Team</span>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

