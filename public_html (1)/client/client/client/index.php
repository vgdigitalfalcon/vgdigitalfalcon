<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/login_form.php';
handle_portal_login('client');
render_login('client', 'Client login', 'One login per client. Reports shown here are the approved ones only.');
