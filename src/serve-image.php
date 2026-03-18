<?php
/**
 * Image Server Script - InfinityFree Version
 * Serves uploaded images securely
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

// Get filename
$filename = $_GET['file'] ?? '';
if (empty($filename)) die('No filename provided');
$filename = basename($filename); // Security: extract only filename

// InfinityFree path: __DIR__ = /home/vol1_4/infinityfree.com/if0_41388641/htdocs/src
// So dirname(__DIR__) = /home/vol1_4/infinityfree.com/if0_41388641/htdocs
$baseDir = dirname(__DIR__);
$uploadDir = $baseDir . '/storage/uploads';

// Try multiple possible paths
$possiblePaths = [
    $uploadDir . '/' . $filename,                      // /htdocs/storage/uploads/filename
    __DIR__ . '/storage/uploads/' . $filename,         // /htdocs/src/storage/uploads/filename
];

// Also try with DOCUMENT_ROOT
if (isset($_SERVER['DOCUMENT_ROOT'])) {
    $possiblePaths[] = $_SERVER['DOCUMENT_ROOT'] . '/storage/uploads/' . $filename;
}

$filePath = null;
foreach ($possiblePaths as $path) {
    if ($path && file_exists($path)) {
        $filePath = $path;
        error_log("serve-image: FOUND at $path");
        break;
    }
    error_log("serve-image: NOT found at $path");
}

if (!$filePath) {
    http_response_code(404);
    error_log("serve-image: File not found: $filename");
    error_log("serve-image: Upload dir ($uploadDir) exists: " . (is_dir($uploadDir) ? 'yes' : 'no'));
    if (is_dir($uploadDir)) {
        $files = scandir($uploadDir);
        $imageFiles = array_values(array_filter($files, function($f) {
            return preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $f);
        }));
        error_log("serve-image: Images in dir (" . count($imageFiles) . "): " . implode(', ', array_slice($imageFiles, 0, 10)));
    }
    die('Image not found: ' . $filename);
}

// Verify it's an image
$imageInfo = getimagesize($filePath);
if (!$imageInfo) {
    http_response_code(403);
    die('Not a valid image');
}

// Serve the image
header('Content-Type: ' . $imageInfo['mime']);
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: public, max-age=31536000');
header('Access-Control-Allow-Origin: *');
header('X-Content-Type-Options: nosniff');
readfile($filePath);
exit;
