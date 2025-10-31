<?php
/**
 * Geex Dashboard - PHP Template System
 * Hauptdatei - Lädt Sidebar und Content statisch
 * Nur der <main>-Inhalt wird dynamisch über AJAX geladen
 */

// Optionales Laden der Datenbank
if (file_exists('config/database.php')) {
    try {
        require_once 'config/database.php';
    } catch (Exception $e) {
        // Datenbank nicht verfügbar
    }
}

// Session starten (für Benutzer-Authentication)
session_start();
?>
<!doctype html>
<html lang="de" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Geex Dashboard - PHP Template</title>

    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/css/bootstrap/bootstrap.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.27.0/dist/apexcharts.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/custom-layout-fix.css">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon.svg">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@iconscout/unicons@4.0.8/css/line.min.css">
    
    <script>
        if (localStorage.theme) document.documentElement.setAttribute("data-theme", localStorage.theme);
        if (localStorage.layout) document.documentElement.setAttribute("data-nav", localStorage.navbar);
        if (localStorage.layout) document.documentElement.setAttribute("dir", localStorage.layout);
    </script>
    
    <style>
        /* Loading Indicator */
        .page-loader {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
        }
        .page-loader.active {
            display: block;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #AB54DB;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>

<body class="geex-dashboard">
    
    <!-- Loading Indicator -->
    <div class="page-loader" id="pageLoader">
        <div class="spinner"></div>
    </div>

<main class="geex-main-content">
    <?php 
    // Sidebar einbinden
    include 'includes/sidebar.php'; 
    ?>
    
    <?php 
    // Customizer einbinden
    include 'includes/customizer.php'; 
    ?>
    
    <div class="geex-content">
        <?php 
        // Content Header einbinden
        include 'includes/content-header.php'; 
        ?>
        
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
