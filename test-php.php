<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP is working!<br>";
echo "Session ID: " . session_id() . "<br>";
echo "PHP Version: " . phpversion() . "<br>";

// Test database
require_once __DIR__ . '/config/config.php';
$pdo = getDbConnection();
echo "Database connected!<br>";

// Test users table
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$count = $stmt->fetchColumn();
echo "Users in database: {$count}<br>";
?>
