<?php
// Configuration File
define('BASE_PATH', __DIR__);
define('AUDIO_PATH', BASE_PATH . '/audio-files/');
define('DB_PATH', BASE_PATH . '/database/links.json');
define('LINK_EXPIRY_HOURS', 24);
define('SITE_URL', 'http://localhost/audio-guide-system');
define('BASE_URL', 'http://localhost/audio-guide-system'); // Added missing constant

// Admin credentials (CHANGE THESE!)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123');

// Timezone (adjust to your location - Sri Lanka)
date_default_timezone_set('Asia/Colombo');

// Email Configuration
// IMPORTANT: Update these with your actual email credentials
define('SMTP_HOST', 'smtp.gmail.com');           // Gmail SMTP server
define('SMTP_PORT', 587);                         // TLS port
define('SMTP_USERNAME', 'jasonrajaratnam@gmail.com'); // Your Gmail address
define('SMTP_PASSWORD', '');   // App password (16 characters from Google) //ifkp dhbh hgid jmnd
define('SMTP_FROM_EMAIL', 'jasonrajaratnam@gmail.com'); // Same as SMTP_USERNAME
define('SMTP_FROM_NAME', 'Expatro Tours');

// Company Information
define('COMPANY_NAME', 'Expatro Tours');
define('COMPANY_EMAIL', 'support@audioguide.com');
define('COMPANY_PHONE', '+94 XX XXX XXXX');

// Security: Start session
session_start();
