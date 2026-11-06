<?php
// ============================================
// ONE-TIME SETUP SCRIPT
// Run this once in your browser to create the first admin login.
// DELETE THIS FILE from the server after use.
// ============================================
require_once 'includes/config.php';

$message = '';
$done = false;

// Prevent creating duplicate setup if an admin already exists
$existing = $conn->query("SELECT COUNT(*) AS c FROM admins")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $full_name = trim($_POST['full_name']);

    if ($username === '' || $password === '') {
        $message = 'Username and password are required.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admins (username, password, full_name) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $username, $hash, $full_name);
        if ($stmt->execute()) {
            $done = true;
        } else {
            $message = 'Error: ' . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Setup Admin - RMB CRM</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="login-wrapper">
    <h1>Create Admin Account</h1>

    <?php if ($existing['c'] > 0): ?>
        <div class="alert alert-error">
            An admin account already exists. For security, please delete this file (setup_admin.php) from the server.
        </div>
    <?php elseif ($done): ?>
        <div class="alert alert-success">
            Admin account created successfully! You can now <a href="login.php">log in here</a>.<br><br>
            <strong>Important:</strong> Please delete setup_admin.php from the server now.
        </div>
    <?php else: ?>
        <?php if ($message): ?><div class="alert alert-error"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <form method="POST">
            <label>Full Name</label>
            <input type="text" name="full_name" placeholder="Administrator">

            <label>Username</label>
            <input type="text" name="username" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit" class="btn btn-full">Create Admin</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
