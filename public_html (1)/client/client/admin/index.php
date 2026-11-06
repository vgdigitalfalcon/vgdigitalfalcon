<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/login_form.php';
handle_portal_login('admin');
render_login('admin', 'Admin login', 'Super admin only. Clients and employees cannot use this page.');
