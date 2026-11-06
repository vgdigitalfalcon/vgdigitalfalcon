<?php

$host = "127.0.0.1:3306";
$user = "u670120666_seopages";
$password = "SEOpages@2026";
$database = "u670120666_seopages";

$conn = new mysqli($host,$user,$password,$database);

if($conn->connect_error){
    die("Database Connection Failed");
}

?>