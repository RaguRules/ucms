<?php
// Database connection configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "courtsmanagement";

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to ensure proper handling of special characters
mysqli_set_charset($conn, "utf8mb4");

// Function to sanitize input data
function sanitizeInput($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data));
}

// Function to execute query and return result
function executeQuery($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log("Query Error: " . mysqli_error($conn) . " - SQL: " . $sql);
    }
    return $result;
}

// Function to get single row from database
function getRow($conn, $sql) {
    $result = executeQuery($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

// Function to get multiple rows from database
function getRows($conn, $sql) {
    $result = executeQuery($conn, $sql);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

// Function to insert data and return inserted ID
function insertData($conn, $table, $data) {
    $columns = implode(", ", array_keys($data));
    $values = "'" . implode("', '", array_values($data)) . "'";
    $sql = "INSERT INTO $table ($columns) VALUES ($values)";
    
    if (executeQuery($conn, $sql)) {
        return mysqli_insert_id($conn);
    }
    return false;
}

// Function to update data
function updateData($conn, $table, $data, $condition) {
    $updates = [];
    foreach ($data as $column => $value) {
        $updates[] = "$column = '$value'";
    }
    $updateStr = implode(", ", $updates);
    $sql = "UPDATE $table SET $updateStr WHERE $condition";
    
    return executeQuery($conn, $sql);
}

// Function to delete data
function deleteData($conn, $table, $condition) {
    $sql = "DELETE FROM $table WHERE $condition";
    return executeQuery($conn, $sql);
}
?>
