<?php
/**
 * Call Management Routes
 * CRUD operations for calls
 */

use App\Utils\Router;
use App\Utils\Response;
use App\Utils\Database;
use App\Utils\Validator;
use App\Middleware\Auth;

/**
 * GET /api/calls
 * Get all calls with filtering
 */
$router->get('/calls', function () {
    Auth::authenticate();

    $db = Database::getInstance();
    $params = Router::getQueryParams();

    $sql = "SELECT c.*,
            l.first_name, l.last_name, l.phone as lead_phone,
            camp.name as campaign_name
            FROM calls c
            LEFT JOIN leads l ON c.lead_id = l.id
            LEFT JOIN campaigns camp ON c.campaign_id = camp.id
            WHERE 1=1";
    $queryParams = [];

    // Filter by lead
    if (!empty($params['leadId'])) {
        $sql .= " AND c.lead_id = ?";
        $queryParams[] = $params['leadId'];
    }

    // Filter by campaign
    if (!empty($params['campaignId'])) {
        $sql .= " AND c.campaign_id = ?";
        $queryParams[] = $params['campaignId'];
    }

    // Filter by status
    if (!empty($params['status'])) {
        $sql .= " AND c.status = ?";
        $queryParams[] = $params['status'];
    }

    // Filter by outcome
    if (!empty($params['outcome'])) {
        $sql .= " AND c.outcome = ?";
        $queryParams[] = $params['outcome'];
    }

    $sql .= " ORDER BY c.called_at DESC";

    $calls = $db->query($sql, $queryParams);

    Response::success(['calls' => $calls]);
});

/**
 * GET /api/calls/:id
 * Get single call
 */
$router->get('/calls/:id', function ($id) {
    Auth::authenticate();

    $db = Database::getInstance();

    $calls = $db->query(
        "SELECT c.*,
         l.first_name, l.last_name, l.phone as lead_phone, l.email as lead_email,
         camp.name as campaign_name
         FROM calls c
         LEFT JOIN leads l ON c.lead_id = l.id
         LEFT JOIN campaigns camp ON c.campaign_id = camp.id
         WHERE c.id = ? LIMIT 1",
        [$id]
    );

    if (empty($calls)) {
        Response::notFound('Call not found');
    }

    Response::success(['call' => $calls[0]]);
});

/**
 * POST /api/calls
 * Create new call
 */
$router->post('/calls', function () {
    Auth::requireAdmin();

    $data = Router::getJsonBody();
    $validator = new Validator();

    // Validate input
    if (!$validator->required($data['lead_id'] ?? '', 'lead_id') ||
        !$validator->integer($data['lead_id'] ?? '', 'lead_id') ||
        !$validator->required($data['campaign_id'] ?? '', 'campaign_id') ||
        !$validator->integer($data['campaign_id'] ?? '', 'campaign_id') ||
        !$validator->required($data['called_at'] ?? '', 'called_at')) {
        Response::validationError($validator->getErrors());
    }

    // Validate status
    $status = $data['status'] ?? 'scheduled';
    if (!$validator->inArray($status, ['scheduled', 'in_progress', 'completed', 'failed', 'no_answer'], 'status')) {
        Response::validationError($validator->getErrors());
    }

    // Validate outcome if provided
    if (!empty($data['outcome']) &&
        !$validator->inArray($data['outcome'], ['answered', 'no_answer', 'voicemail', 'busy', 'failed'], 'outcome')) {
        Response::validationError($validator->getErrors());
    }

    // Validate duration if provided
    if (isset($data['duration']) && !empty($data['duration'])) {
        if (!$validator->integer($data['duration'], 'duration')) {
            Response::validationError($validator->getErrors());
        }
    }

    $db = Database::getInstance();

    // Verify lead exists
    $lead = $db->query(
        "SELECT id FROM leads WHERE id = ? LIMIT 1",
        [$data['lead_id']]
    );

    if (empty($lead)) {
        Response::error('Lead not found', 404);
    }

    // Verify campaign exists
    $campaign = $db->query(
        "SELECT id FROM campaigns WHERE id = ? LIMIT 1",
        [$data['campaign_id']]
    );

    if (empty($campaign)) {
        Response::error('Campaign not found', 404);
    }

    // Create call
    $callId = $db->execute(
        "INSERT INTO calls (lead_id, campaign_id, status, outcome, duration, recording_url, notes, called_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        [
            $data['lead_id'],
            $data['campaign_id'],
            $status,
            $data['outcome'] ?? null,
            $data['duration'] ?? null,
            $data['recording_url'] ?? null,
            $data['notes'] ?? '',
            $data['called_at']
        ]
    );

    // Get created call
    $calls = $db->query(
        "SELECT c.*,
         l.first_name, l.last_name, l.phone as lead_phone,
         camp.name as campaign_name
         FROM calls c
         LEFT JOIN leads l ON c.lead_id = l.id
         LEFT JOIN campaigns camp ON c.campaign_id = camp.id
         WHERE c.id = ? LIMIT 1",
        [$callId]
    );

    Response::success(['call' => $calls[0]], 'Call created successfully', 201);
});

/**
 * PATCH /api/calls/:id
 * Update call (status, outcome, notes)
 */
$router->patch('/calls/:id', function ($id) {
    Auth::requireAdmin();

    $data = Router::getJsonBody();
    $validator = new Validator();
    $db = Database::getInstance();

    // Check if call exists
    $existing = $db->query(
        "SELECT * FROM calls WHERE id = ? LIMIT 1",
        [$id]
    );

    if (empty($existing)) {
        Response::notFound('Call not found');
    }

    $updateFields = [];
    $params = [];

    // Validate and prepare update fields
    if (isset($data['status'])) {
        $validator->inArray($data['status'], ['scheduled', 'in_progress', 'completed', 'failed', 'no_answer'], 'status');
        $updateFields[] = "status = ?";
        $params[] = $data['status'];
    }

    if (isset($data['outcome'])) {
        if (!empty($data['outcome'])) {
            $validator->inArray($data['outcome'], ['answered', 'no_answer', 'voicemail', 'busy', 'failed'], 'outcome');
        }
        $updateFields[] = "outcome = ?";
        $params[] = $data['outcome'];
    }

    if (isset($data['duration'])) {
        if (!empty($data['duration'])) {
            $validator->integer($data['duration'], 'duration');
        }
        $updateFields[] = "duration = ?";
        $params[] = $data['duration'];
    }

    if (isset($data['recording_url'])) {
        $updateFields[] = "recording_url = ?";
        $params[] = $data['recording_url'];
    }

    if (isset($data['notes'])) {
        $updateFields[] = "notes = ?";
        $params[] = $data['notes'];
    }

    if ($validator->hasErrors()) {
        Response::validationError($validator->getErrors());
    }

    if (empty($updateFields)) {
        Response::error('No fields to update', 400);
    }

    // Update call
    $params[] = $id;
    $sql = "UPDATE calls SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $db->execute($sql, $params);

    // Get updated call
    $calls = $db->query(
        "SELECT c.*,
         l.first_name, l.last_name, l.phone as lead_phone,
         camp.name as campaign_name
         FROM calls c
         LEFT JOIN leads l ON c.lead_id = l.id
         LEFT JOIN campaigns camp ON c.campaign_id = camp.id
         WHERE c.id = ? LIMIT 1",
        [$id]
    );

    Response::success(['call' => $calls[0]], 'Call updated successfully');
});

/**
 * DELETE /api/calls/:id
 * Delete call (Superadmin only)
 */
$router->delete('/calls/:id', function ($id) {
    Auth::requireSuperadmin();

    $db = Database::getInstance();

    // Check if call exists
    $existing = $db->query(
        "SELECT id FROM calls WHERE id = ? LIMIT 1",
        [$id]
    );

    if (empty($existing)) {
        Response::notFound('Call not found');
    }

    // Delete call
    $db->execute("DELETE FROM calls WHERE id = ?", [$id]);

    Response::success(null, 'Call deleted successfully');
});
