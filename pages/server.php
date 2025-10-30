<?php
/**
 * Server Management Seite
 */
?>

<div class="geex-content__header">
    <div class="geex-content__header__content">
        <h2 class="geex-content__header__title">Server Management</h2>
        <p class="geex-content__header__subtitle">Server-Übersicht und Verwaltung</p>
    </div>
</div>

<div class="geex-content__wrapper">
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Server Status</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>CPU-Auslastung</span>
                            <span class="text-success"><strong>45%</strong></span>
                        </div>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-success" style="width: 45%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>RAM-Auslastung</span>
                            <span class="text-warning"><strong>67%</strong></span>
                        </div>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-warning" style="width: 67%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Festplatte</span>
                            <span class="text-info"><strong>52%</strong></span>
                        </div>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-info" style="width: 52%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Systeminfo</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>PHP Version:</strong></td>
                            <td><?php echo phpversion(); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Server:</strong></td>
                            <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Betriebssystem:</strong></td>
                            <td><?php echo PHP_OS; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Max Upload:</strong></td>
                            <td><?php echo ini_get('upload_max_filesize'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Memory Limit:</strong></td>
                            <td><?php echo ini_get('memory_limit'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>