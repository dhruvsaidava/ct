<?php
/**
 * Sidebar Navigation Component
 * Reusable sidebar for all pages
 */
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h1>
            <span>Team Manager</span>
        </h1>
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
    </nav>
</div>

