<?php
/**
 * get_data.php
 * Returns all records from data.json as a JSON array.
 * Supports optional query params: ?type=book, ?search=physics
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$file = __DIR__ . '/data.json';

if (!file_exists($file)) {
    // Return empty array if file not yet created
    echo json_encode([]);
    exit;
}

$json    = file_get_contents($file);
$records = json_decode($json, true);

if (!is_array($records)) {
    echo json_encode([]);
    exit;
}

// ── Optional Filtering ──────────────────────────

// Filter by type: ?type=book
if (!empty($_GET['type'])) {
    $type    = strtolower(trim($_GET['type']));
    $records = array_values(array_filter($records, fn($r) =>
        strtolower($r['type'] ?? '') === $type
    ));
}

// Search: ?search=physics
if (!empty($_GET['search'])) {
    $q       = strtolower(trim($_GET['search']));
    $records = array_values(array_filter($records, fn($r) =>
        str_contains(strtolower($r['title']       ?? ''), $q) ||
        str_contains(strtolower($r['content']     ?? ''), $q) ||
        str_contains(strtolower($r['description'] ?? ''), $q)
    ));
}

// Sort newest first
usort($records, fn($a, $b) =>
    strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0)
);

echo json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
