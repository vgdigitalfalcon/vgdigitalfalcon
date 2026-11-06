<?php 
include 'auth.php';
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MKN Kadalaimittai & Snacks</title>
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
        
        .dashboard-header {
            margin-bottom: 2rem;
        }
        
        .dashboard-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }
        
        .dashboard-subtitle {
            color: var(--text-light);
            font-size: 1.1rem;
        }
        
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
        }
        
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        .dashboard-actions {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }
        
        .actions-title {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .action-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background-color: var(--secondary-color);
            border-radius: 6px;
            transition: var(--transition);
            text-decoration: none;
            color: var(--text-color);
        }
        
        .action-item:hover {
            background-color: rgba(230, 126, 34, 0.1);
            transform: translateY(-3px);
        }
        
        .action-icon {
            font-size: 1.5rem;
            margin-right: 12px;
            color: var(--primary-color);
        }
        
        .action-text {
            font-weight: 500;
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
        
        @media (max-width: 576px) {
            .header {
                padding: 0 1rem;
            }
            
            .content {
                padding: 1.5rem 1rem;
            }
            
            .welcome-text {
                display: none;
            }
            
            .quick-stats {
                grid-template-columns: 1fr;
            }
            
            .action-grid {
                grid-template-columns: 1fr;
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
                <div class="sidebar-company">MKN Kadalaimittai</div>
            </div>
            
            <div class="sidebar-nav">
                <a href="#" class="nav-item active">
                    <span class="nav-icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="users_list.php" class="nav-item">
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
                <div class="dashboard-header">
                    <h1 class="dashboard-title">Dashboard</h1>
                    <p class="dashboard-subtitle">Manage your MKN Kadalaimittai & Snacks operations</p>
                </div>
                
                <!-- Quick Stats -->
                <div class="quick-stats">
                    <div class="stat-card">
                        <div class="stat-icon">💰</div>
                        <div class="stat-value">₹12,584</div>
                        <div class="stat-label">Current Wallet Balance</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">📝</div>
                        <div class="stat-value">₹3,245</div>
                        <div class="stat-label">Monthly Expenses</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-value">8</div>
                        <div class="stat-label">Active Users</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">⚖️</div>
                        <div class="stat-value">₹9,339</div>
                        <div class="stat-label">Net Balance</div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="dashboard-actions">
                    <h2 class="actions-title">Quick Actions</h2>
                    
                    <div class="action-grid">
                        <a href="users_list.php" class="action-item">
                            <span class="action-icon">👥</span>
                            <span class="action-text">Manage Users</span>
                        </a>
                        
                        <a href="wallet_list.php" class="action-item">
                            <span class="action-icon">💰</span>
                            <span class="action-text">Wallet</span>
                        </a>
                        
                        <a href="expenses_list.php" class="action-item">
                            <span class="action-icon">📝</span>
                            <span class="action-text">Expenses</span>
                        </a>
                        
                        <a href="balance.php" class="action-item">
                            <span class="action-icon">📊</span>
                            <span class="action-text">Balance</span>
                        </a>
                    </div>
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