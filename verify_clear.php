<?php
require_once 'db.php';
$db = getDB();
echo "People: " . $db->query('SELECT COUNT(*) as c FROM people')->fetch()['c'] . "\n";
echo "Teams: " . $db->query('SELECT COUNT(*) as c FROM teams')->fetch()['c'] . "\n";
echo "Team Members: " . $db->query('SELECT COUNT(*) as c FROM team_members')->fetch()['c'] . "\n";
?>

