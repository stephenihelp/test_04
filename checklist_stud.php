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


// Retrieve user details from the session
$last_name = htmlspecialchars($_SESSION['last_name']);
$first_name = htmlspecialchars($_SESSION['first_name']);
$middle_name = htmlspecialchars($_SESSION['middle_name']);
$password = htmlspecialchars($_SESSION['password']);
$picture = htmlspecialchars($_SESSION['picture']);
$student_id = htmlspecialchars($_SESSION['student_id']);
$contact_no = htmlspecialchars($_SESSION['contact_no']);
$address = htmlspecialchars($_SESSION['address']);
$admission_date = htmlspecialchars($_SESSION['admission_date']);

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

// Optional: You can also fetch additional student details here if needed



// Retrieve user details from the session
$last_name = htmlspecialchars($_SESSION['last_name']);
$first_name = htmlspecialchars($_SESSION['first_name']);
$middle_name = htmlspecialchars($_SESSION['middle_name']);
$password = htmlspecialchars($_SESSION['password']);
$picture = htmlspecialchars($_SESSION['picture']);
$student_id = htmlspecialchars($_SESSION['student_id']);
$contact_no = htmlspecialchars($_SESSION['contact_no']);
$address = htmlspecialchars($_SESSION['address']);
$admission_date = htmlspecialchars($_SESSION['admission_date']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checklist</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
  <style>
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
      left: 120px;
      top: -253px;
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
        padding: 1.5px;
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

    select[name^="final_grade"]:disabled {
        background-color: #fff;
        color: #206018;
        font-weight: bold;
        opacity: 1;
        border: 1px solid #206018;
        cursor: not-allowed;
        box-shadow: none;
    }
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
            <button id="downloadPDF" style="margin-top: 70px; position: relative; right: -610px; top: -215px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">Download PDF</button>
            <button style="margin-top: 100px; position: relative; right: -475px; top: -173px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;  "onclick="window.location.href='home_page_student.php'">Back</button>
            <button id="saveButton" style="margin-top: 80px; position: relative; right: -400px; top: -130px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none;border-radius: 4px;">Save</button>
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
                echo "<tr class='semester-title'>
                        <td colspan='11'>{$row['year']} - {$row['semester']}</td>
                    </tr>";
                $currentYear = $row['year'];
                $currentSemester = $row['semester'];
            }

            // Output course data
            // In checklist_stud.php, replace the current table row output code in the while loop with this:

        echo "<tr>
        <td>{$row['course_code']}</td>
        <td>{$row['course_title']}</td>
        <td>{$row['credit_unit_lec']}</td>
        <td>{$row['credit_unit_lab']}</td>
        <td>{$row['contact_hrs_lec']}</td>
        <td>{$row['contact_hrs_lab']}</td>
        <td>{$row['pre_requisite']}</td>
        <td>{$row['semester']} {$row['year']}</td>
        <td><input type='text' name='professor_instructor[{$row['course_code']}]' value='" . (!empty($row['professor_instructor']) ? htmlspecialchars($row['professor_instructor']) : "") . "' style='border: none; font-size: 8px; border-bottom: 1px solid #000000; width: 100px;'></td>
        <td>
            ";
            if ($row['evaluator_remarks'] === 'Approved') {
                // Show grade as plain text if approved
                echo "<span style='font-size: 10px; color: #206018; font-weight: bold;'>{$row['final_grade']}</span>";
            } else {
                echo "<select name='final_grade[{$row['course_code']}]' style='border: none; font-size: 8px; width: 100px;'>";
                $grades = ['', '1.00', '1.25', '1.50', '1.75', '2.00', '2.25', '2.50', '2.75', '3.00', '4.00','5.00'];
                foreach ($grades as $grade) {
                    $selected = ($grade === $row['final_grade']) ? 'selected' : '';
                    echo "<option value='{$grade}' {$selected}>{$grade}</option>";
                }
                echo "</select>";
            }
            echo "
        </td>
        <td id='remarks_{$row['course_code']}'>" . ($row['evaluator_remarks'] ? htmlspecialchars($row['evaluator_remarks']) :'') . "</td>
        </tr>";
        }
    } else {
        echo "<tr><td colspan='11'>No courses available.</td></tr>";
    }
    ?>
</tbody>
    </table>

    </div>

<script>
    function refreshChecklist() {
        location.reload();
    }

    document.getElementById('saveButton').addEventListener('click', function () {
        let formData = new FormData();
        let professorInputs = document.querySelectorAll('[name^="professor_instructor"]');
        let finalGradeInputs = document.querySelectorAll('[name^="final_grade"]');

        // Collect all course codes from professor_instructor inputs
        professorInputs.forEach(function (profInput) {
            let courseCode = profInput.name.split('[')[1].split(']')[0];
            let professorValue = profInput.value;
            // Find corresponding grade input
            let gradeInput = document.querySelector(`[name='final_grade[${courseCode}]']`);
            let finalGrade = gradeInput ? gradeInput.value : '';

            formData.append('courses[]', courseCode);
            formData.append('final_grades[]', finalGrade);
            formData.append('professor_instructors[]', professorValue);
        });

        formData.append('student_id', '<?= $student_id ?>');

        fetch('save_checklist_stud.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                showSuccessModal('Data saved successfully!');
                fetchAndUpdateChecklist(); // Fetch latest data immediately after save
            }
        })
    });

// Success modal function
function showSuccessModal(message) {
    // Remove any existing modal
    const oldModal = document.getElementById('success-modal');
    if (oldModal) oldModal.remove();

    // Create modal overlay
    const modal = document.createElement('div');
    modal.id = 'success-modal';
    modal.style.position = 'fixed';
    modal.style.top = '0';
    modal.style.left = '0';
    modal.style.width = '100vw';
    modal.style.height = '100vh';
    modal.style.background = 'rgba(32,96,24,0.15)';
    modal.style.display = 'flex';
    modal.style.alignItems = 'center';
    modal.style.justifyContent = 'center';
    modal.style.zIndex = '9999';
    modal.style.animation = 'fadeIn 0.3s';

    // Modal container
    const container = document.createElement('div');
    container.style.background = '#fff';
    container.style.borderRadius = '16px';
    container.style.boxShadow = '0 8px 32px rgba(32,96,24,0.18), 0 1.5px 8px rgba(0,0,0,0.08)';
    container.style.padding = '36px 32px 28px 32px';
    container.style.minWidth = '320px';
    container.style.maxWidth = '90vw';
    container.style.textAlign = 'center';
    container.style.position = 'relative';
    container.style.animation = 'popIn 0.3s';

    // Icon
    const icon = document.createElement('div');
    icon.innerHTML = '<svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="28" cy="28" r="28" fill="#4CAF50"/><path d="M16 29.5L24.5 38L40 22.5" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    icon.style.marginBottom = '12px';

    // Title
    const title = document.createElement('div');
    title.textContent = message;
    title.style.color = '#206018';
    title.style.fontSize = '22px';
    title.style.fontWeight = '700';
    title.style.marginBottom = '10px';
    title.style.letterSpacing = '0.5px';

    // Description
    const desc = document.createElement('div');
    desc.textContent = 'Your checklist has been updated.';
    desc.style.color = '#444';
    desc.style.fontSize = '15px';
    desc.style.marginBottom = '8px';

    container.appendChild(icon);
    container.appendChild(title);
    container.appendChild(desc);
    modal.appendChild(container);
    document.body.appendChild(modal);

    setTimeout(function() {
        container.style.transition = 'opacity 0.4s';
        container.style.opacity = '0';
        setTimeout(function() { modal.remove(); }, 400);
    }, 1500);
}

// Function to fetch and update checklist data
function fetchAndUpdateChecklist() {
    const studentId = '<?php echo $student_id; ?>';
    
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
// Modify the updateChecklistFields function in checklist_stud.php:

    function updateChecklistFields(courses) {
    courses.forEach(course => {
        // Update professor/instructor
        const professorInput = document.querySelector(`input[name="professor_instructor[${course.course_code}]"]`);
        if (professorInput) {
            professorInput.value = course.professor_instructor || '';
        }
        // Update final grade
        const gradeSelect = document.querySelector(`select[name="final_grade[${course.course_code}]"]`);
        if (gradeSelect) {
            if (course.evaluator_remarks === 'Approved') {
                // Replace dropdown with plain text
                const td = gradeSelect.parentElement;
                td.innerHTML = `<span style='font-size: 10px; color: #206018; font-weight: bold;'>${course.final_grade || ''}</span>`;
            } else {
                gradeSelect.value = course.final_grade || '';
                gradeSelect.disabled = false;
            }
        }
        // Update evaluator remarks
        const remarksElement = document.getElementById(`remarks_${course.course_code}`);
        if (remarksElement) {
            remarksElement.textContent = course.evaluator_remarks || '';
        }
    });
}

// Fetch grades immediately on page load
fetchAndUpdateChecklist();
// Poll for updates every 90 seconds
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

</script>
    <script>
        document.getElementById('downloadPDF').addEventListener('click', function() {
            const element = document.querySelector('.container');
            const opt = {
                margin: [0,-6, 3, 5],
                filename: 'BSCS_Checklist.pdf',
                image: { type: 'jpeg', quality: 1 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait', compress: false, precision: 16}
            };
            html2pdf().set(opt).from(element).save();
        });
    </script>
</body>
</html>