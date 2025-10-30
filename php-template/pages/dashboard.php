<?php
/**
 * Dashboard-Seite
 * Zeigt Übersicht mit Statistiken und Daten aus der Datenbank
 */

try {
    $db = Database::getInstance();
    
    // Beispiel: Statistiken aus der Datenbank abrufen
    $userCount = $db->query("SELECT COUNT(*) as count FROM users")->fetch()['count'] ?? 0;
    $postCount = $db->query("SELECT COUNT(*) as count FROM blog_posts WHERE status='published'")->fetch()['count'] ?? 0;
    $todoCount = $db->query("SELECT COUNT(*) as count FROM todos WHERE status='pending'")->fetch()['count'] ?? 0;
    
} catch (Exception $e) {
    // Falls DB-Verbindung fehlschlägt, Dummy-Daten verwenden
    $userCount = 0;
    $postCount = 0;
    $todoCount = 0;
}
?>

<div class="geex-content__header">
    <div class="geex-content__header__content">
        <h2 class="geex-content__header__title">Dashboard</h2>
        <p class="geex-content__header__subtitle">Willkommen zum Geex PHP Template Dashboard</p>
    </div>
</div>

<div class="geex-content__wrapper">
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <strong>PHP Template System aktiv!</strong> Diese Seite wurde dynamisch über AJAX geladen, ohne die gesamte Seite neu zu laden.
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Statistik-Karten -->
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Benutzer</h6>
                            <h2 class="mb-0"><?php echo $userCount; ?></h2>
                        </div>
                        <div class="text-primary">
                            <i class="uil uil-users-alt" style="font-size: 48px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Blog Posts</h6>
                            <h2 class="mb-0"><?php echo $postCount; ?></h2>
                        </div>
                        <div class="text-success">
                            <i class="uil uil-file-alt" style="font-size: 48px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Offene Todos</h6>
                            <h2 class="mb-0"><?php echo $todoCount; ?></h2>
                        </div>
                        <div class="text-warning">
                            <i class="uil uil-check-square" style="font-size: 48px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Funktionsweise des Templates</h4>
                </div>
                <div class="card-body">
                    <h5>Dynamisches Content-Loading</h5>
                    <p>Dieses Template verwendet AJAX, um Inhalte zu laden:</p>
                    <ul>
                        <li><strong>Header, Sidebar und Footer bleiben statisch</strong> - Sie werden nicht neu geladen</li>
                        <li><strong>Nur der <code>&lt;main&gt;</code>-Bereich wird aktualisiert</strong> - Für schnelle Ladezeiten</li>
                        <li><strong>Browser-History wird unterstützt</strong> - Vor/Zurück-Buttons funktionieren</li>
                        <li><strong>MySQL-Datenbank-Integration</strong> - Dynamische Daten aus der DB</li>
                    </ul>
                    
                    <h5 class="mt-4">Verwendung in eigenen Projekten:</h5>
                    <ol>
                        <li>Kopieren Sie den Ordner <code>php-template</code></li>
                        <li>Konfigurieren Sie die Datenbank in <code>config/database.php</code></li>
                        <li>Führen Sie das SQL-Schema aus <code>config/database.sql</code> aus</li>
                        <li>Erstellen Sie neue Seiten in <code>pages/</code></li>
                        <li>Fügen Sie Links mit <code>data-page="seitenname"</code> und Klasse <code>page-link</code> hinzu</li>
                    </ol>
                    
                    <div class="alert alert-success mt-3">
                        <strong>Tipp:</strong> Sie können alle bestehenden HTML-Seiten in PHP-Komponenten umwandeln - extrahieren Sie einfach den Content-Bereich!
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>