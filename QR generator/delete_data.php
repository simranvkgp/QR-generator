<?php
/**
 * delete_data.php
 * Removes a record by ID from data.json
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

$input = file_get_contents('php://input');
$data  = json_decode($input, true);

if (empty($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing id']);
    exit;
}

$id   = (string)$data['id'];
$file = __DIR__ . '/data.json';

if (!file_exists($file)) {
    echo json_encode(['success' => false, 'message' => 'data.json not found']);
    exit;
}

$json    = file_get_contents($file);
$records = json_decode($json, true);

if (!is_array($records)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data.json']);
    exit;
}

$before = count($records);
$records = array_values(array_filter($records, fn($r) => (string)$r['id'] !== $id));

if (count($records) === $before) {
    echo json_encode(['success' => false, 'message' => 'Record not found']);
    exit;
}

$saved = file_put_contents(
    $file,
    json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
);

if ($saved === false) {
    echo json_encode(['success' => false, 'message' => 'Failed to write file']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Record deleted']);
