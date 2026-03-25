<?php
require_once 'config.php';

$error = '';

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
  header('Location: dashboard.php');
  exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_username'] = $username;
    header('Location: dashboard.php');
    exit;
  } else {
    $error = 'Invalid username or password';
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - Audio Guide System</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .login-container {
      background: white;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      max-width: 400px;
      width: 100%;
    }

    .login-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .login-header h1 {
      color: #333;
      margin-bottom: 10px;
    }

    .login-header p {
      color: #666;
      font-size: 14px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-bottom: 5px;
      color: #333;
      font-weight: 600;
    }

    input {
      width: 100%;
      padding: 12px;
      border: 2px solid #ddd;
      border-radius: 8px;
      font-size: 16px;
      transition: border-color 0.3s;
    }

    input:focus {
      outline: none;
      border-color: #667eea;
    }

    .login-btn {
      width: 100%;
      padding: 15px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: transform 0.2s;
    }

    .login-btn:hover {
      transform: translateY(-2px);
    }

    .error {
      background: #fee;
      color: #c33;
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 20px;
      border: 1px solid #fcc;
    }

    .icon {
      font-size: 60px;
      margin-bottom: 20px;
    }
  </style>
</head>

<body>
  <div class="login-container">
    <div class="login-header">
      <div class="icon">🔐</div>
      <h1>Admin Login</h1>
      <p>Audio Guide Management System</p>
    </div>

    <?php if ($error): ?>
      <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required autofocus>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>

      <button type="submit" class="login-btn">Login</button>
    </form>
  </div>
</body>

</html>