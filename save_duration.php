<?php
// Tự động lưu duration khi audio load xong
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false]); exit; }

$id       = $_POST['id'] ?? '';
$duration = $_POST['duration'] ?? '';
$dataFile = __DIR__ . '/data/songs.json';

if (!$id || !$duration || !file_exists($dataFile)) {
    echo json_encode(['success' => false]);
    exit;
}

$data = json_decode(file_get_contents($dataFile), true);
foreach ($data['songs'] as &$song) {
    if ($song['id'] === $id) {
        $song['duration'] = htmlspecialchars($duration, ENT_QUOTES, 'UTF-8');
        break;
    }
}
unset($song);
file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['success' => true]);
