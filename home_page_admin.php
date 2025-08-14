<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin</title>
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
      padding: 6px;
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

    /* Main content styling */
    .content {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 80vh;
    }
    .middle-section h1 {
      position: relative;
      left: -100px;
      color: rgb(0, 0, 0); /* Change color to white */
      font-weight: bolder; /* Make text bold */
      font-size: 30px; /* Adjust font size if needed */
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
  <div class="title-bar">
    <img src="img/cav.png" alt="CvSU Logo" style="height: 32px; width: auto; margin-right: 12px;">
        PRE - ENROLLMENT ASSESSMENT
    <div class="icon-bar">
      <!-- Removed sign out icon from title bar, now in options section -->
    </div>
  </div>

  <!-- Main Content -->
  <div class="content">
    <div class="middle-section">
      <h1>Home</h1>
      <div class="options">
        <!-- Account Manager Option -->
        <div class="option">
          <img src="pix/account.png" onclick="window.location.href='adviser_input_form.html'" alt="Account Manager Icon">
          <label>Create Adviser Account</label>
        </div>
        <!-- Account Manager Option -->
        <div class="option">
          <img src="pix/account.png" onclick="window.location.href='admin_input_form.html'" alt="Account Manager Icon">
          <label>Create Admin Account</label>
        </div>
        <!-- List of Students Option -->
        <div class="option">
          <img src="pix/generic_user.svg" onclick="window.location.href='list_of_students.php'" alt="List of Students Icon">
          <label>List of students</label>
        </div>
        <!-- Settings Option -->
        <div class="option">
          <img src="pix/set.png" onclick="window.location.href='settings.html'" alt="Settings Icon">
          <label>Settings</label>
        </div>
        <!-- Sign Out Option -->
        <div class="option">
          <img src="pix/singout.png" onclick="window.location.href='logout_admin.php'" alt="Sign Out" title="Sign Out">
          <label>Sign Out</label>
        </div>
      </div>
    </div>
  </div>
</body>
</html>