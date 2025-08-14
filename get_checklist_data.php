<?php
// Create a new file: get_checklist_data.php

session_start();
header('Content-Type: application/json');

try {
    if (!isset($_GET['student_id'])) {
        throw new Exception('Student ID is required');
    }

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "e_checklist";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $student_id = $_GET['student_id'];
    
    $sql = "
        SELECT 
            c.course_code,
            sc.final_grade,
            sc.evaluator_remarks,
            sc.professor_instructor
        FROM checklist_bscs c
        LEFT JOIN student_checklists sc ON c.course_code = sc.course_code 
        AND sc.student_id = ?
        ORDER BY c.year, c.semester, c.course_code
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'courses' => $courses
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>