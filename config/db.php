<?php
/**
 * Database Configuration File
 * PDO connection with error handling
 * Port: 3307 (Custom MySQL Port)
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_PORT', '3307');  // Custom MySQL port
define('DB_NAME', 'quotation_system');
define('DB_USER', 'root');
define('DB_PASS', '31052006');
define('DB_CHARSET', 'utf8mb4');

// Create PDO connection
try {
    // DSN with custom port
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    // Log error (in production, don't display sensitive info)
    error_log("Database Connection Failed: " . $e->getMessage());
    die("Database Connection Failed. Please check your configuration.");
}

// Timezone
date_default_timezone_set('Asia/Colombo');
?>