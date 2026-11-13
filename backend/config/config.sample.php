<?php
/**
 * VG Digital Falcon - Backend Configuration Template
 * 
 * Instructions:
 * 1. Copy this file to `config.php` in the same directory (`backend/config/config.php`).
 * 2. Fill in your actual database credentials and SMTP parameters.
 * 3. Never commit `config.php` with sensitive credentials into version control.
 */

return [
    'environment' => 'production', // 'development' or 'production'

    'database' => [
        'host'     => 'localhost',
        'database' => 'vgdigitalfalcon_db',
        'username' => 'vgdigitalfalcon_user',
        'password' => 'YOUR_SECURE_PASSWORD_HERE',
        'charset'  => 'utf8mb4'
    ],

    'mail' => [
        'from_email' => 'contact@vgdigitalfalcon.com',
        'from_name'  => 'VG Digital Falcon Notification',
        'to_emails'  => [
            'contact@vgdigitalfalcon.com',
            'madan.n@vgdigitalfalcon.com'
        ],
        'smtp' => [
            'enabled'    => true,
            'host'       => 'mail.vgdigitalfalcon.com',
            'port'       => 587,
            'encryption' => 'tls', // 'tls' or 'ssl'
            'username'   => 'contact@vgdigitalfalcon.com',
            'password'   => 'YOUR_SMTP_PASSWORD_HERE'
        ]
    ],

    'cors' => [
        'allowed_origins' => [
            'https://vgdigitalfalcon.com',
            'https://vgdigitalfalcon.github.io'
        ]
    ]
];

