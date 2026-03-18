<?php
/**
 * Find My Files - Diagnostic Script for InfinityFree
 * Upload this to your website root and access it via browser
 */

echo "<h1>🔍 Find My Files on InfinityFree</h1>";
echo "<p>This script will help locate your uploaded images</p>";
echo "<hr>";

// Show current location
echo "<h2>📍 Current Location</h2>";
echo "<strong>__FILE__:</strong> " . __FILE__ . "<br>";
echo "<strong>__DIR__:</strong> " . __DIR__ . "<br>";
echo "<strong>DOCUMENT_ROOT:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'not set') . "<br>";
echo "<strong>SCRIPT_NAME:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'not set') . "<br>";
echo "<hr>";

// Search for the specific image file
$targetFile = '660ada1e66053e627b70b207d32d1452_1773538524.webp';
echo "<h2>🔎 Searching for: $targetFile</h2>";

$searchPaths = [
    __DIR__ . '/',
    __DIR__ . '/storage/',
    __DIR__ . '/storage/uploads/',
    __DIR__ . '/uploads/',
    __DIR__ . '/assets/',
    __DIR__ . '/assets/images/',
    dirname(__DIR__) . '/',
    dirname(__DIR__) . '/storage/',
    dirname(__DIR__) . '/storage/uploads/',
    (isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/' : ''),
    (isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/storage/' : ''),
    (isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/storage/uploads/' : ''),
];

$found = false;
foreach ($searchPaths as $basePath) {
    if (empty($basePath)) continue;
    $basePath = str_replace('\\', '/', rtrim($basePath, '/'));
    $fullPath = $basePath . '/' . $targetFile;
    
    if (file_exists($fullPath)) {
        echo "<div style='background:#d4edda; padding:10px; margin:10px 0; border:1px solid #28a745;'>";
        echo "<strong>✅ FOUND!</strong><br>";
        echo "<strong>Path:</strong> $fullPath<br>";
        echo "<strong>Size:</strong> " . filesize($fullPath) . " bytes<br>";
        echo "<strong>Image:</strong> <img src='$fullPath' style='max-width:300px; border:1px solid #000;'>";
        echo "</div>";
        $found = true;
    }
}

if (!$found) {
    echo "<div style='background:#f8d7da; padding:10px; margin:10px 0; border:1px solid #dc3545;'>";
    echo "<strong>❌ File not found in any expected location</strong>";
    echo "</div>";
}

echo "<hr>";

// Show all directories and their contents
echo "<h2>📁 Directory Structure</h2>";

function scanDirRecursive($dir, $prefix = '', $maxDepth = 3, $currentDepth = 0) {
    if ($currentDepth >= $maxDepth) return;
    
    $dirs = [];
    $files = [];
    
    try {
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..' || $item === '.git') continue;
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $dirs[] = $item;
            } else {
                $files[] = $item;
            }
        }
    } catch (Exception $e) {
        return;
    }
    
    foreach ($dirs as $dirName) {
        $path = $dir . '/' . $dirName;
        echo "<div style='margin:5px 0;'><strong>📁 $prefix$dirName/</strong></div>";
        scanDirRecursive($path, $prefix . '  ', $maxDepth, $currentDepth + 1);
    }
    
    foreach ($files as $fileName) {
        $path = $dir . '/' . $fileName;
        $size = filesize($path);
        $sizeStr = $size > 1024 ? round($size/1024, 1) . ' KB' : $size . ' B';
        $isImage = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $fileName);
        $style = $isImage ? 'color:#28a745; font-weight:bold;' : '';
        echo "<div style='margin:2px 0; padding-left:10px; $style'>📄 $prefix$fileName ($sizeStr)</div>";
    }
}

echo "<h3>From current directory (" . __DIR__ . "):</h3>";
echo "<div style='font-family:monospace; font-size:12px; background:#f5f5f5; padding:10px;'>";
scanDirRecursive(__DIR__, '', 3, 0);
echo "</div>";

echo "<hr>";

// Show database contents
echo "<h2>💾 Database Check</h2>";
$dbPaths = [
    __DIR__ . '/storage/db/journal.db',
    dirname(__DIR__) . '/storage/db/journal.db',
    (isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/storage/db/journal.db' : ''),
];

$dbFound = false;
foreach ($dbPaths as $dbPath) {
    if (file_exists($dbPath)) {
        echo "<div style='background:#d1ecf1; padding:10px; margin:10px 0;'>";
        echo "<strong>✅ Database found:</strong> $dbPath<br>";
        
        try {
            $pdo = new PDO('sqlite:' . $dbPath);
            $stmt = $pdo->query("SELECT image_path FROM entry_images LIMIT 10");
            $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<strong>Image paths in database:</strong><br>";
            foreach ($images as $path) {
                echo "- $path<br>";
                // Check if file exists
                $checkPath = dirname(__DIR__) . '/' . $path;
                echo "  → File exists: " . (file_exists($checkPath) ? '✅ YES' : '❌ NO') . " at $checkPath<br>";
            }
        } catch (Exception $e) {
            echo "Error reading database: " . $e->getMessage();
        }
        echo "</div>";
        $dbFound = true;
        break;
    }
}

if (!$dbFound) {
    echo "<div style='background:#f8d7da; padding:10px;'>❌ Database not found</div>";
}

echo "<hr>";
echo "<p><strong>Instructions:</strong></p>";
echo "<ol>";
echo "<li>Look for the green box showing where your image was found</li>";
echo "<li>Note the exact path (e.g., /htdocs/storage/uploads/)</li>";
echo "<li>Check the database section to see what paths are stored</li>";
echo "<li>Share this information to fix the image serving issue</li>";
echo "</ol>";
?>
