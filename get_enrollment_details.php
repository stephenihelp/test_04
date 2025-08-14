<?php
session_start();

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "e_checklist";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Get enrollment_id from URL parameter
$enrollment_id = isset($_GET['enrollment_id']) ? $_GET['enrollment_id'] : null;

if (!$enrollment_id) {
    die(json_encode(['success' => false, 'message' => 'No enrollment ID provided']));
}

// Get enrollment details (main info)
$sql = "SELECT * FROM pre_enrollments WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $enrollment_id);
$stmt->execute();
$result = $stmt->get_result();
$enrollment = $result->fetch_assoc();
$stmt->close();

if ($enrollment) {
    // Get course details (comma-separated fields)
    $sql2 = "SELECT course_codes, course_titles, units FROM pre_enrollment_courses WHERE pre_enrollment_id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("s", $enrollment_id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $row2 = $result2->fetch_assoc();
    $stmt2->close();

    $courses = [];
    if ($row2) {
        $codes = array_map('trim', explode(',', $row2['course_codes']));
        $titles = array_map('trim', explode(',', $row2['course_titles']));
        $units = array_map('trim', explode(',', $row2['units']));
        $count = max(count($codes), count($titles), count($units));
        for ($i = 0; $i < $count; $i++) {
            $courses[] = [
                'course_code' => $codes[$i] ?? '',
                'course_title' => $titles[$i] ?? '',
                'units' => $units[$i] ?? '',
                'day' => '' // No day info in this structure
            ];
        }
    }
    $enrollment['courses'] = $courses;
    echo json_encode(['success' => true, 'enrollment' => $enrollment]);
} else {
    echo json_encode(['success' => false, 'message' => 'Enrollment not found']);
}

$conn->close();
?>
