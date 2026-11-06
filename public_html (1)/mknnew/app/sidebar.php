<?php
// sidebar.php - Include in every page after requireLogin()
$current_page = basename($_SERVER['PHP_SELF']);
function navItem($href, $icon, $label, $current) {
    $active = (basename($href) === $current) ? 'active' : '';
    echo "<a href='$href' class='nav-item $active'>
        <span class='nav-icon'>$icon</span>
        <span class='nav-label-text'>$label</span>
        </a>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title><?php echo isset($page_title) ? $page_title . ' - MKN Bro' : 'MKN Bro'; ?></title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
    background: #f4f6fa;
    min-height: 100vh;
    display: flex;
    overflow-x: hidden;
}

/* ========== SIDEBAR - PREMIUM CORPORATE THEME ========== */
#sidebar {
    width: 280px;
    background: linear-gradient(180deg, #0f2b3d 0%, #1a4a6f 100%);
    color: #fff;
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    box-shadow: 8px 0 25px -10px rgba(0, 0, 0, 0.15);
    transition: transform 0.3s ease-in-out;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.3) rgba(0,0,0,0.2);
}

#sidebar::-webkit-scrollbar {
    width: 5px;
}

#sidebar::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 10px;
}

#sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 10px;
}

/* Sidebar Header */
.sidebar-header {
    padding: 24px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    margin-bottom: 8px;
}

.logo-area h2 {
    font-size: 22px;
    font-weight: 700;
    letter-spacing: -0.3px;
    background: linear-gradient(135deg, #fff 0%, #e0f2fe 100%);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    margin-bottom: 4px;
}

.logo-area p {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.65);
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

/* Navigation Sections */
.nav-section {
    margin: 8px 0;
}

.nav-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: rgba(255, 255, 255, 0.5);
    padding: 12px 20px 6px 20px;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 20px;
    margin: 2px 12px;
    border-radius: 12px;
    color: rgba(255, 255, 255, 0.85);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
    position: relative;
}

.nav-item .nav-icon {
    font-size: 18px;
    width: 24px;
    text-align: center;
}

.nav-item:hover {
    background: rgba(255, 255, 255, 0.12);
    color: #fff;
    transform: translateX(4px);
}

.nav-item.active {
    background: rgba(255, 255, 255, 0.18);
    color: #fff;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    border-left: 3px solid #ffd966;
}

/* User Profile Footer */
.user-footer {
    margin-top: auto;
    padding: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: 16px;
}

.user-info-sidebar {
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(0, 0, 0, 0.2);
    padding: 10px 12px;
    border-radius: 14px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    background: rgba(255, 215, 0, 0.2);
    border-radius: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.user-details {
    flex: 1;
}

.user-details strong {
    font-size: 13px;
    display: block;
    color: #fff;
}

.user-details small {
    font-size: 10px;
    color: rgba(255, 255, 255, 0.6);
}

/* Mobile Menu Toggle */
.mobile-toggle {
    display: none;
    position: fixed;
    top: 16px;
    left: 16px;
    z-index: 1100;
    background: #0f2b3d;
    color: white;
    border: none;
    width: 44px;
    height: 44px;
    border-radius: 12px;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    align-items: center;
    justify-content: center;
}

/* Overlay for mobile */
.sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 999;
}

/* ========== MAIN CONTENT AREA ========== */
#main {
    flex: 1;
    margin-left: 280px;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    transition: margin-left 0.3s ease;
}

/* Topbar Premium */
#topbar {
    background: #ffffff;
    backdrop-filter: blur(0px);
    padding: 14px 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e9edf2;
    position: sticky;
    top: 0;
    z-index: 99;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.02);
}

#topbar .title {
    font-size: 18px;
    font-weight: 700;
    color: #1e4663;
    letter-spacing: -0.2px;
    background: linear-gradient(135deg, #1e4663 0%, #2c6288 100%);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 18px;
    font-size: 13px;
    background: #f8f9fc;
    padding: 6px 12px 6px 16px;
    border-radius: 40px;
    color: #2c3e50;
}

.user-info a {
    color: #c0392b;
    text-decoration: none;
    padding: 6px 14px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 12px;
    background: rgba(192, 57, 43, 0.08);
    transition: all 0.2s;
}

.user-info a:hover {
    background: #c0392b;
    color: white;
}

/* Content Area */
#content {
    padding: 28px;
    flex: 1;
}

/* Cards and UI Elements (keeping original but enhanced) */
.card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03), 0 1px 3px rgba(0, 0, 0, 0.05);
    padding: 22px;
    margin-bottom: 24px;
    border: 1px solid #edf2f7;
    transition: transform 0.1s ease;
}

.card h3 {
    font-size: 16px;
    font-weight: 700;
    color: #1e4663;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid #eef2f6;
}

/* Buttons Premium */
.btn {
    padding: 10px 22px;
    border-radius: 40px;
    font-weight: 600;
    transition: all 0.2s;
}
.btn-primary {
    background: #1e4663;
    color: white;
}
.btn-primary:hover {
    background: #0f2b3d;
    transform: translateY(-1px);
    box-shadow: 0 6px 12px rgba(30,70,99,0.2);
}

/* Table Styling */
table th {
    background: #f8fafd;
    color: #1e4663;
    font-weight: 700;
}

/* Stat boxes */
.stat-box {
    border-top: 4px solid #ffd966;
    border-radius: 20px;
}
.stat-box .val {
    color: #1e4663;
}

/* Responsive Design */
@media (max-width: 768px) {
    .mobile-toggle {
        display: flex;
    }
    
    #sidebar {
        transform: translateX(-100%);
        width: 280px;
        z-index: 1001;
    }
    
    #sidebar.mobile-open {
        transform: translateX(0);
    }
    
    .sidebar-overlay.active {
        display: block;
    }
    
    #main {
        margin-left: 0;
    }
    
    #topbar {
        padding: 12px 16px 12px 70px;
    }
    
    #content {
        padding: 16px;
    }
    
    .form-row {
        flex-direction: column;
        gap: 12px;
    }
    
    .filter-bar {
        flex-direction: column;
        align-items: stretch;
    }
    
    table, thead, tbody, th, td, tr {
        display: block;
    }
    table thead {
        display: none;
    }
    table tr {
        margin-bottom: 16px;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 12px;
    }
    table td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: none;
        padding: 8px 0;
    }
    table td:before {
        content: attr(data-label);
        font-weight: 700;
        width: 40%;
        color: #1e4663;
    }
}

@media (max-width: 480px) {
    .user-info span:first-child {
        display: none;
    }
}
</style>
<!-- Add Inter Font -->
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
</head>
<body>

<!-- Mobile Toggle Button -->
<button class="mobile-toggle" id="mobileToggleBtn">☰</button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div id="sidebar">
  <div class="sidebar-header">
    <div class="logo-area">
      <h2>✨ MKN Bro</h2>
      <p>Billing & Inventory System</p>
    </div>
  </div>
  
  <div class="nav-section">
    <div class="nav-label">Main</div>
    <?php navItem('dashboard.php','🏠','Dashboard',$current_page); ?>
  </div>
  
  <div class="nav-section">
    <div class="nav-label">Expenses</div>
    <?php navItem('expense_add.php','➕','Add Expense',$current_page); ?>
    <?php navItem('expense_dashboard.php','📊','Expense Dashboard',$current_page); ?>
  </div>
  
  <div class="nav-section">
    <div class="nav-label">Customers</div>
    <?php navItem('customer_add.php','👤','Add Customer',$current_page); ?>
    <?php navItem('customer_dashboard.php','👥','Customer Dashboard',$current_page); ?>
  </div>
  
  <div class="nav-section">
    <div class="nav-label">Products</div>
    <?php navItem('category_add.php','🏷️','Add Category',$current_page); ?>
    <?php navItem('category_dashboard.php','📂','Category Dashboard',$current_page); ?>
    <?php navItem('product_add.php','📦','Add Product',$current_page); ?>
    <?php navItem('product_dashboard.php','📋','Product Dashboard',$current_page); ?>
  </div>
  
  <div class="nav-section">
    <div class="nav-label">Stock</div>
    <?php navItem('stock_add.php','📥','Add Stock',$current_page); ?>
    <?php navItem('stock_dashboard.php','📈','Stock Dashboard',$current_page); ?>
    <?php navItem('stock_report.php','📊','Stock Report',$current_page); ?>
  </div>
  
  <div class="nav-section">
    <div class="nav-label">Sales</div>
    <?php navItem('sales_add.php','🧾','Add Sale',$current_page); ?>
    <?php navItem('sales_dashboard.php','💰','Sales Dashboard',$current_page); ?>
    <?php navItem('sales_report.php','📑','Sales Report',$current_page); ?>
  </div>
  
  <div class="user-footer">
    <div class="user-info-sidebar">
      <div class="user-avatar">👨‍💼</div>
      <div class="user-details">
        <strong><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></strong>
        <small>Administrator</small>
      </div>
    </div>
  </div>
</div>

<div id="main">
  <div id="topbar">
    <div class="title"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></div>
    <div class="user-info">
      <span>👋 <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></span>
      <a href="logout.php">Logout</a>
    </div>
  </div>
  <div id="content">

<!-- Mobile Responsive JavaScript -->
<script>
(function() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('mobileToggleBtn');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (toggleBtn && sidebar && overlay) {
        function closeSidebar() {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
        }
        
        function openSidebar() {
            sidebar.classList.add('mobile-open');
            overlay.classList.add('active');
        }
        
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (sidebar.classList.contains('mobile-open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
        
        overlay.addEventListener('click', closeSidebar);
        
        // Close on window resize above tablet
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        });
        
        // Optional: Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('mobile-open')) {
                closeSidebar();
            }
        });
    }
})();
</script>
<?php
// Close the content div, main div, body and html will be closed in footer or calling page
// This sidebar now works with scrolling, premium blue-gradient corporate colors, and full responsive behavior.
?>