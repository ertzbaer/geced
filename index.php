<?php
/**
 * Geex Dashboard - PHP Template System
 * Hauptdatei - Lädt Header, Sidebar und Footer statisch
 * Nur der <main>-Inhalt wird dynamisch über AJAX geladen
 */

require_once 'config/database.php';

// Session starten (für Benutzer-Authentication)
session_start();

// Header einbinden
include 'includes/header.php';
?>

<main class="geex-main-content">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="geex-content">
        <!-- Dieser Bereich wird dynamisch über AJAX geladen -->
        <div id="dynamicContent">
            <!-- Initialer Inhalt wird durch JavaScript geladen -->
            <div style="text-align: center; padding: 50px;">
                <div class="spinner" style="margin: 0 auto;"></div>
                <p>Lade...</p>
            </div>
        </div>
    </div>
</main>

<?php
// Footer einbinden
include 'includes/footer.php';
?>