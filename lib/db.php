<?php

try {
    $conn = new mysqli("localhost", "root", "root", "courtsmanagement");

    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>











