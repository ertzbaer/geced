<?php
/**
 * Password Hash Generator
 * Generates bcrypt hash for custom admin password
 */

// Ändere hier dein gewünschtes Passwort
$password = 'dein-sicheres-passwort';

// Generiere bcrypt Hash
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "==============================================\n";
echo "Password Hash Generator\n";
echo "==============================================\n\n";
echo "Dein Passwort: {$password}\n";
echo "Bcrypt Hash: {$hash}\n\n";
echo "==============================================\n";
echo "SQL zum Erstellen des Admin-Benutzers:\n";
echo "==============================================\n\n";

echo "INSERT INTO users (username, email, password, role, status, created_at)\n";
echo "VALUES (\n";
echo "    'admin',\n";
echo "    'admin@leadmanager.com',\n";
echo "    '{$hash}',\n";
echo "    'superadmin',\n";
echo "    'active',\n";
echo "    CURRENT_TIMESTAMP\n";
echo ");\n";
?>
