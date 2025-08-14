<?php
// Database connection
$host = "localhost";
$dbname = "e_checklist";
$user = "root"; // Replace with your database username
$pass = "";     // Replace with your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve POST data
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $sex = $_POST['sex'];
    $pronoun = $_POST['pronoun'];

    // Insert into the `adviser` table
    $stmt = $pdo->prepare("INSERT INTO adviser (full_name, username, password, sex, pronoun) 
                           VALUES (:full_name, :username, :password, :sex, :pronoun)");
    $stmt->execute([
        ':full_name' => $full_name,
        ':username' => $username,
        ':password' => password_hash($password, PASSWORD_DEFAULT), // Use hashing for security
        ':sex' => $sex,
        ':pronoun' => $pronoun,
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Data saved successfully!']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
?>
