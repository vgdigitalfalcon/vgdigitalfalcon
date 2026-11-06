<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $product_code = $_POST['product_code'];
    $hsn_code = $_POST['hsn_code'];
    $product_name = $_POST['product_name'];
    $piece_weight = $_POST['piece_weight'];
    $packing_mode = $_POST['packing_mode'];
    $basic_price = $_POST['basic_price'];
    $gst = $_POST['gst'];
    $gross_price = $_POST['gross_price'];
    $margin = $_POST['margin']; // This should be the percentage (45)
    $mrp = $_POST['mrp'];

    $sql = "UPDATE products SET 
            product_code = ?, 
            hsn_code = ?, 
            product_name = ?, 
            piece_weight = ?, 
            packing_mode = ?, 
            basic_price = ?, 
            gst = ?, 
            gross_price = ?, 
            margin = ?, 
            mrp = ?
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssi", $product_code, $hsn_code, $product_name, $piece_weight, $packing_mode, $basic_price, $gst, $gross_price, $margin, $mrp, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating product: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>