<?php
/**
 * User Management Screen
 * Allows adding, editing, and deleting portal managers
 */

require_once 'auth.php';
requireLogin();

require_once 'db.php';

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        // Add new user
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if (empty($username) || empty($password) || empty($full_name)) {
            $message = "Username, password, and full name are required.";
            $messageType = 'error';
        } else {
            $result = createUser($username, $password, $full_name, $email);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
        }
    } elseif (isset($_POST['edit_user'])) {
        // Edit user
        $user_id = (int)($_POST['user_id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($username) || empty($full_name) || $user_id <= 0) {
            $message = "Invalid request.";
            $messageType = 'error';
        } else {
            // Only update password if provided
            $result = updateUser($user_id, $username, $full_name, $email, $is_active, 
                                 !empty($password) ? $password : null);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    $result = deleteUser($user_id);
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';
}

// Get all users
$allUsers = getAllUsers();

// Get user being edited
$editingUser = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $editingUser = getUserById($edit_id);
    if (!$editingUser) {
        $message = "User not found.";
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Team Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <span>Portal Manager Management</span>
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
                            <span><?php echo $editingUser ? 'Edit Portal Manager' : 'Add New Portal Manager'; ?></span>
                        </div>
                    </div>
                    
                    <form method="POST" action="">
                        <?php if ($editingUser): ?>
                            <input type="hidden" name="user_id" value="<?php echo $editingUser['id']; ?>">
                            <div class="form-group">
                                <label for="username">Username *</label>
                                <input type="text" id="username" name="username" required
                                       value="<?php echo htmlspecialchars($editingUser['username']); ?>"
                                       placeholder="Enter username">
                            </div>
                            <div class="form-group">
                                <label for="password">New Password</label>
                                <input type="password" id="password" name="password"
                                       placeholder="Leave blank to keep current password">
                                <p class="help-text">Leave blank to keep the current password</p>
                            </div>
                            <div class="form-group">
                                <label for="full_name">Full Name *</label>
                                <input type="text" id="full_name" name="full_name" required
                                       value="<?php echo htmlspecialchars($editingUser['full_name']); ?>"
                                       placeholder="Enter full name">
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email"
                                       value="<?php echo htmlspecialchars($editingUser['email'] ?? ''); ?>"
                                       placeholder="Enter email address">
                            </div>
                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" name="is_active" value="1" 
                                           <?php echo $editingUser['is_active'] ? 'checked' : ''; ?>>
                                    <span>Active</span>
                                </label>
                            </div>
                            <div style="display: flex; gap: 12px;">
                                <button type="submit" name="edit_user" class="btn btn-success">
                                    <span>Update User</span>
                                </button>
                                <a href="users.php" class="btn">
                                    <span>Cancel</span>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="form-group">
                                <label for="username">Username *</label>
                                <input type="text" id="username" name="username" required
                                       placeholder="Enter username"
                                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="password">Password *</label>
                                <input type="password" id="password" name="password" required
                                       placeholder="Enter password">
                            </div>
                            <div class="form-group">
                                <label for="full_name">Full Name *</label>
                                <input type="text" id="full_name" name="full_name" required
                                       placeholder="Enter full name"
                                       value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email"
                                       placeholder="Enter email address"
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                            <button type="submit" name="add_user" class="btn btn-success">
                                <span>Add User</span>
                            </button>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <span>All Portal Managers (<?php echo count($allUsers); ?>)</span>
                        </div>
                    </div>
                    
                    <?php if (empty($allUsers)): ?>
                        <div class="no-members">No users found.</div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Last Login</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allUsers as $user): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email'] ?? '-'); ?></td>
                                            <td>
                                                <?php if ($user['is_active']): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if ($user['last_login']) {
                                                    echo date('Y-m-d H:i', strtotime($user['last_login']));
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 8px;">
                                                    <a href="users.php?edit=<?php echo $user['id']; ?>" 
                                                       class="btn btn-small">
                                                        <span>Edit</span>
                                                    </a>
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <a href="users.php?delete=<?php echo $user['id']; ?>" 
                                                           class="btn btn-danger btn-small"
                                                           onclick="return confirm('Delete <?php echo htmlspecialchars($user['username']); ?>?\n\nThis action cannot be undone.');">
                                                            <span>Delete</span>
                                                        </a>
                                                    <?php endif; ?>
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

