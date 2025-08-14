<?php
// list_of_students.php
// Simple student list for admin

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "e_checklist";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT student_id, CONCAT(last_name, ', ', first_name, ' ', middle_name) AS name FROM students ORDER BY last_name, first_name";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Students</title>
    <style>
        body {
            background: url('pix/school.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 30px 40px;
        }
        h1 {
            text-align: center;
            color: #206018;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #206018;
            color: white;
        }
        tr:nth-child(even) { background: #f9f9f9; }
        tr:hover { background: #f1f1f1; }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 8px 18px;
            background: #206018;
            color: #fff;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }
        .back-btn:hover { background: #174c13; }
    </style>
</head>
<body>
    <!-- Title Bar -->
    <div style="width:100%; background:#206018; color:#fff; display:flex; align-items:center; padding:0 0 0 20px; height:47px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
        <img src="img/cav.png" alt="Logo" style="height:35px; margin-right:18px;">
        <span style="font-size:1.5rem; font-weight:bold; letter-spacing:1px;">Pre-Enrollment Assessment</span>
    </div>
    <div class="container">
        <h1>List of Students</h1>
        <table>
            <thead>
                <tr>
                    <th>Student Number</th>
                    <th>Name</th>
                    <th>Program</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td>BSCS</td>
                            <td>
                                <a href="acc_mng_admin.php?student_id=<?php echo urlencode($row['student_id']); ?>" class="back-btn" style="padding:6px 14px; font-size:14px;">View/Edit</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center;">No students found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <a href="home_page_admin.php" class="back-btn">Back to Home</a>
    </div>
</body>
</html>
<?php $conn->close(); ?>
