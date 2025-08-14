<?php
session_start();

// If admin is logged in and ?student_id is set, load that student's data from DB
$is_admin = isset($_SESSION['admin_id']);
$view_student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;

if ($is_admin && $view_student_id) {
  // Admin viewing a student
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
} else {
  // Student viewing their own profile (default)
  if (!isset($_SESSION['student_id'])) {
    header("Location: login.html");
    exit();
  }
  $last_name = htmlspecialchars($_SESSION['last_name']);
  $first_name = htmlspecialchars($_SESSION['first_name']);
  $middle_name = htmlspecialchars($_SESSION['middle_name']);
  $password = htmlspecialchars($_SESSION['password']);
  $picture = htmlspecialchars($_SESSION['picture']);
  $student_id = htmlspecialchars($_SESSION['student_id']);
  $contact_no = htmlspecialchars($_SESSION['contact_no']);
  $address = htmlspecialchars($_SESSION['address']);
  $admission_date = htmlspecialchars($_SESSION['admission_date']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background: url('pix/school.jpg') no-repeat center center fixed;
      background-size: cover;
    }

    .header {
      background-color: #206018;
      color: white;
      padding: 5px 16px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 8px rgba(32, 96, 24, 0.09);
    }

    .header h1 {
      margin: 0;
      font-size: 24px;
      letter-spacing: 1px;
    }

    .header .icons img {
      width: 32px;
      height: 32px;
      margin-left: 18px;
      cursor: pointer;
      transition: transform 0.2s;
    }
    .header .icons img:hover {
      transform: scale(1.12) rotate(-5deg);
    }

    .container {
      max-width: 700px;
      margin: 40px auto 30px auto;
      background-color: #fff;
      padding: 20px 30px 10px 30px;
      border-radius: 18px;
      box-shadow: 0 8px 32px rgba(32, 96, 24, 0.10), 0 1.5px 4px rgba(0,0,0,0.04);
      position: relative;
    }

    .title {
      background-color: #206018;
      color: white;
      padding: 14px;
      text-align: center;
      border-radius: 8px;
      font-size: 28px;
      font-weight: bold;
      margin-bottom: 8px;
      letter-spacing: 1px;
      box-shadow: 0 2px 8px rgba(32, 96, 24, 0.08);
    }
    .subtitle {
      text-align: center;
      color: #206018;
      font-size: 16px;
      margin-bottom: 18px;
      letter-spacing: 0.5px;
    }

    .divider {
      border: none;
      border-top: 1.5px solid #e0e0e0;
      margin: 18px 0 24px 0;
    }

    .profile {
      display: flex;
      gap: 32px;
      align-items: flex-start;
      justify-content: center;
      flex-wrap: wrap;
    }

    .profile .photo {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
    }
    .profile .photo img {
      width: 140px;
      height: 140px;
      border-radius: 50%;
      border: 3px solid #206018;
      object-fit: cover;
      box-shadow: 0 2px 12px rgba(32, 96, 24, 0.10);
      background: #f4f4f4;
      margin-bottom: 8px;
    }
    .profile .photo button {
      margin-top: 6px;
      padding: 7px 32px;
      background-color: #206018;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 15px;
      font-weight: 500;
      transition: background 0.2s, transform 0.2s;
    }
    .profile .photo button:hover {
      background-color: #184c13;
      transform: translateY(-2px) scale(1.04);
    }

    .profile .details {
      display: grid;
      grid-template-columns: 1fr 1fr;
      row-gap: 32px;
      column-gap: 32px;
      width: 100%;
      min-width: 220px;
      max-width: 340px;
    }
    .profile .details .field {
      display: flex;
      flex-direction: column;
      position: relative;
    }
    .profile .details .field label {
      margin-bottom: 5px;
      font-weight: bold;
      color: #206018;
      font-size: 15px;
      letter-spacing: 0.2px;
    }
    .profile .details .field input {
      width: 100%;
      padding: 10px 12px;
      border: 1.5px solid #c2c2c2;
      border-radius: 6px;
      font-size: 15px;
      background: #f9f9f9;
      transition: border 0.2s;
    }
    .profile .details .field input:focus {
      border: 1.5px solid #206018;
      outline: none;
      background: #fff;
    }
    .profile .details .field i.fas.fa-edit {
      position: static;
      color: #206018;
      cursor: pointer;
      font-size: 16px;
      opacity: 0.7;
      transition: color 0.2s, opacity 0.2s;
    }
    .profile .details .field i.fas.fa-edit:hover {
      color: #184c13;
      opacity: 1;
    }
    .profile .details .field button[type="button"] {
      margin-top: 7px;
      background: #206018;
      color: #fff;
      border: none;
      border-radius: 5px;
      padding: 7px 0;
      font-size: 14px;
      cursor: pointer;
      transition: background 0.2s;
    }
    .profile .details .field button[type="button"]:hover {
      background: #184c13;
    }
    #change-password-container {
      margin-top: 10px;
      background: #f4f4f4;
      border-radius: 6px;
      padding: 12px 10px 8px 10px;
      box-shadow: 0 1px 4px rgba(32, 96, 24, 0.06);
    }
    .buttons {
      text-align: center;
      margin-top: 28px;
    }
    .buttons button {
      padding: 13px 38px;
      background-color: #206018;
      color: white;
      border: none;
      border-radius: 7px;
      font-size: 18px;
      font-weight: bold;
      cursor: pointer;
      box-shadow: 0 2px 8px rgba(32, 96, 24, 0.10);
      letter-spacing: 1px;
      transition: background 0.2s, transform 0.2s;
    }
    .buttons button:hover {
      background-color: #184c13;
      transform: translateY(-2px) scale(1.04);
    }

    /* Modal styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgb(0,0,0);
      background-color: rgba(0,0,0,0.4);
      padding-top: 60px;
    }

    .modal-content {
      background-color: #fefefe;
      margin: 5% auto;
      padding: 20px;
      border: 1px solid #888;
      width: 80%;
      max-width: 400px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .close {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
    }

    .close:hover,
    .close:focus {
      color: black;
      text-decoration: none;
      cursor: pointer;
    }

    .modal-icon {
      text-align: center;
      margin-bottom: 15px;
    }

    .modal-title {
      font-size: 18px;
      font-weight: bold;
      color: #206018;
      margin: 0 0 10px 0;
      text-align: center;
    }

    .modal-message {
      font-size: 16px;
      color: #333;
      margin-bottom: 20px;
      text-align: center;
    }

    .modal-button {
      background-color: #206018;
      color: white;
      border: none;
      border-radius: 5px;
      padding: 10px 20px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s;
      display: block;
      width: 100%;
      text-align: center;
    }

    .modal-button:hover {
      background-color:rgb(39, 186, 26);
    }

    .success-modal {
      background: linear-gradient(135deg,rgb(246, 246, 35), #206018) !important;
    }
  </style>
</head>
<body>
  <div class="header">
    <img src="img/cav.png" alt="CvSU Logo" style="height:32px; width:auto; margin-right:12px; vertical-align:middle;">
    <h1 style="margin: 0; font-size: 24px; letter-spacing: 1px; flex:1;">PRE - ENROLLMENT ASSESSMENT</h1>
    </div>
  </div>

  <div class="container">
    <div class="title">Student Profile</div>
    <div class="subtitle">View and manage your account details</div>
    <hr class="divider" />
    <div class="profile">
      <div class="photo">
        <img id="profile-pic" src="<?= $picture ?>" alt="Profile Photo" />
        <form id="pic-form" enctype="multipart/form-data" style="display:inline;">
          <input id="file-input" name="picture" type="file" accept="image/*" style="display: none;">
        </form>
        <button type="button" onclick="document.getElementById('file-input').click()">Change Picture</button>
      </div>
      <div class="details">
        <div class="field">
          <label for="last_name">Last Name</label>
          <input id="last_name" type="text" value="<?= $last_name ?>" disabled>
        </div>
        <div class="field">
          <label for="first_name">First Name</label>
          <input id="first_name" type="text" value="<?= $first_name ?>" disabled>
        </div>
        <div class="field">
          <label for="middle_name">Middle Name</label>
          <input id="middle_name" type="text" value="<?= $middle_name ?>" disabled>
        </div>
        <div class="field">
          <label for="username">Student Number</label>
          <input id="username" type="text" value="<?= $student_id ?>" disabled>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input id="password" type="password" value=<?= $password ?> disabled>
            <div class="field">
                <button type="button" onclick="togglePasswordForm()">Change Password</button>
            </div>

            <div id="change-password-container" style="display: none;">
                <div class="field">
                    <label for="current_password">Current Password</label>
                    <input id="current_password" type="password" required>
                </div>
                <div class="field">
                    <label for="new_password">New Password</label>
                    <input id="new_password" type="password" required>
                </div>
                <div class="field">
                    <label for="confirm_password">Confirm New Password</label>
                    <input id="confirm_password" type="password" required>
                </div>
                <div class="buttons">
                    <button onclick="changePassword()">Save New Password</button>
                </div>
            </div>

            <script>
                function togglePasswordForm() {
                    const container = document.getElementById('change-password-container');
                    container.style.display = container.style.display === 'none' ? 'block' : 'none';
                }

                function changePassword() {
                    const currentPassword = document.getElementById('current_password').value;
                    const newPassword = document.getElementById('new_password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;

                    if (newPassword !== confirmPassword) {
                        alert('New passwords do not match!');
                        return;
                    }

                    const formData = new FormData();
                    formData.append("student_id", "<?= $student_id ?>");
                    formData.append("current_password", currentPassword);
                    formData.append("new_password", newPassword);

                    fetch("change_password.php", {
                        method: "POST",
                        body: formData,
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("Password changed successfully!");
                            // Optionally reset the form or close it
                            document.getElementById('change-password-container').style.display = 'none';
                        } else {
                            alert("Error: " + data.message);
                        }
                    })
                    .catch(error => console.error("Error:", error));
                }
            </script>

        </div>
        <div class="field">
          <label for="contact_no">Contact Number</label>
          <input id="contact_no" type="text" value="<?= $contact_no ?>" disabled>
          <i class="fas fa-edit" onclick="toggleEdit('contact_no')"></i>
        </div>
        <div class="field">
          <label for="admission_date">Date of Admission</label>
          <input id="admission_date" type="text" value="<?= $admission_date ?>" disabled>
        </div>
        <div class="field">
          <label for="address">Address</label>
          <input id="address" type="text" value="<?= $address ?>" disabled>
          <i class="fas fa-edit" onclick="toggleEdit('address')"></i>
        </div>
      </div>
    </div>
    <div class="buttons">
      <div style="display: flex; gap: 10px; justify-content: center;">
        <button onclick="saveChanges()" style="flex:1;">SAVE</button>
        <button type="button" onclick="window.history.back();" style="flex:1; background-color: #888;">BACK</button>
      </div>
    </div>
  </div>

  <!-- Success Modal -->
<div id="successModal" class="modal">
  <div class="modal-content success-modal">
    <div class="modal-icon">
      <img src="pix/account.png" alt="Success" style="width: 40px; height: 40px; filter: brightness(0) invert(1);">
    </div>
    <div class="modal-title">Profile Updated Successfully</div>
    <div class="modal-message">Your profile has been updated.</div>
    <button class="modal-button" onclick="closeModal('successModal')">OK</button>
  </div>
</div>

  <script>
    function toggleEdit(fieldId) {
      const field = document.getElementById(fieldId);
      field.disabled = !field.disabled;
      if (!field.disabled) field.focus();
    }

    function showSuccessModal() {
  document.getElementById('successModal').style.setProperty('display', 'block', 'important');
}

function closeModal(modalId) {
  document.getElementById(modalId).style.setProperty('display', 'none', 'important');
}

    function saveChanges() {
      const formData = new FormData();
      formData.append("student_id", "<?= $student_id ?>");
      formData.append("contact_no", document.getElementById("contact_no").value);
      formData.append("address", document.getElementById("address").value);

      const fileInput = document.getElementById("file-input");
      if (fileInput.files.length > 0) {
        formData.append("picture", fileInput.files[0]);
        console.log('Picture file:', fileInput.files[0]);
      } else {
        console.log('No picture selected');
      }

      fetch("save_profile.php", {
        method: "POST",
        body: formData,
      })
        .then(response => response.text())
        .then(text => {
          console.log('Server response:', text);
          let data;
          try {
            data = JSON.parse(text);
          } catch (e) {
            alert('Server error: ' + text);
            return;
          }
          if (data.success) {
            showSuccessModal();
            setTimeout(() => location.reload(), 1500);
          } else {
            alert("Error: " + data.message);
          }
        })
        .catch(error => {
          console.error("Error:", error);
          alert('Network error: ' + error);
        });
    }
  </script>
</body>
</html>
