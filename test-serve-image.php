<?php
/**
 * Debug script to diagnose image serving issue
 * Access: https://ojt-journal.free.nf/test-serve-image.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Image Serving Debug Info</h2>";

// Test 1: Check __DIR__ and paths
echo "<h3>1. Path Information</h3>";
echo "<strong>__DIR__:</strong> " . __DIR__ . "<br>";
echo "<strong>dirname(__DIR__):</strong> " . dirname(__DIR__) . "<br>";
echo "<strong>\$_SERVER['DOCUMENT_ROOT']:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'not set') . "<br>";
echo "<strong>\$_SERVER['SCRIPT_NAME']:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'not set') . "<br>";

// Test 2: Check possible upload directories
echo "<h3>2. Upload Directory Check</h3>";
$possibleDirs = [
    dirname(__DIR__) . '/storage/uploads',
    $_SERVER['DOCUMENT_ROOT'] . '/storage/uploads',
    __DIR__ . '/../storage/uploads',
];

foreach ($possibleDirs as $dir) {
    $dir = str_replace('\\', '/', $dir);
    echo "<strong>$dir</strong>: ";
    if (is_dir($dir)) {
        echo "✓ EXISTS<br>";
        $files = scandir($dir);
        $imageFiles = array_filter($files, function($f) {
            return preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $f);
        });
        echo "Files count: " . count($files) . ", Image files: " . count($imageFiles) . "<br>";
        if (count($imageFiles) > 0) {
            echo "Sample images: " . implode(', ', array_slice($imageFiles, 0, 5)) . "<br>";
        }
    } else {
        echo "✗ NOT FOUND<br>";
    }
}

// Test 3: Check if specific image exists
echo "<h3>3. Test Specific Image</h3>";
$testFilename = '660ada1e66053e627b70b207d32d1452_1773538524.webp';
echo "<strong>Testing filename:</strong> $testFilename<br>";

foreach ($possibleDirs as $baseDir) {
    $baseDir = str_replace('\\', '/', $baseDir);
    $filePath = $baseDir . '/' . $testFilename;
    echo "<strong>Path:</strong> $filePath<br>";
    if (file_exists($filePath)) {
        echo "✓ FILE EXISTS<br>";
        echo "Size: " . filesize($filePath) . " bytes<br>";
        $imageInfo = getimagesize($filePath);
        if ($imageInfo) {
            echo "Image type: " . $imageInfo['mime'] . "<br>";
        }
        // Try to display it
        echo "<img src='$filePath' style='max-width: 200px; border: 1px solid red;'>";
    } else {
        echo "✗ FILE NOT FOUND<br>";
    }
    echo "<br>";
}

// Test 4: Check database for image paths
echo "<h3>4. Database Check</h3>";
try {
    $dbPath = dirname(__DIR__) . '/storage/db/journal.db';
    echo "<strong>Database path:</strong> $dbPath<br>";
    
    if (file_exists($dbPath)) {
        echo "✓ Database exists<br>";
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check entry_images table
        $stmt = $pdo->query("SELECT image_path FROM entry_images LIMIT 5");
        $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<strong>Sample image paths from database:</strong><br>";
        foreach ($images as $path) {
            echo "- $path<br>";
            // Check if file exists
            $fullPath = dirname(__DIR__) . '/' . $path;
            echo "  Full path: $fullPath<br>";
            echo "  Exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "<br>";
        }
    } else {
        echo "✗ Database NOT FOUND<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Test 5: Try serving the image directly
echo "<h3>5. Direct Image Serve Test</h3>";
$uploadDir = dirname(__DIR__) . '/storage/uploads';
$testFile = $uploadDir . '/' . $testFilename;

if (file_exists($testFile)) {
    echo "Serving image from: $testFile<br>";
    $imageData = file_get_contents($testFile);
    $imageInfo = getimagesize($testFile);
    header('Content-Type: ' . $imageInfo['mime']);
    echo $imageData;
    exit;
} else {
    echo "Cannot serve - file not found at: $testFile";
}
?>
