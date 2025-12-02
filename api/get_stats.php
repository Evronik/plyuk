<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Получаем статистику из user_stats
    $stmt = $pdo->prepare("
        SELECT us.*, u.username 
        FROM user_stats us 
        JOIN users u ON us.user_id = u.id 
        WHERE us.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $userStats = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userStats) {
        echo json_encode([
            'success' => true,
            'stats' => [
                'totalGames' => $userStats['total_games'] ?? 0,
                'gamesWon' => $userStats['games_won'] ?? 0,
                'totalPoints' => $userStats['total_points'] ?? 0,
                'rating' => $userStats['rating'] ?? 0,
                'bestTimes' => [
                    'easy' => $userStats['best_time_easy'] ?? null,
                    'medium' => $userStats['best_time_medium'] ?? null,
                    'hard' => $userStats['best_time_hard'] ?? null
                ]
            ]
        ]);
    } else {
        // Создаем запись если нет статистики
        $stmt = $pdo->prepare("INSERT INTO user_stats (user_id) VALUES (?)");
        $stmt->execute([$user_id]);
        
        echo json_encode([
            'success' => true,
            'stats' => [
                'totalGames' => 0,
                'gamesWon' => 0,
                'totalPoints' => 0,
                'rating' => 0,
                'bestTimes' => [
                    'easy' => null, 
                    'medium' => null, 
                    'hard' => null
                ]
            ]
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>