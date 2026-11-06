<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/login_form.php';
handle_portal_login('employee');
render_login('employee', 'Employee login', 'SEO, SMO, and Ads staff. This page does not open the admin portal.');
