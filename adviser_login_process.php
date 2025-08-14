<?php
session_start(); // Start the session

// Database connection details
$host = "localhost";
$dbname = "e_checklist";
$user = "root"; // Replace with your database username
$pass = ""; // Replace with your database password

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ensure the form was submitted using POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? ''); // Trim to remove any leading or trailing spaces
        $password = trim($_POST['password'] ?? ''); // Trim to remove any leading or trailing spaces

        // Check if username or password is empty
        if (empty($username) || empty($password)) {
            echo "<script>alert('Username or password cannot be empty.'); window.location.href = 'adviser_login.php';</script>";
            exit();
        }

        // Check if username exists in the `adviser` table
        $stmt = $pdo->prepare("SELECT * FROM adviser WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $adviser = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if adviser was found and password matches
        if ($adviser && password_verify($password, $adviser['password'])) {
            // Store necessary user data in the session
            $_SESSION['id'] = $adviser['id']; // Assuming 'id' is the primary key in the `adviser` table
            $_SESSION['full_name'] = $adviser['full_name'];
            $_SESSION['username'] = $adviser['username'];
            $_SESSION['pronoun'] = $adviser['pronoun'];

            // Redirect to the adviser home page
            header("Location: home_page_adviser.php");
            exit();
        } else {
            // Incorrect credentials
            echo "<script>alert('Invalid username or password.'); window.location.href = 'adviser_login.php';</script>";
        }
    } else {
        // Invalid request method
        echo "<script>alert('Invalid request method.'); window.location.href = 'adviser_login.php';</script>";
    }
} catch (PDOException $e) {
    // Database connection or query error
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    // General error
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
?>
