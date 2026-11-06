<?php
$host = "localhost";
$user = "u670120666_mkn";   // your mysql user
$pass = "u670120666_MKN";       // your mysql password
$db   = "u670120666_mkn";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("DB Connection Failed: " . $conn->connect_error);
}
?>
