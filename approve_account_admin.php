<?php
// Database connection
$host = 'localhost';
$db = 'e_checklist'; // Replace with your database name
$user = 'root';      // Replace with your database username
$pass = '';          // Replace with your database password

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if student_id is set in the URL
if (isset($_GET['student_id'])) {
    $student_id = $conn->real_escape_string($_GET['student_id']); // Sanitize input

    // Update the student's status to 'approved'
    $query = "UPDATE students SET status = 'approved' WHERE student_id = '$student_id'";
    if ($conn->query($query) === TRUE) {
        // Redirect back to the pending accounts page with success message
        header("Location: pending_accs_admin.php?message=Account approved successfully.");
        exit;
    } else {
        // Redirect back with an error message if the update fails
        header("Location: pending_accs_admin.php?message=Error approving account.");
        exit;
    }
} else {
    // Redirect back if student_id is not set
    header("Location: pending_accs_admin.php?message=No account selected.");
    exit;
}

// Close connection
$conn->close();

?>
