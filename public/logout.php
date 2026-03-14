<?php
/**
 * User Logout API Endpoint
 */

session_start();
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Destroy session
$_SESSION = [];
session_destroy();

echo json_encode(['success' => true]);
?>
