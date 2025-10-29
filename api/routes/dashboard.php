<?php
/**
 * Dashboard & Statistics Routes
 * Provides analytics and metrics for dashboard
 */

use App\Utils\Router;
use App\Utils\Response;
use App\Utils\Database;
use App\Middleware\Auth;

/**
 * GET /api/dashboard/stats
 * Get dashboard statistics
 */
$router->get('/dashboard/stats', function () {
    Auth::authenticate();

    $db = Database::getInstance();

    // Total leads
    $totalLeads = $db->query("SELECT COUNT(*) as count FROM leads");

    // New leads (status = 'new')
    $newLeads = $db->query("SELECT COUNT(*) as count FROM leads WHERE status = 'new'");

    // Active campaigns (status = 'active')
    $activeCampaigns = $db->query("SELECT COUNT(*) as count FROM campaigns WHERE status = 'active'");

    // Conversion rate calculation
    $conversionData = $db->query(
        "SELECT
            COUNT(*) as total,
            SUM(CASE WHEN status IN ('qualified', 'converted') THEN 1 ELSE 0 END) as converted
         FROM leads"
    );

    $conversionRate = 0;
    if ($conversionData[0]['total'] > 0) {
        $conversionRate = round(($conversionData[0]['converted'] / $conversionData[0]['total']) * 100, 2);
    }

    // Lead status distribution
    $statusDistribution = $db->query(
        "SELECT status, COUNT(*) as count
         FROM leads
         GROUP BY status
         ORDER BY count DESC"
    );

    // Campaign performance (count by status)
    $campaignPerformance = $db->query(
        "SELECT status, COUNT(*) as count
         FROM campaigns
         GROUP BY status
         ORDER BY count DESC"
    );

    // Lead trend (last 7 days)
    $leadTrend = $db->query(
        "SELECT DATE(created_at) as date, COUNT(*) as count
         FROM leads
         WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
         GROUP BY DATE(created_at)
         ORDER BY date ASC"
    );

    // Recent leads (last 5)
    $recentLeads = $db->query(
        "SELECT id, first_name, last_name, status, created_at
         FROM leads
         ORDER BY created_at DESC
         LIMIT 5"
    );

    // Call statistics
    $callStats = $db->query(
        "SELECT
            COUNT(*) as total_calls,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_calls,
            SUM(CASE WHEN outcome = 'answered' THEN 1 ELSE 0 END) as answered_calls,
            AVG(duration) as avg_duration
         FROM calls"
    );

    Response::success([
        'summary' => [
            'total_leads' => $totalLeads[0]['count'],
            'new_leads' => $newLeads[0]['count'],
            'active_campaigns' => $activeCampaigns[0]['count'],
            'conversion_rate' => $conversionRate
        ],
        'lead_status_distribution' => $statusDistribution,
        'campaign_performance' => $campaignPerformance,
        'lead_trend' => $leadTrend,
        'recent_leads' => $recentLeads,
        'call_statistics' => $callStats[0] ?? null
    ]);
});

/**
 * GET /api/dashboard/charts/lead-status
 * Get data for lead status chart
 */
$router->get('/dashboard/charts/lead-status', function () {
    Auth::authenticate();

    $db = Database::getInstance();

    $data = $db->query(
        "SELECT status, COUNT(*) as count
         FROM leads
         GROUP BY status
         ORDER BY count DESC"
    );

    Response::success(['data' => $data]);
});

/**
 * GET /api/dashboard/charts/campaign-status
 * Get data for campaign status chart
 */
$router->get('/dashboard/charts/campaign-status', function () {
    Auth::authenticate();

    $db = Database::getInstance();

    $data = $db->query(
        "SELECT status, COUNT(*) as count
         FROM campaigns
         GROUP BY status
         ORDER BY count DESC"
    );

    Response::success(['data' => $data]);
});

/**
 * GET /api/dashboard/charts/lead-trend
 * Get lead trend data (last 30 days)
 */
$router->get('/dashboard/charts/lead-trend', function () {
    Auth::authenticate();

    $db = Database::getInstance();

    $params = Router::getQueryParams();
    $days = isset($params['days']) ? (int)$params['days'] : 7;

    if ($days > 90) {
        $days = 90; // Max 90 days
    }

    $data = $db->query(
        "SELECT DATE(created_at) as date, COUNT(*) as count
         FROM leads
         WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
         GROUP BY DATE(created_at)
         ORDER BY date ASC",
        [$days]
    );

    Response::success(['data' => $data]);
});

/**
 * GET /api/dashboard/activity
 * Get recent activity feed
 */
$router->get('/dashboard/activity', function () {
    Auth::authenticate();

    $db = Database::getInstance();

    $params = Router::getQueryParams();
    $limit = isset($params['limit']) ? (int)$params['limit'] : 20;

    if ($limit > 100) {
        $limit = 100;
    }

    // Get recent activities from audit log
    $activities = $db->query(
        "SELECT al.*, u.username
         FROM audit_log al
         LEFT JOIN users u ON al.user_id = u.id
         ORDER BY al.created_at DESC
         LIMIT ?",
        [$limit]
    );

    Response::success(['activities' => $activities]);
});

/**
 * GET /api/dashboard/performance
 * Get performance metrics
 */
$router->get('/dashboard/performance', function () {
    Auth::authenticate();

    $db = Database::getInstance();

    // Leads per user (assigned)
    $leadsPerUser = $db->query(
        "SELECT u.id, u.username, COUNT(l.id) as lead_count
         FROM users u
         LEFT JOIN leads l ON u.id = l.assigned_to
         WHERE u.role IN ('admin', 'agent')
         GROUP BY u.id, u.username
         ORDER BY lead_count DESC"
    );

    // Calls per campaign
    $callsPerCampaign = $db->query(
        "SELECT c.id, c.name, COUNT(ca.id) as call_count
         FROM campaigns c
         LEFT JOIN calls ca ON c.id = ca.campaign_id
         GROUP BY c.id, c.name
         ORDER BY call_count DESC
         LIMIT 10"
    );

    // Success rate per campaign
    $campaignSuccessRate = $db->query(
        "SELECT
            c.id,
            c.name,
            COUNT(ca.id) as total_calls,
            SUM(CASE WHEN ca.outcome = 'answered' THEN 1 ELSE 0 END) as successful_calls
         FROM campaigns c
         LEFT JOIN calls ca ON c.id = ca.campaign_id
         GROUP BY c.id, c.name
         HAVING total_calls > 0
         ORDER BY successful_calls DESC
         LIMIT 10"
    );

    Response::success([
        'leads_per_user' => $leadsPerUser,
        'calls_per_campaign' => $callsPerCampaign,
        'campaign_success_rate' => $campaignSuccessRate
    ]);
});
