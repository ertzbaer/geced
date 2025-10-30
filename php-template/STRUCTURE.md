# 📐 Strukturübersicht - Wie alles zusammenarbeitet

## Datenfluss

```
┌─────────────────────────────────────────────────────────┐
│                    BROWSER (Client)                      │
└─────────────────────────────────────────────────────────┘
                          │
                          │ HTTP Request
                          ▼
┌─────────────────────────────────────────────────────────┐
│                    index.php (Server)                    │
│  ┌───────────────────────────────────────────────────┐  │
│  │  1. Lädt Header (statisch)                        │  │
│  │  2. Lädt Sidebar (statisch)                       │  │
│  │  3. <div id="dynamicContent"> - LEER             │  │
│  │  4. Lädt Footer (statisch)                        │  │
│  │  5. Lädt page-loader.js                          │  │
│  └───────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
                          │
                          │ JavaScript initialisiert
                          ▼
┌─────────────────────────────────────────────────────────┐
│              page-loader.js (JavaScript)                 │
│  ┌───────────────────────────────────────────────────┐  │
│  │  1. Liest URL-Parameter ?page=xyz                 │  │
│  │  2. Bindet Event-Listener an .page-link          │  │
│  │  3. Sendet AJAX-Request                          │  │
│  └───────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
                          │
                          │ AJAX Request
                          ▼
┌─────────────────────────────────────────────────────────┐
│              ajax-handler.php (Server)                   │
│  ┌───────────────────────────────────────────────────┐  │
│  │  1. Empfängt ?page=xyz                           │  │
│  │  2. Prüft Whitelist $allowedPages                │  │
│  │  3. Lädt pages/xyz.php                           │  │
│  │  4. Gibt NUR Content zurück (kein Layout)       │  │
│  └───────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
                          │
                          │ HTML Content
                          ▼
┌─────────────────────────────────────────────────────────┐
│                pages/xyz.php (Server)                    │
│  ┌───────────────────────────────────────────────────┐  │
│  │  1. Verbindet mit Datenbank (optional)           │  │
│  │  2. Lädt Daten aus MySQL                         │  │
│  │  3. Generiert HTML                               │  │
│  │  4. Gibt Content zurück                          │  │
│  └───────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
                          │
                          │ Response (nur Content)
                          ▼
┌─────────────────────────────────────────────────────────┐
│              page-loader.js (JavaScript)                 │
│  ┌───────────────────────────────────────────────────┐  │
│  │  1. Empfängt HTML-Content                        │  │
│  │  2. Ersetzt #dynamicContent                      │  │
│  │  3. Aktualisiert Browser-URL                     │  │
│  │  4. Markiert aktive Links                        │  │
│  └───────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
                          │
                          │ DOM Updated
                          ▼
┌─────────────────────────────────────────────────────────┐
│                BROWSER - Neue Seite sichtbar             │
│  Header bleibt gleich ✓                                  │
│  Sidebar bleibt gleich ✓                                 │
│  NUR Content hat sich geändert ✓                         │
└─────────────────────────────────────────────────────────┘
```

## Datei-Verantwortlichkeiten

### index.php
**Zweck**: Hauptlayout laden (wird nur EINMAL geladen)
- ✅ Header einbinden
- ✅ Sidebar einbinden  
- ✅ Leeren Content-Container erstellen
- ✅ Footer einbinden
- ✅ JavaScript laden

**Wichtig**: Diese Datei wird nach dem ersten Laden NICHT mehr aufgerufen!

### ajax-handler.php
**Zweck**: Router für AJAX-Requests
- ✅ Seiten-Parameter empfangen
- ✅ Whitelist-Prüfung
- ✅ Entsprechende Page laden
- ✅ Nur Content zurückgeben (kein Layout!)

**Wichtig**: Gibt NUR den Inhalt zurück, nicht das ganze HTML!

### pages/*.php
**Zweck**: Einzelne Seiten-Inhalte
- ✅ Datenbank-Abfragen
- ✅ HTML-Generierung
- ✅ Dynamischer Content

**Wichtig**: Diese Dateien enthalten KEIN <!DOCTYPE>, <html>, <head> oder <body>!

### page-loader.js
**Zweck**: AJAX-Mechanismus
- ✅ Link-Klicks abfangen
- ✅ AJAX-Requests senden
- ✅ DOM aktualisieren
- ✅ Browser-History verwalten

## Link-Anatomie

### Richtiger Link (funktioniert):
```html
<a href="#" data-page="blog" class="page-link">Blog</a>
      ↑          ↑                    ↑
      │          │                    └── WICHTIG: Klasse page-link
      │          └── WICHTIG: data-page Attribut
      └── href="#" (wird überschrieben)
```

### Falscher Link (funktioniert NICHT):
```html
<!-- ❌ Fehlt class="page-link" -->
<a href="#" data-page="blog">Blog</a>

<!-- ❌ Fehlt data-page -->
<a href="#" class="page-link">Blog</a>

<!-- ❌ Normaler Link (lädt ganze Seite neu) -->
<a href="blog.html">Blog</a>
```

## Ablauf bei Klick auf einen Link

1. **Benutzer klickt** auf Link mit `class="page-link"`
2. **JavaScript fängt ab** mit `addEventListener('click')`
3. **preventDefault()** verhindert normales Link-Verhalten
4. **data-page Attribut** wird ausgelesen
5. **AJAX-Request** an `ajax-handler.php?page=xyz`
6. **Server lädt** `pages/xyz.php`
7. **HTML wird zurückgegeben** (nur Content!)
8. **JavaScript ersetzt** `#dynamicContent` innerHTML
9. **URL wird aktualisiert** mit `history.pushState()`
10. **Aktive Links** werden markiert

## Was wird WO geladen?

### Beim ersten Besuch:
```
Browser lädt:
├── index.php (inkl. Header, Sidebar, Footer)
├── CSS-Dateien
├── JavaScript-Dateien
└── page-loader.js initialisiert
    └── Sendet ersten AJAX-Request für initiale Seite
```

### Bei jedem weiteren Klick:
```
AJAX-Request:
└── ajax-handler.php?page=xyz
    ├── Lädt pages/xyz.php
    ├── Verbindet mit DB (falls nötig)
    └── Gibt NUR Content zurück
```

**Ergebnis**: Nur ~5KB statt 150KB bei jedem Klick! ⚡

## Datenbank-Integration

### In pages/*.php:
```php
<?php
try {
    // Singleton-Pattern: Eine Verbindung für alles
    $db = Database::getInstance();
    
    // Query mit Prepared Statement (sicher!)
    $posts = $db->query(
        "SELECT * FROM blog_posts WHERE status = ?",
        ['published']
    )->fetchAll();
    
} catch (Exception $e) {
    // Fehlerbehandlung
    $posts = [];
    $error = $e->getMessage();
}
?>

<!-- HTML Output -->
<?php foreach ($posts as $post): ?>
    <h3><?php echo htmlspecialchars($post['title']); ?></h3>
<?php endforeach; ?>
```

## Sicherheit

### 1. Whitelist in ajax-handler.php
```php
// Nur diese Seiten sind erlaubt
$allowedPages = ['dashboard', 'blog', 'todo'];

// Ungültige Requests werden blockiert
if (!in_array($page, $allowedPages)) {
    $page = 'dashboard'; // Fallback
}
```

### 2. Prepared Statements
```php
// ❌ UNSICHER: SQL-Injection möglich
$result = $db->query("SELECT * FROM users WHERE id = " . $_GET['id']);

// ✅ SICHER: Prepared Statement
$result = $db->query("SELECT * FROM users WHERE id = ?", [$_GET['id']]);
```

### 3. HTML Escaping
```php
// ❌ XSS-Angriff möglich
echo $user['name'];

// ✅ SICHER
echo htmlspecialchars($user['name']);
```

## Performance-Vorteile

### Traditionelle Multi-Page-App:
```
Seite 1: 150 KB (Header + Sidebar + Content + Footer)
Seite 2: 150 KB (Header + Sidebar + Content + Footer)
Seite 3: 150 KB (Header + Sidebar + Content + Footer)
─────────────────────────────────────────────────────
Total:   450 KB für 3 Seiten
```

### Unser PHP-Template:
```
Initial:  150 KB (komplettes Layout + JS)
Seite 2:    5 KB (nur Content)
Seite 3:    5 KB (nur Content)
─────────────────────────────────────────────────────
Total:   160 KB für 3 Seiten (65% weniger!)
```

## Browser-History

### URL-Struktur:
```
Initial:     example.com/php-template/
Dashboard:   example.com/php-template/?page=dashboard
Blog:        example.com/php-template/?page=blog
Todo:        example.com/php-template/?page=todo
```

### History API:
```javascript
// Neuer Eintrag in Browser-History
history.pushState({page: 'blog'}, '', '?page=blog');

// Zurück-Button funktioniert
window.addEventListener('popstate', function(e) {
    if (e.state && e.state.page) {
        loadPage(e.state.page);
    }
});
```

## Zusammenfassung

✅ **Layout wird einmal geladen** (Header, Sidebar, Footer bleiben)  
✅ **Content wird dynamisch geladen** (nur das Nötigste)  
✅ **Keine Seiten-Reloads** (bessere User Experience)  
✅ **Browser-Navigation funktioniert** (Vor/Zurück-Buttons)  
✅ **Schnellere Ladezeiten** (weniger Datenübertragung)  
✅ **Sauber strukturiert** (einfach zu warten)  
✅ **MySQL-Integration** (dynamische Daten)  
✅ **Sicherheit** (Whitelist, Prepared Statements)  

Das ist ein **Single-Page-Application (SPA) Ansatz mit PHP**! 🚀
