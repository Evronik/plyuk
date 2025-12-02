<?php
require_once 'config.php';
session_start();

// Очищаем буфер вывода перед отправкой JSON
if (ob_get_length()) ob_clean();

header('Content-Type: application/json; charset=utf-8');

error_log("=== UPDATE_STATS CALLED ===");

if (!isset($_SESSION['user_id'])) {
    error_log("Update stats: Not authorized");
    echo json_encode(['success' => false, 'message' => 'Не авторизован']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    error_log("Update stats: No data received");
    echo json_encode(['success' => false, 'message' => 'Нет данных']);
    exit();
}

$userId = $_SESSION['user_id'];
error_log("Update stats for user: " . $userId);

try {
    // Проверяем существование таблицы
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'user_stats'")->fetch();
    if (!$tableCheck) {
        error_log("Update stats: user_stats table does not exist");
        echo json_encode(['success' => false, 'message' => 'Таблица статистики не найдена']);
        exit();
    }
    
    // Обновляем статистику в базе данных - ДОБАВЛЕНО поле rating
    $stmt = $pdo->prepare("
        INSERT INTO user_stats 
        (user_id, total_games, games_won, total_points, rating, best_time_easy, best_time_medium, best_time_hard)
        VALUES (:user_id, :total_games, :games_won, :total_points, :rating, :best_time_easy, :best_time_medium, :best_time_hard)
        ON DUPLICATE KEY UPDATE 
            total_games = VALUES(total_games),
            games_won = VALUES(games_won),
            total_points = VALUES(total_points),
            rating = VALUES(rating),  -- ← ВАЖНОЕ ДОБАВЛЕНИЕ
            best_time_easy = VALUES(best_time_easy),
            best_time_medium = VALUES(best_time_medium),
            best_time_hard = VALUES(best_time_hard),
            updated_at = CURRENT_TIMESTAMP
    ");
    
    // Используем total_points для rating (как в save_stats.php)
    $totalPoints = $data['totalPoints'] ?? 0;
    
    $result = $stmt->execute([
        ':user_id' => $userId,
        ':total_games' => $data['totalGames'] ?? 0,
        ':games_won' => $data['gamesWon'] ?? 0,
        ':total_points' => $totalPoints,
        ':rating' => $totalPoints,  // ← ВАЖНОЕ ДОБАВЛЕНИЕ
        ':best_time_easy' => $data['bestTimes']['easy'] ?? null,
        ':best_time_medium' => $data['bestTimes']['medium'] ?? null,
        ':best_time_hard' => $data['bestTimes']['hard'] ?? null
    ]);
    
    if ($result) {
        error_log("✅ Update stats: SUCCESS for user: $userId, totalPoints: $totalPoints");
        echo json_encode(['success' => true]);
    } else {
        $errorInfo = $stmt->errorInfo();
        error_log("❌ Update stats SQL error: " . json_encode($errorInfo));
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $errorInfo[2]]);
    }
    
} catch (PDOException $e) {
    error_log("❌ Update stats exception: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}
?>