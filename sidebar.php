<?php
/**
 * Sidebar Navigation Component
 * Reusable sidebar for all pages
 */
require_once 'auth.php';
$current_page = basename($_SERVER['PHP_SELF']);
$currentUser = getCurrentUser();
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h1>
            <span>Team Manager</span>
        </h1>
        <?php if ($currentUser): ?>
            <div style="padding: 12px; margin-top: 12px; background: rgba(255,255,255,0.1); border-radius: 6px; font-size: 13px;">
                <div style="color: var(--white); font-weight: 600; margin-bottom: 4px;">
                    <?php echo htmlspecialchars($currentUser['full_name']); ?>
                </div>
                <div style="color: rgba(255,255,255,0.8); font-size: 11px;">
                    @<?php echo htmlspecialchars($currentUser['username']); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <nav class="sidebar-nav">
        <a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <span>People Entry</span>
        </a>
        <a href="owners.php" class="<?php echo $current_page == 'owners.php' ? 'active' : ''; ?>">
            <span>Owners Management</span>
        </a>
        <a href="assign_owners.php" class="<?php echo $current_page == 'assign_owners.php' ? 'active' : ''; ?>">
            <span>Assign Owners</span>
        </a>
        <a href="teams.php" class="<?php echo $current_page == 'teams.php' ? 'active' : ''; ?>">
            <span>Team Dashboard</span>
        </a>
        <a href="generate_sample.php" class="<?php echo $current_page == 'generate_sample.php' ? 'active' : ''; ?>">
            <span>Generate Sample Data</span>
        </a>
        <a href="clear_data.php" class="<?php echo $current_page == 'clear_data.php' ? 'active' : ''; ?>">
            <span>Clear All Data</span>
        </a>
        <a href="users.php" class="<?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
            <span>Portal Managers</span>
        </a>
        <a href="logout.php" style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 12px;">
            <span>Logout</span>
        </a>
    </nav>
</div>

