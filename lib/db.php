<?php
// Enable MySQLi to throw exceptions on errors
// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Create a new object-oriented MySQLi connection
    $conn = new mysqli("localhost", "root", "", "courtsmanagement");

    // Set charset (optional but good practice)
    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    // Handle connection errors
    die("Database connection failed: " . $e->getMessage());
}
?>
