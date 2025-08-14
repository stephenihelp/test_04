<?php
session_start();
// Enable error logging (not display)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "e_checklist";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log('Connection failed: ' . $conn->connect_error);
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Get student_id from URL parameter if it exists, otherwise use session
$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : (isset($_SESSION['student_id']) ? $_SESSION['student_id'] : null);

if (!$student_id) {
    error_log('No student ID provided');
    die(json_encode(['success' => false, 'message' => 'No student ID provided']));
}

// Get transaction history using the existing structure (comma-separated values)
$sql = "SELECT 
            pe.id,
            pe.created_at,
            pec.course_codes,
            pec.course_titles,
            pec.units
        FROM pre_enrollments pe
        JOIN pre_enrollment_courses pec ON pe.id = pec.pre_enrollment_id
        WHERE pe.student_id = ?
        ORDER BY pe.created_at DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log('Prepare failed: ' . $conn->error);
    die(json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]));
}

$stmt->bind_param("s", $student_id);
if (!$stmt->execute()) {
    error_log('Execute failed: ' . $stmt->error);
    die(json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]));
}

$result = $stmt->get_result();

$transactions = [];
while ($row = $result->fetch_assoc()) {
    // Format the created_at date
    $row['created_at'] = date('M d, Y h:i A', strtotime($row['created_at']));
    $transactions[] = $row;
}
error_log('Transaction history rows returned: ' . count($transactions));

echo json_encode(['success' => true, 'transactions' => $transactions]);

$stmt->close();
$conn->close();
?>
