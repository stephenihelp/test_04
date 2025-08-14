<?php
session_start();

// Check if the student is logged in


// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "e_checklist";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Get student_id from URL parameter instead of session
if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];
    
    // Fetch student details for this specific student
    $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $student_result = $stmt->get_result();
    
    if ($student_result->num_rows > 0) {
        $student_data = $student_result->fetch_assoc();
        // Assign values from database instead of session
        $last_name = htmlspecialchars($student_data['last_name']);
        $first_name = htmlspecialchars($student_data['first_name']);
        $middle_name = htmlspecialchars($student_data['middle_name']);
        $contact_no = htmlspecialchars($student_data['contact_no']);
        $address = htmlspecialchars($student_data['address']);
        $admission_date = htmlspecialchars($student_data['admission_date']); 
    } else {
        die("Student not found");
    }
} else {
    die("No student ID provided");
}


// Updated SQL query
$sql = "
    SELECT 
        c.course_code, c.course_title, c.credit_unit_lec, c.credit_unit_lab, 
        c.contact_hrs_lec, c.contact_hrs_lab, c.pre_requisite, c.year, c.semester,
        sc.final_grade, sc.evaluator_remarks, sc.professor_instructor
    FROM checklist_bscs c
    LEFT JOIN student_checklists sc
    ON c.course_code = sc.course_code AND sc.student_id = ?
    ORDER BY c.year, c.semester, c.course_code
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

// Optional: You can also fetch additional student details here if needed




?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Approve Checklist</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
  <style>
    /* Notification styles */
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }

    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 24px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 12px;
        background: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        animation: slideInRight 0.4s ease-out, fadeOut 0.4s ease-out 2s forwards;
        font-family: Arial, sans-serif;
    }

    .notification.success {
        border-left: 4px solid #4CAF50;
    }

    .notification.error {
        border-left: 4px solid #f44336;
    }

    .notification-icon {
        width: 24px;
        height: 24px;
        flex-shrink: 0;
    }

    .notification-content {
        display: flex;
        flex-direction: column;
    }

    .notification-title {
        font-weight: 600;
        font-size: 16px;
        color: #333;
        margin-bottom: 4px;
    }

    .notification-message {
        font-size: 14px;
        color: #666;
    }

    /* Existing body styles */
    body {
        font-family: Arial, sans-serif;
        background-color: #f9f9f9;
        background: url('pix/school.jpg') no-repeat center center fixed;
        background-size: cover;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }
    .container {
        width: 97%;
        margin-top: 10px;
        margin-bottom: 40px;
        max-width: 794px;
        padding: 20px;
        background-color: #fff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 5px;
    }
    .header {
        text-align: center;
        margin-bottom: 20px;
    }
    .CvSUlogo .logo img {
      width: 85px;
      height: 85px;
      position: relative;
      left: 100px;
      top: -215px;
    }
    .header h1 {
        margin-top: 5px;
        font-size: 9px;
    }
    .header h2 {
        margin-bottom: 1px 0;
        font-size: 9px;
    }
    .header h3 {
        margin-top: 30px;
        font-size: 9px;
    }
    .info {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        margin-top: -220px;
        margin-bottom: 10px;
    }
    .info-left {
        width: 45%;
        margin-left: 50px;
    }
    .info-right {
        width: 30%;
        text-wrap: nowrap;
        position: relative;
        left: -100px;
    }
    table {
        width: 90%;
        height: 90%;
        position: relative;
        top: -10px;
        left: 38px;
        border-collapse: collapse;
        margin-bottom: 20px;
        font-size: 11px;
    }
    th, td {
        border: 1px solid #000;
        padding: 4.50px;
        text-align: center;
        font-size: 8px;
    }
    th {
        background-color: #f2f2f2;
    }
    .semester-title {
        text-align: center;
        font-weight: bold;
        background-color: #f2f2f2;
        
    }
    .total {
        font-weight: bold;
    }
    @media (max-width: 768px) {
        .header h1, .header h2 {
            font-size: 10px;
        }
        .info {
            flex-direction: column;
        }
        .info-left, .info-right {
            width: 100%;
        }
        th, td {
            padding: 4px;
        }
    }

    select[name^="final_grade"],
    select[name^="evaluator_remarks"] {
        transition: background-color 0.3s ease;
    }

    select[name^="final_grade"]:disabled,
    select[name^="evaluator_remarks"]:disabled {
        background-color: #f5f5f5;
        cursor: not-allowed;
}
  </style>
</head>
<body>
    
    <div class="container">
        <div class="header">
            <h1>Republic of the Philippines</h1>
            <h2>CAVITE STATE UNIVERSITY - CARMONA</h2>
            <h2>Carmona Cavite</h2>
            <h3>BACHELOR OF SCIENCE IN COMPUTER SCIENCE</h3>
            <button id="downloadPDF" style="margin-top: 70px; position: relative; right: -650px; top: -215px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">Download PDF</button>
            <button style="margin-top: 100px; position: relative; right: -515px; top: -174px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;  "onclick="window.location.href='checklist_eval_adviser.php'">Back</button>
            <button id="saveButton" style="margin-top: 80px; position: relative; right: -440px; top: -130px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 4px;">Save</button>
            <button id="showApproveMultiple" style="margin-top: 80px; position: relative; right: -365px; top: -90px; padding: 10px 20px; background-color: #2196F3; color: white; border: none; border-radius: 4px; cursor: pointer;">Approve Multiple</button>
        </div>
        <div class="CvSUlogo">
            <div class="logo">
              <img src="img/cav.png" alt="CvSU Logo" height="150"/>
            </div>
        </div>
        <div class="info">
            <div class="info-left">
                <p><strong>Name: <?= htmlspecialchars("$last_name, $first_name $middle_name") ?></p>
                <p><strong>Student #: <?= htmlspecialchars("$student_id") ?></p>
                <p><strong>Address: <?= htmlspecialchars("$address") ?></p>
            </div>
            <div class="info-right">
                <p><strong>Admission Date: <?= htmlspecialchars("$admission_date") ?></p>
                <p><strong>Contact #: <?= htmlspecialchars("$contact_no") ?></p>
                <p><strong>Adviser: <input type="text"  style="border: none; font-size: 8px; border-bottom: 1px solid #000; width: 140px;" readonly></strong></p>
            </div>
        </div>
            <table>
                <thead>
                    <tr>
                        <th rowspan="2">COURSE CODE</th>
                        <th rowspan="2">COURSE TITLE</th>
                        <th colspan="2">CREDIT UNIT</th>
                        <th colspan="2">CONTACT HRS</th>
                        <th rowspan="2">PRE<br>REQUISITE</th>
                        <th rowspan="2">SEM/YR<br>TAKEN</th>
                        <th rowspan="2">PROFESSOR/<br>INSTRUCTOR</th>
                        <th rowspan="2">FINAL<br>GRADE</th>
                        <th rowspan="2">EVALUATOR<br>REMARKS</th>
                        <th rowspan="2" id="approveColHeader" style="display:none">Approve</th>
                    </tr>
                    <tr>
                        <th>Lec</th>
                        <th>Lab</th>
                        <th>Lec</th>
                        <th>Lab</th>
                    </tr>
                </thead>
                <tbody>
                <?php
    // Initialize variables to track semester and year
    $currentSemester = "";
    $currentYear = "";

    // Check if there are any courses
    if ($result->num_rows > 0) {
        // Loop through results
        while ($row = $result->fetch_assoc()) {
            // Check if the current semester or year has changed
            if ($currentYear != $row['year'] || $currentSemester != $row['semester']) {
                $semesterKey = $row['year'] . '-' . $row['semester'];
                echo "<tr class='semester-title'>
                        <td colspan='10' style='text-align:center;'>
                            <span>{$row['year']} - {$row['semester']}</span>
                        </td>
                        <td style='text-align:center; vertical-align:middle; padding:0;'>
                            <div class='semester-selectall-row' style='display:none;'>
                                <input type='checkbox' class='semester-approve-checkbox' data-semester='{$semesterKey}' style='width:16px; height:16px; vertical-align:middle;' title='Select all'>
                                <label style='font-size:10px; margin-left:2px;'>Select all</label>
                            </div>
                        </td>
                    </tr>";
                $currentYear = $row['year'];
                $currentSemester = $row['semester'];
            }

            // Output course data
            echo "<tr data-semester='{$currentYear}-{$currentSemester}'>
                    <td>{$row['course_code']}</td>
                    <td>{$row['course_title']}</td>
                    <td>{$row['credit_unit_lec']}</td>
                    <td>{$row['credit_unit_lab']}</td>
                    <td>{$row['contact_hrs_lec']}</td>
                    <td>{$row['contact_hrs_lab']}</td>
                    <td>{$row['pre_requisite']}</td>
                    <td>{$row['semester']} {$row['year']}</td>
                    <td><input type='text' value='" . (!empty($row['professor_instructor']) ? htmlspecialchars($row['professor_instructor']) : "") . "' style='border: none; font-size: 8px; border-bottom: 1px solid #ffffff; width: 100px;' readonly></td>
                    <td>
                       <select name='final_grade[{$row['course_code']}]' style='border: none; font-size: 8px; width: 100px;'>";
                        $grades = ['', '1.00', '1.25', '1.50', '1.75', '2.00', '2.25', '2.50', '2.75', '3.00', '4.00','5.00'];
                        foreach ($grades as $grade) {
                            $selected = ($grade === $row['final_grade']) ? 'selected' : '';
                            echo "<option value='{$grade}' {$selected}>{$grade}</option>";
                        }
                echo "</select>
                    </td>
                    <td>
                        <select name='evaluator_remarks[{$row['course_code']}]' style='border: none; font-size: 8px; width: 100px;'>";
                        $remarks = ['', 'Approved', 'Pending'];
                        foreach ($remarks as $remark) {
                            $selected = ($remark === $row['evaluator_remarks']) ? 'selected' : '';
                            echo "<option value='{$remark}' {$selected}>{$remark}</option>";
                        }
                        echo "</select>
                    </td>
                   <td style='display:none' class='approve-col'><input type='checkbox' class='approve-checkbox' value='{$row['course_code']}'></td>
                </tr>";
        }
    } else {
        echo "<tr><td colspan='11'>No courses available.</td></tr>";
    }
    ?>
            </tbody>
        </table>
        <button id="bulkApproveButton" style="display:none; margin-left: 38px; margin-bottom: 20px; padding: 8px 16px; background-color: #2196F3; color: white; border: none; border-radius: 4px; cursor: pointer;">Approve Selected Grades</button>

<script>
// Show approve checkboxes and bulk approve button when Approve Multiple is clicked
let approveMultipleActive = false;
document.getElementById('showApproveMultiple').addEventListener('click', function() {
    approveMultipleActive = !approveMultipleActive;
    document.querySelectorAll('.approve-col').forEach(function(td) {
        td.style.display = approveMultipleActive ? '' : 'none';
        if (!approveMultipleActive) {
            var cb = td.querySelector('.approve-checkbox');
            if (cb) cb.checked = false;
        }
    });
    document.getElementById('bulkApproveButton').style.display = approveMultipleActive ? '' : 'none';
    // Show/hide semester select all rows
    document.querySelectorAll('.semester-selectall-row').forEach(function(row) {
        row.style.display = approveMultipleActive ? '' : 'none';
        if (!approveMultipleActive) {
            var cb = row.querySelector('.semester-approve-checkbox');
            if (cb) cb.checked = false;
        }
    });
    // Toggle Approve column header
    var approveHeader = document.getElementById('approveColHeader');
    if (approveHeader) {
        approveHeader.style.display = approveMultipleActive ? '' : 'none';

// Semester approve checkbox logic
function setSemesterApproved(semesterKey, checked) {
    // Check/uncheck all approve-checkboxes for this semester
    document.querySelectorAll(`tr[data-semester='${semesterKey}'] .approve-checkbox`).forEach(function(cb) {
        cb.checked = checked;
        // Set evaluator remarks to Approved if checked, else leave as is
        let courseCode = cb.value;
        let remarksSelect = document.querySelector(`[name='evaluator_remarks[${courseCode}]']`);
        if (remarksSelect) {
            if (checked) {
                remarksSelect.value = 'Approved';
            }
        }
    });
}
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('semester-approve-checkbox')) {
        setSemesterApproved(e.target.dataset.semester, e.target.checked);
    }
});
    }
    if (approveMultipleActive) {
        document.querySelector('table').scrollIntoView({behavior: 'smooth'});
    }
});

// Hide approve checkboxes and bulk approve button after approval
function hideApproveCheckboxes() {
    document.querySelectorAll('.approve-checkbox').forEach(function(cb) {
        cb.checked = false;
        cb.parentElement.style.display = 'none';
    });
    document.getElementById('bulkApproveButton').style.display = 'none';
}
</script>

    <script>
    document.getElementById('saveButton').addEventListener('click', function() {
    let formData = new FormData();
    let courseCodes = document.querySelectorAll('[name^="final_grade"]');
    let evaluatorRemarks = document.querySelectorAll('[name^="evaluator_remarks"]');
    let checkboxes = document.querySelectorAll('.approve-checkbox');

    // Create arrays to store the data
    let courses = [];
    let final_grades = [];
    let remarks = [];

    courseCodes.forEach(function(course) {
        let courseCode = course.name.match(/\[(.*?)\]/)[1];
        let finalGrade = course.value;
        let evaluatorRemark = document.querySelector(`[name="evaluator_remarks[${courseCode}]"]`).value;
        // If checkbox for this course is checked, set remark to Approved
        let checkbox = document.querySelector(`.approve-checkbox[value="${courseCode}"]`);
        if (checkbox && checkbox.checked) {
            evaluatorRemark = 'Approved';
        }
        courses.push(courseCode);
        final_grades.push(finalGrade);
        remarks.push(evaluatorRemark);
    });

    // Append arrays to FormData
    formData.append('student_id', '<?php echo $_GET['student_id']; ?>');
    formData.append('courses', JSON.stringify(courses));
    formData.append('final_grades', JSON.stringify(final_grades));
    formData.append('evaluator_remarks', JSON.stringify(remarks));

    fetch('save_checklist.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showNotification('success', 'Success', 'Your changes have been saved successfully');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification('error', 'Error', 'Failed to save data: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error saving data:', error);
        showNotification('error', 'Error', 'An unexpected error occurred while saving');
    });
});

// Function to fetch and update checklist data
function fetchAndUpdateChecklist() {
// And this line in your fetchAndUpdateChecklist function
    const studentId = '<?php echo $_GET['student_id']; ?>';
    
    fetch(`get_checklist_data.php?student_id=${studentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                updateChecklistFields(data.courses);
            }
        })
        .catch(error => console.error('Error fetching checklist data:', error));
}

// Function to update checklist fields
function updateChecklistFields(courses) {
    courses.forEach(course => {
        // Update final grade
        const gradeSelect = document.querySelector(`select[name="final_grade[${course.course_code}]"]`);
        if (gradeSelect) {
            gradeSelect.value = course.final_grade || '';
        }
        
        // Update evaluator remarks
        const remarksElement = document.querySelector(`[name="evaluator_remarks[${course.course_code}]"]`);
        if (remarksElement) {
            if (remarksElement.tagName === 'SELECT') {
                remarksElement.value = course.evaluator_remarks || '';
            } else {
                remarksElement.textContent = course.evaluator_remarks || '';
            }
        }
    });
}
// Poll for updates every 90 seconds
function showNotification(type, title, message) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    const icon = type === 'success' 
        ? `<svg class="notification-icon" viewBox="0 0 24 24" fill="none" stroke="#4CAF50" stroke-width="2">
             <path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/>
           </svg>`
        : `<svg class="notification-icon" viewBox="0 0 24 24" fill="none" stroke="#f44336" stroke-width="2">
             <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
           </svg>`;
    
    notification.innerHTML = `
        ${icon}
        <div class="notification-content">
            <div class="notification-title">${title}</div>
            <div class="notification-message">${message}</div>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.addEventListener('animationend', () => {
            notification.remove();
        });
    }, 2400); // Total duration: 2.4s (2s delay + 0.4s fade out)
}

const updateInterval = setInterval(fetchAndUpdateChecklist, 90000);

// Clear interval when page is hidden
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        clearInterval(updateInterval);
    } else {
        fetchAndUpdateChecklist();
        setInterval(fetchAndUpdateChecklist, 90000);
    }
});

// Bulk approve selected grades
document.getElementById('bulkApproveButton').addEventListener('click', function() {
    const checkedBoxes = document.querySelectorAll('.approve-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Please select at least one course to approve.');
        return;
    }
    const selectedCourses = Array.from(checkedBoxes).map(cb => cb.value);
    const studentId = '<?php echo $_GET['student_id']; ?>';
    // Optionally, you can also send the current grade value for each course
    let gradeData = {};
    selectedCourses.forEach(courseCode => {
        const grade = document.querySelector(`select[name="final_grade[${courseCode}]"]`).value;
        gradeData[courseCode] = grade;
    });
    fetch('save_checklist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            bulk_approve: true,
            student_id: studentId,
            courses: selectedCourses,
            grades: gradeData
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Selected grades approved successfully!');
            hideApproveCheckboxes(); location.reload();
        } else {
            alert('Failed to approve grades: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error approving grades:', error);
        alert('An unexpected error occurred while approving grades');
    });
});
</script>
<script>
        document.getElementById('downloadPDF').addEventListener('click', function() {
            const element = document.querySelector('.container');
            const opt = {
                margin: [0,-6, 3, 5],
                filename: 'checklist.pdf',
                image: { type: 'jpeg', quality: 1 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait', compress: false, precision: 16}
            };
            html2pdf().set(opt).from(element).save();
        });
    </script>
</body>
</html>
