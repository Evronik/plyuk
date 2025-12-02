<?php
error_reporting(0); // Отключаем вывод ошибок
header('Content-Type: application/json');
require_once __DIR__.'/../config.php';

session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$userId = $_SESSION['user_id'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Загрузка игры
        $gameData = get_user_game($userId);
        echo json_encode($gameData ?: ['error' => 'No game data']);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Сохранение игры
        $data = json_decode(file_get_contents('php://input'), true);
        save_user_game($userId, $data);
        echo json_encode(['success' => true]);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
echo json_encode($dataToReturn);
exit();
?>