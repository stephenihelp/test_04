<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Database connection
    $conn = new mysqli("localhost", "root", "", "e_checklist");
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Debug: log received POST data
    file_put_contents('debug_save_checklist_stud.log', "POST: " . print_r($_POST, true) . "\n", FILE_APPEND);

    if (!isset($_POST['student_id']) || !isset($_POST['courses']) || !isset($_POST['final_grades']) || !isset($_POST['professor_instructors'])) {
        throw new Exception('Missing required data');
    }

    $student_id = $_POST['student_id'];
    $courses = $_POST['courses'];
    $final_grades = $_POST['final_grades'];
    $professor_instructors = $_POST['professor_instructors'];

    $successful = 0;
    $errors = [];

    foreach ($courses as $index => $course_code) {
        $finalGrade = isset($final_grades[$index]) ? $final_grades[$index] : null;
        $professorInstructor = isset($professor_instructors[$index]) ? $professor_instructors[$index] : null;

        // First, check if a record exists and get current values
        $check_stmt = $conn->prepare("SELECT final_grade, evaluator_remarks FROM student_checklists WHERE student_id = ? AND course_code = ?");
        if (!$check_stmt) {
            file_put_contents('debug_save_checklist_stud.log', "Check prepare failed: " . $conn->error . "\n", FILE_APPEND);
            $errors[] = "Check prepare failed for $course_code: " . $conn->error;
            continue;
        }
        $check_stmt->bind_param('ss', $student_id, $course_code);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $existing_record = $check_result->fetch_assoc();
        $check_stmt->close();

        if ($finalGrade !== null && $finalGrade !== '') {
            // Only set evaluator_remarks to 'Pending' if the final grade is different from existing
            $evaluator_remarks = ($existing_record && $existing_record['final_grade'] === $finalGrade) 
                ? $existing_record['evaluator_remarks']  // Keep existing remarks if grade hasn't changed
                : 'Pending';  // Set to 'Pending' if grade has changed

            $stmt = $conn->prepare("INSERT INTO student_checklists (student_id, course_code, final_grade, evaluator_remarks, professor_instructor)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    final_grade = VALUES(final_grade),
                    evaluator_remarks = ?,
                    professor_instructor = VALUES(professor_instructor)");
            if (!$stmt) {
                file_put_contents('debug_save_checklist_stud.log', "Prepare failed: " . $conn->error . "\n", FILE_APPEND);
                $errors[] = "Prepare failed for $course_code: " . $conn->error;
                continue;
            }
            $stmt->bind_param('ssssss', $student_id, $course_code, $finalGrade, $evaluator_remarks, $professorInstructor, $evaluator_remarks);
        } else {
            // If only updating professor/instructor, preserve existing values
            $final_grade = $existing_record ? $existing_record['final_grade'] : '';
            $evaluator_remarks = $existing_record ? $existing_record['evaluator_remarks'] : '';

            $stmt = $conn->prepare("INSERT INTO student_checklists (student_id, course_code, final_grade, evaluator_remarks, professor_instructor)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    professor_instructor = VALUES(professor_instructor),
                    final_grade = VALUES(final_grade),
                    evaluator_remarks = VALUES(evaluator_remarks)");
            if (!$stmt) {
                file_put_contents('debug_save_checklist_stud.log', "Prepare failed: " . $conn->error . "\n", FILE_APPEND);
                $errors[] = "Prepare failed for $course_code: " . $conn->error;
                continue;
            }
            $stmt->bind_param('sssss', $student_id, $course_code, $final_grade, $evaluator_remarks, $professorInstructor);
        }

        if (!$stmt->execute()) {
            $errors[] = "Failed to save data for course: $course_code";
            file_put_contents('debug_save_checklist_stud.log', "Execute failed for $course_code: " . $stmt->error . "\n", FILE_APPEND);
            continue;
        }
        $stmt->close();
        $successful++;
    }

    $conn->close();

    file_put_contents('debug_save_checklist_stud.log', "Success count: $successful\nErrors: " . print_r($errors, true) . "\n", FILE_APPEND);

    echo json_encode([
        'status' => 'success',
        'updated' => $successful,
        'errors' => $errors
    ]);

} catch (Exception $e) {
    file_put_contents('debug_save_checklist_stud.log', "Exception: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>