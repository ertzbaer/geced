<?php
/**
 * Mock API Server for Testing
 * Returns demo data without database
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/api/', '', $path);

// Mock JWT Token
$mockToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.mock.token';

// Mock Data
$mockUser = [
    'id' => 1,
    'username' => 'admin',
    'email' => 'admin@leadmanager.com',
    'role' => 'superadmin'
];

$mockLeads = [
    [
        'id' => 1,
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
        'company' => 'Musterfirma GmbH',
        'phone' => '+49 30 12345678',
        'email' => 'max.mustermann@example.com',
        'status' => 'new',
        'qualification_score' => 75,
        'notes' => 'Sehr interessiert',
        'assigned_to' => 1,
        'assigned_user_name' => 'admin',
        'created_at' => '2025-10-27 10:00:00'
    ],
    [
        'id' => 2,
        'first_name' => 'Anna',
        'last_name' => 'Schmidt',
        'company' => 'Tech Solutions AG',
        'phone' => '+49 89 98765432',
        'email' => 'anna.schmidt@techsolutions.de',
        'status' => 'contacted',
        'qualification_score' => 85,
        'notes' => 'Follow-up geplant',
        'assigned_to' => 1,
        'assigned_user_name' => 'admin',
        'created_at' => '2025-10-26 14:30:00'
    ]
];

$mockCampaigns = [
    [
        'id' => 1,
        'name' => 'Q4 2025 Outreach',
        'voice_provider' => 'openai',
        'status' => 'active',
        'leads_count' => 5,
        'calls_count' => 8,
        'created_at' => '2025-10-20 09:00:00'
    ]
];

$mockStats = [
    'summary' => [
        'total_leads' => 8,
        'new_leads' => 3,
        'active_campaigns' => 1,
        'conversion_rate' => 37.5
    ],
    'lead_status_distribution' => [
        ['status' => 'new', 'count' => 3],
        ['status' => 'contacted', 'count' => 2],
        ['status' => 'qualified', 'count' => 1],
        ['status' => 'converted', 'count' => 2]
    ],
    'campaign_performance' => [
        ['status' => 'active', 'count' => 1],
        ['status' => 'draft', 'count' => 1],
        ['status' => 'paused', 'count' => 1]
    ],
    'lead_trend' => [
        ['date' => '2025-10-23', 'count' => 2],
        ['date' => '2025-10-24', 'count' => 1],
        ['date' => '2025-10-25', 'count' => 0],
        ['date' => '2025-10-26', 'count' => 2],
        ['date' => '2025-10-27', 'count' => 3]
    ],
    'recent_leads' => array_slice($mockLeads, 0, 5)
];

// Route Handler
if ($method === 'POST' && $path === 'auth/login') {
    echo json_encode([
        'success' => true,
        'user' => $mockUser,
        'accessToken' => $mockToken,
        'refreshToken' => $mockToken,
        'message' => 'Login successful (DEMO MODE)'
    ]);
    exit;
}

if ($method === 'GET' && $path === 'auth/check') {
    echo json_encode([
        'success' => true,
        'user' => $mockUser
    ]);
    exit;
}

if ($method === 'POST' && $path === 'auth/logout') {
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
    exit;
}

if ($method === 'GET' && $path === 'leads') {
    echo json_encode([
        'success' => true,
        'leads' => $mockLeads
    ]);
    exit;
}

if ($method === 'GET' && $path === 'campaigns') {
    echo json_encode([
        'success' => true,
        'campaigns' => $mockCampaigns
    ]);
    exit;
}

if ($method === 'GET' && $path === 'dashboard/stats') {
    echo json_encode([
        'success' => true,
        'data' => $mockStats
    ]);
    exit;
}

if ($method === 'GET' && $path === 'dashboard/charts/lead-status') {
    echo json_encode([
        'success' => true,
        'data' => $mockStats['lead_status_distribution']
    ]);
    exit;
}

if ($method === 'GET' && strpos($path, 'users') !== false) {
    echo json_encode([
        'success' => true,
        'users' => [$mockUser]
    ]);
    exit;
}

// Default 404
http_response_code(404);
echo json_encode([
    'success' => false,
    'error' => 'Endpoint not found (Mock API)',
    'path' => $path,
    'method' => $method
]);
