<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php"); exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($conn, $_POST['username']);
    $password = sanitize($conn, $_POST['password']);
    $res = $conn->query("SELECT * FROM users WHERE username='$username' AND password='$password'");
    if ($res && $res->num_rows > 0) {
        $user = $res->fetch_assoc();
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role'];
        header("Location: dashboard.php"); exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login - InventoryPro</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Segoe UI',Arial,sans-serif;background:linear-gradient(135deg,#1a237e 0%,#283593 50%,#3949ab 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;}
.login-box{background:#fff;border-radius:12px;padding:40px;width:380px;box-shadow:0 10px 40px rgba(0,0,0,0.2);}
.login-box h1{font-size:22px;color:#1a237e;text-align:center;margin-bottom:4px;}
.login-box p{text-align:center;color:#888;font-size:13px;margin-bottom:24px;}
.form-group{margin-bottom:16px;}
.form-group label{display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:5px;text-transform:uppercase;letter-spacing:0.5px;}
.form-group input{width:100%;padding:11px 14px;border:1px solid #ddd;border-radius:6px;font-size:14px;transition:border 0.2s;}
.form-group input:focus{outline:none;border-color:#1a237e;}
.btn-login{width:100%;padding:12px;background:linear-gradient(90deg,#1a237e,#3949ab);color:#fff;border:none;border-radius:6px;font-size:15px;font-weight:600;cursor:pointer;margin-top:8px;}
.btn-login:hover{opacity:0.9;}
.alert-error{background:#ffebee;color:#c62828;border-left:4px solid #c62828;padding:10px 14px;border-radius:5px;font-size:13px;margin-bottom:16px;}
.logo-icon{text-align:center;font-size:48px;margin-bottom:12px;}
.setup-link{text-align:center;margin-top:16px;font-size:12px;color:#aaa;}
.setup-link a{color:#1a237e;}
</style>
</head>
<body>
<div class="login-box">
  <div class="logo-icon">📦</div>
  <h1>InventoryPro</h1>
  <p>Billing & Inventory Management System</p>
  <?php if ($error): ?>
    <div class="alert-error">⚠️ <?php echo $error; ?></div>
  <?php endif; ?>
  <form method="POST">
    <div class="form-group">
      <label>Username</label>
      <input type="text" name="username" required autofocus placeholder="Enter username">
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" required placeholder="Enter password">
    </div>
    <button type="submit" class="btn-login">🔐 Login</button>
  </form>
</div>
</body>
</html>
