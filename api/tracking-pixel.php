<?php
// =============================================
// Server-Side Tracking Endpoint
// =============================================
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false]); exit;
}

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$event    = $data['event'] ?? '';
$pixelId  = getSetting('meta_pixel_id', '');
$token    = getSetting('server_side_token', '');
$enabled  = getSetting('server_tracking_enabled', '0') === '1';

if (!$enabled || !$pixelId || !$token || !$event) {
    echo json_encode(['success' => false, 'message' => 'Tracking not configured']); exit;
}

// Build Facebook CAPI payload
$phone = preg_replace('/[^0-9]/', '', $data['phone'] ?? '');
if ($phone && substr($phone, 0, 2) === '01') $phone = '880' . substr($phone, 1);

$eventData = [
    'data' => [[
        'event_name' => $event,
        'event_time' => time(),
        'event_source_url' => $data['url'] ?? ($_SERVER['HTTP_REFERER'] ?? ''),
        'action_source' => 'website',
        'user_data' => array_filter([
            'ph' => $phone ? [hash('sha256', $phone)] : null,
            'client_ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'client_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]),
        'custom_data' => array_filter([
            'currency' => 'BDT',
            'value' => $data['value'] ?? null,
            'content_ids' => isset($data['content_id']) ? [$data['content_id']] : null,
            'content_name' => $data['content_name'] ?? null,
            'order_id' => $data['order_id'] ?? null,
        ]),
    ]],
    'access_token' => $token,
];

// Send to Facebook CAPI
$apiUrl = "https://graph.facebook.com/v19.0/{$pixelId}/events";
$response = sendHttpPost($apiUrl, json_encode($eventData));

echo json_encode(['success' => true, 'fb_response' => $response]);

// Also check custom server-side URL
$customUrl = getSetting('server_side_url', '');
if ($customUrl && $customUrl !== $apiUrl) {
    sendHttpPost($customUrl, json_encode(array_merge($data, ['token' => $token])));
}

function sendHttpPost(string $url, string $body): ?string {
    $ctx = stream_context_create(['http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => $body,
        'timeout' => 5,
    ]]);
    return @file_get_contents($url, false, $ctx) ?: null;
}
