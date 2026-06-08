<?php
header('Content-Type: application/json; charset=utf-8');

// Cho phép CORS nếu cần
header('Access-Control-Allow-Origin: *');

$dataFile = __DIR__ . '/data/songs.json';
$audioDir = __DIR__ . '/uploads/audio/';
$coverDir = __DIR__ . '/uploads/covers/';

// Tạo thư mục nếu chưa có
foreach ([$audioDir, $coverDir, __DIR__ . '/data'] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

// Validate file nhạc
$audio = $_FILES['audio'] ?? null;
if (!$audio || $audio['error'] !== UPLOAD_ERR_OK) {
    $errMsg = 'Thiếu file nhạc';
    if ($audio) {
        $errors = [
            UPLOAD_ERR_INI_SIZE   => 'File quá lớn (php.ini)',
            UPLOAD_ERR_FORM_SIZE  => 'File quá lớn (form)',
            UPLOAD_ERR_PARTIAL    => 'Upload chưa hoàn tất',
            UPLOAD_ERR_NO_FILE    => 'Chưa chọn file',
            UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm',
            UPLOAD_ERR_CANT_WRITE => 'Không ghi được file',
        ];
        $errMsg = $errors[$audio['error']] ?? 'Lỗi upload không xác định';
    }
    echo json_encode(['success' => false, 'message' => $errMsg]);
    exit;
}

// Kiểm tra extension (mime type có thể bị giả)
$audioExt = strtolower(pathinfo($audio['name'], PATHINFO_EXTENSION));
$allowedExts = ['mp3', 'wav', 'ogg', 'm4a', 'flac', 'aac'];
if (!in_array($audioExt, $allowedExts)) {
    echo json_encode(['success' => false, 'message' => 'Chỉ hỗ trợ: MP3, WAV, OGG, M4A, FLAC, AAC']);
    exit;
}

// Tên file an toàn, tránh trùng
$audioName = uniqid('audio_', true) . '.' . $audioExt;
$audioPath = $audioDir . $audioName;
$audioUrl  = 'uploads/audio/' . $audioName;  // Đường dẫn tương đối để dùng trong HTML

if (!move_uploaded_file($audio['tmp_name'], $audioPath)) {
    echo json_encode(['success' => false, 'message' => 'Không thể lưu file nhạc']);
    exit;
}

// Xử lý ảnh bìa (tuỳ chọn)
$coverUrl = '';
$cover = $_FILES['cover'] ?? null;
if ($cover && $cover['error'] === UPLOAD_ERR_OK) {
    $coverExt = strtolower(pathinfo($cover['name'], PATHINFO_EXTENSION));
    $allowedImg = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (in_array($coverExt, $allowedImg)) {
        $coverName = uniqid('cover_', true) . '.' . $coverExt;
        $coverPath = $coverDir . $coverName;
        if (move_uploaded_file($cover['tmp_name'], $coverPath)) {
            $coverUrl = 'uploads/covers/' . $coverName;
        }
    }
}

// Đọc JSON
$data = ['songs' => []];
if (file_exists($dataFile)) {
    $raw = file_get_contents($dataFile);
    $parsed = json_decode($raw, true);
    if ($parsed) $data = $parsed;
}

// Thêm bài hát mới
$newSong = [
    'id'       => uniqid('song_', true),
    'title'    => htmlspecialchars(trim($_POST['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?: 'Không tên',
    'artist'   => htmlspecialchars(trim($_POST['artist'] ?? ''), ENT_QUOTES, 'UTF-8') ?: 'Unknown',
    'album'    => htmlspecialchars(trim($_POST['album'] ?? ''), ENT_QUOTES, 'UTF-8'),
    'audio'    => $audioUrl,   // Lưu đường dẫn tương đối
    'cover'    => $coverUrl,
    'duration' => '',
    'uploaded' => date('Y-m-d H:i:s'),
    'plays'    => 0,
];

array_unshift($data['songs'], $newSong); // Thêm vào đầu danh sách
file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['success' => true, 'song' => $newSong]);
