<?php
/**
 * Lead Management Routes
 * CRUD operations for leads with advanced filtering
 */

use App\Utils\Router;
use App\Utils\Response;
use App\Utils\Database;
use App\Utils\Validator;
use App\Middleware\Auth;

/**
 * GET /api/leads
 * Get all leads with filtering and search
 */
$router->get('/leads', function () {
    Auth::authenticate();

    $db = Database::getInstance();
    $params = Router::getQueryParams();

    $sql = "SELECT l.*, u.username as assigned_user_name
            FROM leads l
            LEFT JOIN users u ON l.assigned_to = u.id
            WHERE 1=1";
    $queryParams = [];

    // Filter by status
    if (!empty($params['status'])) {
        $sql .= " AND l.status = ?";
        $queryParams[] = $params['status'];
    }

    // Filter by assigned user
    if (!empty($params['assignedTo'])) {
        $sql .= " AND l.assigned_to = ?";
        $queryParams[] = $params['assignedTo'];
    }

    // Date range filter
    if (!empty($params['dateFrom'])) {
        $sql .= " AND l.created_at >= ?";
        $queryParams[] = $params['dateFrom'] . ' 00:00:00';
    }

    if (!empty($params['dateTo'])) {
        $sql .= " AND l.created_at <= ?";
        $queryParams[] = $params['dateTo'] . ' 23:59:59';
    }

    // Search functionality
    if (!empty($params['search'])) {
        $searchTerm = '%' . $params['search'] . '%';
        $sql .= " AND (l.first_name LIKE ? OR l.last_name LIKE ? OR l.email LIKE ? OR l.phone LIKE ? OR l.company LIKE ?)";
        $queryParams = array_merge($queryParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }

    $sql .= " ORDER BY l.created_at DESC";

    $leads = $db->query($sql, $queryParams);

    Response::success(['leads' => $leads]);
});

/**
 * GET /api/leads/:id
 * Get single lead with call history
 */
$router->get('/leads/:id', function ($id) {
    Auth::authenticate();

    $db = Database::getInstance();

    // Get lead
    $leads = $db->query(
        "SELECT l.*, u.username as assigned_user_name
         FROM leads l
         LEFT JOIN users u ON l.assigned_to = u.id
         WHERE l.id = ? LIMIT 1",
        [$id]
    );

    if (empty($leads)) {
        Response::notFound('Lead not found');
    }

    $lead = $leads[0];

    // Get call history
    $calls = $db->query(
        "SELECT c.*, camp.name as campaign_name
         FROM calls c
         LEFT JOIN campaigns camp ON c.campaign_id = camp.id
         WHERE c.lead_id = ?
         ORDER BY c.called_at DESC",
        [$id]
    );

    // Get status history
    $statusHistory = $db->query(
        "SELECT sh.*, u.username as changed_by_name
         FROM lead_status_history sh
         LEFT JOIN users u ON sh.changed_by = u.id
         WHERE sh.lead_id = ?
         ORDER BY sh.changed_at DESC",
        [$id]
    );

    $lead['calls'] = $calls;
    $lead['status_history'] = $statusHistory;

    Response::success(['lead' => $lead]);
});

/**
 * POST /api/leads
 * Create new lead
 */
$router->post('/leads', function () {
    Auth::requireAdmin();

    $data = Router::getJsonBody();
    $validator = new Validator();
    $currentUser = Auth::getCurrentUser();

    // Validate input
    if (!$validator->required($data['first_name'] ?? '', 'first_name') ||
        !$validator->required($data['last_name'] ?? '', 'last_name') ||
        !$validator->required($data['phone'] ?? '', 'phone') ||
        !$validator->phone($data['phone'] ?? '', 'phone')) {
        Response::validationError($validator->getErrors());
    }

    // Validate optional email
    if (!empty($data['email']) && !$validator->email($data['email'], 'email')) {
        Response::validationError($validator->getErrors());
    }

    // Validate qualification score
    if (isset($data['qualification_score']) && !empty($data['qualification_score'])) {
        if (!$validator->integer($data['qualification_score'], 'qualification_score') ||
            !$validator->range($data['qualification_score'], 0, 100, 'qualification_score')) {
            Response::validationError($validator->getErrors());
        }
    }

    // Validate status
    $status = $data['status'] ?? 'new';
    if (!$validator->inArray($status, ['new', 'contacted', 'qualified', 'unqualified', 'converted'], 'status')) {
        Response::validationError($validator->getErrors());
    }

    $db = Database::getInstance();

    // Check if phone number already exists
    $existing = $db->query(
        "SELECT id FROM leads WHERE phone = ? LIMIT 1",
        [$data['phone']]
    );

    if (!empty($existing)) {
        Response::error('A lead with this phone number already exists', 409);
    }

    // Create lead
    $db->beginTransaction();
    try {
        $leadId = $db->execute(
            "INSERT INTO leads (first_name, last_name, company, phone, email, status, qualification_score, notes, assigned_to)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                Validator::sanitizeString($data['first_name']),
                Validator::sanitizeString($data['last_name']),
                Validator::sanitizeString($data['company'] ?? ''),
                $data['phone'],
                Validator::sanitizeEmail($data['email'] ?? ''),
                $status,
                $data['qualification_score'] ?? null,
                $data['notes'] ?? '',
                $data['assigned_to'] ?? null
            ]
        );

        // Record status history
        $db->execute(
            "INSERT INTO lead_status_history (lead_id, old_status, new_status, changed_by) VALUES (?, NULL, ?, ?)",
            [$leadId, $status, $currentUser['id']]
        );

        $db->commit();

        // Get created lead
        $leads = $db->query(
            "SELECT l.*, u.username as assigned_user_name
             FROM leads l
             LEFT JOIN users u ON l.assigned_to = u.id
             WHERE l.id = ? LIMIT 1",
            [$leadId]
        );

        Response::success(['lead' => $leads[0]], 'Lead created successfully', 201);
    } catch (\Exception $e) {
        $db->rollback();
        Response::serverError('Failed to create lead');
    }
});

/**
 * PUT /api/leads/:id
 * Update lead
 */
$router->put('/leads/:id', function ($id) {
    Auth::requireAdmin();

    $data = Router::getJsonBody();
    $validator = new Validator();
    $currentUser = Auth::getCurrentUser();
    $db = Database::getInstance();

    // Check if lead exists
    $existing = $db->query(
        "SELECT * FROM leads WHERE id = ? LIMIT 1",
        [$id]
    );

    if (empty($existing)) {
        Response::notFound('Lead not found');
    }

    $oldLead = $existing[0];

    $updateFields = [];
    $params = [];

    // Validate and prepare update fields
    if (isset($data['first_name'])) {
        $validator->required($data['first_name'], 'first_name');
        $updateFields[] = "first_name = ?";
        $params[] = Validator::sanitizeString($data['first_name']);
    }

    if (isset($data['last_name'])) {
        $validator->required($data['last_name'], 'last_name');
        $updateFields[] = "last_name = ?";
        $params[] = Validator::sanitizeString($data['last_name']);
    }

    if (isset($data['company'])) {
        $updateFields[] = "company = ?";
        $params[] = Validator::sanitizeString($data['company']);
    }

    if (isset($data['phone'])) {
        $validator->required($data['phone'], 'phone');
        $validator->phone($data['phone'], 'phone');
        $updateFields[] = "phone = ?";
        $params[] = $data['phone'];
    }

    if (isset($data['email'])) {
        if (!empty($data['email'])) {
            $validator->email($data['email'], 'email');
        }
        $updateFields[] = "email = ?";
        $params[] = Validator::sanitizeEmail($data['email']);
    }

    if (isset($data['status'])) {
        $validator->inArray($data['status'], ['new', 'contacted', 'qualified', 'unqualified', 'converted'], 'status');
        $updateFields[] = "status = ?";
        $params[] = $data['status'];
    }

    if (isset($data['qualification_score'])) {
        if (!empty($data['qualification_score'])) {
            $validator->integer($data['qualification_score'], 'qualification_score');
            $validator->range($data['qualification_score'], 0, 100, 'qualification_score');
        }
        $updateFields[] = "qualification_score = ?";
        $params[] = $data['qualification_score'];
    }

    if (isset($data['notes'])) {
        $updateFields[] = "notes = ?";
        $params[] = $data['notes'];
    }

    if (isset($data['assigned_to'])) {
        $updateFields[] = "assigned_to = ?";
        $params[] = $data['assigned_to'];
    }

    if ($validator->hasErrors()) {
        Response::validationError($validator->getErrors());
    }

    if (empty($updateFields)) {
        Response::error('No fields to update', 400);
    }

    // Update lead
    $db->beginTransaction();
    try {
        $params[] = $id;
        $sql = "UPDATE leads SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $db->execute($sql, $params);

        // Record status change if status was updated
        if (isset($data['status']) && $data['status'] !== $oldLead['status']) {
            $db->execute(
                "INSERT INTO lead_status_history (lead_id, old_status, new_status, changed_by) VALUES (?, ?, ?, ?)",
                [$id, $oldLead['status'], $data['status'], $currentUser['id']]
            );
        }

        $db->commit();

        // Get updated lead
        $leads = $db->query(
            "SELECT l.*, u.username as assigned_user_name
             FROM leads l
             LEFT JOIN users u ON l.assigned_to = u.id
             WHERE l.id = ? LIMIT 1",
            [$id]
        );

        Response::success(['lead' => $leads[0]], 'Lead updated successfully');
    } catch (\Exception $e) {
        $db->rollback();
        Response::serverError('Failed to update lead');
    }
});

/**
 * DELETE /api/leads/:id
 * Delete lead
 */
$router->delete('/leads/:id', function ($id) {
    Auth::requireAdmin();

    $db = Database::getInstance();

    // Check if lead exists
    $existing = $db->query(
        "SELECT id FROM leads WHERE id = ? LIMIT 1",
        [$id]
    );

    if (empty($existing)) {
        Response::notFound('Lead not found');
    }

    // Delete lead (cascade will handle related records)
    $db->execute("DELETE FROM leads WHERE id = ?", [$id]);

    Response::success(null, 'Lead deleted successfully');
});

/**
 * POST /api/leads/bulk-delete
 * Bulk delete leads
 */
$router->post('/leads/bulk-delete', function () {
    Auth::requireAdmin();

    $data = Router::getJsonBody();

    if (empty($data['ids']) || !is_array($data['ids'])) {
        Response::error('Lead IDs are required', 400);
    }

    $db = Database::getInstance();
    $placeholders = str_repeat('?,', count($data['ids']) - 1) . '?';

    $db->execute(
        "DELETE FROM leads WHERE id IN ($placeholders)",
        $data['ids']
    );

    Response::success(null, 'Leads deleted successfully');
});

/**
 * POST /api/leads/bulk-assign
 * Bulk assign leads to user
 */
$router->post('/leads/bulk-assign', function () {
    Auth::requireAdmin();

    $data = Router::getJsonBody();

    if (empty($data['ids']) || !is_array($data['ids'])) {
        Response::error('Lead IDs are required', 400);
    }

    if (!isset($data['assigned_to'])) {
        Response::error('Assigned user ID is required', 400);
    }

    $db = Database::getInstance();
    $placeholders = str_repeat('?,', count($data['ids']) - 1) . '?';
    $params = array_merge([$data['assigned_to']], $data['ids']);

    $db->execute(
        "UPDATE leads SET assigned_to = ? WHERE id IN ($placeholders)",
        $params
    );

    Response::success(null, 'Leads assigned successfully');
});

/**
 * POST /api/leads/bulk-status
 * Bulk update lead status
 */
$router->post('/leads/bulk-status', function () {
    Auth::requireAdmin();
    $currentUser = Auth::getCurrentUser();

    $data = Router::getJsonBody();
    $validator = new Validator();

    if (empty($data['ids']) || !is_array($data['ids'])) {
        Response::error('Lead IDs are required', 400);
    }

    if (!$validator->required($data['status'] ?? '', 'status') ||
        !$validator->inArray($data['status'], ['new', 'contacted', 'qualified', 'unqualified', 'converted'], 'status')) {
        Response::validationError($validator->getErrors());
    }

    $db = Database::getInstance();

    $db->beginTransaction();
    try {
        // Update leads
        $placeholders = str_repeat('?,', count($data['ids']) - 1) . '?';
        $params = array_merge([$data['status']], $data['ids']);

        $db->execute(
            "UPDATE leads SET status = ? WHERE id IN ($placeholders)",
            $params
        );

        // Record status history for each lead
        foreach ($data['ids'] as $leadId) {
            $db->execute(
                "INSERT INTO lead_status_history (lead_id, new_status, changed_by) VALUES (?, ?, ?)",
                [$leadId, $data['status'], $currentUser['id']]
            );
        }

        $db->commit();

        Response::success(null, 'Lead statuses updated successfully');
    } catch (\Exception $e) {
        $db->rollback();
        Response::serverError('Failed to update lead statuses');
    }
});

/**
 * GET /api/leads/export/csv
 * Export leads as CSV
 */
$router->get('/leads/export/csv', function () {
    Auth::authenticate();

    $db = Database::getInstance();
    $leads = $db->query(
        "SELECT l.*, u.username as assigned_user_name
         FROM leads l
         LEFT JOIN users u ON l.assigned_to = u.id
         ORDER BY l.created_at DESC"
    );

    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=leads_export_' . date('Y-m-d_H-i-s') . '.csv');

    // Output CSV
    $output = fopen('php://output', 'w');

    // CSV Headers (German)
    fputcsv($output, [
        'ID',
        'Vorname',
        'Nachname',
        'Firma',
        'Telefon',
        'E-Mail',
        'Status',
        'Qualifikations-Score',
        'Notizen',
        'Zugewiesen an',
        'Erstellt am'
    ], ';');

    // CSV Data
    foreach ($leads as $lead) {
        fputcsv($output, [
            $lead['id'],
            $lead['first_name'],
            $lead['last_name'],
            $lead['company'],
            $lead['phone'],
            $lead['email'],
            $lead['status'],
            $lead['qualification_score'],
            $lead['notes'],
            $lead['assigned_user_name'],
            $lead['created_at']
        ], ';');
    }

    fclose($output);
    exit;
});
