<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Initialize filter variables with session persistence
$search = isset($_GET['search']) ? $_GET['search'] : (isset($_SESSION['shop_search']) ? $_SESSION['shop_search'] : '');
$city_filter = isset($_GET['city']) ? $_GET['city'] : (isset($_SESSION['shop_city']) ? $_SESSION['shop_city'] : '');
$district_filter = isset($_GET['district']) ? $_GET['district'] : (isset($_SESSION['shop_district']) ? $_SESSION['shop_district'] : '');
$status_filter = isset($_GET['status']) ? $_GET['status'] : (isset($_SESSION['shop_status']) ? $_SESSION['shop_status'] : '');
$courier_filter = isset($_GET['courier_status']) ? $_GET['courier_status'] : (isset($_SESSION['shop_courier_status']) ? $_SESSION['shop_courier_status'] : '');
$sort = isset($_GET['sort']) ? $_GET['sort'] : (isset($_SESSION['shop_sort']) ? $_SESSION['shop_sort'] : 'id');
$order = isset($_GET['order']) ? $_GET['order'] : (isset($_SESSION['shop_order']) ? $_SESSION['shop_order'] : 'DESC');
$results_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;

// Store filter preferences in session
$_SESSION['shop_search'] = $search;
$_SESSION['shop_city'] = $city_filter;
$_SESSION['shop_district'] = $district_filter;
$_SESSION['shop_status'] = $status_filter;
$_SESSION['shop_courier_status'] = $courier_filter;
$_SESSION['shop_sort'] = $sort;
$_SESSION['shop_order'] = $order;

// Validate sort column to prevent SQL injection
$allowed_sorts = ['id', 'shop_name', 'city', 'district', 'status', 'courier_status', 'shop_status', 'created_at'];
if (!in_array($sort, $allowed_sorts)) {
    $sort = 'id';
}

// Validate order direction
$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

// Get unique cities and districts for filters
$cities_result = $conn->query("SELECT DISTINCT city FROM shops WHERE city IS NOT NULL AND city != '' ORDER BY city");
$districts_result = $conn->query("SELECT DISTINCT district FROM shops WHERE district IS NOT NULL AND district != '' ORDER BY district");

// Build the base query
$query = "SELECT * FROM shops";
$count_query = "SELECT COUNT(*) as total FROM shops";

// Add filters
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $search_term = '%' . $search . '%';
    $where_conditions[] = "(shop_name LIKE ? OR city LIKE ? OR district LIKE ? OR products_prices LIKE ? OR description LIKE ?)";
    $params = array_merge($params, array_fill(0, 5, $search_term));
}

if (!empty($city_filter)) {
    $where_conditions[] = "city = ?";
    $params[] = $city_filter;
}

if (!empty($district_filter)) {
    $where_conditions[] = "district = ?";
    $params[] = $district_filter;
}

if (!empty($status_filter)) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if (!empty($courier_filter)) {
    $where_conditions[] = "courier_status = ?";
    $params[] = $courier_filter;
}

// Add WHERE clause if conditions exist
if (!empty($where_conditions)) {
    $where = " WHERE " . implode(" AND ", $where_conditions);
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

// Prepare and execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$results = $stmt->get_result();

// Get total count
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $results_per_page);

// Ensure page doesn't exceed total pages
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
    $offset = ($page - 1) * $results_per_page;
    $query = substr($query, 0, strpos($query, "LIMIT")) . " LIMIT $offset, $results_per_page";
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $results = $stmt->get_result();
}

// Handle export functionality
if (isset($_GET['export'])) {
    $format = $_GET['export'];
    
    // Build export query
    $export_query = "SELECT * FROM shops";
    if (!empty($where_conditions)) {
        $export_query .= " WHERE " . implode(" AND ", $where_conditions);
    }
    $export_query .= " ORDER BY $sort $order";
    
    $export_stmt = $conn->prepare($export_query);
    if (!empty($params)) {
        $export_stmt->bind_param($types, ...$params);
    }
    $export_stmt->execute();
    $export_results = $export_stmt->get_result();
    
    if ($format === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=shops_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($output, array('ID', 'Shop Name', 'City', 'District', 'Status', 'Courier Status', 'Shop Status', 'Latitude', 'Longitude', 'Created Date'));
        
        // Add data rows
        while ($row = $export_results->fetch_assoc()) {
            fputcsv($output, array(
                $row['id'],
                $row['shop_name'],
                $row['city'],
                $row['district'],
                $row['status'],
                $row['courier_status'],
                $row['shop_status'],
                $row['location_lat'],
                $row['location_lng'],
                $row['created_at']
            ));
        }
        
        fclose($output);
        exit();
    } elseif ($format === 'excel') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=shops_' . date('Y-m-d') . '.xls');
        
        echo "<table border='1'>";
        echo "<tr>
                <th>ID</th>
                <th>Shop Name</th>
                <th>City</th>
                <th>District</th>
                <th>Status</th>
                <th>Courier Status</th>
                <th>Shop Status</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Created Date</th>
              </tr>";
        
        while ($row = $export_results->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['shop_name']}</td>
                    <td>{$row['city']}</td>
                    <td>{$row['district']}</td>
                    <td>{$row['status']}</td>
                    <td>{$row['courier_status']}</td>
                    <td>{$row['shop_status']}</td>
                    <td>{$row['location_lat']}</td>
                    <td>{$row['location_lng']}</td>
                    <td>{$row['created_at']}</td>
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
    <title>Shops List - Financial Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- Leaflet CSS for Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
        
        .shops-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            min-width: 1300px;
        }
        
        .shops-table th {
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
        
        .shops-table th:hover {
            background-color: var(--primary-light);
        }
        
        .shops-table th i {
            margin-left: 5px;
        }
        
        .shops-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            white-space: nowrap;
        }
        
        .shops-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .shops-table tr:hover {
            background-color: #f1f7ff;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
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
            border: none;
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
        
        .location-btn {
            background: var(--success);
        }
        
        .location-btn:hover {
            background: #3d8b40;
        }
        
        /* Status Badges */
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-active {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-inactive {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .status-pending {
            background-color: #fff3e0;
            color: #ef6c00;
        }
        
        .status-accepted {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-rejected {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .status-closed {
            background-color: #f5f5f5;
            color: #616161;
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
        
        /* Modal Styles */
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
            max-width: 800px;
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
            max-height: 70vh;
            overflow-y: auto;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        /* View Modal Content */
        .shop-details {
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
        
        /* Map in Modal */
        .map-modal-container {
            height: 400px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #ddd;
            margin-top: 10px;
        }
        
        .map-modal {
            height: 100%;
            width: 100%;
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
        
        /* Mobile Shop Cards */
        .mobile-shop-cards {
            display: none;
        }
        
        .shop-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            overflow: hidden;
        }
        
        .shop-card-header {
            background: var(--primary-medium);
            color: white;
            padding: 12px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .shop-card-body {
            padding: 15px;
        }
        
        .shop-card-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .shop-card-row:last-child {
            border-bottom: none;
        }
        
        .shop-card-label {
            color: #666;
            font-weight: 500;
        }
        
        .shop-card-value {
            color: var(--dark-text);
            text-align: right;
        }
        
        .shop-card-actions {
            display: flex;
            gap: 8px;
            padding: 15px;
            border-top: 1px solid #f0f0f0;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        /* Responsive Styles */
        @media (max-width: 1400px) {
            .shops-table {
                min-width: 1200px;
            }
        }
        
        @media (max-width: 1200px) {
            .shops-table {
                font-size: 14px;
                min-width: 1100px;
            }
            
            .shops-table th, .shops-table td {
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
            
            .shops-table {
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
            
            .shops-table {
                display: none;
            }
            
            .mobile-shop-cards {
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
            
            .shop-details {
                grid-template-columns: 1fr;
            }
            
            .map-modal-container {
                height: 300px;
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
            
            .shop-card-actions {
                flex-direction: column;
            }
            
            .action-btn {
                width: 100%;
                justify-content: center;
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
        }

        @media (max-width: 480px) {
            .pagination-controls {
                flex-wrap: wrap;
            }
            
            .pagination a, .pagination span {
                padding: 6px 10px;
                font-size: 0.8rem;
            }
            
            .map-modal-container {
                height: 250px;
            }
        }
        
        /* Additional styles */
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
        
        /* Text truncation */
        .truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 150px;
        }
        
        /* Quick filters */
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
        
        /* Products Prices Display */
        .products-preview {
            max-height: 100px;
            overflow-y: auto;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
            font-size: 0.9rem;
            white-space: pre-wrap;
        }
        
        /* Map Popup Content */
        .map-popup-content {
            padding: 10px;
        }
        
        .map-popup-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: var(--primary-dark);
        }
        
        .map-popup-info {
            font-size: 0.9rem;
            color: #666;
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
                    <li><a href="add_sales.php"><i class="fas fa-cart-plus"></i> <span>Add Sales</span></a></li>
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
                    <div class="menu-title">Shop Management</div>
                    <li><a href="add_shop.php"><i class="fas fa-store"></i> <span>Add Shop</span></a></li>
                    <li><a href="shops_list.php" class="active"><i class="fas fa-store-alt"></i> <span>Shops List</span></a></li>
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
                <span><i class="fas fa-store-alt"></i> Shops List</span>
                <div class="action-buttons">
                    <a href="add_shop.php" class="btn btn-success">
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
                    <?php if (!empty($search) || !empty($city_filter) || !empty($district_filter) || !empty($status_filter) || !empty($courier_filter) || $sort !== 'id' || $order !== 'DESC'): ?>
                    <a href="shops_list.php" class="clear-filters">
                        <i class="fas fa-times"></i> Clear Filters
                    </a>
                    <?php endif; ?>
                </h2>
                <form method="GET" class="filters-form">
                    <input type="hidden" name="page" value="1">
                    <div class="filter-group">
                        <label class="filter-label" for="search">Search</label>
                        <input type="text" class="filter-input" id="search" name="search" placeholder="Search by shop name, city, district, etc." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label" for="city">City</label>
                        <select class="filter-select" id="city" name="city">
                            <option value="">All Cities</option>
                            <?php while($city = $cities_result->fetch_assoc()): ?>
                                <option value="<?= $city['city'] ?>" <?= $city_filter == $city['city'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($city['city']) ?>
                                </option>
                            <?php endwhile; ?>
                            <?php $cities_result->data_seek(0); ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label" for="district">District</label>
                        <select class="filter-select" id="district" name="district">
                            <option value="">All Districts</option>
                            <?php while($district = $districts_result->fetch_assoc()): ?>
                                <option value="<?= $district['district'] ?>" <?= $district_filter == $district['district'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($district['district']) ?>
                                </option>
                            <?php endwhile; ?>
                            <?php $districts_result->data_seek(0); ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label" for="status">Shop Status</label>
                        <select class="filter-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="Active" <?= $status_filter == 'Active' ? 'selected' : '' ?>>Active</option>
                            <option value="Inactive" <?= $status_filter == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="Closed" <?= $status_filter == 'Closed' ? 'selected' : '' ?>>Closed</option>
                            <option value="On Hold" <?= $status_filter == 'On Hold' ? 'selected' : '' ?>>On Hold</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label" for="courier_status">Courier Status</label>
                        <select class="filter-select" id="courier_status" name="courier_status">
                            <option value="">All Courier Status</option>
                            <option value="Pending" <?= $courier_filter == 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Accepted" <?= $courier_filter == 'Accepted' ? 'selected' : '' ?>>Accepted</option>
                            <option value="Rejected" <?= $courier_filter == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                            <option value="In Progress" <?= $courier_filter == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="Delivered" <?= $courier_filter == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label" for="sort">Sort By</label>
                        <select class="filter-select" id="sort" name="sort">
                            <option value="id" <?= $sort == 'id' ? 'selected' : '' ?>>ID</option>
                            <option value="shop_name" <?= $sort == 'shop_name' ? 'selected' : '' ?>>Shop Name</option>
                            <option value="city" <?= $sort == 'city' ? 'selected' : '' ?>>City</option>
                            <option value="district" <?= $sort == 'district' ? 'selected' : '' ?>>District</option>
                            <option value="created_at" <?= $sort == 'created_at' ? 'selected' : '' ?>>Created Date</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label" for="order">Order</label>
                        <select class="filter-select" id="order" name="order">
                            <option value="DESC" <?= $order == 'DESC' ? 'selected' : '' ?>>Descending</option>
                            <option value="ASC" <?= $order == 'ASC' ? 'selected' : '' ?>>Ascending</option>
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
            
            <!-- Quick Stats -->
            <div class="stats-container">
                <?php
                // Get stats
                $stats = [
                    'total' => $total_rows,
                    'active' => $conn->query("SELECT COUNT(*) FROM shops WHERE status = 'Active'")->fetch_row()[0],
                    'pending' => $conn->query("SELECT COUNT(*) FROM shops WHERE courier_status = 'Pending'")->fetch_row()[0],
                    'accepted' => $conn->query("SELECT COUNT(*) FROM shops WHERE courier_status = 'Accepted'")->fetch_row()[0]
                ];
                ?>
                <div class="stat-card">
                    <div class="stat-title">Total Shops</div>
                    <div class="stat-value"><?= $stats['total'] ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Active Shops</div>
                    <div class="stat-value"><?= $stats['active'] ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Pending Courier</div>
                    <div class="stat-value"><?= $stats['pending'] ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Accepted Courier</div>
                    <div class="stat-value"><?= $stats['accepted'] ?></div>
                </div>
            </div>
            
            <!-- Shops Table -->
            <div class="table-container">
                <div class="loading-overlay" id="loadingOverlay">
                    <div class="spinner"></div>
                    <p>Loading shops...</p>
                </div>
                
                <div class="table-header">
                    <h3>All Shops <span style="font-size: 0.9rem; color: #666; font-weight: normal;">(<?= $total_rows ?> total)</span></h3>
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
                    <table class="shops-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Shop Name</th>
                                <th>City</th>
                                <th>District</th>
                                <th>Status</th>
                                <th>Courier Status</th>
                                <th>Shop Status</th>
                                <th>Location</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($results->num_rows > 0): ?>
                                <?php while($row = $results->fetch_assoc()): 
                                    $location = (!empty($row['location_lat']) && !empty($row['location_lng']) && $row['location_lat'] != 0 && $row['location_lng'] != 0);
                                ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><span class="truncate" title="<?= htmlspecialchars($row['shop_name']) ?>"><?= htmlspecialchars($row['shop_name']) ?></span></td>
                                    <td><?= htmlspecialchars($row['city']) ?></td>
                                    <td><?= htmlspecialchars($row['district']) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $row['status'])) ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $row['courier_status'])) ?>">
                                            <?= $row['courier_status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $row['shop_status'])) ?>">
                                            <?= $row['shop_status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($location): ?>
                                            <button class="action-btn location-btn view-location-btn" data-id="<?= $row['id'] ?>" data-lat="<?= $row['location_lat'] ?>" data-lng="<?= $row['location_lng'] ?>">
                                                <i class="fas fa-map-marker-alt"></i> View Map
                                            </button>
                                        <?php else: ?>
                                            <span style="color: #999; font-size: 0.9rem;">Not Set</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                    <td>
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
                                    <td colspan="10" class="text-center">No shops found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Mobile Shop Cards -->
                <div class="mobile-shop-cards">
                    <?php if ($results->num_rows > 0): ?>
                        <?php 
                        $results->data_seek(0);
                        while($row = $results->fetch_assoc()): 
                            $location = (!empty($row['location_lat']) && !empty($row['location_lng']) && $row['location_lat'] != 0 && $row['location_lng'] != 0);
                        ?>
                        <div class="shop-card">
                            <div class="shop-card-header">
                                <div><?= htmlspecialchars($row['shop_name']) ?></div>
                                <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $row['status'])) ?>">
                                    <?= $row['status'] ?>
                                </span>
                            </div>
                            <div class="shop-card-body">
                                <div class="shop-card-row">
                                    <span class="shop-card-label">City:</span>
                                    <span class="shop-card-value"><?= htmlspecialchars($row['city']) ?></span>
                                </div>
                                <div class="shop-card-row">
                                    <span class="shop-card-label">District:</span>
                                    <span class="shop-card-value"><?= htmlspecialchars($row['district']) ?></span>
                                </div>
                                <div class="shop-card-row">
                                    <span class="shop-card-label">Courier Status:</span>
                                    <span class="shop-card-value">
                                        <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $row['courier_status'])) ?>">
                                            <?= $row['courier_status'] ?>
                                        </span>
                                    </span>
                                </div>
                                <div class="shop-card-row">
                                    <span class="shop-card-label">Shop Status:</span>
                                    <span class="shop-card-value">
                                        <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $row['shop_status'])) ?>">
                                            <?= $row['shop_status'] ?>
                                        </span>
                                    </span>
                                </div>
                                <div class="shop-card-row">
                                    <span class="shop-card-label">Created:</span>
                                    <span class="shop-card-value"><?= date('d M Y', strtotime($row['created_at'])) ?></span>
                                </div>
                            </div>
                            <div class="shop-card-actions">
                                <button class="action-btn view-btn" data-id="<?= $row['id'] ?>">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <?php if ($location): ?>
                                    <button class="action-btn location-btn view-location-btn" data-id="<?= $row['id'] ?>" data-lat="<?= $row['location_lat'] ?>" data-lng="<?= $row['location_lng'] ?>">
                                        <i class="fas fa-map-marker-alt"></i> Map
                                    </button>
                                <?php endif; ?>
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
                        <div class="text-center" style="padding: 20px;">No shops found.</div>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination -->
                <div class="pagination">
                    <div class="pagination-controls">
                        <?php if ($total_pages > 1): ?>
                            <?php if ($page > 1): ?>
                                <a href="?page=1&search=<?= urlencode($search) ?>&city=<?= urlencode($city_filter) ?>&district=<?= urlencode($district_filter) ?>&status=<?= urlencode($status_filter) ?>&courier_status=<?= urlencode($courier_filter) ?>&sort=<?= $sort ?>&order=<?= $order ?>&per_page=<?= $results_per_page ?>">First</a>
                                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&city=<?= urlencode($city_filter) ?>&district=<?= urlencode($district_filter) ?>&status=<?= urlencode($status_filter) ?>&courier_status=<?= urlencode($courier_filter) ?>&sort=<?= $sort ?>&order=<?= $order ?>&per_page=<?= $results_per_page ?>">Prev</a>
                            <?php else: ?>
                                <span class="disabled">First</span>
                                <span class="disabled">Prev</span>
                            <?php endif; ?>
                            
                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $start_page + 4);
                            
                            if ($end_page - $start_page < 4) {
                                $start_page = max(1, $end_page - 4);
                            }
                            
                            for ($i = $start_page; $i <= $end_page; $i++): 
                            ?>
                                <?php if ($i == $page): ?>
                                    <span class="current"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&city=<?= urlencode($city_filter) ?>&district=<?= urlencode($district_filter) ?>&status=<?= urlencode($status_filter) ?>&courier_status=<?= urlencode($courier_filter) ?>&sort=<?= $sort ?>&order=<?= $order ?>&per_page=<?= $results_per_page ?>"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&city=<?= urlencode($city_filter) ?>&district=<?= urlencode($district_filter) ?>&status=<?= urlencode($status_filter) ?>&courier_status=<?= urlencode($courier_filter) ?>&sort=<?= $sort ?>&order=<?= $order ?>&per_page=<?= $results_per_page ?>">Next</a>
                                <a href="?page=<?= $total_pages ?>&search=<?= urlencode($search) ?>&city=<?= urlencode($city_filter) ?>&district=<?= urlencode($district_filter) ?>&status=<?= urlencode($status_filter) ?>&courier_status=<?= urlencode($courier_filter) ?>&sort=<?= $sort ?>&order=<?= $order ?>&per_page=<?= $results_per_page ?>">Last</a>
                            <?php else: ?>
                                <span class="disabled">Next</span>
                                <span class="disabled">Last</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="results-per-page">
                        <span class="results-info">Showing <?= ($results->num_rows > 0) ? (($page - 1) * $results_per_page + 1) : 0 ?> to <?= min($page * $results_per_page, $total_rows) ?> of <?= $total_rows ?> shops</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Shop Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Shop Details</h2>
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

    <!-- Location Map Modal -->
    <div id="locationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Shop Location</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div id="locationModalBody"></div>
                <div class="map-modal-container">
                    <div id="locationMap" class="map-modal"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn" id="locationModalClose" style="background: #eee;">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Shop Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit Shop</h2>
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

    <!-- Delete Shop Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Confirm Deletion</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body" id="deleteModalBody">
                <p>Are you sure you want to delete this shop?</p>
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
                <h2 class="modal-title">Export Shops</h2>
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

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
        document.querySelectorAll('.shops-table th').forEach((header, index) => {
            if (!header.querySelector('button')) {
                header.addEventListener('click', function() {
                    let newSort;
                    switch(index) {
                        case 0: newSort = 'id'; break;
                        case 1: newSort = 'shop_name'; break;
                        case 2: newSort = 'city'; break;
                        case 3: newSort = 'district'; break;
                        case 4: newSort = 'status'; break;
                        case 5: newSort = 'courier_status'; break;
                        case 6: newSort = 'shop_status'; break;
                        case 8: newSort = 'created_at'; break;
                        default: newSort = 'id';
                    }
                    
                    const currentSort = '<?php echo $sort; ?>';
                    const currentOrder = '<?php echo $order; ?>';
                    
                    let newOrder = 'DESC';
                    if (newSort === currentSort) {
                        newOrder = currentOrder === 'DESC' ? 'ASC' : 'DESC';
                    }
                    
                    const url = new URL(window.location.href);
                    url.searchParams.set('sort', newSort);
                    url.searchParams.set('order', newOrder);
                    url.searchParams.set('page', 1);
                    
                    window.location.href = url.toString();
                });
            }
        });
        
        // Results per page change
        document.getElementById('per_page').addEventListener('change', function() {
            const perPage = this.value;
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', perPage);
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        });
        
        // View shop functionality
        document.querySelectorAll('.view-btn').forEach(button => {
            button.addEventListener('click', function() {
                const shopId = this.getAttribute('data-id');
                const loadingOverlay = document.getElementById('loadingOverlay');
                loadingOverlay.style.display = 'flex';
                
                fetch(`get_shop.php?id=${shopId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const shop = data.shop;
                            let locationInfo = 'Not Set';
                            if (shop.location_lat && shop.location_lng && shop.location_lat != 0 && shop.location_lng != 0) {
                                locationInfo = `${shop.location_lat}, ${shop.location_lng}`;
                            }
                            
                            let productsPrices = 'No information';
                            if (shop.products_prices && shop.products_prices.trim() !== '') {
                                productsPrices = shop.products_prices;
                            }
                            
                            let description = 'No description';
                            if (shop.description && shop.description.trim() !== '') {
                                description = shop.description;
                            }
                            
                            document.getElementById('viewModalBody').innerHTML = `
                                <div class="shop-details">
                                    <div class="detail-group">
                                        <span class="detail-label">Shop Name</span>
                                        <div class="detail-value">${shop.shop_name}</div>
                                    </div>
                                    <div class="detail-group">
                                        <span class="detail-label">City</span>
                                        <div class="detail-value">${shop.city}</div>
                                    </div>
                                    <div class="detail-group">
                                        <span class="detail-label">District</span>
                                        <div class="detail-value">${shop.district}</div>
                                    </div>
                                    <div class="detail-group">
                                        <span class="detail-label">Status</span>
                                        <div class="detail-value">
                                            <span class="status-badge status-${shop.status.toLowerCase().replace(' ', '-')}">
                                                ${shop.status}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="detail-group">
                                        <span class="detail-label">Courier Status</span>
                                        <div class="detail-value">
                                            <span class="status-badge status-${shop.courier_status.toLowerCase().replace(' ', '-')}">
                                                ${shop.courier_status}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="detail-group">
                                        <span class="detail-label">Shop Status</span>
                                        <div class="detail-value">
                                            <span class="status-badge status-${shop.shop_status.toLowerCase().replace(' ', '-')}">
                                                ${shop.shop_status}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="detail-group">
                                        <span class="detail-label">Location Coordinates</span>
                                        <div class="detail-value">${locationInfo}</div>
                                    </div>
                                    <div class="detail-group">
                                        <span class="detail-label">Created Date</span>
                                        <div class="detail-value">${new Date(shop.created_at).toLocaleDateString('en-US', { 
                                            year: 'numeric', 
                                            month: 'long', 
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        })}</div>
                                    </div>
                                    <div class="detail-group" style="grid-column: 1 / -1;">
                                        <span class="detail-label">Product & Price Details</span>
                                        <div class="products-preview">${productsPrices}</div>
                                    </div>
                                    <div class="detail-group" style="grid-column: 1 / -1;">
                                        <span class="detail-label">Description / Comments</span>
                                        <div style="padding: 10px; background: #f9f9f9; border-radius: 4px; white-space: pre-wrap;">${description}</div>
                                    </div>
                                </div>
                            `;
                            document.getElementById('viewModal').style.display = 'block';
                        } else {
                            Toastify({
                                text: "Error loading shop details",
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
                            text: "Error loading shop details",
                            duration: 3000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#f44336",
                        }).showToast();
                    });
            });
        });
        
        // View location functionality
        let locationMap = null;
        let locationMarker = null;
        
        document.querySelectorAll('.view-location-btn').forEach(button => {
            button.addEventListener('click', function() {
                const shopId = this.getAttribute('data-id');
                const lat = parseFloat(this.getAttribute('data-lat'));
                const lng = parseFloat(this.getAttribute('data-lng'));
                
                // Load shop details for the popup
                fetch(`get_shop.php?id=${shopId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const shop = data.shop;
                            
                            // Set modal title with shop name
                            document.querySelector('#locationModal .modal-title').textContent = `Location: ${shop.shop_name}`;
                            
                            // Initialize map
                            if (locationMap) {
                                locationMap.remove();
                            }
                            
                            locationMap = L.map('locationMap').setView([lat, lng], 15);
                            
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                            }).addTo(locationMap);
                            
                            // Add marker with popup
                            locationMarker = L.marker([lat, lng]).addTo(locationMap)
                                .bindPopup(`
                                    <div class="map-popup-content">
                                        <div class="map-popup-title">${shop.shop_name}</div>
                                        <div class="map-popup-info">
                                            <strong>Address:</strong> ${shop.city}, ${shop.district}<br>
                                            <strong>Status:</strong> ${shop.status}<br>
                                            <strong>Coordinates:</strong> ${lat.toFixed(6)}, ${lng.toFixed(6)}
                                        </div>
                                    </div>
                                `)
                                .openPopup();
                                
                            // Add circle around marker for better visibility
                            L.circle([lat, lng], {
                                color: 'blue',
                                fillColor: '#067ABF',
                                fillOpacity: 0.2,
                                radius: 100
                            }).addTo(locationMap);
                            
                            document.getElementById('locationModal').style.display = 'block';
                            
                            // Trigger map resize after modal is shown
                            setTimeout(() => {
                                locationMap.invalidateSize();
                            }, 100);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Toastify({
                            text: "Error loading shop location",
                            duration: 3000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#f44336",
                        }).showToast();
                    });
            });
        });
        
        // Edit shop functionality
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const shopId = this.getAttribute('data-id');
                const loadingOverlay = document.getElementById('loadingOverlay');
                loadingOverlay.style.display = 'flex';
                
                fetch(`get_shop.php?id=${shopId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const shop = data.shop;
                            document.getElementById('editModalBody').innerHTML = `
                                <form id="editShopForm">
                                    <input type="hidden" name="id" value="${shop.id}">
                                    <div class="form-group">
                                        <label class="form-label" for="edit_shop_name">Shop Name *</label>
                                        <input type="text" class="form-input" id="edit_shop_name" name="shop_name" value="${shop.shop_name}" required>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-col">
                                            <div class="form-group">
                                                <label class="form-label" for="edit_city">City *</label>
                                                <input type="text" class="form-input" id="edit_city" name="city" value="${shop.city}" required>
                                            </div>
                                        </div>
                                        <div class="form-col">
                                            <div class="form-group">
                                                <label class="form-label" for="edit_district">District *</label>
                                                <input type="text" class="form-input" id="edit_district" name="district" value="${shop.district}" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-col">
                                            <div class="form-group">
                                                <label class="form-label" for="edit_status">Status</label>
                                                <select class="form-select" id="edit_status" name="status">
                                                    <option value="Active" ${shop.status == 'Active' ? 'selected' : ''}>Active</option>
                                                    <option value="Inactive" ${shop.status == 'Inactive' ? 'selected' : ''}>Inactive</option>
                                                    <option value="Closed" ${shop.status == 'Closed' ? 'selected' : ''}>Closed</option>
                                                    <option value="On Hold" ${shop.status == 'On Hold' ? 'selected' : ''}>On Hold</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-col">
                                            <div class="form-group">
                                                <label class="form-label" for="edit_shop_status">Shop Status</label>
                                                <select class="form-select" id="edit_shop_status" name="shop_status">
                                                    <option value="Active" ${shop.shop_status == 'Active' ? 'selected' : ''}>Active</option>
                                                    <option value="Inactive" ${shop.shop_status == 'Inactive' ? 'selected' : ''}>Inactive</option>
                                                    <option value="Under Maintenance" ${shop.shop_status == 'Under Maintenance' ? 'selected' : ''}>Under Maintenance</option>
                                                    <option value="Renovation" ${shop.shop_status == 'Renovation' ? 'selected' : ''}>Renovation</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label" for="edit_products_prices">Product and Price Details</label>
                                        <textarea class="form-textarea" id="edit_products_prices" name="products_prices" rows="3">${shop.products_prices || ''}</textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label" for="edit_courier_status">Courier Status</label>
                                        <select class="form-select" id="edit_courier_status" name="courier_status">
                                            <option value="Pending" ${shop.courier_status == 'Pending' ? 'selected' : ''}>Pending</option>
                                            <option value="Accepted" ${shop.courier_status == 'Accepted' ? 'selected' : ''}>Accepted</option>
                                            <option value="Rejected" ${shop.courier_status == 'Rejected' ? 'selected' : ''}>Rejected</option>
                                            <option value="In Progress" ${shop.courier_status == 'In Progress' ? 'selected' : ''}>In Progress</option>
                                            <option value="Delivered" ${shop.courier_status == 'Delivered' ? 'selected' : ''}>Delivered</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label">Location</label>
                                        <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                                            <div style="flex: 1;">
                                                <label style="font-size: 0.9rem; color: #666;">Latitude</label>
                                                <input type="text" class="form-input" id="edit_location_lat" name="location_lat" value="${shop.location_lat || ''}" placeholder="e.g., 12.971598">
                                            </div>
                                            <div style="flex: 1;">
                                                <label style="font-size: 0.9rem; color: #666;">Longitude</label>
                                                <input type="text" class="form-input" id="edit_location_lng" name="location_lng" value="${shop.location_lng || ''}" placeholder="e.g., 77.594566">
                                            </div>
                                        </div>
                                        <div style="font-size: 0.85rem; color: #666; margin-top: 5px;">
                                            <i class="fas fa-info-circle"></i> Leave blank if location not available
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label" for="edit_description">Description / Comments</label>
                                        <textarea class="form-textarea" id="edit_description" name="description" rows="3">${shop.description || ''}</textarea>
                                    </div>
                                </form>
                            `;
                            document.getElementById('editModal').style.display = 'block';
                        }
                        loadingOverlay.style.display = 'none';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        loadingOverlay.style.display = 'none';
                        Toastify({
                            text: "Error loading shop details",
                            duration: 3000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#f44336",
                        }).showToast();
                    });
            });
        });
        
        // Save edited shop
        document.getElementById('editModalSave').addEventListener('click', function() {
            const formData = new FormData(document.getElementById('editShopForm'));
            const loadingOverlay = document.getElementById('loadingOverlay');
            loadingOverlay.style.display = 'flex';
            
            fetch('update_shop.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Toastify({
                        text: "Shop updated successfully",
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#4CAF50",
                    }).showToast();
                    document.getElementById('editModal').style.display = 'none';
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    Toastify({
                        text: data.message || "Error updating shop",
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
                    text: "Error updating shop",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#f44336",
                }).showToast();
            });
        });
        
        // Delete shop functionality
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
            
            fetch(`delete_shop.php?id=${currentDeleteId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Toastify({
                            text: "Shop deleted successfully",
                            duration: 3000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#4CAF50",
                        }).showToast();
                        document.getElementById('deleteModal').style.display = 'none';
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        Toastify({
                            text: data.message || "Error deleting shop",
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
                        text: "Error deleting shop",
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
            
            const url = new URL(window.location.href);
            url.searchParams.set('export', format);
            window.location.href = url.toString();
        });
        
        // Close buttons for modals
        document.getElementById('viewModalClose').addEventListener('click', function() {
            document.getElementById('viewModal').style.display = 'none';
        });
        
        document.getElementById('locationModalClose').addEventListener('click', function() {
            document.getElementById('locationModal').style.display = 'none';
        });
        
        document.getElementById('editModalCancel').addEventListener('click', function() {
            document.getElementById('editModal').style.display = 'none';
        });
        
        document.getElementById('deleteModalCancel').addEventListener('click', function() {
            document.getElementById('deleteModal').style.display = 'none';
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
        
        // Close location modal when pressing escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const locationModal = document.getElementById('locationModal');
                if (locationModal.style.display === 'block') {
                    locationModal.style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>