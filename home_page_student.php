<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.html");
    exit();
}

// Retrieve user details from the session
$last_name = $_SESSION['last_name'];
$first_name = $_SESSION['first_name'];
$middle_name = $_SESSION['middle_name'];
$picture = $_SESSION['picture'];
$student_id = $_SESSION['student_id']
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Homepage</title>
  <style>
    body {
      background: url('pix/school.jpg') no-repeat center;
      opacity: 2;
      background-size: cover;
      font-family: Arial, sans-serif;
      margin: 0px;
      padding: 0;
      color: #333;
    }

    /* Title bar styling */
    .title-bar {
      background-color: #206018;
      color: #fff;
      padding: 10px 20px;
      text-align: left;
      font-size: 20px;
      font-weight: bold;
      display: flex;
      align-items: center;
    }

    .icon-bar {
      position: absolute;
      top: 15px;
      right: 15px;
      display: flex;
      gap: 15px;
    }

    .icon-bar img {
      width: 25px;
      height: 25px;
      cursor: pointer;
    }

    /* Main content styling */
    .content {
      display: flex;
      justify-content: center;
      align-items: center;
      height: calc(100vh - 60px); /* Adjust height to fill screen minus title bar */
    }

    .options {
      position: relative;
      top:-18px;
      left: 0px;
      display: flex;
      grid-template-columns: repeat(2, 1fr); /* Create a 2x2 grid */
      gap: 30px;
      background-color: rgba(255, 255, 255, 0.8);
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
      padding:65px;
      border-radius: 10px;
      text-align: center;
      max-width: 500px;
    }

    /* "Home" header styling */
    .options h1 {
      position: absolute;
      top: 17px;
      left: 35px;
      font-size: 23px;
      margin: 0;
      color: #000000;
    }

    .option {
      text-align: center;
      cursor: pointer;
    }

    .option img {
      position: relative;
      left: 10px;
      width: 140px;
      height: 140px;
      margin-bottom: 10px;
    }

    .option label {
      position: relative;
      left: 10px;
      font-size: 16px;
      display: block;
      font-weight: bold;
      margin-top: 5px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .options {
        grid-template-columns: 1fr; /* Stack items vertically on small screens */
        padding: 20px;
      }
    }


  </style>
</head>
<body>
  <!-- Title Bar -->
  <div class="title-bar">
    <img src="img/cav.png" alt="CvSU Logo" style="height:32px; width:auto; margin-right:12px; vertical-align:middle;">
    <span style="flex:1;">    PRE - ENROLLMENT ASSESSMENT
</span>
    <div class="icon-bar">
      <img src="<?= htmlspecialchars($picture) ?>" alt="Profile Picture">
      <span><?= htmlspecialchars("$last_name, $first_name $middle_name") ; echo " | Student "; ?></span>
    </div>
  </div>
    </div>
  </div>
    </div>  <!-- Main Content -->
  <div class="content">
    <div class="middle-section">
      <div class="options">
        <!-- Header positioned in the top-left corner of the options container -->
        <h1>Home</h1>
        <!-- Update Checklist Option -->
        <div class="option">
          <img src="pix/update.png" onclick="window.location.href='checklist_stud.php'" alt="Update Checklist Icon">
          <label> Checklist</label>
        </div>
        <!-- Account Manager Option -->
        <div class="option">
          <img src="pix/account.png" onclick="window.location.href='acc_mng.php'"alt="Account Manager Icon">
          <label>Your Profile</label>
        </div>
        <!-- Sign Out Option -->
        <div class="option">
          <a href="signout.php" style="text-decoration: none;">
          <img src="pix/singout.png" alt="Sign Out Icon">
          <label>Sign Out</label>
        </div>
         
      </div>
    </div>
  </div>
</body>
</html>