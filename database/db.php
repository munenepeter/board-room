<?php
// db.php - Database connection
$path_to_db_file = __DIR__ . '/dbv2.sqlite';

$db = new \PDO('sqlite:' . $path_to_db_file);

// Check connection
if (!$db) {
    die("Connection failed: " . $db->lastErrorMsg());
}
