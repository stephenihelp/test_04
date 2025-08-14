<?php
session_start();
$host = 'localhost';
$db = 'e_checklist';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch pending accounts
$query = "SELECT * FROM students WHERE status = 'pending'";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Pending Accounts</title>
</head>
<body>
    <h1>Pending Accounts</h1>
    <table border="1">
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Contact</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['student_id']; ?></td>
                <td><?php echo $row['last_name'] . ', ' . $row['first_name']; ?></td>
                <td><?php echo $row['contact_no']; ?></td>
                <td>
                    <form method="POST" action="approve_account.php">
                        <input type="hidden" name="student_id" value="<?php echo $row['student_id']; ?>">
                        <button type="submit" name="action" value="approve">Approve</button>
                        <button type="submit" name="action" value="reject">Reject</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
