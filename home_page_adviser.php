<?php
session_start(); // Start the session

// Check if the user is already logged in
if (!isset($_SESSION['username'])) {
    header("Location: adviser_login.php");
    exit();
}

$adviser_name = isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Homepage - Adviser</title>
  <style>
    body {
      background: url('pix/school.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      color: #333;
    }

    /* Title bar styling */
    .title-bar {
      background-color: #206018;
      color: #fff;
      padding: 10px;
      text-align: left;
      font-size: 20px;
      font-weight: bold;
      position: relative;
      display: flex;
      align-items: center;
    }

    .icon-bar {
      position: absolute;
      top: 10px;
      right: 15px;
      display: flex;
      gap: 15px;
    }

    .icon-bar img {
      width: 25px;
      height: 25px;
      cursor: pointer;
    }

    .title-bar img {
      height: 32px;
      width: auto;
      margin-right: 12px;
      vertical-align: middle;
    }

    .adviser-name {
      position: absolute;
      right: 2px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 20px;
      font-weight: 700;
      color: #facc41;
      font-family: 'Segoe UI', Arial, sans-serif;
      letter-spacing: 1px;
      background: rgba(32,96,24,0.15);
      padding: 6px 18px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(32,96,24,0.08);
    }

    /* Main content styling */
    .content {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 80vh;
    }
    .middle-section h1 {
        position: relative;
        left: 100px;
        color: rgb(0, 0, 0); /* Change color to white */
        font-weight: bolder; /* Make text bold */
        font-size: 24px; /* Adjust font size if needed */
        text-align: center; /* Center-align for aesthetics */
        margin-bottom: -46px; /* Add space below */
        margin-left: -800px;
    }

    .options {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 60px;
      background-color: rgba(255, 255, 255, 0.8);
      padding: 60px;
      border-radius: 10px;
      text-align: center;
    }

    .option {
      text-align: center;
      width: 163px;
      cursor: pointer;
    }

    .option img {
      width: 150px;
      height: 150px;
      margin-bottom: 10px;
    }

    .option label {
      font-size: 16px;
      display: block;
      font-weight: bold;
      margin-top: 5px;
    }
  </style>
</head>
<body>
  <!-- Title Bar -->
  <div class="title-bar" style="position: relative;">
    <img src="img/cav.png" alt="CvSU Logo">
    PRE - ENROLLMENT ASSESSMENT
    <span class="adviser-name"> <?= $adviser_name ; echo " | Adviser " ?> </span>
    <div class="icon-bar">
        </a>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="content">
    <div class="middle-section">
      <h1>Home</h1>
      <div class="options">
        <!-- Pending Accounts Option -->
        <div class="option">
          <img src="pix/update.png" alt="Update Checklist Icon" onclick="window.location.href='pending_accs_adviser.php'">
          <label>Pending Accounts</label>
        </div>
        <!-- Student's Checklist Option -->
        <div class="option">
          <img src="pix/checklist.png" alt="Preregistration Form Icon" onclick="window.location.href='checklist_eval_adviser.php'">
          <label>List of Student</label>
        </div>
        <!-- Sign Out Option -->
        <div class="option">
          <a href="logout_adviser.php" style="text-decoration: none;">
            <img src="pix/singout.png" alt="Sign Out Icon">
            <label>Sign Out</label>
          </a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>