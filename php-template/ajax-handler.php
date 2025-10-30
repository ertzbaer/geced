<?php
/**
 * AJAX Handler - Verarbeitet Seitenanfragen
 * Gibt nur den Content-Bereich zurück (ohne Header/Sidebar/Footer)
 */

require_once 'config/database.php';

// Security: Session prüfen
session_start();

// Seiten-Parameter abrufen
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Erlaubte Seiten (Whitelist für Sicherheit)
$allowedPages = [
    'dashboard',
    'server',
    'banking',
    'todo',
    'chat',
    'blog',
    'blog-detail',
    'contact',
    'calendar'
];

// Prüfen ob Seite erlaubt ist
if (!in_array($page, $allowedPages)) {
    $page = 'dashboard'; // Fallback auf Dashboard
}

// Seitendatei einbinden
$pageFile = __DIR__ . '/pages/' . $page . '.php';

if (file_exists($pageFile)) {
    include $pageFile;
} else {
    // 404-Fallback
    echo '<div class="alert alert-warning">Seite nicht gefunden.</div>';
}
?>