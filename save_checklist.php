<?php
session_start();
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "e_checklist";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Check if this is a bulk approval request (sent as JSON or form-data)
    $isBulkApprove = false;
    $student_id = '';
    $courses = [];
    $grades = [];
    $debug = [];
    if (isset($_POST['bulk_approve']) && $_POST['bulk_approve']) {
        // Bulk approve via form-data
        $isBulkApprove = true;
        $student_id = $_POST['student_id'];
        $courses = isset($_POST['courses']) ? json_decode($_POST['courses'], true) : [];
        $grades = isset($_POST['grades']) ? json_decode($_POST['grades'], true) : [];
        $debug['source'] = 'form-data';
    } else {
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? $_SERVER["CONTENT_TYPE"] : '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            if (isset($input['bulk_approve']) && $input['bulk_approve']) {
                $isBulkApprove = true;
                $student_id = $input['student_id'];
                $courses = $input['courses'];
                $grades = $input['grades'];
                $debug['source'] = 'json';
            }
        }
    }
    $debug['student_id'] = $student_id;
    $debug['courses'] = $courses;
    $debug['grades'] = $grades;

    if ($isBulkApprove) {
        // Defensive: ensure grades is associative array
        if (is_array($grades) && array_values($grades) === $grades) {
            // If grades is a simple array, convert to associative using course codes
            $grades_assoc = [];
            foreach ($courses as $idx => $course_code) {
                $grades_assoc[$course_code] = isset($grades[$idx]) ? $grades[$idx] : '';
            }
            $grades = $grades_assoc;
        }
        $stmt = $conn->prepare("
            INSERT INTO student_checklists (student_id, course_code, final_grade, evaluator_remarks)
            VALUES (?, ?, ?, 'Approved')
            ON DUPLICATE KEY UPDATE 
            final_grade = VALUES(final_grade), 
            evaluator_remarks = 'Approved'
        ");
        if (!$stmt) {
            echo json_encode([
                'status' => 'error',
                'message' => "Prepare failed: " . $conn->error,
                'debug' => $debug
            ]);
            exit;
        }
        $successful = 0;
        foreach ($courses as $course_code) {
            $grade = isset($grades[$course_code]) ? $grades[$course_code] : '';
            $stmt->bind_param('sss', $student_id, $course_code, $grade);
            if (!$stmt->execute()) {
                $debug['error'][] = $stmt->error;
                continue;
            }
            $successful++;
        }
        $stmt->close();
        $conn->close();
        echo json_encode([
            'status' => 'success',
            'message' => "Bulk approved $successful records",
            'debug' => $debug
        ]);
        exit;
    }

    // Standard save (form-data)
    $student_id = $_POST['student_id'];
    $courses = json_decode($_POST['courses'], true);
    $final_grades = json_decode($_POST['final_grades'], true);
    $evaluator_remarks = json_decode($_POST['evaluator_remarks'], true);

    if (!$courses || !$final_grades || !$evaluator_remarks) {
        throw new Exception('Invalid data format');
    }

    $stmt = $conn->prepare("
        INSERT INTO student_checklists (student_id, course_code, final_grade, evaluator_remarks)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        final_grade = VALUES(final_grade), 
        evaluator_remarks = VALUES(evaluator_remarks)
    ");

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $successful = 0;
    for ($i = 0; $i < count($courses); $i++) {
        $stmt->bind_param('ssss', 
            $student_id,
            $courses[$i],
            $final_grades[$i],
            $evaluator_remarks[$i]
        );
        if (!$stmt->execute()) {
            continue;
        }
        $successful++;
    }

    $stmt->close();
    $conn->close();

    echo json_encode([
        'status' => 'success',
        'message' => "Successfully saved $successful records"
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>