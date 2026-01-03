<?php
/**
 * Owners Management Screen
 * Allows adding, editing, and deleting owners
 */

require_once 'auth.php';
requireLogin();

require_once 'db.php';
require_once 'functions.php';

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    
    if (isset($_POST['add_owner'])) {
        // Add new owner
        $owner_name = trim($_POST['owner_name'] ?? '');
        
        if (empty($owner_name)) {
            $message = "Owner name cannot be empty.";
            $messageType = 'error';
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO owners (owner_name) VALUES (?)");
                $stmt->execute([$owner_name]);
                $message = "Owner added successfully!";
                $messageType = 'success';
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'UNIQUE constraint') !== false) {
                    $message = "Owner with this name already exists.";
                } else {
                    $message = "Error: " . $e->getMessage();
                }
                $messageType = 'error';
            }
        }
    } elseif (isset($_POST['edit_owner'])) {
        // Edit owner
        $owner_id = (int)($_POST['owner_id'] ?? 0);
        $owner_name = trim($_POST['owner_name'] ?? '');
        
        if (empty($owner_name) || $owner_id <= 0) {
            $message = "Invalid request.";
            $messageType = 'error';
        } else {
            try {
                $stmt = $db->prepare("UPDATE owners SET owner_name = ? WHERE id = ?");
                $stmt->execute([$owner_name, $owner_id]);
                $message = "Owner updated successfully!";
                $messageType = 'success';
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'UNIQUE constraint') !== false) {
                    $message = "Owner with this name already exists.";
                } else {
                    $message = "Error: " . $e->getMessage();
                }
                $messageType = 'error';
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $owner_id = (int)$_GET['delete'];
    $result = deleteOwner($owner_id);
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';
}

// Get all owners
$db = getDB();
$allOwners = $db->query("SELECT * FROM owners ORDER BY owner_name")->fetchAll();

// Get owner being edited
$editingOwner = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM owners WHERE id = ?");
    $stmt->execute([$edit_id]);
    $editingOwner = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owners Management - Team Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <span>Owners Management</span>
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
                            <span><?php echo $editingOwner ? 'Edit Owner' : 'Add New Owner'; ?></span>
                        </div>
                    </div>
                    
                    <form method="POST" action="">
                        <?php if ($editingOwner): ?>
                            <input type="hidden" name="owner_id" value="<?php echo $editingOwner['id']; ?>">
                            <div class="form-group">
                                <label for="owner_name">Owner Name *</label>
                                <input type="text" id="owner_name" name="owner_name" required
                                       value="<?php echo htmlspecialchars($editingOwner['owner_name']); ?>"
                                       placeholder="Enter owner name">
                            </div>
                            <div style="display: flex; gap: 12px;">
                                <button type="submit" name="edit_owner" class="btn btn-success">
                                    <span>Update Owner</span>
                                </button>
                                <a href="owners.php" class="btn">
                                    <span>Cancel</span>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="form-group">
                                <label for="owner_name">Owner Name *</label>
                                <input type="text" id="owner_name" name="owner_name" required
                                       placeholder="Enter owner name"
                                       value="<?php echo htmlspecialchars($_POST['owner_name'] ?? ''); ?>">
                            </div>
                            <button type="submit" name="add_owner" class="btn btn-success">
                                <span>Add Owner</span>
                            </button>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <span>All Owners (<?php echo count($allOwners); ?>)</span>
                        </div>
                    </div>
                    
                    <?php if (empty($allOwners)): ?>
                        <div class="no-members">No owners found.</div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Owner Name</th>
                                        <th>Teams</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allOwners as $owner): ?>
                                        <?php
                                        // Count teams for this owner
                                        $teamCount = $db->prepare("SELECT COUNT(*) as count FROM teams WHERE owner_id = ?");
                                        $teamCount->execute([$owner['id']]);
                                        $count = $teamCount->fetch()['count'];
                                        ?>
                                        <tr>
                                            <td><?php echo $owner['id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($owner['owner_name']); ?></strong></td>
                                            <td>
                                                <span class="badge badge-info"><?php echo $count; ?></span>
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 8px;">
                                                    <a href="owners.php?edit=<?php echo $owner['id']; ?>" 
                                                       class="btn btn-small">
                                                        <span>Edit</span>
                                                    </a>
                                                    <a href="owners.php?delete=<?php echo $owner['id']; ?>" 
                                                       class="btn btn-danger btn-small"
                                                       onclick="return confirm('Delete <?php echo htmlspecialchars($owner['owner_name']); ?>?\n\nThis will also delete all teams under this owner.');">
                                                        <span>Delete</span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

