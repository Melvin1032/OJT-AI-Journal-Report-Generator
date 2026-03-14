<?php
/**
 * Encryption Helper for API Keys
 * 
 * Provides encryption/decryption for sensitive data like API keys.
 * Uses OpenSSL with AES-256-CBC cipher.
 */

/**
 * Get encryption key from environment or generate one
 * @return string Encryption key
 */
function getEncryptionKey() {
    // Try to get from environment first
    $envKey = getenv('ENCRYPTION_KEY') ?: $_ENV['ENCRYPTION_KEY'] ?? null;
    
    if ($envKey) {
        return $envKey;
    }
    
    // Fallback: Generate and store a key in a secure file
    $keyFile = __DIR__ . '/../storage/.encryption_key';
    
    if (file_exists($keyFile)) {
        return trim(file_get_contents($keyFile));
    }
    
    // Generate new key
    $key = bin2hex(random_bytes(32)); // 256-bit key
    file_put_contents($keyFile, $key);
    
    // Protect the key file
    chmod($keyFile, 0600);
    
    return $key;
}

/**
 * Get initialization vector (IV)
 * @return string IV for encryption
 */
function getIV() {
    return random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
}

/**
 * Encrypt API key
 * @param string $data Data to encrypt
 * @return string Encrypted data (base64 encoded with IV prepended)
 */
function encryptApiKey($data) {
    $key = getEncryptionKey();
    $iv = getIV();
    
    $encrypted = openssl_encrypt(
        $data,
        'aes-256-cbc',
        hash('sha256', $key, true),
        OPENSSL_RAW_DATA,
        $iv
    );
    
    if ($encrypted === false) {
        error_log('Encryption failed: ' . openssl_error_string());
        return $data; // Fallback to unencrypted
    }
    
    // Prepend IV to encrypted data and base64 encode
    return base64_encode($iv . $encrypted);
}

/**
 * Decrypt API key
 * @param string $encryptedData Encrypted data (base64 encoded with IV prepended)
 * @return string Decrypted data
 */
function decryptApiKey($encryptedData) {
    $key = getEncryptionKey();
    
    $data = base64_decode($encryptedData);
    
    if ($data === false) {
        error_log('Base64 decode failed');
        return $encryptedData; // Return as-is if not encrypted
    }
    
    // Extract IV (first 16 bytes) and encrypted data
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data, 0, $ivLength);
    $encrypted = substr($data, $ivLength);
    
    $decrypted = openssl_decrypt(
        $encrypted,
        'aes-256-cbc',
        hash('sha256', $key, true),
        OPENSSL_RAW_DATA,
        $iv
    );
    
    if ($decrypted === false) {
        error_log('Decryption failed: ' . openssl_error_string());
        return $encryptedData; // Return as-is if decryption fails
    }
    
    return $decrypted;
}

/**
 * Check if data is encrypted
 * @param string $data Data to check
 * @return bool True if encrypted
 */
function isEncrypted($data) {
    // Encrypted data is base64 encoded and longer than typical API keys
    if (strlen($data) < 50) {
        return false;
    }
    
    // Try to decode base64
    $decoded = base64_decode($data, true);
    if ($decoded === false) {
        return false;
    }
    
    // Check if decoded data has expected length (IV + encrypted content)
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    if (strlen($decoded) <= $ivLength) {
        return false;
    }
    
    return true;
}

/**
 * Safely get API key (decrypt if needed)
 * @param string $data API key (encrypted or plain)
 * @return string Decrypted API key
 */
function getApiKey($data) {
    if (isEncrypted($data)) {
        return decryptApiKey($data);
    }
    return $data;
}
?>
