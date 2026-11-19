<?php
/**
 * Root Delegate -> backend/api/enquiry.php
 */
$_POST['service'] = $_POST['service'] ?? 'contact';
require_once __DIR__ . '/backend/api/enquiry.php';
