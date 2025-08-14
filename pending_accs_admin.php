<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CVSU-Carmona E-Checklist</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background: url('pix/school.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
        }
        .header {
            background-color: #206018;
            color: #fff;
            padding: 10px;
            text-align: left;
            font-size: 20px;
            font-weight: bold;
            width: 100%;
            margin-top: 0;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
        }
        .header img {
            height: 32px;
            width: auto;
            margin-right: 12px;
            vertical-align: middle;
        }
        .content {
            text-align: center;
            margin: 80px 20px 20px;
        }
        .content h2 {
            background-color: #206018;
            color: #fff;
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
        }
        .table-container {
            margin: 20px auto;
            width: 90%;
            max-width: 1000px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #206018;
            color: #fff;
        }
        td {
            background-color: #f9f9f9;
        }
        .action-icons {
            text-align: left;
        }
        .action-icons i {
            margin: 0 10px;
            cursor: pointer;
            font-size: 16px;
        }
        .student-number {
            color: green;
            font-weight: bold;
        }
        .back-btn {
            margin: 15px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .back-btn:hover {
            background-color: #3e8e41;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="img/cav.png" alt="CvSU Logo">
        <span>PRE - ENROLLMENT ASSESSMENT</span>
    </div>
    <div class="content">
        <h2>STUDENT PENDING ACCOUNTS</h2>
        <a href="home_page_admin.php" class="back-btn">Back</a>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>STUDENT NUMBER</th>
                    <th>FULL NAME</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Display messages
                if (isset($_GET['message'])) {
                    echo "<p style='text-align: center; color: green; font-weight: bold;'>{$_GET['message']}</p>";
                }
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

                // Fetch pending accounts
                $query = "SELECT student_id, last_name, first_name, middle_name FROM students WHERE status = 'pending'";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $fullName = htmlspecialchars($row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name']);
                        echo "
                            <tr>
                                <td class='student-number'>" . htmlspecialchars($row['student_id']) . "</td>
                                <td>{$fullName}</td>
                                <td class='action-icons'>
                                    <a href='approve_account_admin.php?student_id=" . urlencode($row['student_id']) . "' onclick='return confirm(\"Approve this account?\");'>
                                        <i class='fas fa-check' style='color: green;' title='Approve'></i>
                                    </a>
                                    <a href='reject_admin.php?student_id=" . urlencode($row['student_id']) . "' onclick='return confirm(\"Reject this account?\");'>
                                        <i class='fas fa-times' style='color: red;' title='Reject'></i>
                                    </a>
                                </td>
                            </tr>
                        ";
                    }
                } else {
                    echo "
                        <tr>
                            <td colspan='3' style='text-align: center;'>No pending accounts found.</td>
                        </tr>
                    ";
                }

                // Close connection
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>