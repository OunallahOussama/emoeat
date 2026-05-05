<?php
/**
 * PHPUnit Bootstrap - Sets up test environment
 */

// Autoload
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Set environment variables for test database
putenv('DB_HOST=127.0.0.1');
putenv('DB_NAME=emoeat_test');
putenv('DB_USER=emoeat_user');
putenv('DB_PASSWORD=emoeat_pass');

// Define base path
define('BASE_PATH', dirname(__DIR__));
