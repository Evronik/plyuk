<?php
require_once 'config.php';

header('Content-Type: application/json');

// Отладка
error_log("=== GET_LEADERBOARD CALLED ===");

try {
    $stmt = $pdo->query("
        SELECT 
            u.username, 
            COALESCE(us.total_points, 0) as rating,
            COALESCE(us.games_won, 0) as games_won,
            COALESCE(us.total_games, 0) as total_games,
            COALESCE(us.total_points, 0) as total_points,
            COALESCE(
                CASE 
                    WHEN us.total_games > 0 THEN ROUND((us.games_won / us.total_games) * 100)
                    ELSE 0 
                END, 0
            ) as win_rate,
            COALESCE(us.best_time_easy, 0) as best_time_easy,
            COALESCE(us.best_time_medium, 0) as best_time_medium,
            COALESCE(us.best_time_hard, 0) as best_time_hard,
            -- ★★★ ДОБАВЛЕНО: Вычисляем лучшее общее время ★★★
            COALESCE(
                LEAST(
                    COALESCE(us.best_time_easy, 999999),
                    COALESCE(us.best_time_medium, 999999),
                    COALESCE(us.best_time_hard, 999999)
                ), 999999
            ) as best_overall_time
        FROM users u
        LEFT JOIN user_stats us ON u.id = us.user_id
        WHERE u.username IS NOT NULL 
          AND u.username != ''
        ORDER BY 
            us.total_points DESC,
            -- ★★★ ДОБАВЛЕНО: Сортировка по лучшему времени при одинаковом рейтинге ★★★
            best_overall_time ASC,
            us.games_won DESC,
            win_rate DESC,
            us.total_games DESC,
            u.username ASC
        LIMIT 100
    ");
    
    $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Leaderboard data found: " . count($leaderboard) . " players");
    
    // Разбиваем на группы для фронтенда
    $groups = [
        'gold' => array_slice($leaderboard, 0, 10),
        'silver' => array_slice($leaderboard, 10, 10),
        'bronze' => array_slice($leaderboard, 20, 10),
        'other' => array_slice($leaderboard, 30)
    ];
    
    echo json_encode([
        'success' => true,
        'leaderboard' => $leaderboard,
        'groups' => $groups,
        'count' => count($leaderboard)
    ]);
    
} catch (PDOException $e) {
    error_log("Leaderboard error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage(),
        'leaderboard' => [],
        'groups' => []
    ]);
}
?>