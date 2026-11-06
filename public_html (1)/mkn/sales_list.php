<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Initialize filter variables
$filter_customer = isset($_GET['customer']) ? $_GET['customer'] : '';
$filter_city = isset($_GET['city']) ? $_GET['city'] : '';
$filter_date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filter_date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$filter_period = isset($_GET['period']) ? $_GET['period'] : '';
$filter_payment_status = isset($_GET['payment_status']) ? $_GET['payment_status'] : '';
$filter_year = isset($_GET['year']) ? $_GET['year'] : '';
$filter_month = isset($_GET['month']) ? $_GET['month'] : '';

// Calculate turnover metrics based on current filters
$current_range_turnover = 0;
$last_3_months_turnover = 0;
$all_time_turnover = 0;
$yet_to_pay = 0;
$average_sales = 0;

// Build WHERE clause for filtering
$where_conditions = [];
$params = [];
$param_types = '';

if (!empty($filter_customer)) {
    $where_conditions[] = "s.customer_id = ?";
    $params[] = $filter_customer;
    $param_types .= 'i';
}

if (!empty($filter_city)) {
    $where_conditions[] = "c.city LIKE ?";
    $params[] = "%$filter_city%";
    $param_types .= 's';
}

if (!empty($filter_payment_status)) {
    $where_conditions[] = "s.payment_status = ?";
    $params[] = $filter_payment_status;
    $param_types .= 's';
}

// Handle year filter
if (!empty($filter_year)) {
    $where_conditions[] = "YEAR(s.billing_date) = ?";
    $params[] = $filter_year;
    $param_types .= 's';
}

// Handle month filter
if (!empty($filter_month)) {
    $where_conditions[] = "MONTH(s.billing_date) = ?";
    $params[] = $filter_month;
    $param_types .= 's';
}

// Handle date range filter
if (!empty($filter_date_from) && !empty($filter_date_to)) {
    $where_conditions[] = "s.billing_date BETWEEN ? AND ?";
    $params[] = $filter_date_from;
    $params[] = $filter_date_to;
    $param_types .= 'ss';
} elseif (!empty($filter_period)) {
    // Only apply period filter if no custom date range is specified
    $today = date('Y-m-d');
    switch ($filter_period) {
        case 'this_month':
            $where_conditions[] = "MONTH(s.billing_date) = MONTH(CURDATE()) AND YEAR(s.billing_date) = YEAR(CURDATE())";
            break;
        case 'last_month':
            $where_conditions[] = "MONTH(s.billing_date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(s.billing_date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
            break;
        case 'this_quarter':
            $current_quarter = ceil(date('n') / 3);
            $where_conditions[] = "QUARTER(s.billing_date) = ? AND YEAR(s.billing_date) = YEAR(CURDATE())";
            $params[] = $current_quarter;
            $param_types .= 'i';
            break;
        case 'last_quarter':
            $current_quarter = ceil(date('n') / 3);
            $last_quarter = $current_quarter - 1;
            $year = date('Y');
            if ($last_quarter == 0) {
                $last_quarter = 4;
                $year = $year - 1;
            }
            $where_conditions[] = "QUARTER(s.billing_date) = ? AND YEAR(s.billing_date) = ?";
            $params[] = $last_quarter;
            $params[] = $year;
            $param_types .= 'is';
            break;
        case 'this_year':
            $where_conditions[] = "YEAR(s.billing_date) = YEAR(CURDATE())";
            break;
        case 'last_year':
            $where_conditions[] = "YEAR(s.billing_date) = YEAR(CURDATE()) - 1";
            break;
        case 'last_3_months':
            $where_conditions[] = "s.billing_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
            break;
        case 'last_6_months':
            $where_conditions[] = "s.billing_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)";
            break;
    }
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Fetch sales data
$sql = "SELECT s.id, s.invoice_no, s.billing_date, s.transport_cost, s.total_taxable_value,
               s.cgst, s.sgst, s.grand_total, s.apply_gst, s.payment_status, s.amount_paid, s.amount_due,
               c.customer_name, c.shop_name, c.city, c.state,
               GROUP_CONCAT(CONCAT(p.product_name, '||', si.quantity) SEPARATOR '##') as product_details,
               SUM(si.quantity) as total_quantity
        FROM sales s
        LEFT JOIN customers c ON s.customer_id = c.id
        LEFT JOIN sale_items si ON s.id = si.sale_id
        LEFT JOIN products p ON si.product_id = p.id
        $where_clause
        GROUP BY s.id
        ORDER BY s.billing_date DESC, s.id DESC";

$sales = [];
try {
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
    $stmt->execute();
    $sales_result = $stmt->get_result();
    if ($sales_result->num_rows > 0) {
        while ($row = $sales_result->fetch_assoc()) {
            $sales[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Error fetching sales: " . $e->getMessage());
}

// Fetch customers for filter dropdown
$customers = [];
$customer_result = $conn->query("SELECT id, customer_name FROM customers ORDER BY customer_name ASC");
if ($customer_result->num_rows > 0) {
    while ($row = $customer_result->fetch_assoc()) {
        $customers[] = $row;
    }
}

// Fetch unique cities for filter dropdown
$cities = [];
$city_result = $conn->query("SELECT DISTINCT city FROM customers WHERE city IS NOT NULL AND city != '' ORDER BY city ASC");
if ($city_result->num_rows > 0) {
    while ($row = $city_result->fetch_assoc()) {
        $cities[] = $row['city'];
    }
}

// Fetch available years for filter dropdown
$years = [];
$year_result = $conn->query("SELECT DISTINCT YEAR(billing_date) as year FROM sales WHERE billing_date IS NOT NULL ORDER BY year DESC");
if ($year_result->num_rows > 0) {
    while ($row = $year_result->fetch_assoc()) {
        $years[] = $row['year'];
    }
}

// Month names for dropdown
$months = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];

// Current range turnover (based on applied filters)
$current_range_sql = "SELECT SUM(grand_total) as total FROM sales s LEFT JOIN customers c ON s.customer_id = c.id $where_clause";
try {
    $current_range_stmt = $conn->prepare($current_range_sql);
    if (!empty($params)) {
        $current_range_stmt->bind_param($param_types, ...$params);
    }
    $current_range_stmt->execute();
    $current_range_result = $current_range_stmt->get_result();
    if ($current_range_result->num_rows > 0) {
        $row = $current_range_result->fetch_assoc();
        $current_range_turnover = $row['total'] ?? 0;
    }
} catch (Exception $e) {
    error_log("Error calculating current range turnover: " . $e->getMessage());
}

// Last 3 months turnover (ignoring current filters)
$last_3_months_sql = "SELECT SUM(grand_total) as total FROM sales WHERE billing_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
$last_3_months_result = $conn->query($last_3_months_sql);
if ($last_3_months_result->num_rows > 0) {
    $row = $last_3_months_result->fetch_assoc();
    $last_3_months_turnover = $row['total'] ?? 0;
}

// All time turnover (ignoring current filters)
$all_time_sql = "SELECT SUM(grand_total) as total FROM sales";
$all_time_result = $conn->query($all_time_sql);
if ($all_time_result->num_rows > 0) {
    $row = $all_time_result->fetch_assoc();
    $all_time_turnover = $row['total'] ?? 0;
}

// Yet to pay amount (based on current filters)
// Yet to pay amount - ONLY for not_paid and partially_paid statuses
$yet_to_pay_conditions = $where_conditions;
$yet_to_pay_conditions[] = "s.payment_status IN ('not_paid', 'partially_paid')";

$yet_to_pay_where = '';
if (!empty($yet_to_pay_conditions)) {
    $yet_to_pay_where = 'WHERE ' . implode(' AND ', $yet_to_pay_conditions);
}

$yet_to_pay_sql = "SELECT SUM(amount_due) as total FROM sales s LEFT JOIN customers c ON s.customer_id = c.id $yet_to_pay_where";

try {
    $yet_to_pay_stmt = $conn->prepare($yet_to_pay_sql);
    if (!empty($params)) {
        $yet_to_pay_stmt->bind_param($param_types, ...$params);
    }
    $yet_to_pay_stmt->execute();
    $yet_to_pay_result = $yet_to_pay_stmt->get_result();
    if ($yet_to_pay_result->num_rows > 0) {
        $row = $yet_to_pay_result->fetch_assoc();
        $yet_to_pay = $row['total'] ?? 0;
    }
} catch (Exception $e) {
    error_log("Error calculating yet to pay: " . $e->getMessage());
    $yet_to_pay = 0;
}

// Average sales value (based on current filters)
$average_sales_sql = "SELECT AVG(grand_total) as average FROM sales s LEFT JOIN customers c ON s.customer_id = c.id $where_clause";
try {
    $average_sales_stmt = $conn->prepare($average_sales_sql);
    if (!empty($params)) {
        $average_sales_stmt->bind_param($param_types, ...$params);
    }
    $average_sales_stmt->execute();
    $average_sales_result = $average_sales_stmt->get_result();
    if ($average_sales_result->num_rows > 0) {
        $row = $average_sales_result->fetch_assoc();
        $average_sales = $row['average'] ?? 0;
    }
} catch (Exception $e) {
    error_log("Error calculating average sales: " . $e->getMessage());
}

// Calculate wallet amount (Total Sales - Yet to Pay) based on current filters
$wallet_amount = $current_range_turnover - $yet_to_pay;

// Handle payment status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment_status'])) {
    $sale_id = $_POST['sale_id'];
    $payment_status = $_POST['payment_status'];
    $amount_paid = $_POST['amount_paid'] ?? 0;
    
    // Calculate amount due
    $sale_sql = "SELECT grand_total FROM sales WHERE id = ?";
    $stmt = $conn->prepare($sale_sql);
    $stmt->bind_param("i", $sale_id);
    $stmt->execute();
    $sale_result = $stmt->get_result();
    
    if ($sale_result->num_rows > 0) {
        $sale = $sale_result->fetch_assoc();
        $grand_total = $sale['grand_total'];
        
        // Auto-calculate amount paid based on status if not provided
        if ($payment_status === 'paid') {
            $amount_paid = $grand_total;
        } elseif ($payment_status === 'not_paid') {
            $amount_paid = 0;
        }
        
        $amount_due = $grand_total - $amount_paid;
        
        // Update payment status
        $update_sql = "UPDATE sales SET payment_status = ?, amount_paid = ?, amount_due = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sddi", $payment_status, $amount_paid, $amount_due, $sale_id);
        
        if ($stmt->execute()) {
            $message = "✅ Payment status updated successfully!";
        } else {
            $message = "❌ Error updating payment status: " . $stmt->error;
        }
    }
    
    // Redirect to avoid resubmission
    header("Location: sales_list.php?message=" . urlencode($message) . "&" . http_build_query($_GET));
    exit();
}

// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=sales_list_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM to fix UTF-8 in Excel
    fwrite($output, "\xEF\xBB\xBF");
    
    // Header row
    fputcsv($output, [
        'Invoice No', 
        'Date', 
        'Customer', 
        'Shop Name', 
        'City', 
        'Products', 
        'Quantity', 
        'Taxable Value', 
        'CGST', 
        'SGST', 
        'Transport Cost', 
        'Grand Total',
        'Payment Status',
        'Amount Paid',
        'Amount Due'
    ]);
    
    // Data rows
    foreach ($sales as $sale) {
        $products_text = implode(', ', array_map(function($prod) {
            return explode('||', $prod)[0];
        }, explode('##', $sale['product_details'])));
        
        fputcsv($output, [
            $sale['invoice_no'],
            $sale['billing_date'],
            $sale['customer_name'],
            $sale['shop_name'],
            $sale['city'],
            $products_text,
            $sale['total_quantity'],
            $sale['total_taxable_value'],
            $sale['cgst'],
            $sale['sgst'],
            $sale['transport_cost'],
            $sale['grand_total'],
            $sale['payment_status'],
            $sale['amount_paid'],
            $sale['amount_due']
        ]);
    }
    
    fclose($output);
    exit();
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First delete sale items
        $delete_items_sql = "DELETE FROM sale_items WHERE sale_id = ?";
        $stmt = $conn->prepare($delete_items_sql);
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        
        // Then delete the sale
        $delete_sale_sql = "DELETE FROM sales WHERE id = ?";
        $stmt = $conn->prepare($delete_sale_sql);
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Redirect to avoid resubmission
        header("Location: sales_list.php?message=✅ Sale deleted successfully&" . http_build_query($_GET));
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: sales_list.php?message=❌ Error deleting sale: " . $e->getMessage() . "&" . http_build_query($_GET));
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales List - Financial Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            transform: translateX(-100%);
        }
        
        .sidebar.active {
            transform: translateX(0);
        }
        
        .sidebar-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.2);
            text-align: center;
            border-bottom: var(--section-border);
            position: relative;
        }
        
        .sidebar-header h3 {
            font-size: 1.5rem;
            margin: 0;
            color: var(--light-text);
        }
        
        .close-sidebar {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            display: none;
        }
        
        .sidebar-menu {
            padding: 15px 0;
            height: calc(100vh - 80px);
            overflow-y: auto;
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
            transition: margin-left 0.3s ease;
            width: 100%;
        }
        
        /* Top Bar */
        .top-bar {
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        /* Wallet specific styles */
        .wallet-card {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
            padding: 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .wallet-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(30deg);
        }
        
        .wallet-card .metric-title {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
        }
        
        .wallet-card .metric-value {
            color: white;
            font-size: 2rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .wallet-card .metric-subtitle {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .wallet-icon {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 1.5rem;
            opacity: 0.7;
        }
        
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
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
            padding: 20px;
        }
        
        .page-title {
            margin-bottom: 25px;
            color: var(--primary-dark);
            font-size: 1.8rem;
            display: flex;
            align-items: center;
        }
        
        .page-title i {
            margin-right: 10px;
            color: var(--primary-medium);
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
            margin-bottom: 15px;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .filters-title i {
            margin-right: 10px;
            color: var(--primary-medium);
        }
        
        .toggle-filters {
            background: none;
            border: none;
            color: var(--primary-medium);
            font-size: 1.2rem;
            cursor: pointer;
            display: none;
        }
        
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .filter-form.collapsed {
            display: none;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-text);
        }
        
        .form-input, .form-select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border 0.3s ease;
        }
        
        .form-input:focus, .form-select:focus {
            border-color: var(--primary-medium);
            outline: none;
            box-shadow: 0 0 0 3px rgba(6, 122, 191, 0.1);
        }
        
        .form-btn {
            background: var(--primary-medium);
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .form-btn:hover {
            background: var(--primary-light);
        }
        
        .form-btn i {
            margin-right: 8px;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-success {
            background: var(--success);
        }
        
        .btn-success:hover {
            background: #3d8b40;
        }
        
        .btn-danger {
            background: var(--danger);
        }
        
        .btn-danger:hover {
            background: #d32f2f;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
        }
        
        /* Metrics Container */
        .metrics-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .metric-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            padding: 20px;
            text-align: center;
        }
        
        .metric-title {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }
        
        .metric-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-dark);
        }
        
        .metric-subtitle {
            font-size: 0.8rem;
            color: #888;
            margin-top: 5px;
        }
        
        /* Table Container */
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            padding: 20px;
            overflow-x: auto;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .table-title {
            font-size: 1.2rem;
            color: var(--primary-dark);
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            white-space: nowrap;
        }
        
        .data-table th {
            background-color: var(--primary-medium);
            color: white;
            text-align: left;
            padding: 12px;
            font-weight: 600;
        }
        
        .data-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        .data-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .action-cell {
            white-space: nowrap;
        }
        
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 4px;
            color: white;
            margin-right: 5px;
        }
        
        .action-btn.view {
            background: var(--primary-medium);
        }
        
        .action-btn.edit {
            background: var(--warning);
        }
        
        .action-btn.delete {
            background: var(--danger);
        }
        
        .action-btn.print {
            background: #6c757d;
        }
        
        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        /* Payment Status Styles */
        .payment-status-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .payment-status-select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .amount-paid-input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            display: none;
        }
        
        .amount-due-display {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .payment-status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-paid {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border: 1px solid var(--success);
        }
        
        .status-not-paid {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger);
            border: 1px solid var(--danger);
        }
        
        .status-partially-paid {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning);
            border: 1px solid var(--warning);
        }
        
        .update-payment-btn {
            background: var(--success);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            margin-top: 5px;
        }
        
        .update-payment-btn:hover {
            background: #3d8b40;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            text-align: center;
            font-weight: 500;
        }
        
        .success {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border: 1px solid var(--success);
        }
        
        .error {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger);
            border: 1px solid var(--danger);
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #ccc;
        }
        
        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
        
        /* Mobile Menu Toggle */
        .menu-toggle {
            display: flex;
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
        
        /* Mobile Responsive Styles */
        @media (max-width: 992px) {
            .sidebar {
                width: 280px;
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .close-sidebar {
                display: block;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .content {
                padding: 15px;
            }
            
            .filters-container, .table-container {
                padding: 15px;
            }
            
            .filter-form {
                grid-template-columns: 1fr;
            }
            
            .metrics-container {
                grid-template-columns: 1fr;
            }
            
            .data-table {
                font-size: 14px;
            }
            
            .action-cell {
                white-space: normal;
            }
            
            .toggle-filters {
                display: block;
            }
        }
        
        @media (max-width: 768px) {
            .top-bar {
                padding: 15px;
            }
            
            .user-info span {
                display: none;
            }
            
            .table-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .btn-group {
                width: 100%;
                justify-content: space-between;
            }
            
            .form-btn {
                padding: 10px 15px;
                font-size: 13px;
            }
            
            .data-table {
                display: block;
                overflow-x: auto;
            }
            
            .data-table th, .data-table td {
                padding: 8px;
                font-size: 13px;
            }
            
            .payment-status-form {
                min-width: 150px;
            }
        }
        
        @media (max-width: 576px) {
            .page-title {
                font-size: 1.5rem;
            }
            
            .metric-card {
                padding: 15px;
            }
            
            .metric-value {
                font-size: 1.5rem;
            }
            
            .btn-group {
                flex-direction: column;
                gap: 10px;
            }
            
            .form-btn {
                width: 100%;
            }
        }
        
        /* Mobile App-like Styles */
        @media (max-width: 480px) {
            body {
                font-size: 14px;
            }
            
            .content {
                padding: 10px;
            }
            
            .filters-container, .table-container {
                padding: 15px 10px;
                border-radius: 8px;
            }
            
            .metric-card {
                padding: 15px 10px;
            }
            
            .data-table {
                font-size: 12px;
            }
            
            .data-table th, .data-table td {
                padding: 6px 4px;
            }
            
            .action-btn {
                width: 28px;
                height: 28px;
                font-size: 12px;
            }
            
            .payment-status-form {
                min-width: 120px;
            }
            
            .payment-status-select, .amount-paid-input {
                font-size: 12px;
                padding: 6px;
            }
            
            .update-payment-btn {
                font-size: 11px;
                padding: 5px 8px;
            }
        }
        
        /* Overlay for mobile sidebar */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }
        
        .sidebar-overlay.active {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>MKN Bro's</h3>
            <button class="close-sidebar" id="closeSidebar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                
                <div class="menu-section">
                    <div class="menu-title">Sales Management</div>
                    <li><a href="add_sales.php"><i class="fas fa-cart-plus"></i> <span>Add Sales</span></a></li>
                    <li><a href="sales_list.php" class="active"><i class="fas fa-list"></i> <span>Sales List</span></a></li>
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
                
                <!-- Purchase Management section removed as requested -->
                
                <div class="menu-section">
                    <div class="menu-title">User Management</div>
                    <li><a href="users_list.php"><i class="fas fa-users"></i> <span>Users List</span></a></li>
                </div>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="user-info">
                <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iI2ZmZiI+PHBhdGggZD0iTTEyIDJDNi40OCAyIDIgNi40OCAyIDEyczQuNDggMTAgMTAgMTAgMTAtNC40OCAxMC0xMFMxNy41MiAyIDEyIDJ6bTAgM2MyLjIxIDAgNCA1Ljc5IDQgNHMtMS43OSA0LTQgNC00LTEuNzktNC00IDEuNzktNCA0LTR6bTAgMTcuMDJjLTMuMzMgMC02LjI4LTEuNzEtOC02LjAyIDIuMDUtMy4xNiA1LjI5LTUgOC41LTUgMy4yMSAwIDYuNDUgMS44NCA4LjUgNSAwLTQuMzEtMy41LTgtOC01LTguMDJ6Ii8+PC9zdmc+" alt="User">
                <span>Welcome, <?php echo $_SESSION['user']; ?></span>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="content">
            <h1 class="page-title"></h1>
            
            <?php if (isset($_GET['message'])): ?>
                <div class="message <?php echo strpos($_GET['message'], '✅') !== false ? 'success' : 'error'; ?>">
                    <?php echo $_GET['message']; ?>
                </div>
            <?php endif; ?>
                
            <div class="filters-container">
                <h2 class="filters-title">
                    <i class="fas fa-filter"></i> Filter Sales
                    <button class="toggle-filters" id="toggleFilters">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </h2>
                <form method="GET" action="sales_list.php" class="filter-form" id="filterForm">
                    <div class="form-group">
                        <label class="form-label" for="customer">Customer</label>
                        <select class="form-select" id="customer" name="customer">
                            <option value="">All Customers</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?php echo $customer['id']; ?>" <?php echo $filter_customer == $customer['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($customer['customer_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="city">City</label>
                        <select class="form-select" id="city" name="city">
                            <option value="">All Cities</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?php echo $city; ?>" <?php echo $filter_city == $city ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($city); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="payment_status">Payment Status</label>
                        <select class="form-select" id="payment_status" name="payment_status">
                            <option value="">All Status</option>
                            <option value="paid" <?php echo $filter_payment_status == 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="not_paid" <?php echo $filter_payment_status == 'not_paid' ? 'selected' : ''; ?>>Not Paid</option>
                            <option value="partially_paid" <?php echo $filter_payment_status == 'partially_paid' ? 'selected' : ''; ?>>Partially Paid</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="year">Select Year</label>
                        <select class="form-select" id="year" name="year">
                            <option value="">All Years</option>
                            <?php foreach ($years as $year): ?>
                                <option value="<?php echo $year; ?>" <?php echo $filter_year == $year ? 'selected' : ''; ?>>
                                    <?php echo $year; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="month">Select Month</label>
                        <select class="form-select" id="month" name="month">
                            <option value="">All Months</option>
                            <?php foreach ($months as $key => $month): ?>
                                <option value="<?php echo $key; ?>" <?php echo $filter_month == $key ? 'selected' : ''; ?>>
                                    <?php echo $month; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="period">Time Period</label>
                        <select class="form-select" id="period" name="period">
                            <option value="">All Time</option>
                            <option value="this_month" <?php echo $filter_period == 'this_month' ? 'selected' : ''; ?>>This Month</option>
                            <option value="last_month" <?php echo $filter_period == 'last_month' ? 'selected' : ''; ?>>Last Month</option>
                            <option value="this_quarter" <?php echo $filter_period == 'this_quarter' ? 'selected' : ''; ?>>This Quarter</option>
                            <option value="last_quarter" <?php echo $filter_period == 'last_quarter' ? 'selected' : ''; ?>>Last Quarter</option>
                            <option value="this_year" <?php echo $filter_period == 'this_year' ? 'selected' : ''; ?>>This Year</option>
                            <option value="last_year" <?php echo $filter_period == 'last_year' ? 'selected' : ''; ?>>Last Year</option>
                            <option value="last_3_months" <?php echo $filter_period == 'last_3_months' ? 'selected' : ''; ?>>Last 3 Months</option>
                            <option value="last_6_months" <?php echo $filter_period == 'last_6_months' ? 'selected' : ''; ?>>Last 6 Months</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="date_from">Date From</label>
                        <input type="date" class="form-input" id="date_from" name="date_from" value="<?php echo $filter_date_from; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="date_to">Date To</label>
                        <input type="date" class="form-input" id="date_to" name="date_to" value="<?php echo $filter_date_to; ?>">
                    </div>
                    
                    <div class="form-group" style="display: flex; align-items: flex-end;">
                        <button type="submit" class="form-btn">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <a href="sales_list.php" class="form-btn btn-secondary" style="margin-left: 10px;">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </form>
            </div>
            
            <div class="metrics-container">
                <div class="metric-card">
                    <div class="metric-title">Current Range Sales</div>
                    <div class="metric-value">₹<?php echo number_format($current_range_turnover, 2); ?></div>
                    <div class="metric-subtitle">Based on applied filters</div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-title">Last 3 Months</div>
                    <div class="metric-value">₹<?php echo number_format($last_3_months_turnover, 2); ?></div>
                    <div class="metric-subtitle"><?php echo date('F Y', strtotime('-2 months')) . ' - ' . date('F Y'); ?></div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-title">All Time</div>
                    <div class="metric-value">₹<?php echo number_format($all_time_turnover, 2); ?></div>
                    <div class="metric-subtitle">All sales records</div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-title">Yet To Pay</div>
                    <div class="metric-value">₹<?php echo number_format($yet_to_pay, 2); ?></div>
                    <div class="metric-subtitle">Outstanding amount</div>
                </div>
                
                <div class="metric-card">
                    <i class="fas fa-wallet wallet-icon"></i>
                    <div class="metric-title">Amount in Wallet</div>
                    <div class="metric-value">₹<?php echo number_format($wallet_amount, 2); ?></div>
                    <div class="metric-subtitle">Total Sales - Without Adding Yet to Pay</div>
                </div>
            </div>
            
            <div class="table-container">
                <div class="table-header">
                    <h2 class="table-title">Sales Records</h2>
                    <div class="btn-group">
                        <a href="sales_list.php?export=csv<?php echo !empty($_GET) ? '&' . http_build_query($_GET) : ''; ?>" class="form-btn btn-success">
                            <i class="fas fa-file-export"></i> Export CSV
                        </a>
                        <a href="add_sales.php" class="form-btn">
                            <i class="fas fa-plus"></i> Add New Sale
                        </a>
                    </div>
                </div>
                
                <?php if (count($sales) > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Invoice No</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Shop Name</th>
                                <th>City</th>
                                <th>Products</th>
                                <th>Qty</th>
                                <th>Grand Total</th>
                                <th>Payment Status</th>
                                <th>Amount Paid</th>
                                <th>Amount Due</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales as $sale): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($sale['invoice_no']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($sale['billing_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($sale['shop_name']); ?></td>
                                    <td><?php echo htmlspecialchars($sale['city']); ?></td>
                                    <td>
                                        <?php 
                                        $products_list = explode('##', $sale['product_details']);
                                        foreach ($products_list as $prod_item) {
                                            $parts = explode('||', $prod_item);
                                            echo htmlspecialchars($parts[0]) . "<br>";
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        foreach ($products_list as $prod_item) {
                                            $parts = explode('||', $prod_item);
                                            echo intval($parts[1]) . "<br>";
                                        }
                                        ?>
                                    </td>
                                    <td>₹<?php echo number_format($sale['grand_total'], 2); ?></td>
                                    <td>
                                        <form method="post" action="sales_list.php" class="payment-status-form" onsubmit="return validatePaymentForm(this)">
                                            <input type="hidden" name="sale_id" value="<?php echo $sale['id']; ?>">
                                            <input type="hidden" name="update_payment_status" value="1">
                                            
                                            <select class="payment-status-select" name="payment_status" onchange="toggleAmountPaid(this, <?php echo $sale['grand_total']; ?>)">
                                                <option value="not_paid" <?php echo $sale['payment_status'] == 'not_paid' ? 'selected' : ''; ?>>Not Paid</option>
                                                <option value="partially_paid" <?php echo $sale['payment_status'] == 'partially_paid' ? 'selected' : ''; ?>>Partially Paid</option>
                                                <option value="paid" <?php echo $sale['payment_status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                            </select>
                                            
                                            <input type="number" class="amount-paid-input" name="amount_paid" 
                                                   placeholder="Amount Paid" 
                                                   value="<?php echo $sale['amount_paid']; ?>"
                                                   min="0" max="<?php echo $sale['grand_total']; ?>" step="0.01">
                                            
                                            <div class="amount-due-display">
                                                Due: ₹<span><?php echo number_format($sale['amount_due'], 2); ?></span>
                                            </div>
                                            
                                            <button type="submit" class="update-payment-btn">
                                                <i class="fas fa-save"></i> Update
                                            </button>
                                        </form>
                                    </td>
                                    <td>₹<?php echo number_format($sale['amount_paid'], 2); ?></td>
                                    <td>
                                        <span class="payment-status-badge <?php echo 'status-' . str_replace('_', '-', $sale['payment_status']); ?>">
                                            ₹<?php echo number_format($sale['amount_due'], 2); ?>
                                        </span>
                                    </td>
                                    <td class="action-cell">
                                        <a href="view_sale.php?id=<?php echo $sale['id']; ?>" class="action-btn view" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_sale.php?id=<?php echo $sale['id']; ?>" class="action-btn edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="print_sale.php?id=<?php echo $sale['id']; ?>" target="_blank" class="action-btn print" title="Print">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <a href="sales_list.php?delete_id=<?php echo $sale['id']; ?>" class="action-btn delete" title="Delete" onclick="return confirm('Are you sure you want to delete this sale?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-receipt"></i>
                        <p>No sales records found</p>
                        <a href="add_sales.php" class="form-btn">
                            <i class="fas fa-plus"></i> Add Your First Sale
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Mobile sidebar functionality
        const menuToggle = document.getElementById('menuToggle');
        const closeSidebar = document.getElementById('closeSidebar');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        menuToggle.addEventListener('click', function() {
            sidebar.classList.add('active');
            sidebarOverlay.classList.add('active');
        });
        
        closeSidebar.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });
        
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });
        
        // Toggle filters on mobile
        const toggleFilters = document.getElementById('toggleFilters');
        const filterForm = document.getElementById('filterForm');
        
        toggleFilters.addEventListener('click', function() {
            filterForm.classList.toggle('collapsed');
            const icon = toggleFilters.querySelector('i');
            if (filterForm.classList.contains('collapsed')) {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        });
        
        // Enhanced filter management
        const periodSelect = document.getElementById('period');
        const dateFromInput = document.getElementById('date_from');
        const dateToInput = document.getElementById('date_to');
        const yearSelect = document.getElementById('year');
        const monthSelect = document.getElementById('month');
        
        function toggleDateInputs() {
            // Disable date inputs when period is selected
            if (periodSelect.value) {
                dateFromInput.disabled = true;
                dateToInput.disabled = true;
                dateFromInput.value = '';
                dateToInput.value = '';
            } else {
                dateFromInput.disabled = false;
                dateToInput.disabled = false;
            }
            
            // Disable period when custom date range is specified
            if (dateFromInput.value || dateToInput.value) {
                periodSelect.disabled = true;
                periodSelect.value = '';
            } else {
                periodSelect.disabled = false;
            }
            
            // Disable period when year/month are selected
            if (yearSelect.value || monthSelect.value) {
                periodSelect.disabled = true;
                periodSelect.value = '';
            }
        }
        
        // Event listeners for filter interactions
        periodSelect.addEventListener('change', toggleDateInputs);
        dateFromInput.addEventListener('change', toggleDateInputs);
        dateToInput.addEventListener('change', toggleDateInputs);
        yearSelect.addEventListener('change', toggleDateInputs);
        monthSelect.addEventListener('change', toggleDateInputs);
        
        // Initialize on page load
        toggleDateInputs();
        
        // Payment status functionality
        function toggleAmountPaid(selectElement, grandTotal) {
            const form = selectElement.closest('form');
            const amountPaidInput = form.querySelector('.amount-paid-input');
            const amountDueDisplay = form.querySelector('.amount-due-display span');
            
            if (selectElement.value === 'partially_paid') {
                amountPaidInput.style.display = 'block';
                amountPaidInput.focus();
            } else {
                amountPaidInput.style.display = 'none';
                
                // Auto-calculate amount paid based on status
                if (selectElement.value === 'paid') {
                    amountPaidInput.value = grandTotal;
                } else if (selectElement.value === 'not_paid') {
                    amountPaidInput.value = 0;
                }
            }
            
            // Update amount due display
            const amountPaid = parseFloat(amountPaidInput.value) || 0;
            const amountDue = grandTotal - amountPaid;
            amountDueDisplay.textContent = amountDue.toFixed(2);
        }
        
        // Validate payment form
        function validatePaymentForm(form) {
            const paymentStatus = form.querySelector('.payment-status-select').value;
            const amountPaid = parseFloat(form.querySelector('.amount-paid-input').value) || 0;
            const grandTotal = parseFloat(form.querySelector('.amount-paid-input').max);
            
            if (paymentStatus === 'partially_paid') {
                if (amountPaid <= 0) {
                    alert('Please enter a valid amount paid for partially paid status');
                    return false;
                }
                if (amountPaid >= grandTotal) {
                    alert('Amount paid cannot be equal to or greater than grand total for partially paid status');
                    return false;
                }
            }
            
            return true;
        }
        
        // Initialize payment status forms on page load
        document.addEventListener('DOMContentLoaded', function() {
            const paymentForms = document.querySelectorAll('.payment-status-form');
            paymentForms.forEach(form => {
                const select = form.querySelector('.payment-status-select');
                const grandTotal = parseFloat(form.querySelector('.amount-paid-input').max);
                toggleAmountPaid(select, grandTotal);
            });
            
            // Collapse filters by default on mobile
            if (window.innerWidth <= 992) {
                filterForm.classList.add('collapsed');
                const icon = toggleFilters.querySelector('i');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            }
        });
    </script>
</body>
</html>