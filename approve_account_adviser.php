<?php
session_start();
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

    // Get adviser's batches
    if (!isset($_SESSION['id'])) {
        header("Location: pending_accs_adviser.php?message=Access denied. Please log in.");
        exit;
    }
    $adviser_id = $_SESSION['id'];
    $batch_query = $conn->prepare("SELECT batch FROM adviser_batch WHERE id = ?");
    $batch_query->bind_param("i", $adviser_id);
    $batch_query->execute();
    $batch_result = $batch_query->get_result();
    if ($batch_result->num_rows === 0) {
        header("Location: pending_accs_adviser.php?message=No batch assigned to this adviser.");
        exit;
    }
    $batches = [];
    while ($row = $batch_result->fetch_assoc()) {
        $batches[] = $row['batch'];
    }
    $batch_query->close();

    // Check if student_id starts with any of adviser's batches
    $allowed = false;
    foreach ($batches as $batch) {
        if (strpos($student_id, (string)$batch) === 0) {
            $allowed = true;
            break;
        }
    }
    if (!$allowed) {
        echo "<pre style='color:red; background:#fff; padding:12px; border:2px solid #dc3545;'>";
        echo "DEBUG: Approval blocked.\n";
        echo "Adviser batches: "; var_export($batches); echo "\n";
        echo "Student ID: $student_id\n";
        echo "Batch match logic: Only approves if student_id starts with one of the batches.\n";
        echo "</pre>";
        exit;
    }

    // Update the student's status to 'approved'
    $update_stmt = $conn->prepare("UPDATE students SET status = 'approved' WHERE student_id = ?");
    $update_stmt->bind_param("s", $student_id);
    if ($update_stmt->execute()) {
        $update_stmt->close();
        header("Location: pending_accs_adviser.php?message=Account approved successfully");
        exit;
    } else {
        $update_stmt->close();
        header("Location: pending_accs_adviser.php?message=Error approving account.");
        exit;
    }
} else {
    // Redirect back if student_id is not set
    header("Location: pending_accs_adviser.php?message=No account selected.");
    exit;
}

// Close connection
$conn->close();

?>
