<?php
/**
 * Database Configuration File
 * 
 * SETUP INSTRUCTIONS:
 * ------------------
 * 
 * FOR LOCAL DEVELOPMENT (XAMPP):
 * - DB_HOST: 'localhost'
 * - DB_USER: 'root'
 * - DB_PASS: '' (leave empty for default XAMPP)
 * - DB_NAME: 'project_a2'
 * 
 * FOR INFINITYFREE HOSTING:
 * - DB_HOST: Use the hostname from your InfinityFree control panel
 *   (usually something like: sql123.infinityfreeapp.com or sql123.epizy.com)
 * - DB_USER: Your InfinityFree database username (from control panel)
 * - DB_PASS: Your InfinityFree database password (from control panel)
 * - DB_NAME: Your InfinityFree database name (from control panel)
 * 
 * NOTE: InfinityFree database credentials are different from local XAMPP!
 * You'll need to update these when deploying to InfinityFree.
 */

// Database Configuration Constants
// TODO: Update these based on your environment (local XAMPP or InfinityFree)

define('DB_HOST', 'localhost');        // XAMPP: localhost | InfinityFree: sql123.infinityfreeapp.com
define('DB_USER', 'root');             // XAMPP: root | InfinityFree: epiz_xxxxx
define('DB_PASS', '');                 // XAMPP: empty | InfinityFree: your_password
define('DB_NAME', 'project_a2');       // XAMPP: project_a2 | InfinityFree: epiz_xxxxx_project

// Create database connection
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset to utf8mb4 for proper character support
        $conn->set_charset("utf8mb4");
        
        return $conn;
        
    } catch (Exception $e) {
        // Log error (in production, log to file instead of displaying)
        error_log("Database connection error: " . $e->getMessage());
        die("Database connection failed. Please check configuration.");
    }
}

// Helper function to close connection
function closeDBConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}

// Helper function to sanitize output (prevent XSS)
function sanitizeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>