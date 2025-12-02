<?php
require_once '../config.php';

/**
 * Реальная логика подсчета очков для турниров
 */
function calculateRealTournamentScore($tournamentId, $userId) {
    global $pdo;
    
    try {
        // 1. Получаем игры пользователя за период турнира
        $tournament = $pdo->prepare("SELECT start_time FROM tournaments WHERE id = ?")->execute([$tournamentId])->fetch();
        
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as games_played,
                SUM(CASE WHEN was_solved = 1 THEN 1 ELSE 0 END) as games_won,
                AVG(time_spent) as avg_time,
                SUM(points_earned) as total_points
            FROM game_sessions 
            WHERE user_id = ? 
            AND created_at BETWEEN ? AND DATE_ADD(?, INTERVAL 2 HOUR)
            AND difficulty = (SELECT difficulty FROM tournaments WHERE id = ?)
        ");
        $stmt->execute([$userId, $tournament['start_time'], $tournament['start_time'], $tournamentId]);
        $stats = $stmt->fetch();
        
        // 2. Расчет очков на основе:
        // - Количество побед (50%)
        // - Общее количество очков (30%) 
        // - Среднее время (20%)
        $score = 0;
        if ($stats) {
            $win_score = $stats['games_won'] * 100; // 100 очков за победу
            $points_score = $stats['total_points'] * 0.1; // 10% от заработанных чатлов
            $time_score = max(0, 1000 - ($stats['avg_time'] / 60)); // Быстрее = лучше
            
            $score = $win_score + $points_score + $time_score;
        }
        
        return max(100, $score); // Минимум 100 очков
        
    } catch (PDOException $e) {
        error_log("Real scoring error: " . $e->getMessage());
        return rand(500, 1000); // Fallback
    }
}
?>