<?php
// Adviser view of student profile (read-only)
session_start();

// Get student_id from URL parameter
if (!isset($_GET['student_id'])) {
    echo "<script>alert('No student ID provided.'); window.history.back();</script>";
    exit();
}
$student_id = $_GET['student_id'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "e_checklist";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch student details
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "<script>alert('Student not found.'); window.history.back();</script>";
    exit();
}
$row = $result->fetch_assoc();
$last_name = htmlspecialchars($row['last_name']);
$first_name = htmlspecialchars($row['first_name']);
$middle_name = htmlspecialchars($row['middle_name']);
$picture = htmlspecialchars($row['picture']);
$contact_no = htmlspecialchars($row['contact_no']);
$address = htmlspecialchars($row['address']);
$admission_date = htmlspecialchars($row['admission_date']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Profile (Adviser View)</title>
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
      padding: 5px 14px;
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
    .header img {
      height: 24px; /* Reduce the height of the logo */
      width: auto;
      margin-right: 12px;
      vertical-align: middle;
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
    .profile .details .field input:disabled {
      background: #f4f4f4;
      color: #888;
    }
    .profile .details .field input:focus {
      border: 1.5px solid #206018;
      outline: none;
      background: #fff;
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
  </style>
</head>
<body>
  <div class="header">
    <img src="img/cav.png" alt="CvSU Logo" style="height:32px; width:auto; margin-right:12px; vertical-align:middle;">
    <h1 style="margin: 0; font-size: 24px; letter-spacing: 1px; flex:1;"> PRE - ENROLLMENT ASSESSMENT</h1>
    <div class="icons">
      <img src="pix/home1.png" onclick="window.location.href='home_page_student.php'" alt="Home Icon">
    </div>
  </div>

  <div class="container">
    <div class="title">Student Profile (Adviser View)</div>
    <div class="subtitle">Viewing student account details</div>
    <hr class="divider" />
    <div class="profile">
      <div class="photo">
        <img id="profile-pic" src="<?= $picture ?>" alt="Profile Photo" />
      </div>
      <div class="details">
        <div class="field">
          <label>Last Name</label>
          <input type="text" value="<?= $last_name ?>" disabled>
        </div>
        <div class="field">
          <label>First Name</label>
          <input type="text" value="<?= $first_name ?>" disabled>
        </div>
        <div class="field">
          <label>Middle Name</label>
          <input type="text" value="<?= $middle_name ?>" disabled>
        </div>
        <div class="field">
          <label>Student Number</label>
          <input type="text" value="<?= $student_id ?>" disabled>
        </div>
        <div class="field">
          <label>Contact Number</label>
          <input type="text" value="<?= $contact_no ?>" disabled>
        </div>
        <div class="field">
          <label>Date of Admission</label>
          <input type="text" value="<?= $admission_date ?>" disabled>
        </div>
        <div class="field">
          <label>Address</label>
          <input type="text" value="<?= $address ?>" disabled>
        </div>
      </div>
    </div>
    <div class="buttons">
      <button type="button" onclick="window.history.back();" style="background-color: #888;">BACK</button>
    </div>
  </div>
</body>
</html>
