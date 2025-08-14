<?php
session_start();

// Only allow admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.html");
    exit();
}

$view_student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;
if (!$view_student_id) {
    die("No student selected.");
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "e_checklist";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("s", $view_student_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $last_name = htmlspecialchars($row['last_name']);
    $first_name = htmlspecialchars($row['first_name']);
    $middle_name = htmlspecialchars($row['middle_name']);
    $password = htmlspecialchars($row['password']);
    $picture = htmlspecialchars($row['picture']);
    $student_id = htmlspecialchars($row['student_id']);
    $contact_no = htmlspecialchars($row['contact_no']);
    $address = htmlspecialchars($row['address']);
    $admission_date = htmlspecialchars($row['admission_date']);
} else {
    die("Student not found.");
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pre - Enrollment Assessment</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <style>
    body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: url('pix/school.jpg') no-repeat center center fixed; background-size: cover; }
    .header { background-color: #206018; color: white; padding: 5px 16px; display: flex; align-items: center; box-shadow: 0 2px 8px rgba(32, 96, 24, 0.09); }
    .header h1 { margin: 0; font-size: 24px; letter-spacing: 1px; }
    .container { max-width: 700px; margin: 40px auto 30px auto; background-color: #fff; padding: 20px 30px 10px 30px; border-radius: 18px; box-shadow: 0 8px 32px rgba(32, 96, 24, 0.10), 0 1.5px 4px rgba(0,0,0,0.04); position: relative; }
    .title { background-color: #206018; color: white; padding: 14px; text-align: center; border-radius: 8px; font-size: 28px; font-weight: bold; margin-bottom: 8px; letter-spacing: 1px; box-shadow: 0 2px 8px rgba(32, 96, 24, 0.08); }
    .subtitle { text-align: center; color: #206018; font-size: 16px; margin-bottom: 18px; letter-spacing: 0.5px; }
    .divider { border: none; border-top: 1.5px solid #e0e0e0; margin: 18px 0 24px 0; }
    .profile { display: flex; gap: 32px; align-items: flex-start; justify-content: center; flex-wrap: wrap; }
    .profile .photo { display: flex; flex-direction: column; align-items: center; gap: 10px; }
    .profile .photo img { width: 140px; height: 140px; border-radius: 50%; border: 3px solid #206018; object-fit: cover; box-shadow: 0 2px 12px rgba(32, 96, 24, 0.10); background: #f4f4f4; margin-bottom: 8px; }
    .profile .photo input[type="file"] { margin-top: 8px; }
    .profile .details { display: grid; grid-template-columns: 1fr 1fr; row-gap: 32px; column-gap: 32px; width: 100%; min-width: 220px; max-width: 340px; }
    .profile .details .field { display: flex; flex-direction: column; position: relative; }
    .profile .details .field label { margin-bottom: 5px; font-weight: bold; color: #206018; font-size: 15px; letter-spacing: 0.2px; }
    .profile .details .field input { width: 100%; padding: 10px 12px; border: 1.5px solid #c2c2c2; border-radius: 6px; font-size: 15px; background: #f9f9f9; transition: border 0.2s; }
    .profile .details .field input:focus { border: 1.5px solid #206018; outline: none; background: #fff; }
    .profile .details .field button[type="button"] { margin-top: 7px; background: #206018; color: #fff; border: none; border-radius: 5px; padding: 7px 0; font-size: 14px; cursor: pointer; transition: background 0.2s; }
    .profile .details .field button[type="button"]:hover { background: #184c13; }
    .buttons { text-align: center; margin-top: 28px; }
    .buttons button { padding: 13px 38px; background-color: #206018; color: white; border: none; border-radius: 7px; font-size: 18px; font-weight: bold; cursor: pointer; box-shadow: 0 2px 8px rgba(32, 96, 24, 0.10); letter-spacing: 1px; transition: background 0.2s, transform 0.2s; }
    .buttons button:hover { background-color: #184c13; transform: translateY(-2px) scale(1.04); }
  </style>
</head>
<body>
  <div class="header">
    <img src="img/cav.png" alt="CvSU Logo" style="height:32px; width:auto; margin-right:12px; vertical-align:middle;">
    <h1 style="margin: 0; font-size: 24px; letter-spacing: 1px; flex:1;">Pre - Enrollment Assessment</h1>
  </div>
  <div class="container">
    <div class="title">Edit Student Profile</div>
    <div class="subtitle">Admin can edit all fields of the student</div>
    <hr class="divider" />
    <form id="edit-student-form" enctype="multipart/form-data">
      <div class="profile">
        <div class="photo">
          <img id="profile-pic" src="<?= $picture ?>" alt="Profile Photo" />
          <input id="file-input" name="picture" type="file" accept="image/*">
        </div>
        <div class="details">
          <div class="field">
            <label for="student_id">Student Number</label>
            <input id="student_id" name="student_id" type="text" value="<?= $student_id ?>" readonly>
          </div>
          <div class="field">
            <label for="last_name">Last Name</label>
            <input id="last_name" name="last_name" type="text" value="<?= $last_name ?>">
          </div>
          <div class="field">
            <label for="first_name">First Name</label>
            <input id="first_name" name="first_name" type="text" value="<?= $first_name ?>">
          </div>
          <div class="field">
            <label for="middle_name">Middle Name</label>
            <input id="middle_name" name="middle_name" type="text" value="<?= $middle_name ?>">
          </div>
          <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="text" value="" placeholder="Enter new password or leave blank to keep current">
          </div>
          <div class="field">
            <label for="contact_no">Contact Number</label>
            <input id="contact_no" name="contact_no" type="text" value="<?= $contact_no ?>">
          </div>
          <div class="field">
            <label for="admission_date">Date of Admission</label>
            <input id="admission_date" name="admission_date" type="text" value="<?= $admission_date ?>">
          </div>
          <div class="field">
            <label for="address">Address</label>
            <input id="address" name="address" type="text" value="<?= $address ?>">
          </div>
        </div>
      </div>
      <div class="buttons">
        <button type="button" onclick="saveStudentChanges()">SAVE</button>
        <button type="button" onclick="window.history.back();" style="background-color: #888;">BACK</button>
      </div>
    </form>
  </div>
  <script>
    function saveStudentChanges() {
      const form = document.getElementById('edit-student-form');
      const formData = new FormData(form);
      fetch('save_profile.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(text => {
        let data;
        try { data = JSON.parse(text); } catch (e) { alert('Server error: ' + text); return; }
        if (data.success) {
          alert('Student profile updated successfully!');
          location.reload();
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => { alert('Network error: ' + error); });
    }
  </script>
</body>
</html>
