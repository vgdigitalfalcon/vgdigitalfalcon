<?php
// process_form.php - Form processing script
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include database connection
    require_once 'db.php';
    
    // Get and sanitize form data
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $subject = htmlspecialchars(trim($_POST['subject']));
    $message = htmlspecialchars(trim($_POST['message']));
    $consent = isset($_POST['consent']) ? 1 : 0;
    
    try {
        // Prepare SQL statement
        $sql = "INSERT INTO contact_submissions (name, email, phone, subject, message, consent) 
                VALUES (:name, :email, :phone, :subject, :message, :consent)";
        
        $stmt = $pdo->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':consent', $consent);
        
        // Execute the query
        if ($stmt->execute()) {
            // Redirect back to contact page with success message
            header("Location: contact.html?status=success");
            exit();
        } else {
            header("Location: contact.html?status=error");
            exit();
        }
    } catch(PDOException $e) {
        // Log error
        error_log("Database error: " . $e->getMessage());
        header("Location: contact.html?status=error");
        exit();
    }
} else {
    // If not a POST request, redirect to contact page
    header("Location: contact.html");
    exit();
}
?>