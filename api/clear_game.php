<?php
require_once 'config.php'; // Исправлен путь
session_start();

// Очищаем буфер вывода
if (ob_get_length()) ob_clean();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Не авторизован']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Проверяем существование таблицы
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'user_games'")->fetch();
    if (!$tableCheck) {
        error_log("Clear game: user_games table does not exist");
        echo json_encode(['success' => true, 'message' => 'Игра очищена']); // Возвращаем success даже если таблицы нет
        exit();
    }
    
    $stmt = $pdo->prepare("DELETE FROM user_games WHERE user_id = ?");
    $result = $stmt->execute([$user_id]);
    
    if ($result) {
        error_log("Game cleared for user $user_id");
        echo json_encode(['success' => true, 'message' => 'Игра очищена']);
    } else {
        error_log("Failed to clear game for user $user_id");
        echo json_encode(['success' => false, 'message' => 'Ошибка при очистке игры']);
    }
    
} catch (PDOException $e) {
    error_log("Clear game error for user $user_id: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка очистки игры: ' . $e->getMessage()]);
}
?>