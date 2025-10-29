# 🚀 Lead Management System v2.0 - Setup Guide

## 📋 Quick Start

### Schritt 1: Datenbank einrichten

```bash
# MySQL-Console öffnen
mysql -u root -p

# Datenbank-Schema erstellen
source database/migrations/001_create_database_schema.sql

# Demo-Daten einfügen
source database/seeds/001_default_users.sql
```

### Schritt 2: Umgebungsvariablen

```bash
# .env-Datei erstellen
cp .env.example .env

# Datenbank-Zugangsdaten in .env eintragen
DB_HOST=localhost
DB_NAME=lead_management_system
DB_USER=root
DB_PASS=yourpassword

# JWT Secret generieren und eintragen
JWT_SECRET=$(php -r "echo bin2hex(random_bytes(32));")
```

### Schritt 3: Webserver konfigurieren

Die Anwendung benötigt zwei Hauptverzeichnisse:
- `/public/` - Frontend (HTML, CSS, JS)
- `/api/` - Backend (PHP REST API)

Beide müssen vom Webserver erreichbar sein.

### Schritt 4: Zugriff

- Frontend: `http://localhost/public/signin.html`
- API: `http://localhost/api/`

### Default Login

```
Email: admin@leadmanager.com
Passwort: admin123
```

⚠️ Passwort nach erstem Login ändern!

## 📦 Systemanforderungen

- PHP 8.1+
- MySQL 8.0+
- Apache/Nginx
- PHP Extensions: pdo_mysql, mbstring, json, openssl

## 🔧 API Testen

```bash
# Login testen
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@leadmanager.com","password":"admin123"}'

# Leads abrufen (mit Token)
curl http://localhost/api/leads \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

## ⚠️ Wichtige Sicherheitshinweise

1. JWT Secret in `.env` ändern (vor Produktion!)
2. Default-Passwörter ändern
3. HTTPS in Produktion aktivieren
4. `APP_DEBUG=false` in Produktion setzen

## 📖 Vollständige Dokumentation

Siehe `README.md` für vollständige API-Dokumentation und Features.

## 🐛 Problembehandlung

### API gibt 404
- Prüfen Sie, ob `.htaccess` in `/api/` vorhanden ist
- `mod_rewrite` in Apache aktivieren: `sudo a2enmod rewrite`

### Datenbank-Verbindungsfehler
- Prüfen Sie `.env` Einstellungen
- MySQL-Service starten: `sudo systemctl start mysql`

### JWT-Fehler
- Stellen Sie sicher, dass `JWT_SECRET` gesetzt ist
- Token-Format prüfen: `Bearer <token>`

---

Bei weiteren Fragen: support@yourcompany.com
