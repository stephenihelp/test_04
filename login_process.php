<?php
session_start(); // Start the session

// Database connection details
$host = 'localhost';
$db = 'e_checklist'; // Replace with your database name
$user = 'root';      // Replace with your database username
$pass = ''; // Replace with your database password

// Create a database connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure the form was submitted using POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id'] ?? ''); // Trim to remove any leading or trailing spaces
    $password = trim($_POST['password'] ?? ''); // Trim to remove any leading or trailing spaces

    // Check if student_id or password is empty
    if (empty($student_id) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Student ID or password cannot be empty.']);
        exit();
    }

    // Check credentials in the database
    $query = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
    $query->bind_param("s", $student_id);
    $query->execute();
    $result = $query->get_result();

    // Check if student_id exists and password matches
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

    // Check account status
    if ($row['status'] === 'pending') {
        echo json_encode(['status' => 'pending', 'message' => 'Your account is pending approval. Please wait for the admin to approve.']);
        exit();
    } elseif ($row['status'] === 'rejected') {
        echo json_encode(['status' => 'rejected', 'message' => 'Your account was rejected. Please contact admin for more information.']);
        exit();
    }

        // Verify the password using password_verify
        if (password_verify($password, $row['password'])) {
            // Store necessary user data in the session
            $_SESSION['student_id'] = $student_id;
            $_SESSION['last_name'] = $row['last_name'];
            $_SESSION['first_name'] = $row['first_name'];
            $_SESSION['middle_name'] = $row['middle_name'];
            $_SESSION['password'] = $row['password'];
            $_SESSION['contact_no'] = $row['contact_no'];
            $_SESSION['address'] = $row['address'];
            $_SESSION['admission_date'] = $row['admission_date'];
            $_SESSION['picture'] = $row['picture']; // Assuming the picture path is stored in the database

            // Redirect to the home page
            echo json_encode(['status' => 'success', 'redirect' => 'home_page_student.php']);
            exit();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid password. Please try again.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Student ID not found. Please try again.']);
    }
    }
// Close connection
$conn->close();
?>