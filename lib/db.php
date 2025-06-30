<?php

try {
    // Create a new object-oriented MySQLi connection
    $conn = new mysqli("localhost", "root", "", "courtsmanagement");

    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    // Handle connection errors
    die("Database connection failed: " . $e->getMessage());
}
?>











