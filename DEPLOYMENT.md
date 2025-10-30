# Deployment-Anleitung

## 🚀 Production Deployment

### Voraussetzungen
- PHP 8.0+ installiert
- Apache/Nginx Webserver
- MySQL 5.7+ (optional)
- Git (für Repository-Zugriff)

---

## Methode 1: Manuelles Deployment

### Schritt 1: Dateien hochladen
```bash
# Via FTP/SFTP alle Dateien hochladen nach:
/var/www/html/
```

### Schritt 2: Berechtigungen setzen
```bash
chmod -R 755 /var/www/html/
chown -R www-data:www-data /var/www/html/
```

### Schritt 3: Datenbank konfigurieren
```bash
# Kopiere Beispiel-Config
cp config/database.example.php config/database.php

# Bearbeite mit echten Credentials
nano config/database.php
```

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'production_user');
define('DB_PASS', 'secure_password');
define('DB_NAME', 'geex_production');
```

### Schritt 4: Datenbank importieren
```bash
mysql -u production_user -p geex_production < config/database.sql
```

### Schritt 5: Apache/Nginx konfigurieren

**Apache (.htaccess bereits vorhanden):**
```apache
<VirtualHost *:80>
    ServerName ihre-domain.de
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

**Nginx:**
```nginx
server {
    listen 80;
    server_name ihre-domain.de;
    root /var/www/html;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## Methode 2: Git Deployment

### Schritt 1: Repository clonen
```bash
cd /var/www/html
git clone https://github.com/username/geex-php-template.git .
```

### Schritt 2: Dependencies installieren
```bash
composer install --no-dev --optimize-autoloader
```

### Schritt 3: Environment Setup
```bash
cp config/database.example.php config/database.php
# Bearbeite config/database.php mit Production-Credentials
```

### Schritt 4: Updates deployen
```bash
git pull origin main
composer install --no-dev
```

---

## Methode 3: Docker Deployment

### Dockerfile erstellen
```dockerfile
FROM php:8.2-apache

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache modules
RUN a2enmod rewrite

# Copy application
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html/

EXPOSE 80
```

### docker-compose.yml
```yaml
version: '3.8'
services:
  web:
    build: .
    ports:
      - "80:80"
    volumes:
      - .:/var/www/html
    depends_on:
      - db
  
  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: geex_dashboard
      MYSQL_USER: geex_user
      MYSQL_PASSWORD: geex_pass
    ports:
      - "3306:3306"
```

### Deployment
```bash
docker-compose up -d
```

---

## SSL/HTTPS Konfiguration

### Mit Let's Encrypt (Certbot)
```bash
# Certbot installieren
sudo apt install certbot python3-certbot-apache

# Zertifikat erstellen
sudo certbot --apache -d ihre-domain.de

# Auto-Renewal aktivieren
sudo certbot renew --dry-run
```

---

## Performance-Optimierung

### 1. PHP OPcache aktivieren
```ini
; php.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

### 2. Gzip-Kompression (Apache)
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

### 3. Browser-Caching
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

---

## Sicherheit

### 1. Production-Modus aktivieren
```php
// config/config.php
define('DEBUG_MODE', false);
define('ERROR_REPORTING', 0);
ini_set('display_errors', 0);
```

### 2. Datenbank-Credentials schützen
```bash
chmod 600 config/database.php
```

### 3. Directory Listing deaktivieren
```apache
Options -Indexes
```

### 4. SQL-Dateien schützen
```apache
<FilesMatch "\.(sql|md|log)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

---

## Monitoring & Logs

### PHP Error Log
```bash
tail -f /var/log/apache2/error.log
# oder
tail -f /var/log/nginx/error.log
```

### MySQL Slow Query Log
```sql
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;
```

---

## Backup-Strategie

### Datenbank-Backup
```bash
# Daily Backup
mysqldump -u user -p geex_production > backup_$(date +%Y%m%d).sql

# Automatisches Backup (Crontab)
0 2 * * * mysqldump -u user -p geex_production > /backups/db_$(date +\%Y\%m\%d).sql
```

### Datei-Backup
```bash
# Komplettes Backup
tar -czf backup_$(date +%Y%m%d).tar.gz /var/www/html/

# Nur wichtige Dateien
tar -czf backup_$(date +%Y%m%d).tar.gz /var/www/html/uploads/ /var/www/html/config/
```

---

## Troubleshooting

### Problem: 500 Internal Server Error
```bash
# Prüfe Logs
tail -100 /var/log/apache2/error.log

# Prüfe Berechtigungen
ls -la /var/www/html/

# PHP Syntax prüfen
php -l index.php
```

### Problem: AJAX lädt nicht
```bash
# Browser Console öffnen (F12)
# Network Tab prüfen
# Prüfe ob ajax-handler.php erreichbar ist
curl http://ihre-domain.de/ajax-handler.php?page=dashboard
```

### Problem: Datenbank-Verbindung fehlschlägt
```bash
# MySQL Status prüfen
systemctl status mysql

# Verbindung testen
mysql -u user -p -h localhost geex_production
```

---

## Checkliste vor Go-Live

- [ ] PHP 8.0+ installiert
- [ ] Datenbank erstellt und importiert
- [ ] config/database.php mit Production-Credentials
- [ ] DEBUG_MODE = false
- [ ] SSL/HTTPS aktiviert
- [ ] Berechtigungen korrekt gesetzt
- [ ] .htaccess funktioniert
- [ ] Alle 34 Seiten laden
- [ ] AJAX-Navigation funktioniert
- [ ] Backup-Strategie eingerichtet
- [ ] Monitoring aktiviert
- [ ] Performance-Optimierungen aktiv

---

## Support

Bei Problemen:
1. Prüfen Sie die Logs
2. Lesen Sie README_COMPLETE.md
3. Testen Sie lokal zuerst
4. Prüfen Sie Serveranforderungen

---

**Version**: 1.0.0  
**Stand**: 2024  

🚀 **Viel Erfolg mit dem Deployment!**
