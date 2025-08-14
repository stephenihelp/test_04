<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'e_checklist';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Database connection successful!<br>";

// Check if students table exists
$result = $conn->query("SHOW TABLES LIKE 'students'");
if ($result->num_rows > 0) {
    echo "Students table exists!<br>";
    
    // Show table structure
    $result = $conn->query("DESCRIBE students");
    echo "<h3>Students table structure:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Students table does not exist!<br>";
}

// Test a simple insert query
echo "<h3>Testing insert query:</h3>";
$test_stmt = $conn->prepare("INSERT INTO students (student_id, last_name, first_name, middle_name, password, contact_no, address, admission_date, picture, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if ($test_stmt) {
    echo "Prepare statement successful!<br>";
    $test_id = 'TEST' . time();
    $test_status = 'pending';
    $bind_result = $test_stmt->bind_param("ssssssssss", $test_id, 'Test', 'Student', 'Name', 'hashed_password', '1234567890', 'Test Address', '2024-01-01', 'test.jpg', $test_status);
    
    if ($bind_result) {
        echo "Bind parameters successful!<br>";
        
        if ($test_stmt->execute()) {
            echo "Execute successful! Test record inserted.<br>";
            // Clean up test record
            $conn->query("DELETE FROM students WHERE student_id = '$test_id'");
            echo "Test record cleaned up.<br>";
        } else {
            echo "Execute failed: " . $test_stmt->error . "<br>";
        }
    } else {
        echo "Bind parameters failed: " . $test_stmt->error . "<br>";
    }
    $test_stmt->close();
} else {
    echo "Prepare statement failed: " . $conn->error . "<br>";
}

$conn->close();
?> 