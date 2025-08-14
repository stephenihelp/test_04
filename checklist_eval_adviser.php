<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "e_checklist";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the adviser is logged in
if (!isset($_SESSION['id'])) {
    echo "Access denied. Please log in.";
    exit;
}

// Get the logged-in adviser's ID
$adviser_id = $_SESSION['id']; // Make sure session has the 'id' key for the adviser

// Fetch all batches assigned to the adviser
$stmt = $conn->prepare("SELECT batch FROM adviser_batch WHERE id = ?");
$stmt->bind_param("i", $adviser_id);
$stmt->execute();
$result = $stmt->get_result();

$batches = [];
while ($row = $result->fetch_assoc()) {
    $batches[] = $row['batch'];
}

if (empty($batches)) {
    echo '<style>
    .modal-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: #206018;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .modal-container {
        background: #ffffffff;
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(32,96,24,0.18), 0 1.5px 8px rgba(0,0,0,0.08);
        padding: 36px 32px 28px 32px;
        min-width: 340px;
        max-width: 90vw;
        text-align: center;
        position: relative;
        animation: popIn 0.3s;
    }
    @keyframes popIn {
        from { transform: scale(0.85); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .modal-icon {
        margin-bottom: 12px;
    }
    .modal-icon i {
        display: inline-block;
        background: linear-gradient(90deg, #dc3545 60%, #f7b731 100%);
        border-radius: 50%;
        color: #fff;
        font-size: 44px;
        width: 64px;
        height: 64px;
        line-height: 64px;
        box-shadow: 0 2px 8px rgba(220,53,69,0.18);
    }
    .modal-title {
        color: #206018;     
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 10px;
        letter-spacing: 0.5px;
    }
    .modal-desc {
        color: #444;
        font-size: 16px;
        margin-bottom: 8px;
    }
    </style>';
    echo '<script>
    window.onload = function() {
        var modal = document.createElement("div");
        modal.className = "modal-overlay";
        modal.style.display = "flex";
        modal.innerHTML = `
            <div class=\"modal-container active\">
                <div class=\"modal-icon\"><i>&#9888;</i></div>
                <div class=\"modal-title\">Not yet assigned to a batch</div>
                <div class=\"modal-desc\">You currently do not have any batch assignments.<br>Please contact the administrator for assistance.</div>
            </div>
        `;
        document.body.appendChild(modal);
        setTimeout(function() {
            modal.remove();
            window.location.href = "home_page_adviser.php";
        }, 6000);
    };  
    </script>'; 
    exit;
}

// Fetch students whose student_id starts with any of the batch numbers
$batchPlaceholders = implode(',', array_fill(0, count($batches), '?'));
$query = "SELECT student_id, last_name, first_name FROM students WHERE " .
         implode(' OR ', array_map(function($batch) {
             return "student_id LIKE CONCAT($batch, '%')";
         }, $batches)) . " ORDER BY last_name ASC";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of students</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('pix/school.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .header {
            background-color: #206018;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 20px;
            font-weight: bolder;
            display: flex;
            align-items: center;
        }

        .header img {
            height: 32px;
            width: auto;
            margin-right: 12px;
            vertical-align: middle;
        }

        .back-button {
            background-color: #206018;
            position: relative;
            left: 1270px;
            border: none;
            color: white;
            padding: 8px 13px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            margin: 15px;
            cursor: pointer;
            border-radius: 5px;
        }

        .container {
            width: 90%;
            margin: 0 auto;
            margin-top: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table-header {
            background-color: #206018;
            color: white;
            text-align: center;
            padding: 6px;
            font-size: 20px;
            font-weight: bolder;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            text-align: left;
            padding: 10px;
        }

        th {
            background-color: #333;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        a {
            color: #206018;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>
    <div class="header">
        <img src="img/cav.png" alt="CvSU Logo">
        PRE - ENROLLMENT ASSESSMENT
    </div>

    <button onclick="window.location.href='home_page_adviser.php'" class="back-button">BACK</button>

    <div class="container">
        <div class="table-header">
            LIST OF STUDENT
        </div>
        <table border="1">
            <thead>
                <tr>
                    <th>STUDENT NUMBER</th>
                    <th>STUDENT NAME</th>
                    <th>CHECKLIST</th>
                    <th>PRE-ENROLLMENT</th>
                    <th>PROFILE</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_id']) ?></td>
                        <td><?= htmlspecialchars($row['last_name'] . ", " . $row['first_name']) ?></td>
                        <td><a href="checklist_adviser.php?student_id=<?= htmlspecialchars($row['student_id']) ?>">Approve Grades</a></td>
                        <td><a href="pre_enroll.php?student_id=<?= htmlspecialchars($row['student_id']) ?>">Generate Form</a></td>
                        <td>
                            <a href="acc_mng_adviser.php?student_id=<?= htmlspecialchars($row['student_id']) ?>">See Profile</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center;">No students found for your batch.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>