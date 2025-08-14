<?php
$host = 'localhost';
$db = 'e_checklist';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$student_id = $_GET['student_id'];

$query = $conn->prepare("DELETE FROM students WHERE student_id = ?");
$query->bind_param("s", $student_id);
$query->execute();

header("Location: pending_accs_adviser.php");
$conn->close();
?>
