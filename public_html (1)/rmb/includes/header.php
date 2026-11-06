<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>RMB CRM</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <div class="topbar-left">
        <span class="logo">RMB <span class="accent">CRM</span></span>
    </div>
    <?php if (isset($_SESSION['admin_id'])): ?>
    <nav class="topnav">
        <a href="dashboard.php">Dashboard</a>
        <a href="add_member.php">Add Member</a>
        <a href="logout.php" class="logout-link">Logout (<?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>)</a>
    </nav>
    <?php endif; ?>
</header>
<main class="container">
