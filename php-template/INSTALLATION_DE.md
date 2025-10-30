# 🚀 Schnellstart-Anleitung (Deutsch)

## Installation in 5 Minuten

### Schritt 1: Dateien hochladen
Laden Sie den kompletten `php-template` Ordner auf Ihren Webserver hoch.

### Schritt 2: Datenbank erstellen
```sql
CREATE DATABASE geex_dashboard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Schritt 3: SQL importieren
Öffnen Sie phpMyAdmin oder MySQL-Terminal:
```bash
mysql -u root -p geex_dashboard < config/database.sql
```

### Schritt 4: Datenbank-Zugangsdaten eintragen
Bearbeiten Sie `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'ihr_benutzer');
define('DB_PASS', 'ihr_passwort');
define('DB_NAME', 'geex_dashboard');
```

### Schritt 5: Testen
Öffnen Sie im Browser:
```
http://ihre-domain.de/php-template/
```

## ✅ Das war's!

Das Template ist jetzt einsatzbereit.

## 📝 Eigene Seiten erstellen

### 1. Neue PHP-Datei erstellen
Erstellen Sie `pages/meine-seite.php`:

```php
<?php
/**
 * Meine eigene Seite
 */
?>

<div class="geex-content__header">
    <div class="geex-content__header__content">
        <h2 class="geex-content__header__title">Meine Seite</h2>
        <p class="geex-content__header__subtitle">Untertitel</p>
    </div>
</div>

<div class="geex-content__wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4>Willkommen auf meiner Seite!</h4>
                    <p>Hier kommt Ihr Content hin.</p>
                </div>
            </div>
        </div>
    </div>
</div>
```

### 2. Seite registrieren
Fügen Sie in `ajax-handler.php` Ihre Seite hinzu:

```php
$allowedPages = [
    'dashboard',
    'blog',
    'todo',
    'meine-seite',  // <-- Neu
    // ...
];
```

### 3. Link erstellen
Fügen Sie in `includes/sidebar.php` oder `includes/header.php` einen Link hinzu:

```html
<li class="geex-sidebar__menu__item">
    <a href="#" data-page="meine-seite" class="geex-sidebar__menu__link page-link">
        Meine Seite
    </a>
</li>
```

## 🗄️ Datenbank verwenden

```php
<?php
try {
    $db = Database::getInstance();
    
    // Daten abrufen
    $users = $db->query("SELECT * FROM users")->fetchAll();
    
    // Mit Parametern (sicher gegen SQL-Injection)
    $user = $db->query(
        "SELECT * FROM users WHERE id = ?", 
        [1]
    )->fetch();
    
} catch (Exception $e) {
    echo "Fehler: " . $e->getMessage();
}
?>
```

## 🎨 Design anpassen

Das Template verwendet die bestehenden Geex-Styles aus:
- `../assets/css/style.css`
- Bootstrap 
- Unicons Icons

## 🔧 Troubleshooting

### Problem: "Datenbankverbindung fehlgeschlagen"
- Überprüfen Sie die Zugangsdaten in `config/database.php`
- Stellen Sie sicher, dass die Datenbank existiert
- Prüfen Sie, ob MySQL läuft

### Problem: "Seite nicht gefunden"
- Prüfen Sie, ob die Seite in `pages/` existiert
- Prüfen Sie, ob die Seite in `$allowedPages` eingetragen ist
- Schauen Sie in die Browser-Konsole (F12) nach Fehlern

### Problem: Links funktionieren nicht
- Stellen Sie sicher, dass der Link die Klasse `page-link` hat
- Prüfen Sie, ob das `data-page` Attribut gesetzt ist
- Überprüfen Sie, ob JavaScript aktiviert ist

## 📞 Support

Bei Problemen:
1. Prüfen Sie die Browser-Konsole (F12)
2. Schauen Sie in die PHP Error Logs
3. Lesen Sie die ausführliche README.md

## 🎉 Viel Erfolg!

Sie haben jetzt ein vollständiges PHP-Template mit dynamischem Content-Loading!
