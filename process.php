<?php
/**
 * Process.php - Backend handler for OJT Journal entries
 *
 * Handles:
 * - OJT entry creation with title, description, and multiple images
 * - Qwen API integration for image analysis and description enhancement
 * - Database operations for OJT journal entries
 *
 * Security Features:
 * - CSRF protection
 * - Input validation and sanitization
 * - Rate limiting
 * - Secure file uploads
 * - Comprehensive logging
 */

error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in production
ini_set('log_errors', 1);

header('Content-Type: application/json');

// Catch fatal errors and return as JSON
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE)) {
        Logger::error('Fatal Error', ['message' => $error['message']]);
        http_response_code(500);
        echo json_encode(['error' => 'Server error occurred']);
    }
});

require_once __DIR__ . '/config/config.php';

// Log request start
$requestStart = microtime(true);
$startTime = Date('Y-m-d H:i:s');

/**
 * Main request handler
 */
try {
    // Validate CSRF for POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        requireCSRFValidation();
    }

    $action = $_GET['action'] ?? '';

    // Rate limiting for write operations
    $writeActions = ['createEntry', 'delete', 'updateDescription', 'generateISPSCReport', 'generateNarrative'];
    if (in_array($action, $writeActions)) {
        if (!checkRateLimit($action, 10, 60)) { // 10 requests per minute
            $limitInfo = getRateLimitInfo($action);
            Logger::security('Rate limit exceeded', ['action' => $action, 'limit' => $limitInfo]);
            jsonResponse([
                'error' => 'Rate limit exceeded. Try again in ' . ($limitInfo['reset'] - time()) . ' seconds'
            ], 429);
        }
    }

    Logger::info('Request received', ['action' => $action, 'method' => $_SERVER['REQUEST_METHOD']]);

    switch ($action) {
        case 'createEntry':
            createOJTEntry();
            break;
        case 'getWeekly':
            getWeeklyReport();
            break;
        case 'delete':
            deleteEntry();
            break;
        case 'generateNarrative':
            generateNarrativeReport();
            break;
        case 'updateDescription':
            updateDescription();
            break;
        case 'generateISPSCReport':
            generateISPSCReport();
            break;
        case 'generateDownloadReport':
            generateDownloadReport();
            break;
        case 'getCSRFToken':
            // Return CSRF token for AJAX requests
            echo getCSRFTokenJSON();
            break;
        case 'bulkDelete':
            bulkDelete();
            break;
        default:
            if (empty($action)) {
                jsonResponse(['error' => 'No action specified', 'hint' => 'Use ?action=createEntry, ?action=getWeekly, etc.'], 400);
            }
            jsonResponse(['error' => 'Invalid action: ' . htmlspecialchars($action)], 400);
    }
} catch (Exception $e) {
    Logger::error('Request exception', [
        'action' => $action ?? 'unknown',
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ]);

    $statusCode = $e->getCode() !== 0 ? $e->getCode() : 500;
    jsonResponse(['error' => $e->getMessage()], $statusCode);
} catch (Throwable $e) {
    Logger::error('Server error', [
        'action' => $action ?? 'unknown',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);

    jsonResponse(['error' => 'Server error occurred'], 500);
} finally {
    // Log request completion
    $duration = round((microtime(true) - $requestStart) * 1000, 2);
    Logger::apiRequest($action ?? 'unknown', 'completed', $duration);
}

/**
 * Create OJT entry with title, description, and multiple images
 */
function createOJTEntry() {
    $requestStart = microtime(true);

    if (!isApiKeyConfigured()) {
        Logger::error('API key not configured');
        jsonResponse(['error' => 'API key not configured'], 500);
    }

    // Validate and sanitize inputs
    $title = sanitizeInput($_POST['title'] ?? '', 'text', 200);
    $userDescription = sanitizeInput($_POST['description'] ?? '', 'text', 2000);
    $entryDate = sanitizeInput($_POST['entry_date'] ?? '', 'date');

    // Validate required fields
    if (empty($title)) {
        Logger::warning('Create entry failed - missing title', ['post' => $_POST]);
        jsonResponse(['error' => 'Title is required'], 400);
    }

    if (strlen($title) < 3) {
        jsonResponse(['error' => 'Title must be at least 3 characters'], 400);
    }

    if (!$entryDate) {
        jsonResponse(['error' => 'Invalid date format. Use YYYY-MM-DD'], 400);
    }

    // Validate date is not in the future
    if (strtotime($entryDate) > strtotime('tomorrow')) {
        jsonResponse(['error' => 'Date cannot be in the future'], 400);
    }

    // Check if images were uploaded
    if (empty($_FILES['images']) || empty($_FILES['images']['name'][0])) {
        jsonResponse(['error' => 'At least one image is required'], 400);
    }

    // Validate number of images
    $uploadCount = count($_FILES['images']['name']);
    if ($uploadCount > 10) {
        jsonResponse(['error' => 'Maximum 10 images allowed per entry'], 400);
    }

    $pdo = getDbConnection();

    try {
        // Create the OJT entry first
        $stmt = $pdo->prepare("
            INSERT INTO ojt_entries (title, user_description, entry_date, ai_enhanced_description, created_at)
            VALUES (:title, :user_desc, :entry_date, :ai_desc, :created_at)
        ");

        // Generate initial enhanced description (will be updated after AI analysis)
        $initialEnhanced = $userDescription ?: 'Image analysis pending...';

        $stmt->execute([
            ':title' => $title,
            ':user_desc' => $userDescription,
            ':entry_date' => $entryDate,
            ':ai_desc' => $initialEnhanced,
            ':created_at' => date('Y-m-d H:i:s')
        ]);

        $entryId = $pdo->lastInsertId();
        Logger::info('Entry created', ['entry_id' => $entryId, 'title' => $title]);

        // Process images with validation
        $imageResults = [];
        $validImageCount = 0;

        for ($i = 0; $i < $uploadCount; $i++) {
            $file = [
                'name' => $_FILES['images']['name'][$i],
                'type' => $_FILES['images']['type'][$i],
                'tmp_name' => $_FILES['images']['tmp_name'][$i],
                'error' => $_FILES['images']['error'][$i],
                'size' => $_FILES['images']['size'][$i]
            ];

            // Validate image using security helper
            $validation = validateImageUpload($file);

            if (!$validation['valid']) {
                Logger::warning('Image validation failed', [
                    'file' => $file['name'],
                    'error' => $validation['error']
                ]);
                $imageResults[] = ['error' => $validation['error'], 'file' => $file['name']];
                continue;
            }

            // Move file securely
            $moveResult = moveUploadedFileSecurely($file, UPLOAD_DIR, $validation['secure_name']);

            if (!$moveResult['success']) {
                Logger::error('Failed to move uploaded file', ['file' => $file['name']]);
                $imageResults[] = ['error' => $moveResult['error'], 'file' => $file['name']];
                continue;
            }

            $validImageCount++;
            $imagePath = $moveResult['url'];

            // Analyze image with AI
            $aiDescription = analyzeImageWithQwen($imagePath);

            if (is_array($aiDescription) && isset($aiDescription['error'])) {
                Logger::warning('AI analysis failed', ['image' => $imagePath]);
                $aiDescription = 'Image uploaded but analysis unavailable';
            }

            // Save to database
            $stmt = $pdo->prepare("
                INSERT INTO entry_images (entry_id, image_path, image_order, ai_description, created_at)
                VALUES (:entry_id, :image_path, :order, :ai_desc, :created_at)
            ");

            $stmt->execute([
                ':entry_id' => $entryId,
                ':image_path' => $imagePath,
                ':order' => $i,
                ':ai_desc' => is_string($aiDescription) ? $aiDescription : 'Analysis unavailable',
                ':created_at' => date('Y-m-d H:i:s')
            ]);

            $imageResults[] = [
                'success' => true,
                'image_path' => $imagePath,
                'ai_description' => is_string($aiDescription) ? $aiDescription : null
            ];
        }

        if ($validImageCount === 0) {
            // Delete entry if no valid images
            $stmt = $pdo->prepare("DELETE FROM ojt_entries WHERE id = :id");
            $stmt->execute([':id' => $entryId]);
            jsonResponse(['error' => 'No valid images uploaded'], 400);
        }

        // Collect all AI descriptions for enhancement
        $aiDescriptions = [];
        foreach ($imageResults as $result) {
            if (isset($result['ai_description']) && is_string($result['ai_description'])) {
                $aiDescriptions[] = $result['ai_description'];
            }
        }

        // Generate enhanced description combining user input and AI analysis
        $enhancedDescription = generateEnhancedDescription($userDescription, $aiDescriptions, $title);

        // Update the entry with enhanced description
        $stmt = $pdo->prepare("UPDATE ojt_entries SET ai_enhanced_description = :ai_desc WHERE id = :id");
        $stmt->execute([
            ':ai_desc' => $enhancedDescription,
            ':id' => $entryId
        ]);

        $duration = round((microtime(true) - $requestStart) * 1000, 2);
        Logger::info('Entry creation completed', [
            'entry_id' => $entryId,
            'images' => $validImageCount,
            'duration_ms' => $duration
        ]);

        jsonResponse([
            'success' => true,
            'entry_id' => $entryId,
            'images_processed' => $validImageCount,
            'images' => $imageResults
        ]);

    } catch (PDOException $e) {
        Logger::error('Database error in createOJTEntry', ['error' => $e->getMessage()]);
        jsonResponse(['error' => 'Database error occurred'], 500);
    }
}

/**
 * Process a single image and save to database
 */
function processImage($file, $entryId, $order) {
    // Validate upload error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => getUploadErrorMessage($file['error'])];
    }

    // Validate file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['error' => 'File too large. Max: ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB'];
    }

    // Validate file type
    if (!in_array($file['type'], ALLOWED_TYPES)) {
        return ['error' => 'Invalid file type'];
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'ojt_' . $entryId . '_' . uniqid() . '_' . time() . '.' . $extension;
    $destination = UPLOAD_DIR . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['error' => 'Failed to save image'];
    }

    // Analyze image with AI
    $imagePath = 'uploads/' . $filename;
    $aiDescription = analyzeImageWithQwen($destination);

    if (is_array($aiDescription) && isset($aiDescription['error'])) {
        $aiDescription = 'Image analysis unavailable';
    }

    // Save image to database
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("
        INSERT INTO entry_images (entry_id, image_path, image_order, ai_description, created_at)
        VALUES (:entry_id, :image_path, :order, :ai_desc, :created_at)
    ");

    $stmt->execute([
        ':entry_id' => $entryId,
        ':image_path' => $imagePath,
        ':order' => $order,
        ':ai_desc' => $aiDescription,
        ':created_at' => date('Y-m-d H:i:s')
    ]);

    return [
        'id' => $pdo->lastInsertId(),
        'image_path' => $imagePath,
        'ai_description' => $aiDescription
    ];
}

/**
 * Analyze image using AI API with fallback support
 */
function analyzeImageWithQwen($imagePath) {
    // Convert image to base64
    $imageData = base64_encode(file_get_contents($imagePath));
    $mimeType = mime_content_type($imagePath);
    $base64Image = 'data:' . $mimeType . ';base64,' . $imageData;

    // Optimized prompt: concise, direct, token-efficient
    $prompt = "Analyze this image for an OJT journal. Write 1-2 sentences describing: (1) what's shown, (2) the learning purpose. Professional tone, no labels.";

    $requestData = [
        'messages' => [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'image_url',
                        'image_url' => ['url' => $base64Image]
                    ],
                    [
                        'type' => 'text',
                        'text' => $prompt
                    ]
                ]
            ]
        ],
        'max_tokens' => 100,
        'temperature' => 0.5
    ];

    // Use fallback mechanism
    $result = callAIWithFallback($requestData, QWEN_VISION_MODEL, FALLBACK_VISION_MODEL, AI_TIMEOUT);

    if ($result['success']) {
        if ($result['used_fallback']) {
            error_log("Image analysis used fallback model: " . $result['model']);
        }
        return $result['content'];
    }

    return ['error' => $result['error']];
}

/**
 * Clean up AI-generated description
 * Removes unwanted prefixes, titles, bullet points, etc.
 */
function cleanDescription($text) {
    // Remove "Title:" prefix and anything before first newline
    $text = preg_replace('/^Title:\s*[^\n]+\n/i', '', $text);

    // Remove bullet point patterns at the start
    $text = preg_replace('/^[\-\*•]\s*/m', '', $text);

    // Remove numbered list patterns at the start
    $text = preg_replace('/^\d+\.\s*/m', '', $text);

    // Remove common unwanted phrases at the beginning
    $unwantedPatterns = [
        '/^Here\'s? a\s+/i',
        '/^Here\'s? my\s+/i',
        '/^In this\s+/i',
        '/^During this\s+/i',
        '/^This (entry|journal|post|report) (covers|describes|shows|discusses)/i',
        '/^This (session|meeting|day|week) (covers|describes|shows|discusses)/i',
    ];

    foreach ($unwantedPatterns as $pattern) {
        $text = preg_replace($pattern, '', $text);
    }

    // Remove lines that are just labels or headers
    $lines = explode("\n", $text);
    $filteredLines = array_filter($lines, function($line) {
        $trimmed = trim($line);
        // Remove lines that are just bullet points or very short
        if (strlen($trimmed) < 10) return false;
        if (preg_match('/^[\-\*•]$/', $trimmed)) return false;
        return true;
    });

    $text = implode("\n", $filteredLines);

    // Clean up multiple newlines
    $text = preg_replace('/\n{3,}/', "\n\n", $text);

    return trim($text);
}

/**
 * Generate enhanced description combining user input and AI analysis
 */
function generateEnhancedDescription($userDescription, $aiDescriptions, $title) {
    if (empty($userDescription) && empty($aiDescriptions)) {
        return 'No description available';
    }

    // If only AI descriptions, combine them
    if (empty($userDescription)) {
        return implode(' ', $aiDescriptions);
    }

    // If only user description, enhance it with AI
    if (empty($aiDescriptions)) {
        return enhanceUserDescriptionWithAI($userDescription, $title);
    }

    // Combine user description with AI image analysis
    $imageContext = implode('. ', $aiDescriptions);
    $enhancedDescription = enhanceUserDescriptionWithAI($userDescription, $title, $imageContext);

    return $enhancedDescription;
}

/**
 * Enhance user description using AI
 */
function enhanceUserDescriptionWithAI($userDescription, $title, $imageContext = '') {
    // Optimized prompt: direct, token-efficient
    $prompt = "Enhance this OJT journal entry for a weekly report. Make it professional and detailed.\n\n";
    $prompt .= "Entry: {$userDescription}\n";

    if (!empty($imageContext)) {
        $prompt .= "Image context: {$imageContext}\n";
    }

    $prompt .= "\nWrite 2 paragraphs: (1) what was done, (2) skills learned. Professional tone. No titles, bullets, or 'Here's/In this'.";

    $requestData = [
        'messages' => [
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'max_tokens' => 250,
        'temperature' => 0.5
    ];

    // Use fallback mechanism
    $result = callAIWithFallback($requestData, QWEN_TEXT_MODEL, FALLBACK_TEXT_MODEL, AI_TIMEOUT);

    if ($result['success']) {
        if ($result['used_fallback']) {
            error_log("Enhance description used fallback model: " . $result['model']);
        }
        return cleanDescription($result['content']);
    }

    // Fallback to user description if AI fails
    return $userDescription;
}

/**
 * Get weekly report entries
 */
function getWeeklyReport() {
    $pdo = getDbConnection();

    // Get all OJT entries (no date filter)
    $stmt = $pdo->prepare("
        SELECT e.id, e.title, e.user_description, e.entry_date, e.ai_enhanced_description, e.created_at
        FROM ojt_entries e
        ORDER BY e.entry_date DESC, e.created_at DESC
    ");

    $stmt->execute();

    $entries = $stmt->fetchAll();

    // Get images for each entry
    foreach ($entries as &$entry) {
        $stmt = $pdo->prepare("
            SELECT id, image_path, image_order, ai_description
            FROM entry_images
            WHERE entry_id = :entry_id
            ORDER BY image_order ASC
        ");
        $stmt->execute([':entry_id' => $entry['id']]);
        $entry['images'] = $stmt->fetchAll();
    }

    // Get date range from entries
    if (count($entries) > 0) {
        $oldestDate = end($entries)['entry_date'];
        $newestDate = $entries[0]['entry_date'];
        $startDate = date('M j, Y', strtotime($oldestDate));
        $endDate = date('M j, Y', strtotime($newestDate));
    } else {
        $startDate = 'N/A';
        $endDate = 'N/A';
    }

    $weekInfo = [
        'start' => $startDate,
        'end' => $endDate,
        'entries' => $entries
    ];

    jsonResponse(['success' => true, 'week' => $weekInfo]);
}

/**
 * Delete an OJT entry and its images
 */
function deleteEntry() {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if (!$id) {
        jsonResponse(['error' => 'Invalid entry ID'], 400);
    }

    $pdo = getDbConnection();

    // Get all image paths for this entry
    $stmt = $pdo->prepare("SELECT image_path FROM entry_images WHERE entry_id = :id");
    $stmt->execute([':id' => $id]);
    $images = $stmt->fetchAll();

    // Delete image files
    foreach ($images as $image) {
        $filePath = __DIR__ . '/' . $image['image_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // Delete images from database (cascade will handle entry deletion)
    $stmt = $pdo->prepare("DELETE FROM entry_images WHERE entry_id = :id");
    $stmt->execute([':id' => $id]);

    // Delete the entry
    $stmt = $pdo->prepare("DELETE FROM ojt_entries WHERE id = :id");
    $stmt->execute([':id' => $id]);

    jsonResponse(['success' => true]);
}

/**
 * Update entry description
 */
function updateDescription() {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    $description = $data['description'] ?? '';

    if (!$id) {
        jsonResponse(['error' => 'Invalid entry ID'], 400);
    }

    if (empty($description)) {
        jsonResponse(['error' => 'Description cannot be empty'], 400);
    }

    $pdo = getDbConnection();

    // Update both user_description and ai_enhanced_description
    // When user manually edits, both fields should reflect the change
    $stmt = $pdo->prepare("
        UPDATE ojt_entries 
        SET user_description = :description, 
            ai_enhanced_description = :description 
        WHERE id = :id
    ");
    $stmt->execute([
        ':description' => $description,
        ':id' => $id
    ]);

    jsonResponse(['success' => true]);
}

/**
 * Generate AI-powered narrative report for all entries
 */
function generateNarrativeReport() {
    if (!isApiKeyConfigured()) {
        jsonResponse(['error' => 'API key not configured'], 500);
    }

    $pdo = getDbConnection();

    // Get all entries (no date filter)
    $stmt = $pdo->prepare("
        SELECT id, title, user_description, entry_date, ai_enhanced_description
        FROM ojt_entries
        ORDER BY entry_date ASC
    ");

    $stmt->execute();

    $entries = $stmt->fetchAll();

    if (empty($entries)) {
        jsonResponse(['error' => 'No entries found'], 404);
    }

    // Build concise context from entries
    $entriesContext = [];
    foreach ($entries as $entry) {
        $date = date('M j', strtotime($entry['entry_date']));
        $desc = $entry['ai_enhanced_description'] ?: $entry['user_description'] ?: 'No description';
        $entriesContext[] = "{$date}: {$entry['title']} - " . substr($desc, 0, 150);
    }

    $contextText = implode("\n", $entriesContext);

    // Optimized prompt: concise, direct
    $prompt = "Write a 2-paragraph OJT weekly narrative report:\n";
    $prompt .= "Paragraph 1: Summarize activities and skills developed\n";
    $prompt .= "Paragraph 2: Challenges overcome and professional growth\n\n";
    $prompt .= "Entries:\n{$contextText}\n\n";
    $prompt .= "Professional tone, 100-150 words.";

    $requestData = [
        'model' => QWEN_TEXT_MODEL,
        'messages' => [
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'max_tokens' => 300,
        'temperature' => 0.5
    ];

    $ch = curl_init(QWEN_API_ENDPOINT);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . QWEN_API_KEY,
            'HTTP-Referer: http://localhost:8000',
            'X-Title: OJT Journal Generator'
        ],
        CURLOPT_POSTFIELDS => json_encode($requestData),
        CURLOPT_TIMEOUT => 60
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    curl_close($ch);

    if ($response === false) {
        jsonResponse(['error' => 'API connection failed: ' . $curlError], 500);
    }

    $result = json_decode($response, true);

    if ($httpCode !== 200) {
        $errorMsg = $result['message'] ?? $result['error']['message'] ?? 'Unknown API error';
        jsonResponse(['error' => 'API error (' . $httpCode . '): ' . $errorMsg], 500);
    }

    if (isset($result['choices'][0]['message']['content'])) {
        $narrative = trim($result['choices'][0]['message']['content']);

        jsonResponse([
            'success' => true,
            'narrative' => $narrative,
            'entry_count' => count($entries)
        ]);
    } else {
        jsonResponse(['error' => 'Unexpected API response format'], 500);
    }
}

/**
 * Generate full ISPSC-formatted OJT Report with all chapters
 */
function generateISPSCReport() {
    if (!isApiKeyConfigured()) {
        jsonResponse(['error' => 'API key not configured'], 500);
    }

    $pdo = getDbConnection();

    // Get all entries
    $stmt = $pdo->prepare("
        SELECT id, title, user_description, entry_date, ai_enhanced_description
        FROM ojt_entries
        ORDER BY entry_date ASC
    ");

    $stmt->execute();
    $entries = $stmt->fetchAll();

    if (empty($entries)) {
        jsonResponse(['error' => 'No entries found. Add some OJT entries first.'], 404);
    }

    // Get images for each entry
    foreach ($entries as &$entry) {
        $stmt = $pdo->prepare("SELECT image_path FROM entry_images WHERE entry_id = :id ORDER BY image_order ASC");
        $stmt->execute([':id' => $entry['id']]);
        $entry['images'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Build comprehensive context from ALL entries
    $entriesContext = [];
    foreach ($entries as $entry) {
        $date = date('M j, Y', strtotime($entry['entry_date']));
        $desc = $entry['ai_enhanced_description'] ?: $entry['user_description'] ?: 'No description';
        $entriesContext[] = "[$date] {$entry['title']}: " . substr($desc, 0, 200);
    }
    $fullContext = implode("\n\n", $entriesContext);

    // Get date range
    $startDate = date('M j, Y', strtotime($entries[0]['entry_date']));
    $endDate = date('M j, Y', strtotime(end($entries)['entry_date']));
    $totalDays = count($entries);

    // Generate Chapter I: Company Profile (... [truncated]
    $chapter1Prompt = "From these OJT entries, write Chapter I (3 sections, 2-3 sentences each):\n";
    $chapter1Prompt .= "1. INTRODUCTION - Infer company name, location, nature of business from entries\n";
    $chapter1Prompt .= "2. DURATION - Use the date range from entries\n";
    $chapter1Prompt .= "3. PURPOSE - Infer role and objectives from activities\n\n";
    $chapter1Prompt .= "ALL OJT ENTRIES:\n{$fullContext}\n\n";
    $chapter1Prompt .= "Write Chapter I:";

    $chapter1 = callAIAPI($chapter1Prompt, 'Write OJT Company Profile based on entry context. Formal tone, max 150 words.', QWEN_TEXT_MODEL);

    // Generate Chapter II: Background ONLY (AI-powered) - Activities table will use actual entries
    $chapter2BackgroundPrompt = "From these OJT entries, write BACKGROUND OF ACTION PLAN (2-3 sentences):\n";
    $chapter2BackgroundPrompt .= "Describe the preparation and planning before starting the immersion based on the activities shown.\n\n";
    $chapter2BackgroundPrompt .= "ALL OJT ENTRIES:\n{$fullContext}\n\n";
    $chapter2BackgroundPrompt .= "Write Background section:";

    $chapter2Background = callAIAPI($chapter2BackgroundPrompt, 'Write OJT background section. Formal tone, max 100 words.', QWEN_TEXT_MODEL);

    // Chapter II Activities Table - Use ACTUAL entries with AI-enhanced descriptions
    $activitiesTableRows = [];
    foreach ($entries as $index => $entry) {
        $date = date('M j, Y', strtotime($entry['entry_date']));
        $dayNum = $index + 1;
        $activity = htmlspecialchars($entry['title'], ENT_QUOTES, 'UTF-8');
        // Use AI-enhanced description (from when entry was created)
        $remarks = htmlspecialchars($entry['ai_enhanced_description'] ?: $entry['user_description'] ?: 'No description', ENT_QUOTES, 'UTF-8');
        $activitiesTableRows[] = "| Day {$dayNum}<br>{$date} | {$activity} | {$remarks} |";
    }
    $activitiesTable = "| Day/Date | Activity | Remarks |\n| --- | --- | --- |\n" . implode("\n", $activitiesTableRows);

    // Combine Chapter II
    $chapter2 = "### BACKGROUND OF THE ACTION PLAN\n\n{$chapter2Background}\n\n### PROGRAM OF ACTIVITIES – PER DAY\n\n{$activitiesTable}";

    // Generate Chapter III: Conclusion & Recommendations (AI-powered based on ALL entries)
    $chapter3Prompt = "From these OJT entries, write Chapter III (2 sections, 2-3 sentences each):\n";
    $chapter3Prompt .= "1. CONCLUSION - Summarize learnings, skills gained, growth based on activities\n";
    $chapter3Prompt .= "2. RECOMMENDATION - Suggestions for: (a) future OJT students, (b) company, (c) ISPSC\n\n";
    $chapter3Prompt .= "ALL OJT ENTRIES:\n{$fullContext}\n\n";
    $chapter3Prompt .= "Write Chapter III:";

    $chapter3 = callAIAPI($chapter3Prompt, 'Write OJT conclusion and recommendations. Formal, concise, max 150 words.', QWEN_TEXT_MODEL);

    jsonResponse([
        'success' => true,
        'report' => [
            'chapter1' => $chapter1,
            'chapter2' => $chapter2,
            'chapter3' => $chapter3,
            'entries' => $entries,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $totalDays,
            'entry_count' => count($entries),
            'debug_context' => $fullContext // For debugging
        ]
    ]);
}

/**
 * Generate simple download report (non-AI, just entries from database)
 */
function generateDownloadReport() {
    $pdo = getDbConnection();

    // Get all entries
    $stmt = $pdo->prepare("
        SELECT id, title, user_description, entry_date, ai_enhanced_description
        FROM ojt_entries
        ORDER BY entry_date ASC
    ");

    $stmt->execute();
    $entries = $stmt->fetchAll();

    if (empty($entries)) {
        jsonResponse(['error' => 'No entries found. Add some OJT entries first.'], 404);
    }

    // Get images for each entry
    foreach ($entries as &$entry) {
        $stmt = $pdo->prepare("SELECT image_path FROM entry_images WHERE entry_id = :id ORDER BY image_order ASC");
        $stmt->execute([':id' => $entry['id']]);
        $entry['images'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Get date range
    $startDate = date('F j, Y', strtotime($entries[0]['entry_date']));
    $endDate = date('F j, Y', strtotime(end($entries)['entry_date']));
    $totalDays = count($entries);

    // Get student name from first entry title (or use default)
    $studentName = 'JUAN DELA CRUZ';

    jsonResponse([
        'success' => true,
        'report' => [
            'entries' => $entries,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $totalDays,
            'student_name' => $studentName
        ]
    ]);
}

/**
 * Bulk delete multiple entries
 */
function bulkDelete() {
    $input = json_decode(file_get_contents('php://input'), true);
    $ids = $input['ids'] ?? [];

    if (empty($ids)) {
        jsonResponse(['error' => 'No entry IDs provided'], 400);
    }

    // Validate IDs are integers
    $ids = array_map('intval', $ids);
    $ids = array_filter($ids, function($id) { return $id > 0; });

    if (empty($ids)) {
        jsonResponse(['error' => 'Invalid entry IDs'], 400);
    }

    $pdo = getDbConnection();

    try {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("DELETE FROM ojt_entries WHERE id IN ($placeholders)");
        $stmt->execute($ids);

        $deletedCount = $stmt->rowCount();

        Logger::info('Bulk delete completed', ['deleted' => $deletedCount, 'requested' => count($ids)]);

        jsonResponse([
            'success' => true,
            'deleted_count' => $deletedCount
        ]);

    } catch (PDOException $e) {
        Logger::error('Bulk delete failed', ['error' => $e->getMessage()]);
        jsonResponse(['error' => 'Failed to delete entries'], 500);
    }
}

/**
 * Get human-readable upload error message
 */
function getUploadErrorMessage($errorCode) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds server limit',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds form limit',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'PHP extension stopped the upload'
    ];

    return $errors[$errorCode] ?? 'Unknown error';
}

/**
 * Send JSON response
 */
function jsonResponse($data, $statusCode = 200) {
    // Clear any buffered output
    ob_end_clean();

    // Send JSON response
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>
