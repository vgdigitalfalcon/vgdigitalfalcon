<?php
// db.php - Database connection file
$host = '127.0.0.1:3306';
$dbname = 'u670120666_leads';
$username = 'u670120666_leads';
$password = 'Leads@2025';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}
?>