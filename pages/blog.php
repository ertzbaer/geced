<?php
/**
 * Blog-Seite
 * Zeigt alle Blog-Posts aus der Datenbank
 */

try {
    $db = Database::getInstance();
    
    // Blog-Posts aus Datenbank laden
    $posts = $db->query("
        SELECT bp.*, u.username as author_name 
        FROM blog_posts bp 
        LEFT JOIN users u ON bp.author_id = u.id 
        WHERE bp.status = 'published'
        ORDER BY bp.created_at DESC
    ")->fetchAll();
    
} catch (Exception $e) {
    $posts = [];
    $error = $e->getMessage();
}
?>

<div class="geex-content__header">
    <div class="geex-content__header__content">
        <h2 class="geex-content__header__title">Blog</h2>
        <p class="geex-content__header__subtitle">Aktuelle Blog-Posts aus der Datenbank</p>
    </div>
</div>

<div class="geex-content__wrapper">
    <div class="row">
        <?php if (isset($error)): ?>
            <div class="col-12">
                <div class="alert alert-warning">
                    <strong>Hinweis:</strong> Datenbank nicht konfiguriert. Bitte richten Sie die MySQL-Verbindung ein.
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (empty($posts)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    Keine Blog-Posts gefunden. Führen Sie <code>config/database.sql</code> aus, um Beispieldaten zu laden.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="card-title">
                                <a href="#" data-page="blog-detail" class="page-link">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </h3>
                            <p class="text-muted small">
                                Von <?php echo htmlspecialchars($post['author_name']); ?> | 
                                <?php echo date('d.m.Y', strtotime($post['created_at'])); ?>
                            </p>
                            <p class="card-text">
                                <?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 200))); ?>...
                            </p>
                        </div>
                        <div class="card-footer">
                            <a href="#" data-page="blog-detail" class="btn btn-primary btn-sm page-link">Weiterlesen</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5>Dynamischer Content aus MySQL</h5>
                    <p>Diese Seite lädt Blog-Posts direkt aus der MySQL-Datenbank.</p>
                    <p>Die Daten werden über AJAX geladen, ohne die gesamte Seite neu zu laden.</p>
                </div>
            </div>
        </div>
    </div>
</div>