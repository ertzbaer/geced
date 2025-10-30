<?php
/**
 * API Endpoint Beispiel
 * Für AJAX-Requests die JSON zurückgeben
 */

require_once 'config/database.php';
require_once 'config/helpers.php';

session_start();

// Nur POST-Requests erlauben
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Nur POST-Requests erlaubt'], 405);
}

// Action Parameter
$action = $_POST['action'] ?? '';

try {
    $db = Database::getInstance();
    
    switch ($action) {
        case 'get_todos':
            $todos = $db->query(
                "SELECT * FROM todos WHERE user_id = ? ORDER BY created_at DESC",
                [$_SESSION['user_id'] ?? 1]
            )->fetchAll();
            jsonResponse(['success' => true, 'data' => $todos]);
            break;
            
        case 'add_todo':
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            
            if (empty($title)) {
                jsonResponse(['error' => 'Titel ist erforderlich'], 400);
            }
            
            $db->query(
                "INSERT INTO todos (user_id, title, description, status) VALUES (?, ?, ?, 'pending')",
                [$_SESSION['user_id'] ?? 1, $title, $description]
            );
            
            jsonResponse(['success' => true, 'message' => 'Todo erstellt']);
            break;
            
        case 'update_todo_status':
            $todoId = $_POST['todo_id'] ?? 0;
            $status = $_POST['status'] ?? '';
            
            if (!in_array($status, ['pending', 'in_progress', 'completed'])) {
                jsonResponse(['error' => 'Ungültiger Status'], 400);
            }
            
            $db->query(
                "UPDATE todos SET status = ? WHERE id = ?",
                [$status, $todoId]
            );
            
            jsonResponse(['success' => true, 'message' => 'Status aktualisiert']);
            break;
            
        case 'delete_todo':
            $todoId = $_POST['todo_id'] ?? 0;
            
            $db->query(
                "DELETE FROM todos WHERE id = ? AND user_id = ?",
                [$todoId, $_SESSION['user_id'] ?? 1]
            );
            
            jsonResponse(['success' => true, 'message' => 'Todo gelöscht']);
            break;
            
        case 'search':
            $query = $_POST['query'] ?? '';
            
            if (strlen($query) < 2) {
                jsonResponse(['error' => 'Suchbegriff zu kurz'], 400);
            }
            
            $results = $db->query(
                "SELECT * FROM blog_posts WHERE title LIKE ? OR content LIKE ? LIMIT 10",
                ['%' . $query . '%', '%' . $query . '%']
            )->fetchAll();
            
            jsonResponse(['success' => true, 'data' => $results]);
            break;
            
        default:
            jsonResponse(['error' => 'Unbekannte Action'], 400);
    }
    
} catch (Exception $e) {
    jsonResponse(['error' => 'Serverfehler: ' . $e->getMessage()], 500);
}
?>