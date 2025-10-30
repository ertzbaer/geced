# Geex Dashboard - PHP Template System

Ein dynamisches PHP-Template-System mit AJAX-basiertem Content-Loading für das Geex Dashboard.

## 🎯 Features

- **Dynamisches Content-Loading**: Nur der `<main>`-Bereich wird neu geladen, Header/Sidebar bleiben statisch
- **AJAX-basiert**: Schnelle Ladezeiten ohne vollständige Seitenreloads
- **MySQL-Integration**: Dynamische Daten aus der Datenbank
- **Browser-History-Support**: Vor/Zurück-Buttons funktionieren
- **Sauber strukturiert**: Getrennte Komponenten für Header, Sidebar, Footer und Pages
- **Sicherheit**: Whitelist-basiertes Routing, PDO mit Prepared Statements

## 📁 Projektstruktur

```
php-template/
├── index.php              # Hauptdatei mit statischem Layout
├── ajax-handler.php       # AJAX-Request-Handler
├── config/
│   ├── database.php       # Datenbank-Konfiguration & Verbindung
│   └── database.sql       # SQL-Schema mit Beispieldaten
├── includes/
│   ├── header.php         # Header-Komponente (statisch)
│   ├── sidebar.php        # Sidebar-Komponente (statisch)
│   └── footer.php         # Footer-Komponente (statisch)
├── pages/
│   ├── dashboard.php      # Dashboard-Seite
│   ├── blog.php           # Blog-Übersicht
│   ├── todo.php           # Todo-Liste
│   ├── chat.php           # Chat/Nachrichten
│   ├── server.php         # Server Management
│   └── banking.php        # Banking-Übersicht
└── assets/
    └── js/
        └── page-loader.js # AJAX-Loading-Script
```

## 🚀 Installation

### 1. Dateien kopieren

```bash
cp -r php-template /pfad/zu/ihrem/webserver/
```

### 2. Datenbank konfigurieren

Bearbeiten Sie `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'ihr_benutzer');
define('DB_PASS', 'ihr_passwort');
define('DB_NAME', 'geex_dashboard');
```

### 3. Datenbank erstellen

Führen Sie das SQL-Schema aus:

```bash
mysql -u root -p < config/database.sql
```

Oder importieren Sie `config/database.sql` über phpMyAdmin.

### 4. Webserver konfigurieren

Stellen Sie sicher, dass Ihr Webserver auf den `php-template` Ordner zeigt.

### 5. Testen

Öffnen Sie im Browser:

```
http://localhost/php-template/
```

## 📝 Neue Seiten hinzufügen

### 1. PHP-Seite erstellen

Erstellen Sie eine neue Datei in `pages/`, z.B. `pages/meine-seite.php`:

```php
<?php
/**
 * Meine neue Seite
 */
?>

<div class="geex-content__header">
    <div class="geex-content__header__content">
        <h2 class="geex-content__header__title">Meine Seite</h2>
        <p class="geex-content__header__subtitle">Beschreibung</p>
    </div>
</div>

<div class="geex-content__wrapper">
    <!-- Ihr Content hier -->
</div>
```

### 2. Seite in Whitelist aufnehmen

Bearbeiten Sie `ajax-handler.php`:

```php
$allowedPages = [
    'dashboard',
    'blog',
    'todo',
    'meine-seite',  // Neu hinzugefügt
    // ...
];
```

### 3. Link hinzufügen

Fügen Sie einen Link in `includes/header.php` oder `includes/sidebar.php` hinzu:

```html
<li class="geex-header__menu__item">
    <a href="#" data-page="meine-seite" class="geex-header__menu__link page-link">
        Meine Seite
    </a>
</li>
```

## 🔒 Wichtige Klassen

### Links für dynamisches Laden

Verwenden Sie immer:
- **Klasse**: `page-link`
- **Attribut**: `data-page="seitenname"`

```html
<a href="#" data-page="blog" class="page-link">Blog</a>
```

## 📦 Datenbank-Zugriff

Beispiel in einer Page-Datei:

```php
<?php
try {
    $db = Database::getInstance();
    
    // Daten abrufen
    $results = $db->query(
        "SELECT * FROM users WHERE id = ?", 
        [$userId]
    )->fetchAll();
    
} catch (Exception $e) {
    // Fehlerbehandlung
    $error = $e->getMessage();
}
?>
```

## ⚡ Wie es funktioniert

1. **Initiales Laden**: `index.php` wird geladen mit statischem Header, Sidebar und Footer
2. **JavaScript-Initialisierung**: `page-loader.js` initialisiert das AJAX-System
3. **Link-Klick**: Benutzer klickt auf einen Link mit `page-link` Klasse
4. **AJAX-Request**: JavaScript sendet Request an `ajax-handler.php?page=seitenname`
5. **Content-Laden**: PHP lädt die entsprechende Datei aus `pages/`
6. **DOM-Update**: Nur der `#dynamicContent` Bereich wird aktualisiert
7. **History-Update**: Browser-URL wird aktualisiert ohne Reload

## 🌐 Browser-Unterstützung

- Chrome/Edge (neueste Versionen)
- Firefox (neueste Versionen)
- Safari (neueste Versionen)
- Opera (neueste Versionen)

## 🔧 Anpassungen

### Loading-Indicator anpassen

Bearbeiten Sie den Style in `includes/header.php`:

```css
.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #AB54DB;
    /* Ihre Anpassungen */
}
```

### Datenbank-Tabellen erweitern

Fügen Sie neue Tabellen in `config/database.sql` hinzu und führen Sie das SQL aus.

## ✅ Best Practices

1. **Immer PDO Prepared Statements verwenden** - Schutz vor SQL-Injection
2. **HTML Output escapen** - Verwenden Sie `htmlspecialchars()`
3. **Whitelist für Pages** - Nur erlaubte Seiten in `$allowedPages`
4. **Error Handling** - Try-Catch-Blöcke für Datenbankzugriffe
5. **Session-Management** - Für Benutzer-Authentication

## 📊 Vorteile dieses Systems

- ✅ **Schneller**: Nur Content wird neu geladen
- ✅ **Benutzerfreundlich**: Keine Seitenreloads, smooth UX
- ✅ **SEO-freundlich**: URLs werden aktualisiert
- ✅ **Wartbar**: Klare Trennung von Komponenten
- ✅ **Erweiterbar**: Einfach neue Seiten hinzufügen
- ✅ **Sicher**: Whitelist & Prepared Statements

## 👨‍💻 Verwendung in Ihren Projekten

1. Kopieren Sie die Struktur
2. Passen Sie die Datenbank-Config an
3. Erstellen Sie Ihre eigenen Pages in `pages/`
4. Passen Sie Header/Sidebar nach Bedarf an
5. Fügen Sie Ihre eigenen Styles hinzu

## 🎉 Fertig!

Sie haben jetzt ein voll funktionsfähiges PHP-Template mit dynamischem Content-Loading!

Bei Fragen oder Problemen, überprüfen Sie:
- Datenbank-Verbindung in `config/database.php`
- PHP-Fehler im Error-Log
- Browser-Konsole für JavaScript-Fehler