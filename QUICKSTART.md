# ⚡ Quick Start Guide

Schnellstart in 3 Minuten!

## 🚀 Schritt 1: Installation (30 Sekunden)

```bash
# Dateien entpacken/hochladen
unzip geex-php-template.zip -d /var/www/html/
```

## 🔧 Schritt 2: Konfiguration (1 Minute)

```bash
# Optional: Datenbank konfigurieren
cp config/database.example.php config/database.php
nano config/database.php  # Ihre DB-Credentials eintragen
```

## ✅ Schritt 3: Testen (30 Sekunden)

```
Öffnen Sie im Browser:
http://ihre-domain.de/
```

**Fertig! 🎉**

---

## 📱 Was Sie jetzt haben:

✅ **34 fertige Seiten**
- 4 Dashboards (Server, Banking, Crypto, Invoicing)
- 8 Apps (Todo, Chat, Blog, Calendar, Contact, File Manager, Kanban)
- 10 UI-Komponenten (Buttons, Forms, Tables, Charts, etc.)
- 8 Pages (FAQ, Pricing, Terms, Error, etc.)
- 4 Auth-Seiten (Login, Signup, Forgot Password, Verification)

✅ **AJAX-Navigation**
- Keine Page-Reloads
- Schnelle Navigation
- Browser History funktioniert

✅ **Original Geex Design**
- 100% Original-Content
- Responsive
- Modern & Clean

---

## 🎯 Erste Schritte:

### 1. Seiten-Übersicht ansehen
```
http://ihre-domain.de/PAGES_OVERVIEW.html
```
→ Zeigt alle 34 Seiten mit Beschreibungen

### 2. Dashboard öffnen
```
http://ihre-domain.de/
```
→ Lädt automatisch das Server-Dashboard

### 3. Navigation nutzen
- Klicken Sie auf Sidebar-Links
- Alles lädt per AJAX (ohne Reload!)
- Browser Vor/Zurück funktioniert

---

## 🛠️ Anpassungen:

### Eigene Seite hinzufügen?
1. Erstellen: `pages/meine-seite.php`
2. Registrieren: In `ajax-handler.php` → `$allowedPages`
3. Verlinken: In `includes/sidebar.php`

**→ Siehe README_COMPLETE.md für Details**

### Datenbank nutzen?
```bash
mysql -u root -p < config/database.sql
```

**→ Siehe DEPLOYMENT.md für Details**

---

## 📚 Dokumentation:

- **README_COMPLETE.md** - Vollständige Anleitung
- **DEPLOYMENT.md** - Production Deployment
- **PAGES_OVERVIEW.html** - Interaktive Übersicht
- **STRUCTURE.md** - Technische Details

---

## 🐛 Probleme?

### Seite lädt nicht?
```bash
# Logs prüfen
tail -f /var/log/apache2/error.log
```

### AJAX funktioniert nicht?
→ Browser-Konsole öffnen (F12)

### Assets fehlen?
→ Prüfen Sie Asset-Pfade in `includes/header.php`

---

## ✨ Features:

| Feature | Status |
|---------|--------|
| 34 Seiten | ✅ |
| AJAX-Loading | ✅ |
| MySQL (optional) | ✅ |
| Responsive | ✅ |
| Browser History | ✅ |
| Original Design | ✅ |
| Dokumentation | ✅ |

---

## 🎊 Das war's!

**Sie haben jetzt ein vollständiges Admin-Dashboard!**

→ Starten Sie mit der Anpassung
→ Fügen Sie eigene Daten hinzu
→ Deployen Sie in Production

**Viel Erfolg! 🚀**

---

**Brauchen Sie Hilfe?**
→ Lesen Sie README_COMPLETE.md
→ Siehe DEPLOYMENT.md für Production
→ Prüfen Sie Browser-Konsole bei Fehlern
