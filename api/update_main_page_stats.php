<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
    exit();
}

// Для гостей сохраняем в сессии, для авторизованных - в БД
if (isset($_SESSION['user_id'])) {
    // Для авторизованных пользователей
    $user_id = $_SESSION['user_id'];
    
    // Обновляем статистику
    if (isset($data['stats'])) {
        save_user_stats($user_id, $data['stats']);
    }
    
    // Обновляем достижения
    if (isset($data['achievements'])) {
        save_user_achievements($user_id, $data['achievements']);
    }
    
    echo json_encode(['success' => true]);
} else {
    // Для гостей сохраняем в сессии
    if (isset($data['stats'])) {
        $_SESSION['guest_stats'] = $data['stats'];
    }
    
    if (isset($data['achievements'])) {
        $_SESSION['guest_achievements'] = $data['achievements'];
    }
    
    echo json_encode(['success' => true, 'guest' => true]);
}
?>