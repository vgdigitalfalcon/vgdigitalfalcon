<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Initialize filter variables with session persistence
$search = isset($_GET['search']) ? $_GET['search'] : (isset($_SESSION['purchase_search']) ? $_SESSION['purchase_search'] : '');
$sort = isset($_GET['sort']) ? $_GET['sort'] : (isset($_SESSION['purchase_sort']) ? $_SESSION['purchase_sort'] : 'date');
$order = isset($_GET['order']) ? $_GET['order'] : (isset($_SESSION['purchase_order']) ? $_SESSION['purchase_order'] : 'DESC');
$month_filter = isset($_GET['month']) ? $_GET['month'] : (isset($_SESSION['purchase_month']) ? $_SESSION['purchase_month'] : '');
$year_filter = isset($_GET['year']) ? $_GET['year'] : (isset($_SESSION['purchase_year']) ? $_SESSION['purchase_year'] : date('Y'));
$results_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;

// Store filter preferences in session
$_SESSION['purchase_search'] = $search;
$_SESSION['purchase_sort'] = $sort;
$_SESSION['purchase_order'] = $order;
$_SESSION['purchase_month'] = $month_filter;
$_SESSION['purchase_year'] = $year_filter;

// Validate sort column to prevent SQL injection
$allowed_sorts = ['id', 'item_name', 'amount', 'unit_type', 'quantity', 'date'];
if (!in_array($sort, $allowed_sorts)) {
    $sort = 'date';
}

// Validate order direction
$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

// Build the base query
$query = "SELECT p.id, 
                 COALESCE(pr.product_name, p.purchase_name) AS item_name,
                 p.amount, p.unit_type, p.quantity, p.date
          FROM purchases p
          LEFT JOIN products pr ON p.product_id = pr.id";
$count_query = "SELECT COUNT(*) as total 
                FROM purchases p
                LEFT JOIN products pr ON p.product_id = pr.id";

// Add search filter if provided
$where_clauses = [];
if (!empty($search)) {
    $search_term = $conn->real_escape_string($search);
    $where_clauses[] = "(pr.product_name LIKE '%$search_term%' OR p.purchase_name LIKE '%$search_term%')";
}

// Add month/year filter if provided
if (!empty($month_filter) && !empty($year_filter)) {
    $where_clauses[] = "MONTH(p.date) = '$month_filter' AND YEAR(p.date) = '$year_filter'";
}

if (count($where_clauses) > 0) {
    $where = " WHERE " . implode(" AND ", $where_clauses);
    $query .= $where;
    $count_query .= $where;
}

// Add sorting
$query .= " ORDER BY $sort $order";

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $results_per_page;
$query .= " LIMIT $offset, $results_per_page";

// Execute queries
$results = $conn->query($query);
$count_result = $conn->query($count_query);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $results_per_page);

// Ensure page doesn't exceed total pages
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
    $offset = ($page - 1) * $results_per_page;
    $query = substr($query, 0, strpos($query, "LIMIT")) . " LIMIT $offset, $results_per_page";
    $results = $conn->query($query);
}

// Get monthly statistics
// Get monthly statistics
$current_month = date('m');
$current_year = date('Y');
$monthly_stats_query = "
    SELECT 
        COUNT(*) as purchase_count,
        COALESCE(SUM(p.amount), 0) as total_amount
    FROM purchases p
    WHERE MONTH(p.date) = '$current_month' AND YEAR(p.date) = '$current_year'
";
$monthly_stats_result = $conn->query($monthly_stats_query);
$monthly_stats = $monthly_stats_result->fetch_assoc();

// Get available years for filter dropdown
$years_query = "SELECT DISTINCT YEAR(date) as year FROM purchases ORDER BY year DESC";
$years_result = $conn->query($years_query);
$years = [];
while ($row = $years_result->fetch_assoc()) {
    $years[] = $row['year'];
}

// Handle export functionality
if (isset($_GET['export'])) {
    $format = $_GET['export'];
    $export_month = isset($_GET['export_month']) ? $_GET['export_month'] : '';
    $export_year = isset($_GET['export_year']) ? $_GET['export_year'] : '';
    
    // Build export query
    $export_query = "SELECT p.id, 
                            COALESCE(pr.product_name, p.purchase_name) AS item_name,
                            p.amount, p.unit_type, p.quantity, p.date
                     FROM purchases p
                     LEFT JOIN products pr ON p.product_id = pr.id";
    
    $export_where_clauses = [];
    if (!empty($search)) {
        $search_term = $conn->real_escape_string($search);
        $export_where_clauses[] = "(pr.product_name LIKE '%$search_term%' OR p.purchase_name LIKE '%$search_term%')";
    }
    
    if (!empty($export_month) && !empty($export_year)) {
        $export_where_clauses[] = "MONTH(p.date) = '$export_month' AND YEAR(p.date) = '$export_year'";
    }
    
    if (count($export_where_clauses) > 0) {
        $export_query .= " WHERE " . implode(" AND ", $export_where_clauses);
    }
    
    $export_query .= " ORDER BY $sort $order";
    
    $export_results = $conn->query($export_query);
    
    if ($format === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=purchases_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($output, array('ID', 'Item Name', 'Amount', 'Unit Type', 'Quantity', 'Date'));
        
        // Add data rows
        while ($row = $export_results->fetch_assoc()) {
            fputcsv($output, array(
                $row['id'],
                $row['item_name'],
                $row['amount'],
                $row['unit_type'],
                $row['quantity'],
                $row['date']
            ));
        }
        
        fclose($output);
        exit();
    } elseif ($format === 'excel') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=purchases_' . date('Y-m-d') . '.xls');
        
        echo "<table border='1'>";
        echo "<tr>
                <th>ID</th>
                <th>Item Name</th>
                <th>Amount</th>
                <th>Unit Type</th>
                <th>Quantity</th>
                <th>Date</th>
              </tr>";
        
        while ($row = $export_results->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['item_name']}</td>
                    <td>{$row['amount']}</td>
                    <td>{$row['unit_type']}</td>
                    <td>{$row['quantity']}</td>
                    <td>{$row['date']}</td>
                  </tr>";
        }
        
        echo "</table>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchases List - Financial Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        :root {
            --primary-dark: #0F1440;
            --primary-medium: #067ABF;
            --primary-light: #4A90E2;
            --dark-text: #212121;
            --light-text: #FFFFFF;
            --gray-light: #F5F5F5;
            --gray-medium: #E0E0E0;
            --success: #4CAF50;
            --warning: #FFC107;
            --danger: #F44336;
            --info: #2196F3;
            --section-border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            color: var(--dark-text);
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: var(--primary-dark);
            color: var(--light-text);
            transition: all 0.3s ease;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.2);
            text-align: center;
            border-bottom: var(--section-border);
        }
        
        .sidebar-header h3 {
            font-size: 1.5rem;
            margin: 0;
            color: var(--light-text);
        }
        
        .sidebar-menu {
            padding: 15px 0;
        }
        
        .menu-section {
            margin-bottom: 20px;
        }
        
        .menu-title {
            padding: 10px 20px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.7);
            border-bottom: var(--section-border);
            margin-bottom: 10px;
        }
        
        .sidebar-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin: 3px 0;
        }
        
        .sidebar-menu a {
            color: var(--light-text);
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 12px 20px 12px 30px;
            transition: all 0.3s;
            font-size: 1rem;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: var(--primary-medium);
            border-left: 4px solid var(--primary-light);
        }
        
        .sidebar-menu i {
            margin-right: 12px;
            font-size: 18px;
            width: 24px;
            text-align: center;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            transition: margin-left 0.3s ease;
        }
        
        /* Top Bar */
        .top-bar {
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-medium);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .logout-btn {
            background: var(--primary-medium);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: var(--primary-light);
        }
        
        .logout-btn i {
            margin-right: 5px;
        }
        
        /* Content Area */
        .content {
            padding: 30px;
        }
        
        .page-title {
            margin-bottom: 25px;
            color: var(--primary-dark);
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .page-title i {
            margin-right: 10px;
            color: var(--primary-medium);
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 6px;
            font-weight: 500;
            display: flex;
            align-items: center;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: var(--primary-medium);
        }
        
        .btn-primary:hover {
            background: var(--primary-light);
        }
        
        .btn-success {
            background: var(--success);
        }
        
        .btn-success:hover {
            background: #3d8b40;
        }
        
        .btn-info {
            background: var(--info);
        }
        
        .btn-info:hover {
            background: #0b7dda;
        }
        
        .btn i {
            margin-right: 6px;
        }

        /* Toggle Filters Button */
        .toggle-filters {
            background: var(--primary-medium);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        
        .toggle-filters:hover {
            background: var(--primary-light);
        }
        
        .toggle-filters i {
            margin-right: 8px;
        }
        
        /* Filters Container */
        .filters-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            padding: 20px;
            margin-bottom: 25px;
            display: none; /* Hidden by default */
        }
        
        .filters-container.active {
            display: block;
        }
        
        .filters-title {
            font-size: 1.2rem;
            color: var(--primary-dark);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .filters-title i {
            margin-right: 10px;
            color: var(--primary-medium);
        }
        
        .clear-filters {
            color: var(--danger);
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }
        
        .clear-filters:hover {
            text-decoration: underline;
        }
        
        .filters-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-text);
        }
        
        .filter-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }
        
        .filter-select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            background: white;
        }
        
        .filter-btn {
            background: var(--primary-medium);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
            display: flex;
            align-items: center;
        }
        
        .filter-btn:hover {
            background: var(--primary-light);
        }
        
        .filter-btn i {
            margin-right: 8px;
        }
        
        /* Quick Stats */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        
        .stat-title {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-dark);
        }
        
        .stat-change {
            margin-top: 10px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }
        
        .positive {
            color: var(--success);
        }
        
        .negative {
            color: var(--danger);
        }
        
        /* Table Container */
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            padding: 20px;
            position: relative;
        }
        
        /* Table Scroll Container */
        .table-scroll-container {
            overflow-x: auto;
            max-width: 100%;
            margin-bottom: 20px;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .table-actions {
            display: flex;
            gap: 10px;
        }
        
        .table-action-btn {
            background: var(--gray-light);
            border: 1px solid var(--gray-medium);
            border-radius: 4px;
            padding: 8px 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }
        
        .table-action-btn:hover {
            background: var(--gray-medium);
        }
        
        .table-action-btn i {
            margin-right: 5px;
        }
        
        .purchases-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            min-width: 1000px;
        }
        
        .purchases-table th {
            background-color: var(--primary-medium);
            color: white;
            text-align: left;
            padding: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            position: relative;
            white-space: nowrap;
        }
        
        .purchases-table th:hover {
            background-color: var(--primary-light);
        }
        
        .purchases-table th i {
            margin-left: 5px;
        }
        
        .purchases-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            white-space: nowrap;
        }
        
        .purchases-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .purchases-table tr:hover {
            background-color: #f1f7ff;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .rupee-symbol {
            font-family: Arial, sans-serif;
        }
        
        .action-cell {
            display: flex;
            gap: 8px;
        }
        
        .action-btn {
            padding: 5px 8px;
            border-radius: 4px;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            font-size: 0.8rem;
            cursor: pointer;
        }
        
        .view-btn {
            background: var(--primary-medium);
        }
        
        .view-btn:hover {
            background: var(--primary-light);
        }
        
        .edit-btn {
            background: var(--info);
        }
        
        .edit-btn:hover {
            background: #0b7dda;
        }
        
        .delete-btn {
            background: var(--danger);
        }
        
        .delete-btn:hover {
            background: #d32f2f;
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .pagination-controls {
            display: flex;
            gap: 8px;
        }
        
        .pagination a, .pagination span {
            padding: 10px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .pagination a {
            color: var(--primary-medium);
            border: 1px solid var(--primary-medium);
        }
        
        .pagination a:hover {
            background: var(--primary-medium);
            color: white;
        }
        
        .pagination .current {
            background: var(--primary-medium);
            color: white;
            border: 1px solid var(--primary-medium);
        }
        
        .pagination .disabled {
            color: #ccc;
            border: 1px solid #eee;
            cursor: not-allowed;
        }
        
        .results-per-page {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .results-per-page select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
        .results-info {
            color: #666;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            width: 90%;
            max-width: 600px;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .modal-title {
            font-size: 1.5rem;
            color: var(--primary-dark);
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: var(--dark-text);
        }
        
        .modal-body {
            margin-bottom: 20px;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        /* Purchase Details in Modal */
        .purchase-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .detail-group {
            margin-bottom: 15px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #666;
            margin-bottom: 5px;
            display: block;
        }
        
        .detail-value {
            font-size: 1.1rem;
            color: var(--dark-text);
        }
        
        /* Form Styles for Edit Modal */
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--dark-text);
        }
        
        .form-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }
        
        /* Export Options */
        .export-options {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .export-option {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 1px solid #eee;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .export-option:hover {
            background: #f9f9f9;
        }
        
        .export-option i {
            margin-right: 10px;
            font-size: 1.2rem;
            color: var(--primary-medium);
        }
        
        /* Loading Overlay */
        .loading-overlay {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.8);
            z-index: 100;
            border-radius: 10px;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid var(--gray-light);
            border-top: 5px solid var(--primary-medium);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Mobile Menu Toggle */
        .menu-toggle {
            display: none;
            background: var(--primary-medium);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 5px;
            font-size: 1.5rem;
            cursor: pointer;
            align-items: center;
            justify-content: center;
        }
        
        /* Mobile Card View */
        .mobile-purchase-cards {
            display: none;
        }
        
        .purchase-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            overflow: hidden;
        }
        
        .purchase-card-header {
            background: var(--primary-medium);
            color: white;
            padding: 12px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .purchase-card-body {
            padding: 15px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        .purchase-card-row {
            display: flex;
            flex-direction: column;
            padding: 8px 0;
        }
        
        .purchase-card-label {
            color: #666;
            font-weight: 500;
            font-size: 0.85rem;
        }
        
        .purchase-card-value {
            color: var(--dark-text);
            font-weight: 500;
        }
        
        .purchase-card-actions {
            display: flex;
            gap: 8px;
            padding: 15px;
            border-top: 1px solid #f0f0f0;
            justify-content: center;
        }
        
        /* Responsive Styles */
        @media (max-width: 1400px) {
            .purchases-table {
                min-width: 900px;
            }
        }
        
        @media (max-width: 1200px) {
            .purchases-table {
                font-size: 14px;
                min-width: 800px;
            }
            
            .purchases-table th, .purchases-table td {
                padding: 10px 8px;
            }
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                text-align: center;
            }
            
            .sidebar-header h3 {
                display: none;
            }
            
            .menu-title {
                display: none;
            }
            
            .sidebar-menu a span {
                display: none;
            }
            
            .sidebar-menu a {
                padding: 12px;
                justify-content: center;
            }
            
            .sidebar-menu i {
                margin-right: 0;
                font-size: 1.5rem;
            }
            
            .main-content {
                margin-left: 80px;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .purchases-table {
                min-width: 700px;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -280px;
                width: 280px;
                z-index: 1000;
                transition: left 0.3s ease;
            }
            
            .sidebar.active {
                left: 0;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .menu-toggle {
                display: flex;
            }
            
            .content {
                padding: 15px;
            }
            
            .filters-container, .table-container {
                padding: 15px;
            }
            
            .page-title {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .action-buttons {
                width: 100%;
                justify-content: space-between;
                margin-top: 15px;
            }
            
            .filters-form {
                flex-direction: column;
            }
            
            .filter-group {
                min-width: 100%;
            }
            
            .pagination {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .results-per-page {
                width: 100%;
                justify-content: flex-start;
            }
            
            .purchases-table {
                display: none;
            }
            
            .mobile-purchase-cards {
                display: block;
            }
            
            .table-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .table-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .modal-content {
                width: 95%;
                margin: 10% auto;
                padding: 15px;
            }
            
            .purchase-details {
                grid-template-columns: 1fr;
            }
            
            .logout-btn span {
                display: none;
            }
            
            .logout-btn {
                padding: 8px;
            }
            
            .user-info span {
                display: none;
            }
            
            .purchase-card-body {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .top-bar {
                padding: 10px 15px;
            }
            
            .pagination a, .pagination span {
                padding: 8px 12px;
                font-size: 0.9rem;
            }
            
            .btn {
                padding: 6px 10px;
                font-size: 0.8rem;
            }
            
            .filter-btn {
                width: 100%;
                justify-content: center;
            }
            
            .purchase-card-actions {
                flex-direction: column;
            }
            
            .action-btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .pagination-controls {
                flex-wrap: wrap;
            }
            
            .pagination a, .pagination span {
                padding: 6px 10px;
                font-size: 0.8rem;
            }
        }

          
        /* Additional styles for enhanced UI */
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-active {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-inactive {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .stock-low {
            color: #f57c00;
            font-weight: 600;
        }
        
        .stock-out {
            color: #d32f2f;
            font-weight: 600;
        }
        
        .quick-filters {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .quick-filter-btn {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .quick-filter-btn:hover, .quick-filter-btn.active {
            background: var(--primary-medium);
            color: white;
            border-color: var(--primary-medium);
        }
        
        .advanced-filters {
            display: none;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            border-left: 4px solid var(--primary-medium);
        }
        
        .toggle-advanced {
            color: var(--primary-medium);
            cursor: pointer;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            margin-top: 10px;
            background: none;
            border: none;
            padding: 0;
        }
        
        .toggle-advanced i {
            margin-right: 5px;
            transition: transform 0.3s;
        }
        
        .toggle-advanced.active i {
            transform: rotate(180deg);
        }
        
        .batch-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            align-items: center;
        }
        
        .select-all-checkbox {
            margin-right: 5px;
        }
        
        .batch-action-btn {
            padding: 8px 15px;
            border-radius: 4px;
            background: #f5f5f5;
            border: 1px solid #ddd;
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }
        
        .batch-action-btn:hover {
            background: #e0e0e0;
        }
        
        .batch-action-btn i {
            margin-right: 5px;
        }
        
        .purchase-image {
            width: 40px;
            height: 40px;
            border-radius: 4px;
            object-fit: cover;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 0.8rem;
        }
        
        /* Enhanced modal styles */
        .modal-tabs {
            display: flex;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .modal-tab {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            font-size: 0.9rem;
        }
        
        .modal-tab.active {
            border-bottom-color: var(--primary-medium);
            color: var(--primary-medium);
            font-weight: 500;
        }
        
        .modal-tab-content {
            display: none;
        }
        
        .modal-tab-content.active {
            display: block;
        }
        
        /* Enhanced loading animation */
        .pulse {
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .form-col {
            flex: 1;
            min-width: 200px;
        }
        
        .calculation-box {
            background: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid var(--primary-medium);
        }
        
        .calculation-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .calculation-row:last-child {
            border-bottom: none;
        }
        
        .calculation-label {
            color: #666;
        }
        
        .calculation-value {
            font-weight: 500;
            color: var(--dark-text);
        }
        
        /* Mobile overlay when sidebar is open */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        
        .overlay.active {
            display: block;
        }
        
        /* Month selector styles */
        .month-selector {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        
        .month-selector .filter-group {
            min-width: 120px;
        }
    </style>
</head>
<body>
    <!-- Mobile overlay -->
    <div class="overlay" id="overlay"></div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>MKN Bro's</h3>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                
                <div class="menu-section">
                    <div class="menu-title">Sales Management</div>
                    <li><a href="add_sales.php" ><i class="fas fa-cart-plus"></i> <span>Add Sales</span></a></li>
                    <li><a href="sales_list.php"><i class="fas fa-list"></i> <span>Sales List</span></a></li>
                </div>
                
                <div class="menu-section">
                    <div class="menu-title">Customer Management</div>
                    <li><a href="add_customer.php"><i class="fas fa-user-plus"></i> <span>Add Customer</span></a></li>
                    <li><a href="customers_list.php"><i class="fas fa-users"></i> <span>Customers List</span></a></li>
                </div>
                
                <div class="menu-section">
                    <div class="menu-title">Expense Tracking</div>
                    <li><a href="add_amount.php"><i class="fas fa-plus-circle"></i> <span>Add Amount</span></a></li>
                    <li><a href="amounts_list.php"><i class="fas fa-list"></i> <span>Amounts List</span></a></li>
                    <li><a href="add_expense.php"><i class="fas fa-minus-circle"></i> <span>Add Expense</span></a></li>
                    <li><a href="expenses_list.php"><i class="fas fa-file-invoice-dollar"></i> <span>Expenses List</span></a></li>
                </div>
                
                <div class="menu-section">
                    <div class="menu-title">Product Management</div>
                    <li><a href="add_product.php"><i class="fas fa-cube"></i> <span>Add Product</span></a></li>
                    <li><a href="products_list.php"><i class="fas fa-cubes"></i> <span>Products List</span></a></li>
                </div>
                
                <div class="menu-section">
                    <div class="menu-title">Purchase Management</div>
                    <li><a href="add_purchase.php"><i class="fas fa-shopping-cart"></i> <span>Add Purchase</span></a></li>
                    <li><a href="purchases_list.php" class="active"><i class="fas fa-shopping-basket"></i> <span>Purchases List</span></a></li>
                </div>
                
                <div class="menu-section">
                    <div class="menu-title">User Management</div>
                    <li><a href="users_list.php"><i class="fas fa-users"></i> <span>Users List</span></a></li>
                </div>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="user-info">
                <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZillPg==" alt="User">
                <span>Welcome, <?php echo $_SESSION['user']; ?></span>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </a>
        </div>

        <!-- Content Area -->
        <div class="content">
            <h1 class="page-title">
                <span><i class="fas fa-shopping-basket"></i> Purchases List</span>
                <div class="action-buttons">
                    <a href="add_purchase.php" class="btn btn-success">
                        <i class="fas fa-plus"></i> Add New
                    </a>
                    <button class="btn btn-primary" id="exportBtn">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </h1>
            
            <!-- Monthly Stats Section -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-title">Total Purchases This Month</div>
                    <div class="stat-value"><?= $monthly_stats['purchase_count'] ?></div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> Monthly purchases
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Total Amount Spent</div>
                    <div class="stat-value"><span class="rupee-symbol">₹</span><?= number_format($monthly_stats['total_amount'], 2) ?></div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> This month's spending
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Total Purchases</div>
                    <div class="stat-value"><?= $total_rows ?></div>
                    <div class="stat-change">
                        <i class="fas fa-database"></i> All-time purchases
                    </div>
                </div>
            </div>
            
            <!-- Toggle Filters Button -->
            <button class="toggle-filters" id="toggleFilters">
                <i class="fas fa-filter"></i> Show Filters
            </button>
            
            <!-- Filters Section -->
            <div class="filters-container" id="filtersContainer">
                <h2 class="filters-title">
                    <span><i class="fas fa-filter"></i> Filters</span>
                    <?php if (!empty($search) || $sort !== 'date' || $order !== 'DESC' || !empty($month_filter)): ?>
                    <a href="purchases_list.php" class="clear-filters">
                        <i class="fas fa-times"></i> Clear Filters
                    </a>
                    <?php endif; ?>
                </h2>
                <form method="GET" class="filters-form">
                    <input type="hidden" name="page" value="1">
                    <div class="filter-group">
                        <label class="filter-label" for="search">Search</label>
                        <input type="text" class="filter-input" id="search" name="search" placeholder="Search by item name" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="month-selector">
                        <div class="filter-group">
                            <label class="filter-label" for="month">Month</label>
                            <select class="filter-select" id="month" name="month">
                                <option value="">All Months</option>
                                <option value="01" <?= $month_filter === '01' ? 'selected' : '' ?>>January</option>
                                <option value="02" <?= $month_filter === '02' ? 'selected' : '' ?>>February</option>
                                <option value="03" <?= $month_filter === '03' ? 'selected' : '' ?>>March</option>
                                <option value="04" <?= $month_filter === '04' ? 'selected' : '' ?>>April</option>
                                <option value="05" <?= $month_filter === '05' ? 'selected' : '' ?>>May</option>
                                <option value="06" <?= $month_filter === '06' ? 'selected' : '' ?>>June</option>
                                <option value="07" <?= $month_filter === '07' ? 'selected' : '' ?>>July</option>
                                <option value="08" <?= $month_filter === '08' ? 'selected' : '' ?>>August</option>
                                <option value="09" <?= $month_filter === '09' ? 'selected' : '' ?>>September</option>
                                <option value="10" <?= $month_filter === '10' ? 'selected' : '' ?>>October</option>
                                <option value="11" <?= $month_filter === '11' ? 'selected' : '' ?>>November</option>
                                <option value="12" <?= $month_filter === '12' ? 'selected' : '' ?>>December</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label" for="year">Year</label>
                            <select class="filter-select" id="year" name="year">
                                <?php foreach ($years as $year): ?>
                                <option value="<?= $year ?>" <?= $year_filter == $year ? 'selected' : '' ?>><?= $year ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label" for="sort">Sort By</label>
                        <select class="filter-select" id="sort" name="sort">
                            <option value="date" <?= $sort === 'date' ? 'selected' : '' ?>>Date</option>
                            <option value="item_name" <?= $sort === 'item_name' ? 'selected' : '' ?>>Item Name</option>
                            <option value="amount" <?= $sort === 'amount' ? 'selected' : '' ?>>Amount</option>
                            <option value="quantity" <?= $sort === 'quantity' ? 'selected' : '' ?>>Quantity</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label" for="order">Order</label>
                        <select class="filter-select" id="order" name="order">
                            <option value="DESC" <?= $order === 'DESC' ? 'selected' : '' ?>>Descending</option>
                            <option value="ASC" <?= $order === 'ASC' ? 'selected' : '' ?>>Ascending</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label" for="per_page">Results Per Page</label>
                        <select class="filter-select" id="per_page" name="per_page">
                            <option value="10" <?= $results_per_page == 10 ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= $results_per_page == 25 ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= $results_per_page == 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= $results_per_page == 100 ? 'selected' : '' ?>>100</option>
                        </select>
                    </div>

                    <button type="submit" class="filter-btn">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                </form>
            </div>
            
            <!-- Purchases Table -->
            <div class="table-container">
                <div class="loading-overlay" id="loadingOverlay">
                    <div class="spinner"></div>
                    <p>Loading purchases...</p>
                </div>
                
                <div class="table-header">
                    <h3>All Purchases <span style="font-size: 0.9rem; color: #666; font-weight: normal;">(<?= $total_rows ?> total)</span></h3>
                    <div class="table-actions">
                        <button class="table-action-btn" id="refreshTable">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <button class="table-action-btn" id="printTable">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>
                
                <!-- Table Scroll Container -->
                <div class="table-scroll-container">
                    <table class="purchases-table">
                        <thead>
                            <tr>
                                <th>ID <i class="fas fa-sort"></i></th>
                                <th>Item Name <i class="fas fa-sort"></i></th>
                                <th class="text-right">Amount (₹) <i class="fas fa-sort"></i></th>
                                <th>Unit Type <i class="fas fa-sort"></i></th>
                                <th class="text-right">Quantity <i class="fas fa-sort"></i></th>
                                <th>Date <i class="fas fa-sort"></i></th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($results->num_rows > 0): ?>
                                <?php while($row = $results->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['item_name']) ?></td>
                                    <td class="text-right"><span class="rupee-symbol">₹</span><?= number_format($row['amount'], 2) ?></td>
                                    <td><?= htmlspecialchars($row['unit_type']) ?></td>
                                    <td class="text-right"><?= $row['quantity'] ?></td>
                                    <td><?= $row['date'] ?></td>
                                    <td class="text-center">
                                        <div class="action-cell">
                                            <a href="view_purchase.php?id=<?= $row['id'] ?>" class="action-btn view-btn">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="edit_purchase.php?id=<?= $row['id'] ?>" class="action-btn edit-btn">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="delete_purchase.php?id=<?= $row['id'] ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure to delete this record?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No purchases found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Mobile Purchase Cards -->
                <div class="mobile-purchase-cards">
                    <?php if ($results->num_rows > 0): ?>
                        <?php 
                        // Reset pointer to iterate through results again
                        $results->data_seek(0);
                        while($row = $results->fetch_assoc()): 
                        ?>
                        <div class="purchase-card">
                            <div class="purchase-card-header">
                                <div><?= htmlspecialchars($row['item_name']) ?></div>
                                <span class="status-badge status-active">ID: <?= $row['id'] ?></span>
                            </div>
                            <div class="purchase-card-body">
                                <div class="purchase-card-row">
                                    <span class="purchase-card-label">Amount:</span>
                                    <span class="purchase-card-value"><span class="rupee-symbol">₹</span><?= number_format($row['amount'], 2) ?></span>
                                </div>
                                <div class="purchase-card-row">
                                    <span class="purchase-card-label">Unit Type:</span>
                                    <span class="purchase-card-value"><?= htmlspecialchars($row['unit_type']) ?></span>
                                </div>
                                <div class="purchase-card-row">
                                    <span class="purchase-card-label">Quantity:</span>
                                    <span class="purchase-card-value"><?= $row['quantity'] ?></span>
                                </div>
                                <div class="purchase-card-row">
                                    <span class="purchase-card-label">Date:</span>
                                    <span class="purchase-card-value"><?= $row['date'] ?></span>
                                </div>
                            </div>
                            <div class="purchase-card-actions">
                                <a href="view_purchase.php?id=<?= $row['id'] ?>" class="action-btn view-btn">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="edit_purchase.php?id=<?= $row['id'] ?>" class="action-btn edit-btn">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete_purchase.php?id=<?= $row['id'] ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure to delete this record?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center" style="padding: 20px;">No purchases found.</div>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination -->
                <div class="pagination">
                    <div class="pagination-controls">
                        <?php if ($total_pages > 1): ?>
                            <?php if ($page > 1): ?>
                                <a href="?page=1&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&month=<?= $month_filter ?>&year=<?= $year_filter ?>&per_page=<?= $results_per_page ?>">First</a>
                                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&month=<?= $month_filter ?>&year=<?= $year_filter ?>&per_page=<?= $results_per_page ?>">Prev</a>
                            <?php else: ?>
                                <span class="disabled">First</span>
                                <span class="disabled">Prev</span>
                            <?php endif; ?>
                            
                            <?php
                            // Show page numbers
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $start_page + 4);
                            
                            // Adjust if we're near the end
                            if ($end_page - $start_page < 4) {
                                $start_page = max(1, $end_page - 4);
                            }
                            
                            for ($i = $start_page; $i <= $end_page; $i++): 
                            ?>
                                <?php if ($i == $page): ?>
                                    <span class="current"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&month=<?= $month_filter ?>&year=<?= $year_filter ?>&per_page=<?= $results_per_page ?>"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&month=<?= $month_filter ?>&year=<?= $year_filter ?>&per_page=<?= $results_per_page ?>">Next</a>
                                <a href="?page=<?= $total_pages ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&month=<?= $month_filter ?>&year=<?= $year_filter ?>&per_page=<?= $results_per_page ?>">Last</a>
                            <?php else: ?>
                                <span class="disabled">Next</span>
                                <span class="disabled">Last</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="results-per-page">
                        <span class="results-info">Showing <?= ($results->num_rows > 0) ? (($page - 1) * $results_per_page + 1) : 0 ?> to <?= min($page * $results_per_page, $total_rows) ?> of <?= $total_rows ?> purchases</span>
                    </div>
                </div>
            </div>
        </div>

    <!-- Export Modal -->
    <div id="exportModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Export Purchases</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="export-options">
                    <label class="export-option">
                        <input type="radio" name="exportFormat" value="csv" checked>
                        <i class="fas fa-file-csv"></i>
                        <span>CSV Format (Comma separated values)</span>
                    </label>
                    <label class="export-option">
                        <input type="radio" name="exportFormat" value="excel">
                        <i class="fas fa-file-excel"></i>
                        <span>Excel Format (XLSX)</span>
                    </label>
                </div>
                <div class="form-group" style="margin-top: 20px;">
                    <label class="form-label">Export Data For:</label>
                    <div class="form-row">
                        <div class="form-col">
                            <select class="form-input" id="exportMonth" name="exportMonth">
                                <option value="">All Months</option>
                                <option value="01">January</option>
                                <option value="02">February</option>
                                <option value="03">March</option>
                                <option value="04">April</option>
                                <option value="05">May</option>
                                <option value="06">June</option>
                                <option value="07">July</option>
                                <option value="08">August</option>
                                <option value="09">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                        <div class="form-col">
                            <select class="form-input" id="exportYear" name="exportYear">
                                <?php foreach ($years as $year): ?>
                                <option value="<?= $year ?>" <?= $year_filter == $year ? 'selected' : '' ?>><?= $year ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="exportConfirm">
                    <i class="fas fa-download"></i> Export
                </button>
                <button class="btn" id="exportCancel" style="background: #eee;">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        // Toggle sidebar on mobile
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
        });
        
        // Close sidebar when clicking outside on mobile
        document.getElementById('overlay').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.remove('active');
            this.classList.remove('active');
        });
        
        // Toggle filters section
        document.getElementById('toggleFilters').addEventListener('click', function() {
            const filtersContainer = document.getElementById('filtersContainer');
            const toggleButton = document.getElementById('toggleFilters');
            
            filtersContainer.classList.toggle('active');
            
            if (filtersContainer.classList.contains('active')) {
                toggleButton.innerHTML = '<i class="fas fa-filter"></i> Hide Filters';
            } else {
                toggleButton.innerHTML = '<i class="fas fa-filter"></i> Show Filters';
            }
        });
        
        // Make table headers clickable for sorting
        document.querySelectorAll('.purchases-table th').forEach(header => {
            header.addEventListener('click', function() {
                const column = this.cellIndex;
                const currentSort = '<?php echo $sort; ?>';
                const currentOrder = '<?php echo $order; ?>';
                
                let newSort;
                switch(column) {
                    case 0: newSort = 'id'; break;
                    case 1: newSort = 'item_name'; break;
                    case 2: newSort = 'amount'; break;
                    case 3: newSort = 'unit_type'; break;
                    case 4: newSort = 'quantity'; break;
                    case 5: newSort = 'date'; break;
                    default: newSort = 'date';
                }
                
                let newOrder = 'DESC';
                if (newSort === currentSort) {
                    newOrder = currentOrder === 'DESC' ? 'ASC' : 'DESC';
                }
                
                // Build URL with all parameters
                const url = new URL(window.location.href);
                url.searchParams.set('sort', newSort);
                url.searchParams.set('order', newOrder);
                url.searchParams.set('page', 1);
                
                window.location.href = url.toString();
            });
        });
        
        // Results per page change
        document.getElementById('per_page').addEventListener('change', function() {
            const perPage = this.value;
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', perPage);
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        });
        
        // Export modal functionality
        const exportModal = document.getElementById('exportModal');
        const exportBtn = document.getElementById('exportBtn');
        const exportConfirm = document.getElementById('exportConfirm');
        const exportCancel = document.getElementById('exportCancel');
        const closeModal = document.querySelectorAll('.close');
        
        exportBtn.addEventListener('click', function() {
            // Set current month/year in export modal
            document.getElementById('exportMonth').value = '<?= $month_filter ?>';
            document.getElementById('exportYear').value = '<?= $year_filter ?>';
            exportModal.style.display = 'block';
        });
        
        closeModal.forEach(closeBtn => {
            closeBtn.addEventListener('click', function() {
                this.closest('.modal').style.display = 'none';
            });
        });
        
        exportCancel.addEventListener('click', function() {
            exportModal.style.display = 'none';
        });
        
        exportConfirm.addEventListener('click', function() {
            const format = document.querySelector('input[name="exportFormat"]:checked').value;
            const exportMonth = document.getElementById('exportMonth').value;
            const exportYear = document.getElementById('exportYear').value;
            
            exportModal.style.display = 'none';
            
            // Build export URL with current filters
            const url = new URL(window.location.href);
            url.searchParams.set('export', format);
            if (exportMonth) url.searchParams.set('export_month', exportMonth);
            if (exportYear) url.searchParams.set('export_year', exportYear);
            
            // Redirect to export URL
            window.location.href = url.toString();
        });
        
        // Close modals if clicked outside
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        });
        
        // Refresh table button
        document.getElementById('refreshTable').addEventListener('click', function() {
            const loadingOverlay = document.getElementById('loadingOverlay');
            loadingOverlay.style.display = 'flex';
            
            setTimeout(function() {
                window.location.reload();
            }, 800);
        });
        
        // Print table button
        document.getElementById('printTable').addEventListener('click', function() {
            window.print();
        });
        
        // Auto-hide loading overlay after page load
        window.addEventListener('load', function() {
            const loadingOverlay = document.getElementById('loadingOverlay');
            setTimeout(function() {
                loadingOverlay.style.display = 'none';
            }, 500);
        });

        // Real-time search with debounce
        let searchTimeout;
        document.getElementById('search').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.querySelector('input[name="page"]').value = 1;
                this.form.submit();
            }, 500);
        });
    </script>
</body>
</html>