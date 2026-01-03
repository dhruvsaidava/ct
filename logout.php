<?php
/**
 * Logout Page
 */

require_once 'auth.php';

logout();

header('Location: login.php');
exit;
?>

