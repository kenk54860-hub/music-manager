<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$dataFile = __DIR__ . '/data/songs.json';

function loadData($file) {
    if (!file_exists($file)) return ['songs' => []];
    $raw = file_get_contents($file);
    $data = json_decode($raw, true);
    return $data ?: ['songs' => []];
}

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        $data = loadData($dataFile);
        echo json_encode($data);
        break;

    case 'play':
        $id   = $_GET['id'] ?? '';
        $data = loadData($dataFile);
        foreach ($data['songs'] as &$song) {
            if ($song['id'] === $id) {
                $song['plays']++;
                break;
            }
        }
        unset($song);
        file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo json_encode(['success' => true]);
        break;

    case 'search':
        $q    = mb_strtolower($_GET['q'] ?? '', 'UTF-8');
        $data = loadData($dataFile);
        if ($q) {
            $data['songs'] = array_values(array_filter($data['songs'], function($s) use ($q) {
                return mb_strpos(mb_strtolower($s['title'], 'UTF-8'), $q) !== false
                    || mb_strpos(mb_strtolower($s['artist'], 'UTF-8'), $q) !== false;
            }));
        }
        echo json_encode($data);
        break;

    default:
        echo json_encode(['error' => 'Unknown action']);
}
