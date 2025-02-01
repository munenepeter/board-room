<?php
// db.php - Database connection
$db = new \PDO('sqlite:database/database.sqlite');

// Check connection
if (!$db) {
    die("Connection failed: " . $db->lastErrorMsg());
}
?>