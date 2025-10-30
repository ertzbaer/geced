<?php
/**
 * Todo-Seite
 * Zeigt alle Todos aus der Datenbank
 */

try {
    $db = Database::getInstance();
    
    // Todos aus Datenbank laden
    $todos = $db->query("
        SELECT t.*, u.username 
        FROM todos t 
        LEFT JOIN users u ON t.user_id = u.id 
        ORDER BY 
            CASE t.priority 
                WHEN 'high' THEN 1 
                WHEN 'medium' THEN 2 
                WHEN 'low' THEN 3 
            END,
            t.due_date ASC
    ")->fetchAll();
    
} catch (Exception $e) {
    // Dummy-Daten wenn DB nicht verfügbar
    $todos = [
        [
            'title' => 'PHP-Template testen',
            'description' => 'Alle Funktionen des dynamischen Templates überprüfen',
            'username' => 'Demo User',
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => date('Y-m-d', strtotime('+3 days'))
        ],
        [
            'title' => 'Datenbank konfigurieren',
            'description' => 'MySQL-Verbindung in config/database.php einrichten',
            'username' => 'Admin',
            'priority' => 'medium',
            'status' => 'in_progress',
            'due_date' => date('Y-m-d', strtotime('+1 week'))
        ]
    ];
    $error = null;
}

// Status-Badge-Klassen
$statusClasses = [
    'pending' => 'badge-warning',
    'in_progress' => 'badge-info',
    'completed' => 'badge-success'
];

$statusLabels = [
    'pending' => 'Ausstehend',
    'in_progress' => 'In Bearbeitung',
    'completed' => 'Abgeschlossen'
];

$priorityClasses = [
    'high' => 'text-danger',
    'medium' => 'text-warning',
    'low' => 'text-success'
];
?>

<div class="geex-content__header">
    <div class="geex-content__header__content">
        <h2 class="geex-content__header__title">Todo-Liste</h2>
        <p class="geex-content__header__subtitle">Verwalten Sie Ihre Aufgaben</p>
    </div>
</div>

<div class="geex-content__wrapper">
    <?php if (isset($error)): ?>
        <div class="alert alert-warning">
            <strong>Hinweis:</strong> Datenbank nicht konfiguriert. Bitte richten Sie die MySQL-Verbindung ein.
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Alle Aufgaben</h4>
                    <button class="btn btn-primary btn-sm">Neue Aufgabe</button>
                </div>
                <div class="card-body">
                    <?php if (empty($todos)): ?>
                        <div class="alert alert-info">
                            Keine Todos gefunden. Führen Sie <code>config/database.sql</code> aus, um Beispieldaten zu laden.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Titel</th>
                                        <th>Beschreibung</th>
                                        <th>Benutzer</th>
                                        <th>Priorität</th>
                                        <th>Status</th>
                                        <th>Fällig am</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($todos as $todo): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($todo['title']); ?></strong></td>
                                            <td><?php echo htmlspecialchars(substr($todo['description'] ?? '', 0, 50)); ?></td>
                                            <td><?php echo htmlspecialchars($todo['username']); ?></td>
                                            <td>
                                                <span class="<?php echo $priorityClasses[$todo['priority']]; ?>">
                                                    <i class="uil uil-flag"></i> <?php echo ucfirst($todo['priority']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $statusClasses[$todo['status']]; ?>">
                                                    <?php echo $statusLabels[$todo['status']]; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $todo['due_date'] ? date('d.m.Y', strtotime($todo['due_date'])) : '-'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>