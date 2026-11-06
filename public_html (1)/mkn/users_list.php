<?php 
include 'db.php';
include 'auth.php';
// Start session and check authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch users from database
$result = $conn->query("SELECT * FROM users ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users List - MKN Kadalaimittai & Snacks</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        :root {
            --primary-color: #e67e22;
            --primary-dark: #d35400;
            --secondary-color: #f8f9fa;
            --text-color: #333;
            --text-light: #777;
            --sidebar-width: 250px;
            --header-height: 60px;
            --transition: all 0.3s ease;
        }
        
        body {
            background-color: #f5f5f5;
            color: var(--text-color);
            line-height: 1.6;
        }
        
        /* Layout */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
            color: white;
            position: fixed;
            height: 100vh;
            transition: var(--transition);
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
        }
        
        .sidebar-logo {
            font-size: 1.8rem;
            margin-right: 10px;
        }
        
        .sidebar-company {
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .sidebar-nav {
            padding: 1.5rem 0;
        }
        
        .nav-item {
            padding: 0.9rem 1.5rem;
            display: flex;
            align-items: center;
            transition: var(--transition);
            cursor: pointer;
            text-decoration: none;
            color: white;
        }
        
        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .nav-item.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 4px solid white;
        }
        
        .nav-icon {
            margin-right: 12px;
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: var(--transition);
        }
        
        /* Header */
        .header {
            background-color: white;
            height: var(--header-height);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .menu-toggle {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-color);
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .welcome-text {
            margin-right: 15px;
            font-weight: 500;
        }
        
        .logout-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: var(--transition);
        }
        
        .logout-btn:hover {
            background-color: var(--primary-dark);
        }
        
        /* Content Area */
        .content {
            padding: 2rem;
        }
        
        .page-header {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .page-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--text-color);
        }
        
        .page-subtitle {
            color: var(--text-light);
            font-size: 1.1rem;
            width: 100%;
        }
        
        .add-btn {
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            padding: 0.7rem 1.2rem;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            transition: var(--transition);
        }
        
        .add-btn:hover {
            background-color: var(--primary-dark);
        }
        
        .add-btn-icon {
            margin-right: 8px;
        }
        
        /* Table Styles */
        .table-container {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            overflow-x: auto;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            background-color: #f8f9fa;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--text-color);
            border-bottom: 1px solid #eee;
        }
        
        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .data-table tr:last-child td {
            border-bottom: none;
        }
        
        .data-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn {
            padding: 0.5rem 0.8rem;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            transition: var(--transition);
        }
        
        .btn-view {
            background-color: #17a2b8;
            color: white;
        }
        
        .btn-view:hover {
            background-color: #138496;
        }
        
        .btn-edit {
            background-color: #ffc107;
            color: #212529;
        }
        
        .btn-edit:hover {
            background-color: #e0a800;
        }
        
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #c82333;
        }
        
        .btn-icon {
            margin-right: 5px;
        }
        
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--text-light);
        }
        
        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        /* Responsive Styles */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                width: 70%;
                max-width: 300px;
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 999;
                display: none;
            }
            
            .overlay.open {
                display: block;
            }
        }
        
        @media (max-width: 768px) {
            .content {
                padding: 1.5rem;
            }
            
            .data-table {
                min-width: 600px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
        
        @media (max-width: 576px) {
            .header {
                padding: 0 1rem;
            }
            
            .content {
                padding: 1rem;
            }
            
            .welcome-text {
                display: none;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">🥜</div>
                <div class="sidebar-company">MKN Snacks</div>
            </div>
            
            <div class="sidebar-nav">
                <a href="index.php" class="nav-item">
                    <span class="nav-icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="users_list.php" class="nav-item active">
                    <span class="nav-icon">👥</span>
                    <span>Manage Users</span>
                </a>
                <a href="wallet_list.php" class="nav-item">
                    <span class="nav-icon">💰</span>
                    <span>Wallet</span>
                </a>
                <a href="expenses_list.php" class="nav-item">
                    <span class="nav-icon">📝</span>
                    <span>Expenses</span>
                </a>
                <a href="balance.php" class="nav-item">
                    <span class="nav-icon">⚖️</span>
                    <span>Balance</span>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <div class="menu-toggle" id="menuToggle">☰</div>
                <div class="user-info">
                    <div class="welcome-text">Welcome, <?php echo $_SESSION['name']; ?> 👋</div>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Content Area -->
            <div class="content">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Users List</h1>
                        <p class="page-subtitle">Manage all users in the MKN Kadalaimittai & Snacks system</p>
                    </div>
                    <a href="add_user.php" class="add-btn">
                        <span class="add-btn-icon">➕</span>
                        <span>Add New User</span>
                    </a>
                </div>
                
                <!-- Users Table -->
                <div class="table-container">
                    <?php if ($result->num_rows > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="view_user.php?id=<?php echo $row['id']; ?>" class="btn btn-view">
                                                <span class="btn-icon">👁️</span>
                                                <span>View</span>
                                            </a>
                                            <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">
                                                <span class="btn-icon">✏️</span>
                                                <span>Edit</span>
                                            </a>
                                            <a href="delete_user.php?id=<?php echo $row['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this user?')">
                                                <span class="btn-icon">🗑️</span>
                                                <span>Delete</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">👥</div>
                            <h3>No Users Found</h3>
                            <p>Get started by adding your first user</p>
                            <a href="add_user.php" class="add-btn" style="margin-top: 1rem;">
                                <span class="add-btn-icon">➕</span>
                                <span>Add New User</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Mobile overlay -->
        <div class="overlay" id="overlay"></div>
    </div>

    <script>
        // Toggle sidebar on mobile
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        });
        
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        });
    </script>
</body>
</html>