<?php
// db.php - Database connection
$db = new \PDO('sqlite:database/dbv2.sqlite');

// Check connection
if (!$db) {
    die("Connection failed: " . $db->lastErrorMsg());
}
?>