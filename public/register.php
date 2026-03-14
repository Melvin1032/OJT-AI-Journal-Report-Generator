<?php
/**
 * User Registration API Endpoint
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
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $confirmPassword = $input['confirm_password'] ?? '';

    // Validation
    if (empty($username) || strlen($username) < 3) {
        throw new Exception('Username must be at least 3 characters');
    }
    
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        throw new Exception('Username can only contain letters, numbers, and underscores');
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }
    
    if (empty($password) || strlen($password) < 8) {
        throw new Exception('Password must be at least 8 characters');
    }
    
    if ($password !== $confirmPassword) {
        throw new Exception('Passwords do not match');
    }

    $pdo = getDbConnection();

    // Check if username exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Username already taken');
    }

    // Check if email exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Email already registered');
    }

    // Create user
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $passwordHash]);

    error_log("User registered: {$username}");
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log('Registration error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
?>
