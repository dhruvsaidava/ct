<?php
/**
 * Authentication and Session Management
 */

require_once 'db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Require login - redirect to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Get current logged in user
 * @return array|false
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return false;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Login user
 * @param string $username
 * @param string $password
 * @return array ['success' => bool, 'message' => string, 'user' => array|false]
 */
function login($username, $password) {
    $db = getDB();
    
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return ['success' => false, 'message' => 'Invalid username or password.', 'user' => false];
    }
    
    if (!password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Invalid username or password.', 'user' => false];
    }
    
    // Update last login
    $updateStmt = $db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
    $updateStmt->execute([$user['id']]);
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    
    return ['success' => true, 'message' => 'Login successful.', 'user' => $user];
}

/**
 * Logout user
 */
function logout() {
    $_SESSION = array();
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}

/**
 * Create a new user
 * @param string $username
 * @param string $password
 * @param string $full_name
 * @param string $email
 * @return array ['success' => bool, 'message' => string]
 */
function createUser($username, $password, $full_name, $email = '') {
    $db = getDB();
    
    // Check if username already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Username already exists.'];
    }
    
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $db->prepare("
            INSERT INTO users (username, password_hash, full_name, email, is_active)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$username, $passwordHash, $full_name, $email, 1]);
        return ['success' => true, 'message' => 'User created successfully.'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Update user
 * @param int $user_id
 * @param string $username
 * @param string $password (optional, only if changing password)
 * @param string $full_name
 * @param string $email
 * @param int $is_active
 * @return array ['success' => bool, 'message' => string]
 */
function updateUser($user_id, $username, $full_name, $email = '', $is_active = 1, $password = null) {
    $db = getDB();
    
    // Check if username already exists (excluding current user)
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $user_id]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Username already exists.'];
    }
    
    try {
        if ($password !== null && !empty($password)) {
            // Update with password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("
                UPDATE users 
                SET username = ?, password_hash = ?, full_name = ?, email = ?, is_active = ?
                WHERE id = ?
            ");
            $stmt->execute([$username, $passwordHash, $full_name, $email, $is_active, $user_id]);
        } else {
            // Update without password
            $stmt = $db->prepare("
                UPDATE users 
                SET username = ?, full_name = ?, email = ?, is_active = ?
                WHERE id = ?
            ");
            $stmt->execute([$username, $full_name, $email, $is_active, $user_id]);
        }
        return ['success' => true, 'message' => 'User updated successfully.'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Delete user
 * @param int $user_id
 * @return array ['success' => bool, 'message' => string]
 */
function deleteUser($user_id) {
    $db = getDB();
    
    // Prevent deleting yourself
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
        return ['success' => false, 'message' => 'You cannot delete your own account.'];
    }
    
    // Prevent deleting if it's the last user
    $count = $db->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
    if ($count <= 1) {
        return ['success' => false, 'message' => 'Cannot delete the last user.'];
    }
    
    try {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return ['success' => true, 'message' => 'User deleted successfully.'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Get all users
 * @return array
 */
function getAllUsers() {
    $db = getDB();
    $stmt = $db->query("SELECT id, username, full_name, email, is_active, created_at, last_login FROM users ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

/**
 * Get user by ID
 * @param int $user_id
 * @return array|false
 */
function getUserById($user_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}
?>

