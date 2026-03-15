<?php
/**
 * Image Server Script
 * Serves uploaded images securely
 * 
 * Usage: <img src="src/serve-image.php?file=filename.jpg">
 */

session_start();
require_once __DIR__ . '/../config/config.php';

// Get filename from query parameter
$filename = $_GET['file'] ?? '';

// Validate filename - only allow safe characters
if (empty($filename) || !preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
    http_response_code(400);
    die('Invalid filename');
}

// Prevent directory traversal
if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
    http_response_code(403);
    die('Access denied');
}

// Build file path
$filePath = UPLOAD_DIR . $filename;

// Check if file exists
if (!file_exists($filePath)) {
    http_response_code(404);
    die('Image not found');
}

// Verify file is an image
$imageInfo = getimagesize($filePath);
if (!$imageInfo) {
    http_response_code(403);
    die('Not a valid image');
}

// Set appropriate content type
header('Content-Type: ' . $imageInfo['mime']);
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: public, max-age=31536000'); // Cache for 1 year

// For InfinityFree: Add headers to prevent blocking
header('Access-Control-Allow-Origin: *');
header('X-Content-Type-Options: nosniff');

// Output the image
readfile($filePath);
exit;
