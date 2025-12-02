<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

try {
    // Обновляем user_stats таблицу
    $stmt = $pdo->prepare("
        INSERT INTO user_stats 
        (user_id, total_games, games_won, total_points, rating, best_time_easy, best_time_medium, best_time_hard) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            total_games = VALUES(total_games),
            games_won = VALUES(games_won),
            total_points = VALUES(total_points),
            rating = VALUES(rating),
            best_time_easy = VALUES(best_time_easy),
            best_time_medium = VALUES(best_time_medium),
            best_time_hard = VALUES(best_time_hard),
            updated_at = NOW()
    ");
    
    $stmt->execute([
        $user_id,
        $input['totalGames'] ?? 0,
        $input['gamesWon'] ?? 0,
        $input['totalPoints'] ?? 0,
        $input['rating'] ?? 0,
        $input['bestTimes']['easy'] ?? null,
        $input['bestTimes']['medium'] ?? null,
        $input['bestTimes']['hard'] ?? null
    ]);
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    error_log("Save stats error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>