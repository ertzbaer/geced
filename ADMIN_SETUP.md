# Admin Setup Anleitung

## Standard Login-Daten

Nach der Datenbank-Einrichtung:

- **Email:** `admin@leadmanager.com`
- **Passwort:** `admin123`
- **Rolle:** Superadmin

⚠️ **WICHTIG:** Ändere das Passwort nach dem ersten Login!

## Datenbank Setup - Schritt für Schritt

### Methode 1: phpMyAdmin (Empfohlen)

1. **Öffne phpMyAdmin**
2. **Wähle Datenbank** `d04532ea` aus
3. **Klicke auf "SQL" Tab**
4. **Führe folgende SQL-Dateien aus:**

#### Schritt 1: Schema erstellen
```sql
-- Öffne: database/migrations/001_create_database_schema.sql
-- WICHTIG: Ändere "USE lead_management_system;" zu "USE d04532ea;"
-- Kopiere gesamten Inhalt und führe aus
```

#### Schritt 2: Admin & Demo-Daten
```sql
-- Öffne: database/seeds/001_default_users.sql
-- WICHTIG: Ändere "USE lead_management_system;" zu "USE d04532ea;"
-- Kopiere gesamten Inhalt und führe aus
```

### Methode 2: Nur Admin-Benutzer (Schnellstart)

Führe nur dieses SQL aus:

```sql
USE d04532ea;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'admin', 'agent') NOT NULL DEFAULT 'agent',
    status ENUM('active', 'inactive', 'locked') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (username, email, password, role, status)
VALUES (
    'admin',
    'admin@leadmanager.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'superadmin',
    'active'
);
```

## Eigenes Passwort generieren

1. **Bearbeite** `generate-password.php`:
   ```php
   $password = 'dein-sicheres-passwort';
   ```

2. **Führe aus:**
   ```bash
   php generate-password.php
   ```

3. **Kopiere den generierten SQL-Befehl** und führe ihn in phpMyAdmin aus

## Nächste Schritte nach Datenbank-Setup

1. ✅ Datenbank-Tabellen erstellt
2. ✅ Admin-Benutzer erstellt
3. ⏭️ JWT Secret generieren (siehe unten)
4. ⏭️ Teste Login auf: `https://datatobase.com/x/geced-app/source/public/signin.html`

### JWT Secret generieren

Generiere einen sicheren JWT Secret:

```bash
php -r "echo bin2hex(random_bytes(32));"
```

Aktualisiere `.env`:
```env
JWT_SECRET=dein-generierter-secret-hier
```

## Troubleshooting

### "Login failed" Fehler
- ✅ Datenbank-Tabellen erstellt?
- ✅ Admin-Benutzer in `users` Tabelle vorhanden?
- ✅ Richtige Datenbank-Zugangsdaten in `.env`?

### Überprüfe Datenbank
```sql
USE d04532ea;
SHOW TABLES;
SELECT * FROM users;
```

### Überprüfe API
Öffne: `https://datatobase.com/x/geced-app/source/status-check.php`

## Standard Demo-Benutzer (nach Seeds)

| Email | Passwort | Rolle |
|-------|----------|-------|
| admin@leadmanager.com | admin123 | Superadmin |
| manager@leadmanager.com | admin123 | Admin |
| agent@leadmanager.com | admin123 | Agent |

⚠️ **Sicherheit:** Ändere alle Passwörter in der Produktion!
