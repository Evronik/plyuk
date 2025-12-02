<?php
require_once '../config.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($_SESSION['user_id'])) {
        // Для авторизованных пользователей сохраняем в БД
        $user_id = $_SESSION['user_id'];
        
        if (isset($input['stats'])) {
            save_user_stats($user_id, $input['stats']);
        }
        
        if (isset($input['achievements'])) {
            save_user_achievements($user_id, $input['achievements']);
        }
        
        echo json_encode(['success' => true]);
    } else {
        // Для гостей возвращаем успех (данные сохраняются в localStorage на клиенте)
        echo json_encode(['success' => true, 'message' => 'Guest data saved in localStorage']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>