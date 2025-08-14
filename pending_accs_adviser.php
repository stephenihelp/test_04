<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Accounts</title>
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
            position: relative;
            left: 50px;
            text-align: center;
            margin: 65px 20px 20px;
        }
        .content h2 {
            background-color: #206018;
            color: #fff;
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
        }
        .table-container {
            margin: -20px auto;
            width: 90%;
            max-width: 1000px;
            background-color: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(32, 96, 24, 0.12);
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 17px;
            background: #fff;
        }
        th, td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background: linear-gradient(90deg, #206018 80%,rgba(25, 126, 16, 1) 100%);
            color: #fff;
            font-size: 18px;
            letter-spacing: 1px;
            border-top: none;
            border-bottom: 1px solid #e0e0e0; /* Subtle gray border for cleaner look */
        }
        tbody tr:nth-child(even) td {
            background-color: #f4f8f4;
        }
        tbody tr:hover td {
            background-color: #eaf6e9;
            transition: background 0.2s;
        }
        td {
            background-color: #fff;
            border-radius: 0 0 8px 8px;
            box-shadow: none;
        }
        .student-number {
            color: #206018;
            font-weight: bold;
            font-size: 16px;
            letter-spacing: 1px;
        }
        .action-icons {
            text-align: left;
            display: flex;
            gap: 12px;
        }
        .action-icons i {
            margin: 0 2px;
            cursor: pointer;
            font-size: 18px;
            transition: transform 0.15s, color 0.15s;
        }
        .action-icons i:hover {
            transform: scale(1.2);
            color: #206018;
        }
        .back-btn {
            position: relative;
            top: -30px;
            left: -830px;
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
        .modal {
            display: none !important;
            position: fixed !important;
            z-index: 1000 !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            height: 100% !important;
            background-color: rgba(0, 0, 0, 0.5) !important;
            animation: fadeIn 0.3s ease-in-out !important;
        }
        .modal-content {
            margin: 150px auto !important;
            padding: 12px !important;
            border-radius: 15px !important;
            width: 90% !important;
            max-width: 400px !important;
            text-align: center !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
            animation: slideIn 0.4s ease-out !important;
            position: relative !important;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .approve-modal {
            background: linear-gradient(135deg,rgb(229, 240, 30), #206018) !important;
        }
        .reject-modal {
            background: linear-gradient(135deg, #dc3545,rgb(207, 12, 32)) !important;
        }
        .modal-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
            font-weight: bold;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            border: 3px solid rgba(255, 255, 255, 0.3);
        }
        .modal-title {
            color: white !important;
            font-size: 24px !important;
            font-weight: bold !important;
            margin-bottom: 10px !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important;
        }
        .modal-message {
            color: rgba(255, 255, 255, 0.9) !important;
            font-size: 16px !important;
            margin: 12px !important;
            line-height: 1.5 !important;
            text-align: center;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-button {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 12px 30px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .modal-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="img/cav.png" alt="CvSU Logo">
        <span> PRE - ENROLLMENT ASSESSMENT</span>
    </div>
    <div class="content">
        <h2>STUDENT PENDING ACCOUNTS</h2>
        <a href="home_page_adviser.php" class="back-btn">Back</a>
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
                // if (isset($_GET['message'])) {
                //     echo "<p style='text-align: center; color: green; font-weight: bold;'>{$_GET['message']}</p>";
                // }
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

                // Get adviser's batches
                if (!isset($_SESSION['id'])) {
                    echo "<tr><td colspan='3' style='text-align: center;'>Access denied. Please log in.</td></tr>";
                    exit;
                }
                $adviser_id = $_SESSION['id'];
                $batch_query = $conn->prepare("SELECT batch FROM adviser_batch WHERE id = ?");
                $batch_query->bind_param("i", $adviser_id);
                $batch_query->execute();
                $batch_result = $batch_query->get_result();
                if ($batch_result->num_rows === 0) {
                    echo "<tr><td colspan='3' style='text-align: center;'>No batch assigned to this adviser.</td></tr>";
                    exit;
                }
                $batches = [];
                while ($row = $batch_result->fetch_assoc()) {
                    $batches[] = $row['batch'];
                }
                $batch_query->close();

                if (count($batches) === 0) {
                    echo "<tr><td colspan='3' style='text-align: center;'>No batch assigned to this adviser.</td></tr>";
                    exit;
                }

                // Build query for all batches
                $placeholders = implode(',', array_fill(0, count($batches), '?'));
                $query = "SELECT student_id, last_name, first_name, middle_name FROM students WHERE status = 'pending' AND (";
                foreach ($batches as $i => $batch) {
                    $query .= ($i > 0 ? " OR " : "") . "student_id LIKE CONCAT(?, '%')";
                }
                $query .= ")";
                $stmt = $conn->prepare($query);
                // Bind all batch values
                $stmt->bind_param(str_repeat('s', count($batches)), ...$batches);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $displayed = [];
                    while ($row = $result->fetch_assoc()) {
                        // Avoid duplicate display if student_id appears in multiple batches
                        if (in_array($row['student_id'], $displayed)) continue;
                        $displayed[] = $row['student_id'];
                        $fullName = htmlspecialchars($row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name']);
                        echo "
                            <tr>
                                <td class='student-number'>" . htmlspecialchars($row['student_id']) . "</td>
                                <td>{$fullName}</td>
                                <td class='action-icons'>
                                    <a href='javascript:void(0);' onclick='showApproveModal(" . json_encode($row['student_id']) . ")'>
                                        <i class='fas fa-check' style='color: green;' title='Approve'></i>
                                    </a>
                                    <a href='javascript:void(0);' onclick='showRejectModal(" . json_encode($row['student_id']) . ")'>
                                        <i class='fas fa-times' style='color: red;' title='Reject'></i>
                                    </a>
                                </td>
                            </tr>
                        ";
                    }
                } else {
                    echo "
                        <tr>
                            <td colspan='3' style='text-align: center;'>No pending accounts found for your batches.</td>
                        </tr>
                    ";
                }

                // Close connection
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
    <!-- Approve Modal -->
    <div id="approveModal" class="modal">
      <div class="modal-content approve-modal">
        <div class="modal-icon">
          <i class="fas fa-check" style="color: white;"></i>
        </div>
        <div class="modal-title">Approve Account</div>
        <div class="modal-message">Are you sure you want to approve this account?</div>
        <button class="modal-button" id="confirmApprove">Yes, Approve</button>
        <button class="modal-button" onclick="closeModal('approveModal')">Cancel</button>
      </div>
    </div>
    <!-- Reject Modal -->
    <div id="rejectModal" class="modal">
      <div class="modal-content reject-modal">
        <div class="modal-icon">
          <i class="fas fa-times" style="color: white;"></i>
        </div>
        <div class="modal-title">Reject Account</div>
        <div class="modal-message">Are you sure you want to reject this account?</div>
        <button class="modal-button" id="confirmReject">Yes, Reject</button>
        <button class="modal-button" onclick="closeModal('rejectModal')">Cancel</button>
      </div>
    </div>
    <!-- Success Modal -->
    <div id="successModal" class="modal">
      <div class="modal-content approve-modal">
        <div class="modal-icon">
          <i class="fas fa-check" style="color: white;"></i>
        </div>
        <div class="modal-title">Account Approved Successfully</div>
        <div class="modal-message">The account has been approved.</div>
        <button class="modal-button" onclick="closeModal('successModal')">OK</button>
      </div>
    </div>
    <script>
    function showApproveModal(studentId) {
      document.getElementById('approveModal').style.setProperty('display', 'block', 'important');
      document.getElementById('confirmApprove').onclick = function() {
        window.location.href = 'approve_account_adviser.php?student_id=' + encodeURIComponent(studentId);
      };
    }
    function showRejectModal(studentId) {
      document.getElementById('rejectModal').style.setProperty('display', 'block', 'important');
      document.getElementById('confirmReject').onclick = function() {
        window.location.href = 'reject_adviser.php?student_id=' + encodeURIComponent(studentId);
      };
    }
    function closeModal(modalId) {
      document.getElementById(modalId).style.setProperty('display', 'none', 'important');
    }
    function showSuccessModal() {
      document.getElementById('successModal').style.setProperty('display', 'block', 'important');
      setTimeout(function() {
        closeModal('successModal');
      }, 1500);
    }
    // Show modal if message is present
    window.addEventListener('DOMContentLoaded', function() {
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('message') === 'Account approved successfully') {
        showSuccessModal();
        // Remove message from URL after showing
        setTimeout(function() {
          window.location.href = window.location.pathname;
        }, 1600);
      }
    });
    </script>
</body>
</html>