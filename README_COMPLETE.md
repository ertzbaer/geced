# Geex Dashboard - PHP Template mit AJAX

Ein vollständiges PHP-Template-System basierend auf dem Geex HTML-Dashboard mit dynamischem Content-Loading.

## 🚀 Features

- ✅ **34 vollständige Seiten** mit Original-Content
- ✅ **Dynamisches AJAX-Loading** - nur Content wird neu geladen
- ✅ **Kein Page-Reload** - schnelle Navigation
- ✅ **Browser History Support** - Vor/Zurück funktioniert
- ✅ **Responsive Design** - funktioniert auf allen Geräten
- ✅ **MySQL-Integration** (optional)
- ✅ **Original Geex Design** - 1:1 übernommen

## 📂 Verfügbare Seiten (34)

### Dashboards
- Dashboard (Server Management)
- Banking
- Crypto
- Invoicing

### Apps
- Todo-Liste
- Chat/Messaging
- Blog & Blog-Details
- Calendar
- Contact
- File Manager
- Kanban Board

### UI-Komponenten
- Badge
- Button
- Chart
- Color
- Form
- Icon
- Navigation
- Table
- Typography

### Pages
- Blank Page
- Coming Soon
- Error 404
- FAQ
- Maintenance
- Pricing
- Terms & Conditions
- Testimonial

### Authentication
- Sign In
- Sign Up
- Forgot Password
- Verification

## 🛠️ Installation

### 1. Server-Anforderungen
- PHP 8.0+
- MySQL (optional)
- Apache/Nginx

### 2. Dateien hochladen
Kopieren Sie alle Dateien auf Ihren Webserver.

### 3. MySQL konfigurieren (optional)
```php
// config/database.php
define('DB_HOST', 'localhost');
define('DB_USER', 'ihr_benutzer');
define('DB_PASS', 'ihr_passwort');
define('DB_NAME', 'geex_dashboard');
```

Importieren Sie das Schema:
```bash
mysql -u root -p < config/database.sql
```

### 4. Fertig!
Öffnen Sie `http://ihre-domain.de/` im Browser.

## 📝 Neue Seite hinzufügen

### Schritt 1: PHP-Seite erstellen
Erstellen Sie `pages/meine-seite.php`:
```php
<div class="geex-content__header">
    <div class="geex-content__header__content">
        <h2 class="geex-content__header__title">Meine Seite</h2>
    </div>
</div>

<div class="geex-content__wrapper">
    <!-- Ihr Content -->
</div>
```

### Schritt 2: Zur Whitelist hinzufügen
In `ajax-handler.php`:
```php
$allowedPages = [
    'dashboard',
    'meine-seite',  // NEU
    // ...
];
```

### Schritt 3: Link hinzufügen
In `includes/sidebar.php`:
```html
<li class="geex-sidebar__menu__item">
    <a href="#" data-page="meine-seite" class="geex-sidebar__menu__link page-link">
        Meine Seite
    </a>
</li>
```

## 🎯 Wie es funktioniert

### Struktur
```
index.php (lädt einmal)
  ├── Sidebar (statisch)
  ├── Customizer (statisch)
  └── Content-Bereich
      └── dynamicContent (lädt per AJAX)
```

### Navigation
1. User klickt auf Link mit `class="page-link"` und `data-page="xyz"`
2. JavaScript fängt Klick ab
3. AJAX-Request an `ajax-handler.php?page=xyz`
4. PHP lädt `pages/xyz.php`
5. Content wird in `#dynamicContent` eingefügt
6. Browser-URL wird aktualisiert

## 🔧 Server-Management

### PHP-Server neustarten
```bash
sudo supervisorctl restart php-server
```

### Status prüfen
```bash
sudo supervisorctl status php-server
```

### Logs anzeigen
```bash
tail -f /var/log/supervisor/php-server.err.log
```

## 📊 Performance

**Vorteile gegenüber traditionellen Multi-Page-Apps:**
- **Erstes Laden**: 150KB (komplettes Layout)
- **Weitere Seiten**: 5-50KB (nur Content)
- **Ersparnis**: Bis zu 65% weniger Datenübertragung

## 🎨 Anpassungen

### Theme (Light/Dark)
Der Customizer (rechts oben) erlaubt:
- Light/Dark Mode
- Top/Side Navigation
- RTL/LTR Layout

### CSS anpassen
Ändern Sie `assets/css/style.css` oder fügen Sie eigene CSS hinzu.

### Custom Layout-Fixes
In `assets/css/custom-layout-fix.css` befinden sich spezifische CSS-Anpassungen für das PHP-Template.

## 🔐 Sicherheit

### Whitelist-Routing
Nur Seiten in `$allowedPages` können geladen werden.

### Prepared Statements
Alle DB-Queries verwenden Prepared Statements:
```php
$db->query("SELECT * FROM users WHERE id = ?", [$userId]);
```

### HTML Escaping
Verwenden Sie immer `htmlspecialchars()`:
```php
echo htmlspecialchars($user['name']);
```

## 📁 Dateistruktur

```
/
├── index.php                    # Hauptdatei
├── ajax-handler.php             # AJAX-Router
├── config/
│   ├── database.php            # DB-Verbindung
│   ├── database.sql            # SQL-Schema
│   └── helpers.php             # Hilfsfunktionen
├── includes/
│   ├── sidebar.php             # Sidebar mit Navigation
│   ├── customizer.php          # Theme-Customizer
│   └── footer.php              # Footer mit Scripts
├── pages/                       # 34 Content-Seiten
│   ├── dashboard.php
│   ├── banking.php
│   ├── blog.php
│   └── ... (31 weitere)
├── assets/
│   ├── css/
│   │   ├── style.css           # Original CSS
│   │   └── custom-layout-fix.css  # Template-Fixes
│   ├── js/
│   │   ├── main-simple.js      # Vereinfachtes Main-JS
│   │   └── page-loader.js      # AJAX-System
│   ├── img/                    # Bilder & Icons
│   └── vendor/                 # Third-party Libraries
└── Dokumentation/
    ├── README.md               # Diese Datei
    ├── INSTALLATION_DE.md      # Kurzanleitung
    └── STRUCTURE.md            # Technische Details
```

## 🐛 Troubleshooting

### Problem: Seite lädt nicht
**Lösung**: Prüfen Sie:
1. Ist die Seite in `pages/` vorhanden?
2. Ist die Seite in der Whitelist (`ajax-handler.php`)?
3. Browser-Konsole auf Fehler prüfen

### Problem: Styling fehlt
**Lösung**: Prüfen Sie die Asset-Pfade in `includes/header.php`

### Problem: AJAX funktioniert nicht
**Lösung**: Prüfen Sie `assets/js/page-loader.js` und Browser-Konsole

### Problem: Datenbank-Fehler
**Lösung**: 
1. Prüfen Sie `config/database.php`
2. Stellen Sie sicher, dass MySQL läuft
3. Importieren Sie `config/database.sql`

## 📞 Support

Bei Fragen oder Problemen:
- Prüfen Sie die Browser-Konsole (F12)
- Schauen Sie in die PHP Error Logs
- Lesen Sie `STRUCTURE.md` für technische Details

## 📄 Lizenz

Dieses Template basiert auf dem Geex Admin Dashboard Template.

## ✨ Credits

- Original Template: Geex Admin Dashboard
- AJAX-System: Custom PHP Implementation
- Icons: Unicons

---

**Version**: 1.0.0  
**Erstellt**: 2024  
**PHP-Version**: 8.0+

**Viel Erfolg mit Ihrem PHP-Template! 🚀**
