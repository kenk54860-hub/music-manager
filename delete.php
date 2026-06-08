<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$id       = $_POST['id'] ?? '';
$dataFile = __DIR__ . '/data/songs.json';

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu ID']);
    exit;
}

if (!file_exists($dataFile)) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy data']);
    exit;
}

$data = json_decode(file_get_contents($dataFile), true);
$deleted = false;

foreach ($data['songs'] as $i => $song) {
    if ($song['id'] === $id) {
        // Xóa file vật lý
        if (!empty($song['audio'])) {
            $audioPath = __DIR__ . '/' . $song['audio'];
            if (file_exists($audioPath)) unlink($audioPath);
        }
        if (!empty($song['cover'])) {
            $coverPath = __DIR__ . '/' . $song['cover'];
            if (file_exists($coverPath)) unlink($coverPath);
        }
        array_splice($data['songs'], $i, 1);
        $deleted = true;
        break;
    }
}

if ($deleted) {
    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy bài hát']);
}
