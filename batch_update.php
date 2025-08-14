<!-- batch_update -->
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $host = 'localhost';
    $db = 'e_checklist';
    $user = 'root';
    $pass = '';

    try {
        $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get adviser ID from username
        $stmt = $conn->prepare("SELECT id FROM adviser WHERE username = ?");
        $stmt->execute([$_POST['username']]);
        $adviser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($adviser) {
            if (isset($_POST['unassign']) && $_POST['unassign'] == '1') {
                // Unassign all batches for this adviser
                $del = $conn->prepare("DELETE FROM adviser_batch WHERE id = ?");
                $del->execute([$adviser['id']]);
                header("Location: adviser_management.php?message=" . urlencode("All batches unassigned!"));
                exit();
            } elseif (isset($_POST['batches'])) {
                // Assign multiple batches
                // Ensure the batches array is properly processed
                $batches = isset($_POST['batches']) && is_array($_POST['batches']) ? $_POST['batches'] : [];

                if (!empty($batches)) {
                    // Remove all previous assignments
                    $del = $conn->prepare("DELETE FROM adviser_batch WHERE id = ?");
                    $del->execute([$adviser['id']]);

                    // Assign new batches
                    foreach ($batches as $batch) {
                        try {
                            $ins = $conn->prepare("INSERT INTO adviser_batch (batch, id) VALUES (?, ?)");
                            $ins->execute([$batch, $adviser['id']]);
                        } catch (PDOException $e) {
                            if ($e->getCode() == '23000') { // Handle duplicate entry error
                                continue; // Skip duplicate entries
                            } else {
                                throw $e; // Re-throw other exceptions
                            }
                        }
                    }
                    echo "<div style='position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: #4CAF50; color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); text-align: center;' id='success-modal'>
                        <h2 style='margin: 0; font-size: 18px;'>Batches Successfully Updated!</h2>
                    </div>";
                    echo "<script>
                        setTimeout(function() {
                            document.getElementById('success-modal').style.display = 'none';
                            window.location.href = 'adviser_management.php';
                        }, 500);
                    </script>";
                    exit();
                } else {
                    header("Location: adviser_management.php?error=" . urlencode("No batches selected."));
                    exit();
                }
            }
        } else {
            header("Location: adviser_management.php?error=" . urlencode("Adviser not found."));
            exit();
        }
    } catch (PDOException $e) {
        // Add detailed error logging for debugging
        error_log("Error occurred: " . $e->getMessage());
        header("Location: adviser_management.php?error=" . urlencode("An error occurred while updating the batch."));
        exit();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_batch'])) {
    $host = 'localhost';
    $db = 'e_checklist';
    $user = 'root';
    $pass = '';

    try {
        $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Add new batch
        $newBatch = $_POST['new_batch'];
        $stmt = $conn->prepare("INSERT INTO batches (batch) VALUES (?)");
        $stmt->execute([$newBatch]);

        header("Location: adviser_management.php?message=" . urlencode("New batch added successfully!"));
        exit();
    } catch (PDOException $e) {
        // Add detailed error logging for debugging
        error_log("Error occurred: " . $e->getMessage());
        header("Location: adviser_management.php?error=" . urlencode("An error occurred while adding the new batch."));
        exit();
    }
} else {
    header("Location: adviser_management.php?error=" . urlencode("Invalid request."));
    exit();
}
?>