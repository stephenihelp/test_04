<?php
// Save the programs to the database

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

// Get the programs from the request
$data = json_decode(file_get_contents("php://input"), true);
$programs = $data['programs'];

// Delete all previous records (optional, depending on your use case)
$conn->query("DELETE FROM programs");

// Insert new programs
$stmt = $conn->prepare("INSERT INTO programs (name) VALUES (?)");

foreach ($programs as $program) {
    $stmt->bind_param("s", $program);
    $stmt->execute();
}

$stmt->close();
$conn->close();

echo json_encode(['success' => true]);
?>
