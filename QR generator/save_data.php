<?php
/**
 * save_data.php
 * Receives a JSON POST body and appends it to data.json
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST allowed']);
    exit;
}

// Read request body
$input = file_get_contents('php://input');
$data  = json_decode($input, true);

if (!$data || empty($data['title']) || empty($data['content'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields: title, content']);
    exit;
}

// Sanitize
$record = [
    'id'          => isset($data['id'])          ? (int)$data['id']                 : time() * 1000,
    'title'       => htmlspecialchars(trim($data['title']),       ENT_QUOTES, 'UTF-8'),
    'type'        => in_array($data['type'] ?? '', ['link','book','pdf','other'])
                       ? $data['type'] : 'other',
    'content'     => trim($data['content']),
    'description' => htmlspecialchars(trim($data['description'] ?? ''), ENT_QUOTES, 'UTF-8'),
    'created_at'  => isset($data['created_at'])
                       ? $data['created_at']
                       : date('c'),  // ISO 8601
];

// Load existing records
$file    = __DIR__ . '/data.json';
$records = [];

if (file_exists($file)) {
    $json = file_get_contents($file);
    $decoded = json_decode($json, true);
    if (is_array($decoded)) {
        $records = $decoded;
    }
}

// Check for duplicate ID
foreach ($records as $r) {
    if ((string)$r['id'] === (string)$record['id']) {
        echo json_encode(['success' => false, 'message' => 'Record with this ID already exists']);
        exit;
    }
}

// Append and save
$records[] = $record;

$saved = file_put_contents(
    $file,
    json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
);

if ($saved === false) {
    echo json_encode(['success' => false, 'message' => 'Failed to write data.json — check file permissions']);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Record saved successfully',
    'record'  => $record
]);
