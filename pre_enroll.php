<?php
session_start();

// Database connection parameters
$servername = "localhost"; 
$username = "root";
$password = "";
$dbname = "e_checklist";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get student_id from URL parameter if it exists, otherwise use session
$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : (isset($_SESSION['student_id']) ? $_SESSION['student_id'] : null);

if (!$student_id) {
    header("Location: login.html");
    exit();
}

// Fetch student details
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$student_result = $stmt->get_result();

if ($student_result->num_rows > 0) {
    $student_data = $student_result->fetch_assoc();
    $last_name = $student_data['last_name'];
    $first_name = $student_data['first_name'];
    $middle_name = $student_data['middle_name'];
    $picture = $student_data['picture'];
    $address = isset($student_data['address']) ? $student_data['address'] : '';
    // Calculate age from birthdate if available, else 'N/A'
    $age = 'N/A';
    if (!empty($student_data['birthdate'])) {
        $dob = DateTime::createFromFormat('Y-m-d', $student_data['birthdate']);
        $now = new DateTime();
        if ($dob && $dob <= $now) {
            $age = $now->diff($dob)->y;
        }
    }
} else {
    header("Location: login.html");
    exit();
}
$stmt->close();

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the selected year and semester from URL parameters
$year = isset($_GET['year']) ? $_GET['year'] : '1st Yr';
$semester = isset($_GET['semester']) ? $_GET['semester'] : '1st Sem';

// Function to get next semester
function getNextSemester($current_year, $current_semester) {
    $years = ['1st Yr', '2nd Yr', '3rd Yr', '4th Yr'];
    $semesters = ['1st Sem', '2nd Sem'];
    
    $year_index = array_search($current_year, $years);
    $sem_index = array_search($current_semester, $semesters);
    
    if ($sem_index == 0) {
        // If first semester, return same year but next semester
        return [$current_year, $semesters[1]];
    } else {
        // If second semester, return next year and first semester
        if ($year_index < count($years) - 1) {
            return [$years[$year_index + 1], $semesters[0]];
        } else {
            // If last year second sem, return same (no more next semester)
            return [$current_year, $current_semester];
        }
    }
}

// Get next semester for pre-enrollment
list($next_year, $next_semester) = getNextSemester($year, $semester);

// SQL query for pre-enrollment
$preenroll_sql = "
    SELECT cb.course_code, cb.course_title, 
           cb.credit_unit_lec, cb.credit_unit_lab 
    FROM checklist_bscs cb
    WHERE cb.year = ? AND cb.semester = ?
    ORDER BY cb.course_code";

// Prepare and execute pre-enrollment query
$preenroll_stmt = $conn->prepare($preenroll_sql);
$preenroll_stmt->bind_param("ss", $next_year, $next_semester);
$preenroll_stmt->execute();
$preenroll_result = $preenroll_stmt->get_result();

$preenroll_courses = [];
while ($row = $preenroll_result->fetch_assoc()) {
    $preenroll_courses[] = $row;
}

// Student details have already been retrieved above from database

// Auto-detect year level based on latest year/semester with a final grade
$year_level = '';
$year_map = [
    '1st Yr' => 1,
    '2nd Yr' => 2,
    '3rd Yr' => 3,
    '4th Yr' => 4
];
$reverse_year_map = array_flip($year_map);



// Query: join student_checklists with checklist_bscs to get the year and semester for grade checking
$detect_sql = "SELECT cb.year, cb.semester, COUNT(*) as completed_subjects
FROM student_checklists sc
JOIN checklist_bscs cb ON sc.course_code = cb.course_code
WHERE sc.student_id = ? 
AND sc.final_grade IS NOT NULL 
AND sc.final_grade != '' 
AND sc.final_grade != '0'
GROUP BY cb.year, cb.semester
ORDER BY 
    CASE cb.year 
        WHEN '1st Yr' THEN 1 
        WHEN '2nd Yr' THEN 2
        WHEN '3rd Yr' THEN 3
        WHEN '4th Yr' THEN 4
    END,
    CASE cb.semester
        WHEN '1st Sem' THEN 1
        WHEN '2nd Sem' THEN 2
        ELSE 3
    END";

$conn2 = new mysqli($servername, $username, $password, $dbname);
if ($conn2->connect_error) {
    $year_level = '';
} else {
    $stmt = $conn2->prepare($detect_sql);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $stmt->bind_result($year, $semester, $completed_subjects);
    
    // Store all results
    $results = array();
    while ($stmt->fetch()) {
        $results[] = array(
            'year' => $year,
            'semester' => $semester,
            'completed' => $completed_subjects
        );
    }
    
    // Find the highest year with grades
    $highest_year = '1st Yr';
    foreach ($results as $result) {
        $current = $result['year'];
        $current_idx = array_search($current, array_keys($year_map));
        $highest_idx = array_search($highest_year, array_keys($year_map));
        
        if ($current_idx > $highest_idx) {
            $highest_year = $current;
        }
    }
    
    // First try to get year level from the latest pre-enrollment
    $pre_enroll_year_query = "SELECT year_level FROM pre_enrollments WHERE student_id = ? ORDER BY created_at DESC LIMIT 1";
    $pre_stmt = $conn2->prepare($pre_enroll_year_query);
    $pre_stmt->bind_param("s", $student_id);
    $pre_stmt->execute();
    $pre_result = $pre_stmt->get_result();
    
    if ($pre_result->num_rows > 0) {
        // Use year level from the latest pre-enrollment
        $year_level = $pre_result->fetch_assoc()['year_level'];
    } else if (!empty($results)) {
        // If no pre-enrollment, use the highest year where grades exist
        $year_level = $highest_year;
    } else {
        $year_level = '1st Yr'; // Default for new students
    }
    $pre_stmt->close();
    $stmt->close();
    $conn2->close();
}

// Get failed/missing grade subjects details
$failed_subjects_sql = "
    SELECT cb.year, cb.semester, cb.course_code, cb.course_title, cb.pre_requisite,
           COALESCE(sc.final_grade, 'No Grade') as final_grade,
           cb.credit_unit_lec + cb.credit_unit_lab as total_units
    FROM student_checklists sc
    JOIN checklist_bscs cb ON sc.course_code = cb.course_code
    WHERE sc.student_id = ? 
    AND (sc.final_grade >= 4.0 OR sc.final_grade = '' OR sc.final_grade IS NULL)
    ORDER BY 
        CASE cb.year 
            WHEN '1st Yr' THEN 1 
            WHEN '2nd Yr' THEN 2
            WHEN '3rd Yr' THEN 3
            WHEN '4th Yr' THEN 4
        END,
        CASE cb.semester
            WHEN '1st Sem' THEN 1
            WHEN '2nd Sem' THEN 2
            WHEN 'Mid Year' THEN 3
        END,
        cb.course_code";

// Store failed subjects per year/semester
$failed_subjects = array();
$failed_counts = array(
    '1st Yr' => array('1st Sem' => 0, '2nd Sem' => 0),
    '2nd Yr' => array('1st Sem' => 0, '2nd Sem' => 0),
    '3rd Yr' => array('1st Sem' => 0, '2nd Sem' => 0, 'Mid Year' => 0),
    '4th Yr' => array('1st Sem' => 0, '2nd Sem' => 0)
);

$conn3 = new mysqli($servername, $username, $password, $dbname);
if (!$conn3->connect_error) {
    $stmt = $conn3->prepare($failed_subjects_sql);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if (!isset($failed_subjects[$row['year']])) {
            $failed_subjects[$row['year']] = array();
        }
        if (!isset($failed_subjects[$row['year']][$row['semester']])) {
            $failed_subjects[$row['year']][$row['semester']] = array();
        }
        $failed_subjects[$row['year']][$row['semester']][] = $row;
        $failed_counts[$row['year']][$row['semester']]++;
    }
    
    $stmt->close();
    $conn3->close();
}

// Close other statements
$preenroll_stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre-Enrollment Form</title>
    <style>
        /* Popup styles */
        .popup {
            display: none;
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .popup-content {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            min-width: 300px;
        }

        .popup select {
            width: 100%;
            padding: 8px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .popup-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 15px;
        }

        .popup-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .popup-btn.confirm {
            background-color: #206018;
            color: white;
        }

        .popup-btn.cancel {
            background-color: #6c757d;
            color: white;
        }

        /* Header styles */
        .header {
            background-color: #206018;
            padding: 10px 20px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .header-title {
            color: white;
            position: relative;
            left: -1020px;
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        } 

        /* Add margin to body to account for fixed header */
        body {
            margin-top: 60px !important;
        }

        /* Position the logo */
        .logo {
            position: relative;
            top: -590px; /* Adjust as needed */
            left: 270px; /* Adjust as needed */
            width: 110px; /* Resize as needed */
            margin-top: 100px; /* Add more space above the logo */
        }

        body {
            font-family: Arial, sans-serif;
            background: url('pix/school.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow-y: auto;
        }
        h1 {
            text-align: center;
            color: #333;
            margin: 5px 0;
        }
        
        .container {
            max-width: 1020px;
            width: calc(100% - 40px);
            padding: 20px 40px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
            height: fit-content;
            min-height: auto;
            position: relative;
            overflow-y: visible;
            margin: 10px auto;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        table {
            position: relative;
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            text-align: center;
            flex: 1;
            min-height: 50px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: center;
            font-size: 10px;
        }
        th {
            background-color: #206018;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .btn {
            position: relative;
            display: inline-block;
            width: 110px;
            padding: 10px;
            text-align: center;
            background-color: #206018;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 2px;
        }
        .btn:hover {
            background-color: #206018;
        }



        .CvSUlogo .logo img {
            width: 110px;
            height: 110px;
            position: relative;
            right: -200px;
            
        }
        
        .profile .pic img {
            width: 110px;
            height: 110px;
            position: relative;
            right: -200px;
            top: -100px;
        }

        h4 {
            text-align: center;
            color: #000000;
            margin-bottom: -20px;
            position: relative;
            top: -40px;
        }
        
        h3 {
            margin-top: -220px;
            margin-bottom: 40px;
            text-align: center;
        }

        h5 {
            margin-top: 30px;
            margin-left: 50px;
            margin-bottom: -20px;
        }
        
        .leftside .info h5,
        .leftside .info2 h5,
        .leftside .info3 h5 {
            text-align: right;
            text-wrap: nowrap;
            position: relative;
            top: -75px;
        }
        h1, h2 {
            margin-left: 0px; /* Add margin to avoid overlap with the logo */
        }
        
        .leftside .info h5 { right: 217px; }
        .leftside .info2 h5 { right: 200px; }
        .leftside .info3 h5 { right: 199px; }

        body, table, th, td {
            font-size: 9px;
        }
        th, td {
            padding: 4px;
        }
        .logo {
            margin-top: 40px;
        }
        h4, h1, h2 {
            margin-top: 5px;
            margin-bottom: 5px;
        }
        
        /* Adjust spacing for input groups */
        h2 input, h2 select {
            margin: 2px 0;
        }
        @media print {
            @page {
                margin: 8mm;
            }
        }
    </style>
    <script>
        function editTable() {
            // Get failed subjects data from PHP
            const failedSubjects = <?php echo json_encode($failed_subjects); ?>;
            const semesterCounts = <?php echo json_encode($failed_counts); ?>;

            const popup = document.createElement('div');
            popup.className = 'popup';
            popup.innerHTML = `
                <div class="popup-content" style="width: 80%; max-width: 800px;">
                    <h3 style="margin-top: 0; font-size: 24px; color: #206018;">Select Year and Semester</h3>
                    <select id="yearSemSelect" onchange="updateSubjectsTable(this.value)">
                        ${Object.entries(semesterCounts).map(([year, semesters]) => 
                            Object.entries(semesters)
                                .filter(([sem, count]) => {
                                    // Only show Mid Year for 3rd Year
                                    if (sem === 'Mid Year' && year !== '3rd Yr') {
                                        return false;
                                    }
                                    return true;
                                })
                                .map(([sem, count]) => 
                                    `<option value="${year}_${sem}">${year} ${sem} (${count})</option>`
                                )
                            .join('')
                        ).join('')}
                    </select>
                    <div id="subjectsTableContainer" style="margin-top: 20px; max-height: 300px; overflow-y: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <th style="border: 1px solid #ddd; padding: 8px; background-color: #206018; color: white;">Course Code</th>
                                    <th style="border: 1px solid #ddd; padding: 8px; background-color: #206018; color: white;">Course Title</th>
                                    <th style="border: 1px solid #ddd; padding: 8px; background-color: #206018; color: white;">Pre-requisite</th>
                                </tr>
                            </thead>
                            <tbody id="subjectsTableBody">
                            </tbody>
                        </table>
                    </div>
                    <div class="popup-buttons">
                        <button class="popup-btn cancel" onclick="closePopup()">Cancel</button>
                        <button class="popup-btn confirm" onclick="confirmSelection()">Confirm</button>
                    </div>
                </div>
            `;
            document.body.appendChild(popup);
            popup.style.display = 'block';
        }

        function closePopup() {
            const popup = document.querySelector('.popup');
            if (popup) {
                popup.remove();
            }
        }

        function checkPrerequisites(subject, failedSubjects) {
            if (!subject.pre_requisite || subject.pre_requisite === '') return true;
            
            // Split prerequisites if there are multiple (assuming they're comma-separated)
            const prerequisites = subject.pre_requisite.split(',').map(p => p.trim());
            
            // Check each prerequisite
            for (const prereq of prerequisites) {
                // Look for the prerequisite in all years and semesters of failed subjects
                for (const yearData of Object.values(failedSubjects)) {
                    for (const semData of Object.values(yearData)) {
                        const failedPrereq = semData.find(s => s.course_code === prereq);
                        if (failedPrereq) {
                            // If prerequisite has failing grade (4.0, 5.0) or no grade
                            const grade = parseFloat(failedPrereq.final_grade);
                            if (isNaN(grade) || grade >= 4.0 || failedPrereq.final_grade === 'No Grade') {
                                return false;
                            }
                        }
                    }
                }
            }
            return true;
        }

        function updateSubjectsTable(value) {
            const [year, sem] = value.split('_');
            const failedSubjects = <?php echo json_encode($failed_subjects); ?>;
            const tbody = document.getElementById('subjectsTableBody');
            tbody.innerHTML = '';

            if (failedSubjects[year] && failedSubjects[year][sem]) {
                failedSubjects[year][sem].forEach(subject => {
                    const prerequisitesMet = checkPrerequisites(subject, failedSubjects);
                    const checkboxHtml = prerequisitesMet ? 
                        `<input type="checkbox" style="margin: 0;" value="${subject.course_code}">` :
                        `<input type="checkbox" style="margin: 0;" value="${subject.course_code}" disabled title="Prerequisites not met">`;
                    
                    tbody.innerHTML += `
                        <tr data-units="${subject.total_units}">
                            <td style="border: 1px solid #ddd; padding: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    ${checkboxHtml}
                                    <span>${subject.course_code}</span>
                                </div>
                            </td>
                            <td style="border: 1px solid #ddd; padding: 8px;">${subject.course_title}</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">${subject.pre_requisite || 'None'}</td>
                        </tr>
                    `;
                });
            }
        }

        function confirmSelection() {
            const select = document.getElementById('yearSemSelect');
            const [year, sem] = select.value.split('_');
            
            // Update the Year Level field based on selected year
            const yearLevelInput = document.querySelector('input[name="year_level"]');
            if (yearLevelInput) {
                yearLevelInput.value = year;
            }
            
            // Get all checked subjects
            const checkedBoxes = document.querySelectorAll('#subjectsTableBody input[type="checkbox"]:checked');
            const selectedRows = Array.from(checkedBoxes).map(checkbox => {
                const row = checkbox.closest('tr');
                return {
                    courseCode: checkbox.value,
                    courseTitle: row.cells[1].textContent.trim(),
                    units: row.getAttribute('data-units') || ''
                };
            });

            // Find the table in the main form
            const mainTable = document.querySelector('.container table');
            if (mainTable) {
                // Clear existing table content
                mainTable.innerHTML = `
                    <thead>
                        <tr>
                            <th style="border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;">COURSE CODE</th>
                            <th style="border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;">COURSE TITLE</th>
                            <th style="border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;">GRADE</th>
                            <th style="border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;">UNIT</th>
                            <th style="border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;">DAY</th>
                        </tr>
                    </thead>
                    <tbody>
                `;

                // Add selected subjects to the table and calculate total units
                let totalUnits = 0;
                selectedRows.forEach(subject => {
                    const units = parseFloat(subject.units) || 0;
                    totalUnits += units;
                    mainTable.innerHTML += `
                        <tr>
                            <td style="border: 1px solid black; padding: 8px; text-align: center;">${subject.courseCode}</td>
                            <td style="border: 1px solid black; padding: 8px; text-align: left;">${subject.courseTitle}</td>
                            <td style="border: 1px solid black; padding: 8px; text-align: center;"></td>
                            <td style="border: 1px solid black; padding: 8px; text-align: center;">${subject.units}</td>
                            <td style="border: 1px solid black; padding: 8px; text-align: center;"></td>
                        </tr>
                    `;
                });

                // Add total row
                mainTable.innerHTML += `
                    <tr>
                        <td colspan="3" style="border: 1px solid black; padding: 8px; text-align: right; font-weight: bold;">Total Units:</td>
                        <td style="border: 1px solid black; padding: 8px; text-align: center; font-weight: bold;">${totalUnits}</td>
                        <td style="border: 1px solid black; padding: 8px; text-align: center;"></td>
                    </tr>
                </tbody>`;
            }
            
            closePopup();
        }

        function showTransactionHistory(showAfterSubmit = false) {
            const popup = document.createElement('div');
            popup.className = 'popup';
            popup.innerHTML = `
                <div class="popup-content" style="width: 60%; max-width: 800px;">
                    <h3 style="margin-top: 0; font-size: 24px; color: #206018;">Transaction History</h3>
                    <div style="margin-top: 20px; max-height: 400px; overflow-y: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <th style="border: 1px solid #ddd; padding: 8px; background-color: #206018; color: white;">Time & Date</th>
                                    <th style="border: 1px solid #ddd; padding: 8px; background-color: #206018; color: white;">Course Codes</th>
                                    <th style="border: 1px solid #ddd; padding: 8px; background-color: #206018; color: white;">Course Titles</th>
                                    <th style="border: 1px solid #ddd; padding: 8px; background-color: #206018; color: white;">Total Units</th>
                                    <th style="border: 1px solid #ddd; padding: 8px; background-color: #206018; color: white;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="transactionTableBody">
                            </tbody>
                        </table>
                    </div>
                    <div class="popup-buttons">
                        <button class="popup-btn cancel" onclick="closeTransactionPopup()">Close</button>
                    </div>
                </div>
            `;
            document.body.appendChild(popup);
            popup.style.display = 'block';

            // Fetch transaction history
            const urlParams = new URLSearchParams(window.location.search);
            const studentId = urlParams.get('student_id');
            const url = studentId ? `get_transaction_history.php?student_id=${studentId}` : 'get_transaction_history.php';
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    console.log('Transaction history response:', data);
                    const tbody = document.getElementById('transactionTableBody');
                    tbody.innerHTML = '';
                    if (data.success && data.transactions) {
                        data.transactions.forEach(transaction => {
                            // Calculate total units (sum of all units in the comma-separated string)
                            let totalUnits = 0;
                            if (transaction.units) {
                                totalUnits = transaction.units.split(',').reduce((sum, u) => {
                                    const num = parseFloat(u.trim());
                                    return sum + (isNaN(num) ? 0 : num);
                                }, 0);
                            }
                            tbody.innerHTML += `
                                <tr data-enrollment-id="${transaction.id}">
                                    <td style="border: 1px solid #ddd; padding: 8px;">${transaction.created_at}</td>
                                    <td style="border: 1px solid #ddd; padding: 8px;">${transaction.course_codes}</td>
                                    <td style="border: 1px solid #ddd; padding: 8px;">${transaction.course_titles}</td>
                                    <td style="border: 1px solid #ddd; padding: 8px;">${totalUnits}</td>
                                    <td style="border: 1px solid #ddd; padding: 8px;">
                                        <button class="btn" style="padding: 4px 10px; font-size: 10px;" onclick="loadHistoricalEnrollment('${transaction.id}')">View</button>
                                    </td>
                                </tr>
                            `;
                        });
                    }
                })
                .catch(error => console.error('Error loading transaction history:', error));
        }

        function closeTransactionPopup() {
            const popup = document.querySelector('.popup');
            if (popup) {
                popup.remove();
            }
        }

        function loadHistoricalEnrollment(enrollmentId) {
            fetch(`get_enrollment_details.php?enrollment_id=${enrollmentId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.enrollment) {
                        // Update all form fields
                        if (data.enrollment.year_level !== undefined) {
                            document.querySelector('input[name="year_level"]').value = data.enrollment.year_level;
                        }
                        if (data.enrollment.name !== undefined) {
                            document.querySelector('input[name="name"]').value = data.enrollment.name;
                        }
                        if (data.enrollment.student_id !== undefined) {
                            document.querySelector('input[name="student_number"]').value = data.enrollment.student_id;
                        }
                        if (data.enrollment.course !== undefined) {
                            document.querySelector('select[name="course"]').value = data.enrollment.course;
                        }
                        if (data.enrollment.section_major !== undefined) {
                            document.querySelector('input[name="section_major"]').value = data.enrollment.section_major || 'N/A';
                        }
                        if (data.enrollment.classification !== undefined) {
                            document.querySelector('select[name="classification"]').value = data.enrollment.classification;
                        }
                        if (data.enrollment.registration_status !== undefined) {
                            document.querySelector('select[name="registration_status"]').value = data.enrollment.registration_status;
                        }
                        if (data.enrollment.scholarship_awarded !== undefined) {
                            document.querySelector('input[name="scholarship_awarded"]').value = data.enrollment.scholarship_awarded;
                        }
                        if (data.enrollment.mode_of_payment !== undefined) {
                            document.querySelector('select[name="mode_of_payment"]').value = data.enrollment.mode_of_payment;
                        }
                        // Update the table with historical courses
                        const mainTable = document.querySelector('.container table');
                        if (mainTable && data.enrollment.courses) {
                            let totalUnits = 0;
                            let tableHtml = `
                                <thead>
                                    <tr>
                                        <th style="border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;">COURSE CODE</th>
                                        <th style="border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;">COURSE TITLE</th>
                                        <th style="border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;">GRADE</th>
                                        <th style="border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;">UNIT</th>
                                        <th style="border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;">DAY</th>
                                    </tr>
                                </thead>
                                <tbody>
                            `;
                            data.enrollment.courses.forEach(course => {
                                totalUnits += parseFloat(course.units) || 0;
                                tableHtml += `
                                    <tr>
                                        <td style="border: 1px solid black; padding: 8px; text-align: center;">${course.course_code}</td>
                                        <td style="border: 1px solid black; padding: 8px; text-align: left;">${course.course_title}</td>
                                        <td style="border: 1px solid black; padding: 8px; text-align: center;"></td>
                                        <td style="border: 1px solid black; padding: 8px; text-align: center;">${course.units}</td>
                                        <td style="border: 1px solid black; padding: 8px; text-align: center;">${course.day || ''}</td>
                                    </tr>
                                `;
                            });
                            tableHtml += `
                                <tr>
                                    <td colspan="3" style="border: 1px solid black; padding: 8px; text-align: right; font-weight: bold;">Total Units:</td>
                                    <td style="border: 1px solid black; padding: 8px; text-align: center; font-weight: bold;">${totalUnits}</td>
                                    <td style="border: 1px solid black; padding: 8px; text-align: center;"></td>
                                </tr>
                            </tbody>`;
                            mainTable.innerHTML = tableHtml;
                        }
                        // Close the transaction history popup
                        closeTransactionPopup();
                    }
                })
                .catch(error => console.error('Error loading enrollment details:', error));
        }

        function submitForm() {
            if (confirm('Are you sure you want to submit this pre-enrollment form?')) {
                // Get all the form data
                const formData = {
                    student_id: document.querySelector('input[name="student_number"]').value,
                    name: document.querySelector('input[name="name"]').value,
                    year_level: document.querySelector('input[name="year_level"]').value,
                    course: document.querySelector('select[name="course"]').value,
                    section_major: document.querySelector('input[name="section_major"]').value,
                    classification: document.querySelector('select[name="classification"]').value,
                    registration_status: document.querySelector('select[name="registration_status"]').value,
                    scholarship_awarded: document.querySelector('input[name="scholarship_awarded"]').value,
                    mode_of_payment: document.querySelector('select[name="mode_of_payment"]').value,
                    courses: []
                };

                // Get all rows from the table except the last one (total row)
                const rows = Array.from(document.querySelectorAll('table tbody tr')).slice(0, -1);
                rows.forEach(row => {
                    const cells = row.cells;
                    if (cells.length >= 5) {
                        formData.courses.push({
                            course_code: cells[0].textContent.trim(),
                            course_title: cells[1].textContent.trim(),
                            units: cells[3].textContent.trim(),
                            day: cells[4].textContent.trim()
                        });
                    }
                });

                // Send the data to the server
                console.log('Sending form data:', formData);
                const urlParams = new URLSearchParams(window.location.search);
                const studentId = urlParams.get('student_id');
                const saveUrl = studentId ? `save_pre_enrollment.php?student_id=${studentId}` : 'save_pre_enrollment.php';

                fetch(saveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => {
                    console.log('Raw response:', response);
                    return response.text();
                })
                .then(text => {
                    console.log('Response text:', text);
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            alert('Pre-enrollment form submitted successfully!');

                            // Refresh transaction history popup if open
                            const tbody = document.getElementById('transactionTableBody');
                            if (tbody) {
                                // Fetch and update transaction history
                                const urlParams = new URLSearchParams(window.location.search);
                                const studentId = urlParams.get('student_id');
                                const url = studentId ? `get_transaction_history.php?student_id=${studentId}` : 'get_transaction_history.php';
                                fetch(url)
                                    .then(response => response.json())
                                    .then(data => {
                                        tbody.innerHTML = '';
                                        if (data.success && data.transactions) {
                                            data.transactions.forEach(transaction => {
                                                tbody.innerHTML += `
                                                    <tr data-enrollment-id="${transaction.id}" style="cursor: pointer;" onclick="loadHistoricalEnrollment(${transaction.id})">
                                                        <td style=\"border: 1px solid #ddd; padding: 8px;\">${transaction.created_at}</td>
                                                        <td style=\"border: 1px solid #ddd; padding: 8px;\">${transaction.course_codes}</td>
                                                        <td style=\"border: 1px solid #ddd; padding: 8px;\">${transaction.course_titles}</td>
                                                        <td style=\"border: 1px solid #ddd; padding: 8px;\">${transaction.total_units}</td>
                                                    </tr>
                                                `;
                                            });
                                        }
                                    })
                                    .catch(error => console.error('Error loading transaction history:', error));
                            }

                            // Reload the form data to ensure it's displayed
                            const urlParams2 = new URLSearchParams(window.location.search);
                            const studentId2 = urlParams2.get('student_id');
                            const url2 = studentId2 ? `load_pre_enrollment.php?student_id=${studentId2}` : 'load_pre_enrollment.php';

                            fetch(url2)
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success && data.data) {
                                        document.querySelector('select[name="course"]').value = data.data.course;
                                        document.querySelector('input[name="section_major"]').value = data.data.section_major || 'N/A';
                                        document.querySelector('select[name="classification"]').value = data.data.classification;
                                        document.querySelector('select[name="registration_status"]').value = data.data.registration_status;
                                        document.querySelector('input[name="scholarship_awarded"]').value = data.data.scholarship_awarded;
                                        document.querySelector('select[name="mode_of_payment"]').value = data.data.mode_of_payment;
                                        if (data.data.year_level) {
                                            document.querySelector('input[name="year_level"]').value = data.data.year_level;
                                        }

                                        const mainTable = document.querySelector('.container table');
                                        if (mainTable && data.data.courses && data.data.courses.length > 0) {
                                            let totalUnits = 0;
                                            let tableHtml = `
                                                <thead>
                                                    <tr>
                                                        <th style=\"border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;\">COURSE CODE</th>
                                                        <th style=\"border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;\">COURSE TITLE</th>
                                                        <th style=\"border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;\">GRADE</th>
                                                        <th style=\"border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;\">UNIT</th>
                                                        <th style=\"border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;\">DAY</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                            `;

                                            data.data.courses.forEach(course => {
                                                const units = parseFloat(course.units) || 0;
                                                totalUnits += units;
                                                tableHtml += `
                                                    <tr>
                                                        <td style=\"border: 1px solid black; padding: 8px; text-align: center;\">${course.course_code}</td>
                                                        <td style=\"border: 1px solid black; padding: 8px; text-align: left;\">${course.course_title}</td>
                                                        <td style=\"border: 1px solid black; padding: 8px; text-align: center;\"></td>
                                                        <td style=\"border: 1px solid black; padding: 8px; text-align: center;\">${course.units}</td>
                                                        <td style=\"border: 1px solid black; padding: 8px; text-align: center;\">${course.day || ''}</td>
                                                    </tr>
                                                `;
                                            });

                                            tableHtml += `
                                                    <tr>
                                                        <td colspan=\"3\" style=\"border: 1px solid black; padding: 8px; text-align: right; font-weight: bold;\">Total Units:</td>
                                                        <td style=\"border: 1px solid black; padding: 8px; text-align: center; font-weight: bold;\">${totalUnits}</td>
                                                        <td style=\"border: 1px solid black; padding: 8px; text-align: center;\"></td>
                                                    </tr>
                                                </tbody>
                                            `;

                                            mainTable.innerHTML = tableHtml;
                                        }
                                    }
                                })
                                .catch(error => console.error('Error reloading saved data:', error));
                        } else {
                            alert('Error submitting form: ' + data.message);
                        }
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        alert('Error processing server response');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error submitting form. Please try again.');
                });
            }
        }
    </script>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <img src="img/cav.png" alt="CvSU Logo" style="height:32px; width:auto; margin-right:-1200px; vertical-align:middle;">
        <h1 class="header-title">PRE - ENROLLMENT ASSESSMENT</h1>
    </div>

    <!-- Add the logo -->
    <div class="container">
        <h1 style="font-size: medium; font-weight: bold;">PRE-ENROLLMENT FORM</h1>

    <h2 style="font-size: medium; font-weight: normal;">
        Name: <input type="text" name="name" value="<?php echo htmlspecialchars(ucwords(strtolower($last_name . ', ' . $first_name . ' ' . $middle_name))); ?>" style="width: 300px; border: none; border-bottom: 1px solid #000" readonly>
        Student Number: <input type="text" name="student_number" value="<?php echo htmlspecialchars($student_id); ?>" style="width: 150px; border: none; border-bottom: 1px solid #000" readonly>
    Age: <input type="text" name="age" value="<?php echo htmlspecialchars($age); ?>" style="width: 50px; border: none; border-bottom: 1px solid #000" readonly>
    </h2>
    <h2 style="font-size: medium; font-weight: normal;">
        Year Level: <input type="text" name="year_level" value="<?php echo htmlspecialchars($year_level); ?>" style="width: 50px; border: none; border-bottom: 1px solid #000" readonly>
        Course: <select name="course" style="width: 160px; border: none; border-bottom: 1px solid #000;">
            <option value="BSCS" selected>BSCS</option>
            <option value="BSIT">BSIT</option>
            <option value="BSIS">BSIS</option>
            <option value="BSCE">BSCE</option>
            <option value="BSEd">BSEd</option>
            <option value="BSA">BSA</option>
            <option value="BSBA">BSBA</option>
            <option value="BSTM">BSTM</option>
            <option value="BSPsych">BSPsych</option>
            <!-- Add more courses as needed -->
        </select>
        Section & Major: <input type="text" name="section_major" value="N/A" placeholder="N/A" style="width: 150px; border: none; border-bottom: 1px solid #000" oninput="if(this.value === '') this.value='N/A';" defaultValue="N/A">
        Address: <input type="text" name="address" value="<?php echo htmlspecialchars($address); ?>" style="width: 250px; border: none; border-bottom: 1px solid #000" readonly>
    </h2>
<h2 style="font-size: medium; font-weight: normal;">
    Classification: 
    <select name="classification" style="width: 160px; border: none; border-bottom: 1px solid #000;">
        <option value="Old" selected>Old</option>
        <option value="New">New</option>
        <option value="Transferee">Transferee</option>
        <option value="Cross Reg. Form">Cross Reg. Form</option>
    </select>
</h2>
<h2 style="font-size: medium; font-weight: normal;">
    Registration Status: 
    <select name="registration_status" style="width: 160px; border: none; border-bottom: 1px solid #000;">
        <option value="Regular" selected>Regular</option>
        <option value="Irregular">Irregular</option>
    </select>
</h2>
<h2 style="font-size: medium; font-weight: normal;">
    Scholarship Awarded: <input type="text" name="scholarship_awarded" value="N/A" style="width: 300px; border: none; border-bottom: 1px solid #000" 
</h2>
    <h2 style="font-size: medium; font-weight: normal; margin-bottom: 0;">
    Mode of Payment: 
    <select name="mode_of_payment" style="width: 160px; border: none; border-bottom: 1px solid #000;">
        <option value="Cash" selected>Cash</option>
        <option value="Installment">Installment</option>
    </select>
</h2>

    <table style="border: 1px solid black; margin-top: 20px;">
        <thead>
            <tr>
                <th style="border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;">COURSE CODE</th>
                <th style="border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;">COURSE TITLE</th>
                <th style="border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;">GRADE</th>
                <th style="border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;">UNIT</th>
                <th style="border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;">DAY</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

        <div style="margin-top: 20px;">
            <!-- Center buttons container -->
            <div style="display: flex; justify-content: center; gap: 10px;">
                <button class="btn" onclick="editTable()" style="font-weight: bold; font-size: 14px; margin: 0;">Edit</button>
            </div>
        </div>
    </div> <!-- End of container -->

    <!-- Submit and Back buttons below container -->
    <div style="width: 100%; max-width: 1020px; margin: -20px auto; padding: 0 40px; position: relative;">
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
            <a href="/test_04/checklist_eval_adviser.php" class="btn" style="font-size: 14px; font-weight: bold; margin: 0;">Back</a>
            <button class="btn" onclick="showTransactionHistory()" style="font-weight: bold; font-size: 14px; margin: 0;">Transaction History</button>
            <button class="btn" onclick="submitForm()" style="font-weight: bold; font-size: 14px; position: relative;">Submit</button>
        </div>
    </div>

    <script>
        // Load saved pre-enrollment data when page loads
        window.addEventListener('load', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const studentId = urlParams.get('student_id');
            const url = studentId ? `load_pre_enrollment.php?student_id=${studentId}` : 'load_pre_enrollment.php';
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        // Update form fields
                        document.querySelector('select[name="course"]').value = data.data.course;
                        document.querySelector('input[name="section_major"]').value = data.data.section_major || 'N/A';
                        document.querySelector('select[name="classification"]').value = data.data.classification;
                        document.querySelector('select[name="registration_status"]').value = data.data.registration_status;
                        document.querySelector('input[name="scholarship_awarded"]').value = data.data.scholarship_awarded;
                        document.querySelector('select[name="mode_of_payment"]').value = data.data.mode_of_payment;
                        // Update year level if it exists in the saved data
                        if (data.data.year_level) {
                            document.querySelector('input[name="year_level"]').value = data.data.year_level;
                        }

                        // Update table with courses
                        const mainTable = document.querySelector('.container table');
                        if (mainTable && data.data.courses && data.data.courses.length > 0) {
                            let totalUnits = 0;
                            let tableHtml = `
                                <thead>
                                    <tr>
                                        <th style="border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;">COURSE CODE</th>
                                        <th style="border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;">COURSE TITLE</th>
                                        <th style="border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;">GRADE</th>
                                        <th style="border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;">UNIT</th>
                                        <th style="border: 1px solid black; background-color: #206018; color: white; padding: 8px; text-align: center;">DAY</th>
                                    </tr>
                                </thead>
                                <tbody>
                            `;

                            data.data.courses.forEach(course => {
                                const units = parseFloat(course.units) || 0;
                                totalUnits += units;
                                tableHtml += `
                                    <tr>
                                        <td style="border: 1px solid black; padding: 8px; text-align: center;">${course.course_code}</td>
                                        <td style="border: 1px solid black; padding: 8px; text-align: left;">${course.course_title}</td>
                                        <td style="border: 1px solid black; padding: 8px; text-align: center;"></td>
                                        <td style="border: 1px solid black; padding: 8px; text-align: center;">${course.units}</td>
                                        <td style="border: 1px solid black; padding: 8px; text-align: center;">${course.day || ''}</td>
                                    </tr>
                                `;
                            });

                            tableHtml += `
                                    <tr>
                                        <td colspan="3" style="border: 1px solid black; padding: 8px; text-align: right; font-weight: bold;">Total Units:</td>
                                        <td style="border: 1px solid black; padding: 8px; text-align: center; font-weight: bold;">${totalUnits}</td>
                                        <td style="border: 1px solid black; padding: 8px; text-align: center;"></td>
                                    </tr>
                                </tbody>
                            `;

                            mainTable.innerHTML = tableHtml;
                        }
                    }
                })
                .catch(error => console.error('Error loading saved data:', error));
        });
    </script>

    <style>
        @media print {
            .btn {
                display: none;
            }
        }
    </style>
</body>
</html>