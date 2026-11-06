<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Initialize filter variables with session persistence
$search = isset($_GET['search']) ? $_GET['search'] : (isset($_SESSION['product_search']) ? $_SESSION['product_search'] : '');
$sort = isset($_GET['sort']) ? $_GET['sort'] : (isset($_SESSION['product_sort']) ? $_SESSION['product_sort'] : 'id');
$order = isset($_GET['order']) ? $_GET['order'] : (isset($_SESSION['product_order']) ? $_SESSION['product_order'] : 'DESC');
$results_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;

// Store filter preferences in session
$_SESSION['product_search'] = $search;
$_SESSION['product_sort'] = $sort;
$_SESSION['product_order'] = $order;

// Validate sort column to prevent SQL injection
$allowed_sorts = ['id', 'product_code', 'product_name', 'hsn_code', 'piece_weight', 'packing_mode', 'basic_price', 'gst', 'gross_price', 'margin', 'mrp'];
if (!in_array($sort, $allowed_sorts)) {
    $sort = 'id';
}

// Validate order direction
$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

// Build the base query
$query = "SELECT *, 
          ROUND(basic_price * 0.45, 2) as calculated_margin,
          ROUND(basic_price * 1.45, 2) as calculated_gross_price,
          ROUND(basic_price * 1.45 * 1.05, 0) as calculated_mrp,
          ROUND(basic_price + 2, 2) as city_price
          FROM products";
$count_query = "SELECT COUNT(*) as total FROM products";

// Add search filter if provided
if (!empty($search)) {
    $search_term = $conn->real_escape_string($search);
    $where = " WHERE product_code LIKE '%$search_term%' OR 
                     product_name LIKE '%$search_term%' OR 
                     hsn_code LIKE '%$search_term%' OR
                     packing_mode LIKE '%$search_term%'";
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

// Handle export functionality
if (isset($_GET['export'])) {
    $format = $_GET['export'];
    
    // Build export query
    $export_query = "SELECT * FROM products";
    if (!empty($search)) {
        $search_term = $conn->real_escape_string($search);
        $export_query .= " WHERE product_code LIKE '%$search_term%' OR 
                          product_name LIKE '%$search_term%' OR 
                          hsn_code LIKE '%$search_term%' OR
                          packing_mode LIKE '%$search_term%'";
    }
    $export_query .= " ORDER BY $sort $order";
    
    $export_results = $conn->query($export_query);
    
    if ($format === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=products_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($output, array('ID', 'Product Code', 'HSN Code', 'Product Name', 'Piece Weight (g)', 'Packing Mode', 'Basic Price', 'GST', 'Gross Price', 'Margin (%)', 'MRP', 'City Price'));
        
        // Add data rows
        while ($row = $export_results->fetch_assoc()) {
            fputcsv($output, array(
                $row['id'],
                $row['product_code'],
                $row['hsn_code'],
                $row['product_name'],
                $row['piece_weight'],
                $row['packing_mode'],
                $row['basic_price'],
                $row['gst'],
                $row['gross_price'],
                $row['margin'],
                $row['mrp'],
                $row['basic_price'] + 2
            ));
        }
        
        fclose($output);
        exit();
    } elseif ($format === 'excel') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=products_' . date('Y-m-d') . '.xls');
        
        echo "<table border='1'>";
        echo "<tr>
                <th>ID</th>
                <th>Product Code</th>
                <th>HSN Code</th>
                <th>Product Name</th>
                <th>Piece Weight (g)</th>
                <th>Packing Mode</th>
                <th>Basic Price</th>
                <th>GST</th>
                <th>Gross Price</th>
                <th>Margin (%)</th>
                <th>MRP</th>
                <th>City Price</th>
              </tr>";
        
        while ($row = $export_results->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['product_code']}</td>
                    <td>{$row['hsn_code']}</td>
                    <td>{$row['product_name']}</td>
                    <td>{$row['piece_weight']}</td>
                    <td>{$row['packing_mode']}</td>
                    <td>{$row['basic_price']}</td>
                    <td>{$row['gst']}</td>
                    <td>{$row['gross_price']}</td>
                    <td>{$row['margin']}</td>
                    <td>{$row['mrp']}</td>
                    <td>" . ($row['basic_price'] + 2) . "</td>
                  </tr>";
        }
        
        echo "</table>";
        exit();
    }
}

// Get column names for display
$columns = [];
if ($results->num_rows > 0) {
    $field_info = $results->fetch_fields();
    foreach ($field_info as $field) {
        $columns[] = $field->name;
    }
    $results->data_seek(0); // Reset pointer
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products List - Financial Management System</title>
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

        
        
        /* Filters Container */
        .filters-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            padding: 20px;
            margin-bottom: 25px;
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
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            min-width: 1300px;
        }
        
        .products-table th {
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
        
        .products-table th:hover {
            background-color: var(--primary-light);
        }
        
        .products-table th i {
            margin-left: 5px;
        }
        
        .products-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            white-space: nowrap;
        }
        
        .products-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .products-table tr:hover {
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
        
        /* Product Details in Modal */
        .product-details {
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
        .mobile-product-cards {
            display: none;
        }
        
        .product-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            overflow: hidden;
        }
        
        .product-card-header {
            background: var(--primary-medium);
            color: white;
            padding: 12px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .product-card-body {
            padding: 15px;
        }
        
        .product-card-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .product-card-row:last-child {
            border-bottom: none;
        }
        
        .product-card-label {
            color: #666;
            font-weight: 500;
        }
        
        .product-card-value {
            color: var(--dark-text);
            text-align: right;
        }
        
        .product-card-actions {
            display: flex;
            gap: 8px;
            padding: 15px;
            border-top: 1px solid #f0f0f0;
            justify-content: center;
        }
        
        /* Responsive Styles */
        @media (max-width: 1400px) {
            .products-table {
                min-width: 1200px;
            }
        }
        
        @media (max-width: 1200px) {
            .products-table {
                font-size: 14px;
                min-width: 1100px;
            }
            
            .products-table th, .products-table td {
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
            
            .products-table {
                min-width: 1000px;
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
            
            .products-table {
                display: none;
            }
            
            .mobile-product-cards {
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
            
            .product-details {
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
            
            .product-card-actions {
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
        
        .product-image {
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
        }
        
        .modal-tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
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
                    <li><a href="add_sales.php" <i class="fas fa-cart-plus"></i> <span>Add Sales</span></a></li>
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
                    <li><a href="products_list.php" class="active"><i class="fas fa-cubes"></i> <span>Products List</span></a></li>
                </div>
                
                <div class="menu-section">
                    <div class="menu-title">Purchase Management</div>
                    <li><a href="add_purchase.php"><i class="fas fa-shopping-cart"></i> <span>Add Purchase</span></a></li>
                    <li><a href="purchases_list.php"><i class="fas fa-shopping-basket"></i> <span>Purchases List</span></a></li>
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
                <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iI2ZmZiI+PHBhdGggZD0iTTEyIDJDNi40OCAyIDIgNi40OCAyIDEyczQuNDggMTAgMTAgMTAgMTAtNC40OCAxMC0xMFMxNy41MiAyIDEyIDJ6bTAgM2MyLjIxIDAgNCA1Ljc5IDQgNHMtMS43OSA0LTQgNC00LTEuNzktNC00IDEuNzktNCA0LTR6bTAgMTcuMDJjLTMuMzMgMC02LjI4LTEuNzEtOC02LjAyIDIuMDUtMy4xY2UgNS4yOS01IDguNS01IDMuMjEgMCA2LjQ1IDEuODQgOC41IDUgMC00LjMxLTMuNS04LTgtNS04LjAyeiIvPjwvc3ZnPg==" alt="User">
                <span>Welcome, <?php echo $_SESSION['user']; ?></span>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </a>
        </div>


    <!-- Content Area -->
    <div class="content">
        <h1 class="page-title">
            <span><i class="fas fa-cubes"></i> Products List</span>
            <div class="action-buttons">
                <a href="add_product.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Add New
                </a>
                <button class="btn btn-primary" id="exportBtn">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </h1>
        
      
        <!-- Filters Section -->
        <div class="filters-container">
            <h2 class="filters-title">
                <span><i class="fas fa-filter"></i> Filters</span>
                <?php if (!empty($search) || $sort !== 'id' || $order !== 'DESC'): ?>
                <a href="products_list.php" class="clear-filters">
                    <i class="fas fa-times"></i> Clear Filters
                </a>
                <?php endif; ?>
            </h2>
            <form method="GET" class="filters-form">
                <input type="hidden" name="page" value="1">
                <div class="filter-group">
                    <label class="filter-label" for="search">Search</label>
                    <input type="text" class="filter-input" id="search" name="search" placeholder="Search by code, name, HSN or packing mode" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="filter-group">
                    <label class="filter-label" for="sort">Sort By</label>
                    <select class="filter-select" id="sort" name="sort">
                        <option value="id" <?php echo $sort === 'id' ? 'selected' : ''; ?>>ID</option>
                        <option value="product_code" <?php echo $sort === 'product_code' ? 'selected' : ''; ?>>Product Code</option>
                        <option value="product_name" <?php echo $sort === 'product_name' ? 'selected' : ''; ?>>Product Name</option>
                        <option value="hsn_code" <?php echo $sort === 'hsn_code' ? 'selected' : ''; ?>>HSN Code</option>
                        <option value="piece_weight" <?php echo $sort === 'piece_weight' ? 'selected' : ''; ?>>Piece Weight</option>
                        <option value="packing_mode" <?php echo $sort === 'packing_mode' ? 'selected' : ''; ?>>Packing Mode</option>
                        <option value="basic_price" <?php echo $sort === 'basic_price' ? 'selected' : ''; ?>>Basic Price</option>
                        <option value="mrp" <?php echo $sort === 'mrp' ? 'selected' : ''; ?>>MRP</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label" for="order">Order</label>
                    <select class="filter-select" id="order" name="order">
                        <option value="DESC" <?php echo $order === 'DESC' ? 'selected' : ''; ?>>Descending</option>
                        <option value="ASC" <?php echo $order === 'ASC' ? 'selected' : ''; ?>>Ascending</option>
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
                
                <button type="button" class="toggle-advanced" id="toggleAdvanced">
                    <i class="fas fa-chevron-down"></i> Advanced Filters
                </button>
            </form>
            
            <!-- Advanced Filters -->
            <div class="advanced-filters" id="advancedFilters">
                <h3 style="margin-bottom: 15px;">Advanced Filters</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div class="filter-group">
                        <label class="filter-label" for="price_min">Min Price (₹)</label>
                        <input type="number" class="filter-input" id="price_min" name="price_min" placeholder="Minimum price" step="0.01">
                    </div>
                    <div class="filter-group">
                        <label class="filter-label" for="price_max">Max Price (₹)</label>
                        <input type="number" class="filter-input" id="price_max" name="price_max" placeholder="Maximum price" step="0.01">
                    </div>
                    <div class="filter-group">
                        <label class="filter-label" for="margin_min">Min Margin (%)</label>
                        <input type="number" class="filter-input" id="margin_min" name="margin_min" placeholder="Minimum margin" step="0.01">
                    </div>
                    <div class="filter-group">
                        <label class="filter-label" for="margin_max">Max Margin (%)</label>
                        <input type="number" class="filter-input" id="margin_max" name="margin_max" placeholder="Maximum margin" step="0.01">
                    </div>
                </div>
                <button type="button" class="filter-btn" style="margin-top: 15px;">
                    <i class="fas fa-filter"></i> Apply Advanced Filters
                </button>
            </div>
        </div>
        
   
        
        <!-- Products Table -->
        <div class="table-container">
            <div class="loading-overlay" id="loadingOverlay">
                <div class="spinner"></div>
                <p>Loading products...</p>
            </div>
            
            <div class="table-header">
                <h3>All Products <span style="font-size: 0.9rem; color: #666; font-weight: normal;">(<?= $total_rows ?> total)</span></h3>
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
                <table class="products-table">
                    <thead>
                        <tr>
                            <th width="50"><input type="checkbox" id="tableSelectAll"></th>
                            <th>ID <i class="fas fa-sort"></i></th>
                            <th>Product Code <i class="fas fa-sort"></i></th>
                            <th>HSN Code <i class="fas fa-sort"></i></th>
                            <th>Product Name <i class="fas fa-sort"></i></th>
                            <th>Piece Weight (g) <i class="fas fa-sort"></i></th>
                            <th>Packing Mode <i class="fas fa-sort"></i></th>
                            <th class="text-right">Basic Price (₹) <i class="fas fa-sort"></i></th>
                            <th class="text-right">GST (₹) <i class="fas fa-sort"></i></th>
                            <th class="text-right">Gross Price (₹) <i class="fas fa-sort"></i></th>
                            <th class="text-right">Margin (₹) <i class="fas fa-sort"></i></th>
                            <th class="text-right">MRP (₹) <i class="fas fa-sort"></i></th>
                            <th class="text-right">City Price (₹) <i class="fas fa-sort"></i></th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($results->num_rows > 0): ?>
                            <?php while($row = $results->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center"><input type="checkbox" class="product-checkbox" data-id="<?= $row['id'] ?>"></td>
                                <td><?= $row['id'] ?></td>
                               
                                <td><?= htmlspecialchars($row['product_code']) ?></td>
                                <td><?= htmlspecialchars($row['hsn_code']) ?></td>
                                <td><?= htmlspecialchars($row['product_name']) ?></td>
                                <td><?= $row['piece_weight'] ?></td>
                                <td><?= htmlspecialchars($row['packing_mode']) ?></td>
                                <td class="text-right"><span class="rupee-symbol">₹</span><?= number_format($row['basic_price'], 2) ?></td>
                                <td class="text-right"><span class="rupee-symbol">₹</span><?= number_format($row['gst'], 2) ?></td>
                                <td class="text-right"><span class="rupee-symbol">₹</span><?= number_format($row['gross_price'], 2) ?></td>
                                <td class="text-right"><span class="rupee-symbol">₹</span><?= number_format($row['calculated_margin'], 2) ?></td>
                                <td class="text-right"><span class="rupee-symbol">₹</span><?= number_format($row['calculated_mrp'], 0) ?></td>
                                <td class="text-right"><span class="rupee-symbol">₹</span><?= number_format($row['city_price'], 2) ?></td>
                                <td class="text-center">
                                    <span class="status-badge status-active">Active</span>
                                </td>
                                <td class="text-center">
                                    <div class="action-cell">
                                        <button class="action-btn view-btn" data-id="<?= $row['id'] ?>">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button class="action-btn edit-btn" data-id="<?= $row['id'] ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="action-btn delete-btn" data-id="<?= $row['id'] ?>">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="16" class="text-center">No products found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Mobile Product Cards -->
            <div class="mobile-product-cards">
                <?php if ($results->num_rows > 0): ?>
                    <?php 
                    // Reset pointer to iterate through results again
                    $results->data_seek(0);
                    while($row = $results->fetch_assoc()): 
                    ?>
                    <div class="product-card">
                        <div class="product-card-header">
                            <div><?= htmlspecialchars($row['product_code']) ?></div>
                            <span class="status-badge status-active">Active</span>
                        </div>
                        <div class="product-card-body">
                            <div class="product-card-row">
                                <span class="product-card-label">Product Name:</span>
                                <span class="product-card-value"><?= htmlspecialchars($row['product_name']) ?></span>
                            </div>
                            <div class="product-card-row">
                                <span class="product-card-label">HSN Code:</span>
                                <span class="product-card-value"><?= htmlspecialchars($row['hsn_code']) ?></span>
                            </div>
                            <div class="product-card-row">
                                <span class="product-card-label">Weight:</span>
                                <span class="product-card-value"><?= $row['piece_weight'] ?>g</span>
                            </div>
                            <div class="product-card-row">
                                <span class="product-card-label">Packing:</span>
                                <span class="product-card-value"><?= htmlspecialchars($row['packing_mode']) ?></span>
                            </div>
                            <div class="product-card-row">
                                <span class="product-card-label">Base Price:</span>
                                <span class="product-card-value"><span class="rupee-symbol">₹</span><?= number_format($row['basic_price'], 2) ?></span>
                            </div>
                            <div class="product-card-row">
                                <span class="product-card-label">MRP:</span>
                                <span class="product-card-value"><span class="rupee-symbol">₹</span><?= number_format($row['calculated_mrp'], 0) ?></span>
                            </div>
                        </div>
                        <div class="product-card-actions">
                            <button class="action-btn view-btn" data-id="<?= $row['id'] ?>">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="action-btn edit-btn" data-id="<?= $row['id'] ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" data-id="<?= $row['id'] ?>">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center" style="padding: 20px;">No products found.</div>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <div class="pagination">
                <div class="pagination-controls">
                    <?php if ($total_pages > 1): ?>
                        <?php if ($page > 1): ?>
                            <a href="?page=1&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&per_page=<?= $results_per_page ?>">First</a>
                            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&per_page=<?= $results_per_page ?>">Prev</a>
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
                                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&per_page=<?= $results_per_page ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&per_page=<?= $results_per_page ?>">Next</a>
                            <a href="?page=<?= $total_pages ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&per_page=<?= $results_per_page ?>">Last</a>
                        <?php else: ?>
                            <span class="disabled">Next</span>
                            <span class="disabled">Last</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <div class="results-per-page">
                    <span class="results-info">Showing <?= ($results->num_rows > 0) ? (($page - 1) * $results_per_page + 1) : 0 ?> to <?= min($page * $results_per_page, $total_rows) ?> of <?= $total_rows ?> products</span>
                </div>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Product Details</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body" id="viewModalBody">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button class="btn" id="viewModalClose" style="background: #eee;">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit Product</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body" id="editModalBody">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="editModalSave">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <button class="btn" id="editModalCancel" style="background: #eee;">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Confirm Deletion</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body" id="deleteModalBody">
                <p>Are you sure you want to delete this product?</p>
                <p><strong>This action cannot be undone.</strong></p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" id="deleteModalConfirm">
                    <i class="fas fa-trash"></i> Delete
                </button>
                <button class="btn" id="deleteModalCancel" style="background: #eee;">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Export Modal -->
    <div id="exportModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Export Products</h2>
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
        
        // Make table headers clickable for sorting
        document.querySelectorAll('.products-table th').forEach(header => {
            header.addEventListener('click', function() {
                const column = this.cellIndex;
                const currentSort = '<?php echo $sort; ?>';
                const currentOrder = '<?php echo $order; ?>';
                
                let newSort;
                switch(column) {
                    case 1: newSort = 'id'; break;
                    case 2: newSort = 'product_code'; break;
                    case 3: newSort = 'hsn_code'; break;
                    case 4: newSort = 'product_name'; break;
                    case 5: newSort = 'piece_weight'; break;
                    case 6: newSort = 'packing_mode'; break;
                    case 7: newSort = 'basic_price'; break;
                    case 8: newSort = 'gst'; break;
                    case 9: newSort = 'gross_price'; break;
                    case 10: newSort = 'margin'; break;
                    case 11: newSort = 'mrp'; break;
                    default: newSort = 'id';
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
        
        // View product functionality
        document.querySelectorAll('.view-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const loadingOverlay = document.getElementById('loadingOverlay');
                loadingOverlay.style.display = 'flex';
                
                // Fetch product details via AJAX
                fetch(`get_product.php?id=${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const product = data.product;
                            document.getElementById('viewModalBody').innerHTML = `
                                <div class="product-details">
                                    <div class="detail-group">
                                        <span class="detail-label">Product Code</span>
                                        <div class="detail-value">${product.product_code}</div>
                                    </div>
                                    <div class="detail-group">
                                        <span class="detail-label">HSN Code</span>
                                        <div class="detail-value">${product.hsn_code}</div>
                                    </div>
                                    <div class="detail-group">
                                        <span class="detail-label">Product Name</span>
                                        <div class="detail-value">${product.product_name}</div>
                                    </div>
                                    <div class="detail-group">
                                        <span class="detail-label">Piece Weight (g)</span>
                                        <div class="detail-value">${product.piece_weight}</div>
                                    </div>
                                    <div class="detail-group">
                                        <span class="detail-label">Packing Mode</span>
                                        <div class="detail-value">${product.packing_mode}</div>
                                    </div>
                                    <div class="detail-group">
                                        <span class="detail-label">Basic Price</span>
                                            <div class="detail-value">₹${parseFloat(product.basic_price).toFixed(2)}</div>
                                </div>
                                <div class="detail-group">
                                    <span class="detail-label">GST</span>
                                    <div class="detail-value">₹${parseFloat(product.gst).toFixed(2)}</div>
                                </div>
                                <div class="detail-group">
                                    <span class="detail-label">Gross Price</span>
                                    <div class="detail-value">₹${parseFloat(product.gross_price).toFixed(2)}</div>
                                </div>
                                <div class="detail-group">
                                    <span class="detail-label">Margin (₹)</span>
                                    <div class="detail-value">₹${(parseFloat(product.basic_price) * 0.45).toFixed(2)}</div>
                                </div>
                                <div class="detail-group">
                                    <span class="detail-label">MRP</span>
                                    <div class="detail-value">₹${Math.round(parseFloat(product.basic_price) * 1.45 * 1.05)}</div>
                                </div>
                                <div class="detail-group">
                                    <span class="detail-label">City Price</span>
                                    <div class="detail-value">₹${(parseFloat(product.basic_price) + 2).toFixed(2)}</div>
                                </div>
                            </div>
                        `;
                        document.getElementById('viewModal').style.display = 'block';
                    } else {
                        Toastify({
                            text: "Error loading product details",
                            duration: 3000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#f44336",
                        }).showToast();
                    }
                    loadingOverlay.style.display = 'none';
                })
                .catch(error => {
                    console.error('Error:', error);
                    loadingOverlay.style.display = 'none';
                    Toastify({
                        text: "Error loading product details",
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#f44336",
                    }).showToast();
                });
        });
    });
    
    // Edit product functionality
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const loadingOverlay = document.getElementById('loadingOverlay');
            loadingOverlay.style.display = 'flex';
            
            // Fetch product details via AJAX
            fetch(`get_product.php?id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const product = data.product;
                        document.getElementById('editModalBody').innerHTML = `
<form id="editProductForm">
    <input type="hidden" name="id" value="${product.id}">
    <div class="form-row">
        <div class="form-col">
            <div class="form-group">
                <label class="form-label" for="edit_product_code">Product Code</label>
                <input type="text" class="form-input" id="edit_product_code" name="product_code" value="${product.product_code}" required>
            </div>
        </div>
        <div class="form-col">
            <div class="form-group">
                <label class="form-label" for="edit_hsn_code">HSN Code</label>
                <input type="text" class="form-input" id="edit_hsn_code" name="hsn_code" value="${product.hsn_code}">
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label class="form-label" for="edit_product_name">Product Name</label>
        <input type="text" class="form-input" id="edit_product_name" name="product_name" value="${product.product_name}" required>
    </div>
    
    <div class="form-row">
        <div class="form-col">
            <div class="form-group">
                <label class="form-label" for="edit_piece_weight">Piece Weight (grams)</label>
                <input type="number" step="0.01" min="0" class="form-input" id="edit_piece_weight" name="piece_weight" value="${product.piece_weight}">
            </div>
        </div>
        <div class="form-col">
            <div class="form-group">
                <label class="form-label" for="edit_packing_mode">Packing Mode</label>
                <input type="text" class="form-input" id="edit_packing_mode" name="packing_mode" value="${product.packing_mode}">
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label class="form-label" for="edit_basic_price">Base Price (₹)</label>
        <input type="number" step="0.01" min="0" class="form-input" id="edit_basic_price" name="basic_price" value="${product.basic_price}" oninput="calculateEditPrices()">
    </div>
    
    <div class="calculation-box">
        <h3 style="margin-bottom: 15px; color: var(--primary-dark);">Price Calculation</h3>
        <div class="calculation-row">
            <span class="calculation-label">Base Price:</span>
            <span class="calculation-value"><span class="rupee-symbol">₹</span><span id="edit_display_base_price">${parseFloat(product.basic_price).toFixed(2)}</span></span>
        </div>
        <div class="calculation-row">
            <span class="calculation-label">Seller Margin (45%):</span>
            <span class="calculation-value"><span class="rupee-symbol">₹</span><span id="edit_display_margin">${(parseFloat(product.basic_price) * 0.45).toFixed(2)}</span></span>
        </div>
        <div class="calculation-row">
            <span class="calculation-label">Gross Price (Base + Margin):</span>
            <span class="calculation-value"><span class="rupee-symbol">₹</span><span id="edit_display_pre_gst">${(parseFloat(product.basic_price) * 1.45).toFixed(2)}</span></span>
        </div>
        <div class="calculation-row">
            <span class="calculation-label">GST (5% of Gross Price):</span>
            <span class="calculation-value"><span class="rupee-symbol">₹</span><span id="edit_display_gst">${(parseFloat(product.basic_price) * 0.05).toFixed(2)}</span></span>
        </div>
        <div class="calculation-row">
            <span class="calculation-label">City Price (Base + 2):</span>
            <span class="calculation-value"><span class="rupee-symbol">₹</span><span id="edit_display_city_price">${(parseFloat(product.basic_price) + 2).toFixed(2)}</span></span>
        </div>
        <div class="calculation-row" style="border-bottom: none; padding-bottom: 0;">
            <span class="calculation-label" style="font-weight: 700;">Final MRP (Rounded):</span>
            <span class="calculation-value" style="font-weight: 700; color: var(--primary-dark);"><span class="rupee-symbol">₹</span><span id="edit_display_mrp">${Math.round(parseFloat(product.basic_price) * 1.45 * 1.05)}</span></span>
        </div>
    </div>
    
    <!-- Hidden fields to store calculated values -->
    <input type="hidden" id="edit_gst" name="gst" value="${product.gst}">
    <input type="hidden" id="edit_gross_price" name="gross_price" value="${product.gross_price}">
    <input type="hidden" id="edit_margin_percent" name="margin" value="45">
    <input type="hidden" id="edit_mrp" name="mrp" value="${product.mrp}">
</form>
`;
document.getElementById('editModal').style.display = 'block';
// Initialize calculation
calculateEditPrices();
} else {
Toastify({
text: "Error loading product details",
duration: 3000,
close: true,
gravity: "top",
position: "right",
backgroundColor: "#f44336",
}).showToast();
}
loadingOverlay.style.display = 'none';
})
.catch(error => {
console.error('Error:', error);
loadingOverlay.style.display = 'none';
Toastify({
text: "Error loading product details",
duration: 3000,
close: true,
gravity: "top",
position: "right",
backgroundColor: "#f44336",
}).showToast();
});
});
});
    // Save edited product
    document.getElementById('editModalSave').addEventListener('click', function() {
        const formData = new FormData(document.getElementById('editProductForm'));
        const loadingOverlay = document.getElementById('loadingOverlay');
        loadingOverlay.style.display = 'flex';
        
        fetch('update_product.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Toastify({
                    text: "Product updated successfully",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#4CAF50",
                }).showToast();
                document.getElementById('editModal').style.display = 'none';
                // Reload the page after a short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                Toastify({
                    text: data.message || "Error updating product",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#f44336",
                }).showToast();
            }
            loadingOverlay.style.display = 'none';
        })
        .catch(error => {
            console.error('Error:', error);
            loadingOverlay.style.display = 'none';
            Toastify({
                text: "Error updating product",
                duration: 3000,
                close: true,
                gravity: "top",
                position: "right",
                backgroundColor: "#f44336",
            }).showToast();
        });
    });
    
    // Delete product functionality
    let currentDeleteId = null;
    
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            currentDeleteId = this.getAttribute('data-id');
            document.getElementById('deleteModal').style.display = 'block';
        });
    });
    
    document.getElementById('deleteModalConfirm').addEventListener('click', function() {
        if (!currentDeleteId) return;
        
        const loadingOverlay = document.getElementById('loadingOverlay');
        loadingOverlay.style.display = 'flex';
        
        fetch(`delete_product.php?id=${currentDeleteId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Toastify({
                        text: "Product deleted successfully",
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#4CAF50",
                    }).showToast();
                    document.getElementById('deleteModal').style.display = 'none';
                    // Reload the page after a short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    Toastify({
                        text: data.message || "Error deleting product",
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#f44336",
                    }).showToast();
                }
                loadingOverlay.style.display = 'none';
            })
            .catch(error => {
                console.error('Error:', error);
                loadingOverlay.style.display = 'none';
                Toastify({
                    text: "Error deleting product",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#f44336",
                }).showToast();
            });
    });
    
    // Export modal functionality
    const exportModal = document.getElementById('exportModal');
    const exportBtn = document.getElementById('exportBtn');
    const exportConfirm = document.getElementById('exportConfirm');
    const exportCancel = document.getElementById('exportCancel');
    const closeModal = document.querySelectorAll('.close');
    
    exportBtn.addEventListener('click', function() {
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
        exportModal.style.display = 'none';
        
        // Build export URL with current filters
        const url = new URL(window.location.href);
        url.searchParams.set('export', format);
        
        // Redirect to export URL
        window.location.href = url.toString();
    });
    
    // Close modals if clicked outside
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    });
    
    // Close buttons for modals
    document.getElementById('viewModalClose').addEventListener('click', function() {
        document.getElementById('viewModal').style.display = 'none';
    });
    
    document.getElementById('editModalCancel').addEventListener('click', function() {
        document.getElementById('editModal').style.display = 'none';
    });
    
    document.getElementById('deleteModalCancel').addEventListener('click', function() {
        document.getElementById('deleteModal').style.display = 'none';
    });
    
    // Edit price calculation function
    function calculateEditPrices() {
        const basePrice = parseFloat(document.getElementById('edit_basic_price').value) || 0;
        
        // Calculate seller margin (45% of base price)
        const marginAmount = basePrice * 0.45;
        
        // Calculate pre-GST selling price
        const preGstPrice = basePrice + marginAmount;
        
        // Calculate GST (5% of pre-GST price)
        let gstAmount = basePrice * 0.05;
        
        // Calculate gross price (base price + GST)
        let grossPrice = basePrice + gstAmount;
        
        // Calculate final MRP (rounded)
        let mrp = Math.round(preGstPrice + gstAmount);
        
        // Calculate city price
        let cityPrice = basePrice + 2;
        
        // Round all values to 2 decimal places for display
        const displayBasePrice = basePrice.toFixed(2);
        const displayMargin = marginAmount.toFixed(2);
        const displayGrossPrice = grossPrice.toFixed(2);
        const displayGst = gstAmount.toFixed(2);
        const displayMrp = mrp;
        const displayCityPrice = cityPrice.toFixed(2);
        
        // Update display values
        document.getElementById('edit_display_base_price').textContent = displayBasePrice;
        document.getElementById('edit_display_margin').textContent = displayMargin;
        document.getElementById('edit_display_pre_gst').textContent = displayGrossPrice;
        document.getElementById('edit_display_gst').textContent = displayGst;
        document.getElementById('edit_display_mrp').textContent = displayMrp;
        document.getElementById('edit_display_city_price').textContent = displayCityPrice;
        
        // Update hidden form fields with precise values
        document.getElementById('edit_gst').value = gstAmount;
        document.getElementById('edit_gross_price').value = grossPrice;
        document.getElementById('edit_mrp').value = mrp;
        document.getElementById('edit_margin_percent').value = marginAmount;
    }
    
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

    // Toggle advanced filters
    document.getElementById('toggleAdvanced').addEventListener('click', function() {
        this.classList.toggle('active');
        document.getElementById('advancedFilters').style.display = 
            document.getElementById('advancedFilters').style.display === 'block' ? 'none' : 'block';
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
</body> </html>