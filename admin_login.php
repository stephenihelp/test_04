<!DOCTYPE html>
<html>
<head>
  <title>Admin login</title>
  <style>
    html, body {
      height: 100vh;
      margin: 0;
      padding: 0;
      overflow: hidden;
    }
    body {
      background: url('pix/school.jpg') no-repeat;
      background-size: cover;
      background-position: center;
      font-family: sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
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
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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
        /* Modal Styles */
    .modal {
      display: none !important;
      position: fixed !important;
      z-index: 1000 !important;
      left: 0 !important;
      top: 0 !important;
      width: 100% !important;
      height: 100% !important;
      background-color: rgba(0, 0, 0, 0.5) !important;
      animation: fadeIn 0.3s ease-in-out !important;
    }
    .modal-content {
      margin: 150px auto !important;
      padding: 12px !important;
      border-radius: 15px !important;
      width: 90% !important;
      max-width: 400px !important;
      text-align: center !important;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
      animation: slideIn 0.4s ease-out !important;
      position: relative !important;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg,rgb(220, 235, 53), #206018) !important;
    }
    .modal-icon {
      width: 80px;
      height: 80px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 50%;
      margin: 0 auto 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 40px;
      color: white;
      font-weight: bold;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
      border: 3px solid rgba(255, 255, 255, 0.3);
    }
    .modal-title {
      color: white !important;
      font-size: 24px !important;
      font-weight: bold !important;
      margin-bottom: 10px !important;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important;
    }
    .modal-message {
      color: rgba(255, 255, 255, 0.9) !important;
      font-size: 16px !important;
      margin: 12px !important;
      line-height: 1.5 !important;
      text-align: center;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .modal-button {
      background: rgba(255, 255, 255, 0.2);
      color: white;
      border: 2px solid rgba(255, 255, 255, 0.3);
      padding: 12px 30px;
      border-radius: 25px;
      cursor: pointer;
      font-size: 16px;
      font-weight: bold;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }
    .modal-button:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    </style>
</head>
<body>
  <img class="background" height="1080" width="1920"/>
  <div class="login-container">
    <h2>ADMIN LOG IN</h2>
    <form id = "loginForm" action="admin_login_process.php" method="post">
    <input type="text" name="username" placeholder="Enter username" required>
    <input type="password" name="password" placeholder="Enter password" required>
    <button type="submit">Login</button>
</form>


    <hr/>
  </div>

  <!-- Success Modal -->
<div id="successModal" class="modal">
  <div class="modal-content">
    <div class="modal-icon">
      <i class="fas fa-check" style="color: white;"></i>
    </div>
    <div class="modal-title">Login Successful</div>
    <div class="modal-message">Welcome, Admin! Redirecting to dashboard...</div>
  </div>
</div>

  <script>
    // No JS needed for login, let the form submit normally
  </script>
</body>
</html>