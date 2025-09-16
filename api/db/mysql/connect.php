<?php 

    // Database configuration
    $servername = "localhost";
    $username = "staging_sql_psc_musicweb";
    $password = "63SdEIi72i9jbB";
    $dbname = "psc-musicweb";
    $port = 3306; // Default MySQL port

    // Enable error reporting for debugging
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        // Create Connection with explicit port and charset
        $conn = mysqli_connect($servername, $username, $password, $dbname, $port);
        
        // Set charset to utf8mb4 for better Unicode support
        mysqli_set_charset($conn, "utf8mb4");
        
        // Optional: Set timezone if needed
        // mysqli_query($conn, "SET time_zone = '+00:00'");
        
    } catch (mysqli_sql_exception $e) {
        // Log the error for debugging (don't expose sensitive info to users)
        error_log("Database connection failed: " . $e->getMessage());
        
        // For production, use a generic error message
        die("Database connection failed. Please try again later.");
        
        // For development/debugging, you can show detailed error:
        // die("Connection failed: " . $e->getMessage());
    }

    // Additional connection check (redundant with exception handling, but kept for compatibility)
    if (!$conn) {
        error_log("Database connection failed: " . mysqli_connect_error());
        die("Connection failed. Please try again later.");
    }

?>