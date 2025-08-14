<?php
// Fetch the programs from the database

$servername = "localhost";
$username = "root";
$password = "Kwistyan10!";
$dbname = "e_checklist";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch programs from the database
$sql = "SELECT name FROM programs";
$result = $conn->query($sql);

$programs = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row['name'];
    }
}

$conn->close();

// Return programs as a JSON response
echo json_encode(['programs' => $programs]);
?>
