<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['captcha_answer'])) {
    $_SESSION['captcha_answer'] = $_POST['captcha_answer'];
}
?>