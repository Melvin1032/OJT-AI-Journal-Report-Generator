<?php
/**
 * Debug: Check what image URLs are being generated
 */
session_start();
require_once 'config/config.php';

requireAuth();

$pdo = getDbConnection();
$currentUserId = getCurrentUserId();

if (!$currentUserId) {
    die('Not logged in');
}

// Get entries with images
$stmt = $pdo->prepare("
    SELECT e.id, e.title, e.entry_date, i.image_path, i.id as image_id
    FROM ojt_entries e
    LEFT JOIN entry_images i ON e.id = i.entry_id AND i.user_id = ?
    WHERE e.user_id = ?
    ORDER BY e.entry_date DESC
    LIMIT 5
");
$stmt->execute([$currentUserId, $currentUserId]);
$results = $stmt->fetchAll();

echo "<h2>Image URL Debug</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
echo "<tr>
        <th>Entry ID</th>
        <th>Title</th>
        <th>Date</th>
        <th>Image Path (DB)</th>
        <th>Extracted Filename</th>
        <th>Generated URL</th>
        <th>Test</th>
      </tr>";

foreach ($results as $row) {
    if (empty($row['image_path'])) continue;
    
    $imagePath = str_replace('\\', '/', $row['image_path']);
    $filename = basename($imagePath);
    
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = dirname($_SERVER['SCRIPT_NAME']);
    $basePath = rtrim($basePath, '/');
    if ($basePath === '') $basePath = '/';
    $imageUrl = $protocol . '://' . $host . $basePath . '/src/serve-image.php?file=' . urlencode($filename);
    
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
    echo "<td>{$row['entry_date']}</td>";
    echo "<td>" . htmlspecialchars($row['image_path']) . "</td>";
    echo "<td>" . htmlspecialchars($filename) . "</td>";
    echo "<td style='font-size: 11px;'>" . htmlspecialchars($imageUrl) . "</td>";
    echo "<td><a href='$imageUrl' target='_blank'>Test Image</a></td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h3>Raw Database Paths:</h3>";
$stmt = $pdo->query("SELECT image_path FROM entry_images LIMIT 10");
$paths = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach ($paths as $path) {
    echo "<div>";
    echo "DB: " . htmlspecialchars($path) . "<br>";
    echo "Filename: " . htmlspecialchars(basename(str_replace('\\', '/', $path))) . "<br>";
    echo "File exists at htdocs/: " . (file_exists(__DIR__ . '/' . str_replace('\\', '/', $path)) ? 'YES' : 'NO') . "<br>";
    echo "</div>";
}
?>
