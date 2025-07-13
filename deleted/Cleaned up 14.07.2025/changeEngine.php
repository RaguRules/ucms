<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "courtsmanagement"; // ← change this

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch MyISAM tables
$sql = "SELECT TABLE_NAME 
        FROM information_schema.TABLES 
        WHERE TABLE_SCHEMA = ? AND ENGINE = 'MyISAM'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $dbname);
$stmt->execute();
$result = $stmt->get_result();

$converted = 0;

while ($row = $result->fetch_assoc()) {
    $table = $row['TABLE_NAME'];
    echo "Converting table `$table` to InnoDB... ";

    $alter = "ALTER TABLE `$table` ENGINE=InnoDB";
    if ($conn->query($alter)) {
        echo "✅ Done\n";
        $converted++;
    } else {
        echo "❌ Failed: " . $conn->error . "\n";
    }
}

if ($converted === 0) {
    echo "No MyISAM tables found in `$dbname`.\n";
} else {
    echo "🎉 Converted $converted table(s) to InnoDB.\n";
}

$conn->close();
