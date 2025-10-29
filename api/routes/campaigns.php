<?php
/**
 * Campaign Management Routes
 * CRUD operations for campaigns
 */

use App\Utils\Router;
use App\Utils\Response;
use App\Utils\Database;
use App\Utils\Validator;
use App\Middleware\Auth;

/**
 * GET /api/campaigns
 * Get all campaigns with filters
 */
$router->get('/campaigns', function () {
    Auth::authenticate();

    $db = Database::getInstance();
    $params = Router::getQueryParams();

    $sql = "SELECT c.*,
            (SELECT COUNT(*) FROM campaign_leads WHERE campaign_id = c.id) as leads_count,
            (SELECT COUNT(*) FROM calls WHERE campaign_id = c.id) as calls_count
            FROM campaigns c
            WHERE 1=1";
    $queryParams = [];

    // Filter by status
    if (!empty($params['status'])) {
        $sql .= " AND c.status = ?";
        $queryParams[] = $params['status'];
    }

    // Search by name
    if (!empty($params['search'])) {
        $sql .= " AND c.name LIKE ?";
        $queryParams[] = '%' . $params['search'] . '%';
    }

    $sql .= " ORDER BY c.created_at DESC";

    $campaigns = $db->query($sql, $queryParams);

    Response::success(['campaigns' => $campaigns]);
});

/**
 * GET /api/campaigns/:id
 * Get single campaign with details
 */
$router->get('/campaigns/:id', function ($id) {
    Auth::authenticate();

    $db = Database::getInstance();

    // Get campaign
    $campaigns = $db->query(
        "SELECT * FROM campaigns WHERE id = ? LIMIT 1",
        [$id]
    );

    if (empty($campaigns)) {
        Response::notFound('Campaign not found');
    }

    $campaign = $campaigns[0];

    // Get assigned leads
    $leads = $db->query(
        "SELECT l.*, cl.assigned_at
         FROM leads l
         INNER JOIN campaign_leads cl ON l.id = cl.lead_id
         WHERE cl.campaign_id = ?
         ORDER BY cl.assigned_at DESC",
        [$id]
    );

    // Get call statistics
    $stats = $db->query(
        "SELECT
            COUNT(*) as total_calls,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_calls,
            SUM(CASE WHEN outcome = 'answered' THEN 1 ELSE 0 END) as answered_calls,
            AVG(duration) as avg_duration
         FROM calls
         WHERE campaign_id = ?",
        [$id]
    );

    $campaign['leads'] = $leads;
    $campaign['statistics'] = $stats[0] ?? null;

    Response::success(['campaign' => $campaign]);
});

/**
 * POST /api/campaigns
 * Create new campaign
 */
$router->post('/campaigns', function () {
    Auth::requireAdmin();

    $data = Router::getJsonBody();
    $validator = new Validator();

    // Validate input
    if (!$validator->required($data['name'] ?? '', 'name') ||
        !$validator->required($data['voice_provider'] ?? '', 'voice_provider') ||
        !$validator->inArray($data['voice_provider'] ?? '', ['openai', 'elevenlabs'], 'voice_provider') ||
        !$validator->required($data['agent_prompt'] ?? '', 'agent_prompt')) {
        Response::validationError($validator->getErrors());
    }

    // Validate status
    $status = $data['status'] ?? 'draft';
    if (!$validator->inArray($status, ['draft', 'active', 'paused', 'completed'], 'status')) {
        Response::validationError($validator->getErrors());
    }

    $db = Database::getInstance();

    // Check if campaign name exists
    $existing = $db->query(
        "SELECT id FROM campaigns WHERE name = ? LIMIT 1",
        [Validator::sanitizeString($data['name'])]
    );

    if (!empty($existing)) {
        Response::error('A campaign with this name already exists', 409);
    }

    // Process qualification questions
    $qualificationQuestions = null;
    if (!empty($data['qualification_questions'])) {
        $qualificationQuestions = is_array($data['qualification_questions'])
            ? json_encode($data['qualification_questions'])
            : $data['qualification_questions'];
    }

    // Create campaign
    $campaignId = $db->execute(
        "INSERT INTO campaigns (name, voice_provider, agent_prompt, qualification_questions, status)
         VALUES (?, ?, ?, ?, ?)",
        [
            Validator::sanitizeString($data['name']),
            $data['voice_provider'],
            $data['agent_prompt'],
            $qualificationQuestions,
            $status
        ]
    );

    // Get created campaign
    $campaigns = $db->query(
        "SELECT * FROM campaigns WHERE id = ? LIMIT 1",
        [$campaignId]
    );

    Response::success(['campaign' => $campaigns[0]], 'Campaign created successfully', 201);
});

/**
 * PUT /api/campaigns/:id
 * Update campaign
 */
$router->put('/campaigns/:id', function ($id) {
    Auth::requireAdmin();

    $data = Router::getJsonBody();
    $validator = new Validator();
    $db = Database::getInstance();

    // Check if campaign exists
    $existing = $db->query(
        "SELECT * FROM campaigns WHERE id = ? LIMIT 1",
        [$id]
    );

    if (empty($existing)) {
        Response::notFound('Campaign not found');
    }

    $updateFields = [];
    $params = [];

    // Validate and prepare update fields
    if (isset($data['name'])) {
        $validator->required($data['name'], 'name');
        $updateFields[] = "name = ?";
        $params[] = Validator::sanitizeString($data['name']);
    }

    if (isset($data['voice_provider'])) {
        $validator->inArray($data['voice_provider'], ['openai', 'elevenlabs'], 'voice_provider');
        $updateFields[] = "voice_provider = ?";
        $params[] = $data['voice_provider'];
    }

    if (isset($data['agent_prompt'])) {
        $validator->required($data['agent_prompt'], 'agent_prompt');
        $updateFields[] = "agent_prompt = ?";
        $params[] = $data['agent_prompt'];
    }

    if (isset($data['qualification_questions'])) {
        $qualificationQuestions = is_array($data['qualification_questions'])
            ? json_encode($data['qualification_questions'])
            : $data['qualification_questions'];
        $updateFields[] = "qualification_questions = ?";
        $params[] = $qualificationQuestions;
    }

    if (isset($data['status'])) {
        $validator->inArray($data['status'], ['draft', 'active', 'paused', 'completed'], 'status');
        $updateFields[] = "status = ?";
        $params[] = $data['status'];
    }

    if ($validator->hasErrors()) {
        Response::validationError($validator->getErrors());
    }

    if (empty($updateFields)) {
        Response::error('No fields to update', 400);
    }

    // Update campaign
    $params[] = $id;
    $sql = "UPDATE campaigns SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $db->execute($sql, $params);

    // Get updated campaign
    $campaigns = $db->query(
        "SELECT * FROM campaigns WHERE id = ? LIMIT 1",
        [$id]
    );

    Response::success(['campaign' => $campaigns[0]], 'Campaign updated successfully');
});

/**
 * DELETE /api/campaigns/:id
 * Delete campaign
 */
$router->delete('/campaigns/:id', function ($id) {
    Auth::requireAdmin();

    $db = Database::getInstance();

    // Check if campaign exists
    $existing = $db->query(
        "SELECT id FROM campaigns WHERE id = ? LIMIT 1",
        [$id]
    );

    if (empty($existing)) {
        Response::notFound('Campaign not found');
    }

    // Delete campaign (cascade will handle related records)
    $db->execute("DELETE FROM campaigns WHERE id = ?", [$id]);

    Response::success(null, 'Campaign deleted successfully');
});

/**
 * POST /api/campaigns/:id/assign-leads
 * Assign leads to campaign
 */
$router->post('/campaigns/:id/assign-leads', function ($id) {
    Auth::requireAdmin();

    $data = Router::getJsonBody();

    if (empty($data['lead_ids']) || !is_array($data['lead_ids'])) {
        Response::error('Lead IDs are required', 400);
    }

    $db = Database::getInstance();

    // Check if campaign exists
    $existing = $db->query(
        "SELECT id FROM campaigns WHERE id = ? LIMIT 1",
        [$id]
    );

    if (empty($existing)) {
        Response::notFound('Campaign not found');
    }

    // Assign leads to campaign
    $db->beginTransaction();
    try {
        foreach ($data['lead_ids'] as $leadId) {
            // Check if already assigned
            $assigned = $db->query(
                "SELECT id FROM campaign_leads WHERE campaign_id = ? AND lead_id = ? LIMIT 1",
                [$id, $leadId]
            );

            if (empty($assigned)) {
                $db->execute(
                    "INSERT INTO campaign_leads (campaign_id, lead_id) VALUES (?, ?)",
                    [$id, $leadId]
                );
            }
        }

        $db->commit();

        Response::success(null, 'Leads assigned to campaign successfully');
    } catch (\Exception $e) {
        $db->rollback();
        Response::serverError('Failed to assign leads to campaign');
    }
});

/**
 * DELETE /api/campaigns/:id/remove-lead/:leadId
 * Remove lead from campaign
 */
$router->delete('/campaigns/:id/remove-lead/:leadId', function ($id, $leadId) {
    Auth::requireAdmin();

    $db = Database::getInstance();

    $db->execute(
        "DELETE FROM campaign_leads WHERE campaign_id = ? AND lead_id = ?",
        [$id, $leadId]
    );

    Response::success(null, 'Lead removed from campaign');
});
