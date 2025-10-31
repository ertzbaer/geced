<?php
/**
 * AJAX Handler - Verarbeitet Seitenanfragen
 * Gibt nur den Content-Bereich zurück (ohne Header/Sidebar/Footer)
 */

// Optionales Laden der Datenbank (nur wenn verfügbar)
if (file_exists('config/database.php')) {
    try {
        require_once 'config/database.php';
    } catch (Exception $e) {
        // Datenbank nicht verfügbar - fortfahren ohne DB
    }
}

// Security: Session prüfen
session_start();

// Seiten-Parameter abrufen
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Erlaubte Seiten (Whitelist für Sicherheit)
$allowedPages = [
    'dashboard',
    'index',
    'index-2',
    'index-3',
    'index-4',
    'banking',
    'crypto',
    'invoicing',
    'todo',
    'chat',
    'blog',
    'blog-details',
    'contact',
    'calendar',
    'file-manager',
    'chart',
    'form',
    'table',
    'faq',
    'pricing',
    'badge',
    'blank',
    'button',
    'color',
    'coming-soon',
    'error',
    'icon',
    'kanban',
    'maintenance',
    'navigation',
    'signin',
    'signup',
    'forgot-password',
    'verification',
    'terms',
    'testimonial',
    'typography',
    'welcome',
    'pages-overview'
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