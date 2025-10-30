<?php
/**
 * Hilfs-Funktionen für das Template
 */

/**
 * HTML-Output sicher escapen
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * URL generieren
 */
function url($page = '', $params = []) {
    $url = 'index.php';
    if ($page) {
        $params['page'] = $page;
    }
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    return $url;
}

/**
 * Redirect
 */
function redirect($page = 'dashboard') {
    header('Location: ' . url($page));
    exit;
}

/**
 * CSRF Token generieren
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF Token überprüfen
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Prüfen ob Benutzer eingeloggt ist
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Aktuellen Benutzer abrufen
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        $db = Database::getInstance();
        $user = $db->query(
            "SELECT id, username, email FROM users WHERE id = ?",
            [$_SESSION['user_id']]
        )->fetch();
        return $user ?: null;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Datum formatieren
 */
function formatDate($date, $format = 'd.m.Y') {
    if (empty($date)) return '-';
    return date($format, strtotime($date));
}

/**
 * Datetime formatieren
 */
function formatDateTime($datetime, $format = 'd.m.Y H:i') {
    if (empty($datetime)) return '-';
    return date($format, strtotime($datetime));
}

/**
 * Text kürzen
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Debug-Ausgabe
 */
function dd($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    die();
}

/**
 * JSON Response senden
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Success Message setzen
 */
function setSuccessMessage($message) {
    $_SESSION['success_message'] = $message;
}

/**
 * Error Message setzen
 */
function setErrorMessage($message) {
    $_SESSION['error_message'] = $message;
}

/**
 * Messages anzeigen und löschen
 */
function displayMessages() {
    $html = '';
    
    if (isset($_SESSION['success_message'])) {
        $html .= '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        $html .= e($_SESSION['success_message']);
        $html .= '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>';
        $html .= '</div>';
        unset($_SESSION['success_message']);
    }
    
    if (isset($_SESSION['error_message'])) {
        $html .= '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        $html .= e($_SESSION['error_message']);
        $html .= '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>';
        $html .= '</div>';
        unset($_SESSION['error_message']);
    }
    
    return $html;
}
?>