<?php
require_once 'config.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Не авторизован']);
    exit();
}

$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';

try {
    $sql = "
        SELECT 
            u.username, 
            COALESCE(us.games_won, 0) as games_won,
            COALESCE(us.total_games, 0) as total_games,
            COALESCE(
                CASE 
                    WHEN us.total_games > 0 THEN ROUND((us.games_won / us.total_games) * 100)
                    ELSE 0 
                END, 0
            ) as win_rate,
            us.best_time_easy,
            us.best_time_medium,
            us.best_time_hard
        FROM users u
        LEFT JOIN user_stats us ON u.id = us.user_id
        WHERE (us.games_won > 0 OR us.total_games > 0)
    ";
    
    if (!empty($searchTerm)) {
        $sql .= " AND u.username LIKE :searchTerm";
        $params = ['searchTerm' => '%' . $searchTerm . '%'];
    } else {
        $params = [];
    }
    
    $sql .= " ORDER BY us.games_won DESC, win_rate DESC LIMIT 10";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'leaderboard' => $leaderboard,
        'searchTerm' => $searchTerm
    ]);
    
} catch (PDOException $e) {
    error_log("Search leaderboard error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка поиска'
    ]);
}
?>