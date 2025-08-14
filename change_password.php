<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database connection details
    $host = 'localhost';
    $db = 'e_checklist'; // Replace with your database name
    $user = 'root';      // Replace with your database username
    $pass = ''; // Replace with your database password

    // Create connection
    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    $student_id = $_SESSION['student_id'];  // Get the student ID from the session
    $current_password = htmlspecialchars($_POST['current_password']);
    $new_password = htmlspecialchars($_POST['new_password']);

    // Fetch the current password from the database
    $sql = "SELECT password FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stored_password = $result->fetch_assoc()['password'];

    // Verify the current password
    if (!password_verify($current_password, $stored_password)) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        exit;
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

    // Update the password in the database
    $sql = "UPDATE students SET password = ? WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $hashed_password, $student_id);

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Failed to update password']);
        exit;
    }

    echo json_encode(['success' => true]);
    $conn->close();
}
?>
