98% of storage used … If you run out, you can't create, edit, and upload files. Get 100 GB of storage for ₱89.00 ₱22.25/month for 3 months.
<?php
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

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $student_id = trim($_POST['student_id']);
    $last_name = trim($_POST['last_name']);
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $password = trim($_POST['password']);
    $contact_no = trim($_POST['contact_no']);
    $address = trim($_POST['address']);
    $admission_date = trim($_POST['admission_date']);

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Handle picture upload
    $target_dir = "uploads/"; // Directory to save uploaded files
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true); // Create directory if it does not exist
    }

    $target_file = $target_dir . basename($_FILES["picture"]["name"]);
    $upload_ok = 1;
    $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is valid
    if (isset($_FILES["picture"]) && $_FILES["picture"]["size"] > 0) {
        $check = getimagesize($_FILES["picture"]["tmp_name"]);
        if ($check === false) {
            echo json_encode(['status' => 'error', 'message' => 'File is not an image.']);
            $upload_ok = 0;
        }

        // Check file size (limit to 5MB)
        if ($_FILES["picture"]["size"] > 5000000) {
            echo json_encode(['status' => 'error', 'message' => 'Sorry, your file is too large.']);
            $upload_ok = 0;
        }

        // Allow only certain file formats
        if (!in_array($image_file_type, ["jpg", "png", "jpeg", "gif"])) {
            echo json_encode(['status' => 'error', 'message' => 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.']);
            $upload_ok = 0;
        }

        // Attempt to upload file if no errors
        if ($upload_ok && !move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
            echo json_encode(['status' => 'error', 'message' => 'Sorry, there was an error uploading your file.']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No file uploaded.']);
        exit;
    }

    // Insert data into the database
    $stmt = $conn->prepare("INSERT INTO students (student_id, last_name, first_name, middle_name, password, contact_no, address, admission_date, picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $student_id, $last_name, $first_name, $middle_name, $hashed_password, $contact_no, $address, $admission_date, $target_file);


    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Student data saved successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error saving data: ' . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>