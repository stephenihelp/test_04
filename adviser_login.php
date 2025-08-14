<?php
session_start(); // Start the session

// Check if the user is already logged in
if (isset($_SESSION['username'])) {
    header("Location: home_page_adviser.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Adviser Login</title>
  <style>
    body {
      background: url('pix/school.jpg') no-repeat center center fixed;
      background-size: cover;
      background-position: center;
      font-family: sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin-bottom: -100px;
      gap: 40px;
    }

    .background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
        opacity: 0.2;
    }

    .login-container {
        background-color: white;
        padding: 35px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.8);
        text-align: center;
        border-radius: 4px;
        width: 300px;
    }

    .login-container h2 {
        margin-bottom: 20px;
        font-size: 26px;
        color: #333;
    }

    .login-container input[type="text"],
    .login-container input[type="password"] {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
    }

    .login-container button {
        width: 100%;
        padding: 10px;
        background-color: #2d572c;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
    }

    .login-container button:hover {
        background-color: #244a23;
    }

    .login-container hr {
        margin: 20px 0;
        border: 0;
        border-top: 1px solid #ccc;
    }
  </style>
</head>
<body>
  <img class="background" height="1080" width="1920"/>
  <div class="login-container">
    <h2>ADVISER LOG IN</h2>
    <form id="loginForm" action="adviser_login_process.php" method="post">
      <input type="text" name="username" placeholder="Enter username" required>
      <input type="password" name="password" placeholder="Enter password" required>
      <button type="submit">Login</button>
    </form>
    <hr>
  </div>
</body>
</html>
