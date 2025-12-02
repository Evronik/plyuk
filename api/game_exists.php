<?php
error_reporting(0); // Отключаем вывод ошибок
header('Content-Type: application/json');
require_once __DIR__.'/../config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['exists' => false]);
    exit();
}

try {
    $exists = has_user_game($_SESSION['user_id']);
    echo json_encode(['exists' => $exists]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
echo json_encode($dataToReturn);
exit();
?>