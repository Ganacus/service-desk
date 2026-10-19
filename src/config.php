<?php
declare(strict_types=1);

/**
 * Application configuration.
 * Values can be overridden with environment variables (useful for deployment).
 */

define('APP_NAME', 'MwananchiDesk');
define('APP_BASE', getenv('APP_BASE') ?: '');               // e.g. "/service-desk" if hosted in a subfolder
define('DB_PATH', getenv('DB_PATH') ?: dirname(__DIR__) . '/storage/app.sqlite');
define('UPLOAD_DIR', dirname(__DIR__) . '/public/uploads');
define('MAX_UPLOAD_BYTES', 2 * 1024 * 1024);                // 2 MB

// Default admin account, created automatically on first run. CHANGE IN PRODUCTION.
define('ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: 'admin@example.com');
define('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD') ?: 'Admin@12345');

const CATEGORIES = [
    'Broken Streetlight',
    'Water Issue',
    'Garbage Collection',
    'Road Damage',
    'Drainage / Sewage',
    'Other',
];

const STATUSES = ['Pending', 'In Progress', 'Resolved'];
