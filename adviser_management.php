<?php
session_start();
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    echo '<div style="color:red; text-align:center; font-size:1.2em; margin-top:40px;">Access denied. Please log in.</div>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adviser Batch</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Modal styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            z-index: 2000;
            animation: fadeIn 0.3s ease;
        }
        
        .modal-container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.7);
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
            text-align: center;
            z-index: 2001;
            opacity: 0;
            transition: all 0.3s ease;
            min-width: 300px;
        }
        
        .modal-container.active {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }
        
        .modal-icon {
            font-size: 48px;
            color: #4CAF50;
            margin-bottom: 15px;
        }
        
        .modal-title {
            color: #206018;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .modal-close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            color: #aaa;
            cursor: pointer;
            transition: color 0.2s;
        }
        
        .modal-close:hover {
            color: #206018;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        body {
            background: url('pix/school.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
        }
        .header {
            background-color: #206018;
            color: #fff;
            padding: 9px;
            text-align: left;
            font-size: 20px;
            font-weight: bold;
            width: 100%;
            margin-top: 0;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }
        .header img {
            height: 30px;
            width: auto;
            margin-right: 7px;
            vertical-align: middle;
        }
        .content {
            text-align: center;
            margin: 80px 20px 20px;
        }
        .content h2 {
            margin-top: -100px;
            margin-left: 100px;
            background-color: #206018;
            color: #fff;
            display: inline-block;
            padding: 10px 30px;
            border-radius: 5px;
        }
        .table-container {
            margin: 20px auto;
            width: 900px;
            max-width: 900px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th {
            background-color: #206018;
            color: #fff;
            padding: 12px;
            text-align: left;
            font-size: 16px;
        }
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
        }
        /* Set specific widths for columns */
        th:nth-child(1), td:nth-child(1) {
            width: 30%;
        }
        th:nth-child(2), td:nth-child(2) {
            width: 70%;
        }
        .batch-form {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
        }
        .batch-select {
            flex-grow: 1;
        }.back-btn {
            position: absolute;
            top: 100px;
            right: 20px;
            padding: 10px 20px;
            background-color: #48c326ff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            z-index: 1;
        }
        .back-btn:hover {
            background-color: #3e8e41;
        }

        .submit-btn {
            flex-shrink: 0;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 6px 14px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: background 0.2s;
        }
        .submit-btn:hover {
            background-color: #3e8e41;
        }
        .disabled-batch {
            background-color: #e0e0e0;
            color: #888;
            pointer-events: none;
        }
        .unassign-btn {
            margin-left: 10px;
            background-color: #dc3545;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 6px 12px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: background 0.2s;
            position: static;
            display: inline-block;
            vertical-align: middle;
        }
        .unassign-btn:hover {
            background-color: #a71d2a;
        }
        .add-batch-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 6px 14px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: background 0.2s;
        }
        .add-batch-btn:hover {
            background-color: #3e8e41;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="img/cav.png" alt="CvSU Logo">
    PRE - ENROLLMENT ASSESSMENT
    </div>
    <div class="content">
        <h2>Adviser Management</h2>
        <a href="settings.html" class="back-btn">Back</a>
    </div>
    <!-- Modal for Success Message -->
    <div id="successModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="modal-title" id="modalMessage">
                Batches Successfully Updated
            </div>
        </div>
    </div>
    <?php
    if (isset($_GET['message'])) {
        echo "<script>
            window.onload = function() {
                var modal = document.getElementById('successModal');
                var container = modal.querySelector('.modal-container');
                var msg = document.getElementById('modalMessage');
                modal.style.display = 'block';
                msg.textContent = '" . htmlspecialchars($_GET['message']) . "';
                setTimeout(() => container.classList.add('active'), 10);
                setTimeout(function() {
                    container.classList.remove('active');
                    setTimeout(function() { modal.style.display = 'none'; }, 300);
                }, 1500);
            };
        </script>";
    }
    if (isset($_GET['error'])) {
        echo "<div style='text-align: center; color: red; font-weight: bold; margin: 10px 0;'>" . htmlspecialchars($_GET['error']) . "</div>";
    }
    ?>
    <div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Assign Batch</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $host = 'localhost';
            $db = 'e_checklist';
            $user = 'root';
            $pass = '';

            try {
                $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $query = "SELECT a.full_name, a.username, ab.batch FROM adviser a LEFT JOIN adviser_batch ab ON a.id = ab.id";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $advisers = [];
                $batchOwners = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $username = $row['username'];
                    if (!isset($advisers[$username])) {
                        $advisers[$username] = [
                            'full_name' => htmlspecialchars($row['full_name']),
                            'username' => $username,
                            'batches' => []
                        ];
                    }
                    if ($row['batch']) {
                        $advisers[$username]['batches'][] = $row['batch'];
                        // Track which adviser owns which batch
                        if (!isset($batchOwners[$row['batch']])) {
                            $batchOwners[$row['batch']] = $username;
                        }
                    }
                }
            } catch (PDOException $e) {
                echo "<tr><td colspan='3' style='text-align: center; color: red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
            }

            if (empty($advisers)) {
                echo "<tr><td colspan='2' id='unassigned-message' style='text-align:center; padding:40px 0; color:#206018; font-size:20px; font-weight:600; background:rgba(255,255,255,0.85); border-radius:8px;'>All batch unassigned</td></tr>";
                echo "<script>
                    window.onload = function() {
                        var msg = document.getElementById('unassigned-message');
                        if (msg) {
                            setTimeout(function() {
                                msg.style.transition = 'opacity 0.4s';
                                msg.style.opacity = '0';
                                setTimeout(function() { msg.remove(); }, 400);  
                            }, 1500);
                        }
                    };
                </script>";
            } else {
                foreach ($advisers as $adviser) {
                    $fullName = $adviser['full_name'];
                    $username = $adviser['username'];
                    $assignedBatches = $adviser['batches'];
                    ?>
                    <tr>
                        <td><?php echo $fullName; ?></td>
                        <td>
                            <form method="POST" action="batch_update.php" style="display: flex; align-items: center; gap: 10px;">
                                <input type="hidden" name="username" value="<?php echo $username; ?>">
                                <div style="display: flex; gap: 10px;">
                                    <?php
                                    $batchQuery = "SELECT batch FROM batches";
                                    $batchStmt = $conn->prepare($batchQuery);
                                    $batchStmt->execute();
                                    $batches = $batchStmt->fetchAll(PDO::FETCH_COLUMN);

                                    foreach ($batches as $batch) {
                                        $checked = in_array($batch, $assignedBatches) ? 'checked' : '';
                                        $disabled = (isset($batchOwners[$batch]) && $batchOwners[$batch] !== $username) ? 'disabled' : '';
                                        $labelClass = $disabled ? 'disabled-batch' : '';
                                        echo "<label class='$labelClass' style='display: flex; align-items: center; gap: 5px;'>";
                                        echo "<input type='checkbox' name='batches[]' value='" . htmlspecialchars($batch) . "' $checked $disabled style='transform: scale(1.2);'>";
                                        echo htmlspecialchars($batch);
                                        echo "</label>";
                                    }
                                    ?>
                                </div>
                                <button type="submit" class="submit-btn" onclick="return confirmUpdate(event);">
                                    <i class="fas fa-check" style="color: green;" title="Update Batch"></i>
                                </button>
                                <button type="button" id="add-batch-toggle" class="add-batch-btn" style="background-color: #4CAF50; color: white; border: none; border-radius: 4px; padding: 6px 14px; cursor: pointer; font-size: 14px; font-weight: bold; transition: background 1.5s; margin-left: 10px;">
                                    +
                                </button>
                            </form>
                            </form>
                            <form method="POST" action="batch_update.php" style="display:inline-block; vertical-align:middle;">
                                <input type="hidden" name="username" value="<?php echo $username; ?>">
                                <input type="hidden" name="unassign" value="1">
                                <button type="submit" class="unassign-btn">Unassign</button>
                            </form>
                        </td>
                        <td class="action-icons">
                            <!-- Additional actions can be added here if needed -->
                        </td>
                    </tr>
                    <?php
                } // Close foreach loop
            }
            ?>
            </tbody>
        </table>
        <div id="new-batch-container" style="display: none; text-align: center; margin: 20px;">
            <form method="POST" action="batch_update.php" style="display: inline-block;">
                <input type="text" name="new_batch" placeholder="Enter new batch" required style="padding: 6px; border: 1px solid #ccc; border-radius: 4px;">
                <button type="submit" class="add-batch-btn" style="background-color: #4CAF50; color: white; border: none; border-radius: 4px; padding: 6px 14px; cursor: pointer; font-size: 14px; font-weight: bold; transition: background 0.2s;">
                    Add Batch
                </button>
            </form>
        </div>
    </div>
<script>
    document.getElementById('add-batch-toggle').addEventListener('click', function() {
        const container = document.getElementById('new-batch-container');
        if (container.style.display === 'none' || container.style.display === '') {
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
        }
    });
    // Modal close logic removed; modal now disappears automatically

    // Custom confirmation dialog
    function confirmUpdate(event) {
        event.preventDefault();
        const form = event.target.closest('form');
        
        // Create confirmation modal
        const confirmModal = document.createElement('div');
        confirmModal.className = 'modal-overlay';
        confirmModal.innerHTML = `
            <div class="modal-container">
                <span class="modal-close">&times;</span>
                <div class="modal-icon">
                    <i class="fas fa-question-circle" style="color: #206018;"></i>
                </div>
                <div class="modal-title">
                    Confirm batch update?
                </div>
                <div style="margin-top: 20px;">
                    <button class="submit-btn" style="margin-right: 10px;">Yes</button>
                    <button class="unassign-btn" style="background-color: #6c757d;">No</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(confirmModal);
        const container = confirmModal.querySelector('.modal-container');
        confirmModal.style.display = 'block';
        setTimeout(() => container.classList.add('active'), 10);

        // Handle close button
        const closeBtn = confirmModal.querySelector('.modal-close');
        closeBtn.onclick = () => {
            container.classList.remove('active');
            setTimeout(() => {
                confirmModal.remove();
            }, 1500);
        };

        // Handle click outside
        confirmModal.onclick = (e) => {
            if (e.target === confirmModal) {
                container.classList.remove('active');
                setTimeout(() => {
                    confirmModal.remove();
                }, 1500);
            }
        };

        // Handle buttons
        const buttons = confirmModal.querySelectorAll('button');
        buttons[0].onclick = () => {
            form.submit();
        };
        buttons[1].onclick = () => {
            container.classList.remove('active');
            setTimeout(() => {
                confirmModal.remove();
            }, 300);
        };

        return false;
    }
</script>
</body>
</html>