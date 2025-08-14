<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Start the session to access session variables

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


    // Get student_id from POST (for admin or student update)
    if (!isset($_POST['student_id'])) {
        echo json_encode(['success' => false, 'message' => 'No student ID provided']);
        exit;
    }
    $student_id = $_POST['student_id'];

    // Get all fields
    $last_name = isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : null;
    $first_name = isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : null;
    $middle_name = isset($_POST['middle_name']) ? htmlspecialchars($_POST['middle_name']) : null;
    $password = isset($_POST['password']) ? $_POST['password'] : null;
    $contact_no = isset($_POST['contact_no']) ? htmlspecialchars($_POST['contact_no']) : null;
    $address = isset($_POST['address']) ? htmlspecialchars($_POST['address']) : null;
    $admission_date = isset($_POST['admission_date']) ? htmlspecialchars($_POST['admission_date']) : null;
    $picture = $_FILES['picture'] ?? null;

    // If password is not empty, update it; otherwise, keep old password
    if ($password !== null && $password !== '') {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    } else {
        // Get current password from DB
        $result = $conn->query("SELECT password FROM students WHERE student_id = '" . $conn->real_escape_string($student_id) . "'");
        $row = $result ? $result->fetch_assoc() : null;
        $hashed_password = $row ? $row['password'] : '';
    }

    // Update all fields except picture
    $sql = "UPDATE students SET last_name=?, first_name=?, middle_name=?, password=?, contact_no=?, address=?, admission_date=? WHERE student_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssss', $last_name, $first_name, $middle_name, $hashed_password, $contact_no, $address, $admission_date, $student_id);

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile information']);
        exit;
    }

    // Handle profile picture update
    if ($picture && $picture['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $ext = pathinfo($picture['name'], PATHINFO_EXTENSION);
        $uniqueName = uniqid() . '.' . $ext;
        $filePath = $uploadDir . $uniqueName;

        // Move the uploaded file to the upload directory
        if (move_uploaded_file($picture['tmp_name'], $filePath)) {
            $sql = "UPDATE students SET picture = ? WHERE student_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $filePath, $student_id);

            if (!$stmt->execute()) {
                echo json_encode(['success' => false, 'message' => 'Failed to update profile picture']);
                exit;
            }
            // Update session variable so new image shows after reload
            $_SESSION['picture'] = $filePath;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload the picture']);
            exit;
        }
    }

    echo json_encode(['success' => true]);
    $conn->close();
}
?>
