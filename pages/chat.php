<?php
/**
 * Chat-Seite
 * Zeigt Chat-Nachrichten aus der Datenbank
 */

try {
    $db = Database::getInstance();
    
    // Beispiel: Letzte Nachrichten abrufen
    $messages = $db->query("
        SELECT m.*, 
               sender.username as sender_name,
               receiver.username as receiver_name
        FROM messages m
        LEFT JOIN users sender ON m.sender_id = sender.id
        LEFT JOIN users receiver ON m.receiver_id = receiver.id
        ORDER BY m.created_at DESC
        LIMIT 20
    ")->fetchAll();
    
} catch (Exception $e) {
    // Dummy-Daten wenn DB nicht verfügbar
    $messages = [
        [
            'sender_name' => 'Admin',
            'receiver_name' => 'Demo User',
            'message' => 'Willkommen zum Chat-System! Hier können Sie Nachrichten senden und empfangen.',
            'is_read' => false,
            'created_at' => date('Y-m-d H:i:s', strtotime('-10 minutes'))
        ],
        [
            'sender_name' => 'Demo User',
            'receiver_name' => 'Admin',
            'message' => 'Vielen Dank! Das Template funktioniert super.',
            'is_read' => true,
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))
        ]
    ];
    $error = null;
}
?>

<div class="geex-content__header">
    <div class="geex-content__header__content">
        <h2 class="geex-content__header__title">Chat</h2>
        <p class="geex-content__header__subtitle">Nachrichten-Übersicht</p>
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
                <div class="card-header">
                    <h4>Nachrichten</h4>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    <?php if (empty($messages)): ?>
                        <div class="alert alert-info">
                            Keine Nachrichten gefunden.
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($messages as $msg): ?>
                                <div class="list-group-item <?php echo !$msg['is_read'] ? 'list-group-item-action' : ''; ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">
                                            <strong><?php echo htmlspecialchars($msg['sender_name']); ?></strong> 
                                            → 
                                            <?php echo htmlspecialchars($msg['receiver_name']); ?>
                                        </h6>
                                        <small><?php echo date('d.m.Y H:i', strtotime($msg['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-1"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                    <?php if (!$msg['is_read']): ?>
                                        <small class="text-primary"><i class="uil uil-envelope"></i> Ungelesen</small>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>