<?php
/**
 * Beispiel-Konfiguration
 * Kopieren Sie diese Datei und benennen Sie sie in config.php um
 */

// === ALLGEMEINE EINSTELLUNGEN ===
define('SITE_NAME', 'Geex Dashboard');
define('SITE_URL', 'http://localhost/php-template/');
define('ADMIN_EMAIL', 'admin@example.com');

// === SICHERHEIT ===
define('SESSION_TIMEOUT', 3600); // 1 Stunde in Sekunden
define('CSRF_TOKEN_LENGTH', 32);

// === ENTWICKLUNG ===
define('DEBUG_MODE', true); // Auf false in Produktion setzen
define('ERROR_REPORTING', E_ALL);

// === FEATURES ===
define('ENABLE_REGISTRATION', true);
define('ENABLE_PASSWORD_RESET', true);
define('REQUIRE_EMAIL_VERIFICATION', false);

// === PAGINATION ===
define('ITEMS_PER_PAGE', 10);
define('BLOG_POSTS_PER_PAGE', 6);

// === UPLOAD ===
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);

// === ZEITZONE ===
date_default_timezone_set('Europe/Berlin');

// === ERROR HANDLING ===
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    error_reporting(ERROR_REPORTING);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
?>