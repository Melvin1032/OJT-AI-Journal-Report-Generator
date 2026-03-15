<?php
/**
 * User Login API Endpoint
 */

session_start();
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
        exit;
    }

    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';

    if (empty($username)) {
        throw new Exception('Username or email is required');
    }

    if (empty($password)) {
        throw new Exception('Password is required');
    }

    $pdo = getDbConnection();

    // Find user
    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception('Invalid credentials');
    }

    if (!password_verify($password, $user['password_hash'])) {
        throw new Exception('Invalid credentials');
    }

    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];

    // Restore API keys from database to session
    $userKeys = getUserApiKeys();
    if ($userKeys) {
        $_SESSION['api_keys'] = $userKeys;
        // Check if at least OpenRouter key is configured
        if (!empty($userKeys['openrouter'])) {
            $_SESSION['api_keys_configured'] = true;
        } else {
            $_SESSION['api_keys_configured'] = false;
        }
    } else {
        // No API keys found - user needs to configure them
        $_SESSION['api_keys_configured'] = false;
    }

    session_regenerate_id(true);

    error_log("User logged in: {$user['username']} (ID: {$user['id']})");
    echo json_encode([
        'success' => true,
        'api_keys_configured' => isset($_SESSION['api_keys_configured']) && $_SESSION['api_keys_configured'] === true
    ]);

} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
?>
