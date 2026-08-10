<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['status' => 401, 'message' => 'Unauthorized. Silakan login.']);
    exit;
}

require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance();

try {
    if (isset($_GET['id'])) {
        if ($_SESSION['user']['level'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['status' => 403, 'message' => 'Hanya admin yang dapat mengakses data user lain.']);
            exit;
        }
        $st = $db->prepare('SELECT id_user, username, nama_lengkap, level, status FROM users WHERE id_user = ?');
        $st->execute([(int)$_GET['id']]);
        $user = $st->fetch();
        echo json_encode(['status' => $user ? 200 : 404, 'data' => $user ?: null]);
    } else {
        echo json_encode(['status' => 200, 'data' => $_SESSION['user']]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 500, 'message' => $e->getMessage()]);
}