<?php
// api/calculate_tournament_final_results.php
require_once 'config.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

// Для автоматического запуска из крона
$isCron = (php_sapi_name() === 'cli');

if (!$isCron) {
    // Проверяем авторизацию для веб-доступа
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
        exit;
    }
    
    // Только админы могут запускать расчет
    if ($_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
        exit;
    }
    
    $tournamentId = $_POST['tournament_id'] ?? $_GET['tournament_id'] ?? 0;
} else {
    // Для крона: получаем ID турниров, которые нужно рассчитать
    $tournamentId = $argv[1] ?? 0;
}

try {
    global $pdo;
    
    // Если не указан конкретный турнир, ищем все завершенные без результатов
    if (!$tournamentId) {
        $stmt = $pdo->prepare("
            SELECT t.id 
            FROM tournaments t
            WHERE t.status = 'active' 
            AND t.end_time <= NOW()
            AND NOT EXISTS (
                SELECT 1 FROM tournament_results tr 
                WHERE tr.tournament_id = t.id
            )
        ");
        $stmt->execute();
        $tournaments = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $results = [];
        foreach ($tournaments as $tid) {
            $results[] = calculateSingleTournament($tid);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Рассчитано турниров: ' . count($tournaments),
            'results' => $results
        ]);
        exit;
    }
    
    // Расчет одного турнира
    $result = calculateSingleTournament($tournamentId);
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка расчета: ' . $e->getMessage()
    ]);
}

/**
 * Основная функция расчета одного турнира
 */
function calculateSingleTournament($tournamentId) {
    global $pdo;
    
    $pdo->beginTransaction();
    
    try {
        // 1. Получаем информацию о турнире
        $stmt = $pdo->prepare("
            SELECT * FROM tournaments 
            WHERE id = ? 
            AND status IN ('active', 'completed')
        ");
        $stmt->execute([$tournamentId]);
        $tournament = $stmt->fetch();
        
        if (!$tournament) {
            throw new Exception("Турнир не найден");
        }
        
        $prizePool = (float)$tournament['prize_pool'];
        
        // 2. Получаем только участников, которые СЫГРАЛИ хотя бы 1 игру
        $stmt = $pdo->prepare("
            SELECT DISTINCT
                tgs.user_id,
                u.username
            FROM tournament_game_stats tgs
            INNER JOIN users u ON tgs.user_id = u.id
            WHERE tgs.tournament_id = ?
            AND tgs.chatls_earned > 0
        ");
        $stmt->execute([$tournamentId]);
        $playersWithGames = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($playersWithGames)) {
            throw new Exception("Нет игроков с сыгранными играми в турнире");
        }
        
        // 3. Для каждого игрока получаем полную турнирную статистику
        $players = [];
        foreach ($playersWithGames as $player) {
            $userId = $player['user_id'];
            
            $stmt = $pdo->prepare("
                SELECT 
                    -- Общие чатлы в турнире (ПРИОРИТЕТ 1)
                    SUM(chatls_earned) as total_chatls,
                    
                    -- Лучшее время в турнире (ПРИОРИТЕТ 2)
                    MIN(time_seconds) as best_time,
                    
                    -- Общее время в турнире
                    SUM(time_seconds) as total_time,
                    
                    -- Количество игр в турнире
                    COUNT(*) as games_count,
                    
                    -- Количество побед в турнире (ПРИОРИТЕТ 3)
                    SUM(CASE WHEN won_game = 1 THEN 1 ELSE 0 END) as wins_count,
                    
                    -- Процент побед в турнире (ПРИОРИТЕТ 4)
                    CASE 
                        WHEN COUNT(*) > 0 THEN 
                            (SUM(CASE WHEN won_game = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100
                        ELSE 0 
                    END as win_percentage,
                    
                    -- Дополнительная статистика для разрешения ничьих
                    SUM(mistakes) as total_mistakes,
                    SUM(hints_used) as total_hints
                    
                FROM tournament_game_stats 
                WHERE tournament_id = ? AND user_id = ?
                GROUP BY user_id
            ");
            $stmt->execute([$tournamentId, $userId]);
            $stats = $stmt->fetch();
            
            if ($stats) {
                $players[] = [
                    'user_id' => $userId,
                    'username' => $player['username'],
                    'total_chatls' => (float)$stats['total_chatls'],
                    'best_time' => (int)$stats['best_time'],
                    'total_time' => (int)$stats['total_time'],
                    'games_count' => (int)$stats['games_count'],
                    'wins_count' => (int)$stats['wins_count'],
                    'win_percentage' => (float)$stats['win_percentage'],
                    'total_mistakes' => (int)$stats['total_mistakes'],
                    'total_hints' => (int)$stats['total_hints']
                ];
            }
        }
        
        // 4. СОРТИРОВКА по правилам:
        usort($players, function($a, $b) {
    // 1. Больше чатлов = выше
    if ($a['total_chatls'] != $b['total_chatls']) {
        return $b['total_chatls'] <=> $a['total_chatls'];
    }
    
    // 2. Меньше лучшего времени = выше
    if ($a['best_time'] != $b['best_time']) {
        return $a['best_time'] <=> $b['best_time'];
    }
    
    // 3. Больше побед = выше
    if ($a['wins_count'] != $b['wins_count']) {
        return $b['wins_count'] <=> $a['wins_count'];
    }
    
    // 4. Больше процент побед = выше
    return $b['win_percentage'] <=> $a['win_percentage'];
});
        
        // 5. Распределяем места и призы
        $results = [];
        $prizeDistribution = [1 => 0.50, 2 => 0.30, 3 => 0.20];
        
        foreach ($players as $index => $player) {
            $position = $index + 1;
            $prize = 0;
            
            // Призы только первым трем местам при наличии ≥3 игроков
            if ($position <= 3 && count($players) >= 3 && $prizePool > 0) {
                $prize = round($prizePool * $prizeDistribution[$position], 2);
            }
            
            // Сохраняем результат
            $resultData = [
               'tournament_id' => $tournamentId,
               'user_id' => $player['user_id'],
               'position' => $position,
               'score' => $player['total_chatls'],
               'prize' => $prize,
               'entry_fee' => $tournament['entry_fee'] ?? 0,
               'total_points' => $player['total_chatls'] + $prize,
               'games_played' => $player['games_count'],
               'games_won' => $player['wins_count'],
               'win_rate' => $player['win_percentage'],
               'best_time' => $player['best_time'],
               'avg_time' => $player['avg_time'] ?? 0,
               'total_time' => $player['total_time'],
               'total_mistakes' => $player['total_mistakes'],
               'total_hints' => $player['total_hints'],
               'completed_at' => date('Y-m-d H:i:s')
            ];
            
            // Сохраняем в tournament_results
            $stmt = $pdo->prepare("
    INSERT INTO tournament_results 
    (tournament_id, user_id, position, score, prize, entry_fee, total_points, 
     games_played, games_won, win_rate, best_time, avg_time, total_time, 
     total_mistakes, total_hints, completed_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
    position = VALUES(position),
    score = VALUES(score),
    prize = VALUES(prize),
    total_points = VALUES(total_points),
    games_played = VALUES(games_played),
    games_won = VALUES(games_won),
    win_rate = VALUES(win_rate),
    best_time = VALUES(best_time),
    avg_time = VALUES(avg_time),
    total_time = VALUES(total_time),
    total_mistakes = VALUES(total_mistakes),
    total_hints = VALUES(total_hints),
    completed_at = VALUES(completed_at)
");

$avgTime = $player['games_count'] > 0 ? round($player['total_time'] / $player['games_count']) : 0;

$stmt->execute([
    $resultData['tournament_id'],
    $resultData['user_id'],
    $resultData['position'],
    $resultData['score'],
    $resultData['prize'],
    $resultData['entry_fee'],
    $resultData['total_points'],
    $player['games_count'], // games_played
    $player['wins_count'],  // games_won
    $player['win_percentage'],
    $player['best_time'],
    $avgTime,
    $player['total_time'],
    $player['total_mistakes'],
    $player['total_hints'],
    $resultData['completed_at']
]);
            
            // Начисляем призовые чатлы
            if ($prize > 0) {
                // Обновляем общий баланс
                $stmt = $pdo->prepare("
                    UPDATE user_stats 
                    SET total_points = total_points + ?
                    WHERE user_id = ?
                ");
                $stmt->execute([$prize, $player['user_id']]);
                
                // Запись в историю
                $stmt = $pdo->prepare("
                    INSERT INTO payment_history 
                    (user_id, amount, method, status, external_id, description) 
                    VALUES (?, ?, 'tournament_prize', 'completed', ?, ?)
                ");
                $stmt->execute([
                    $player['user_id'],
                    $prize,
                    'tournament_' . $tournamentId,
                    "Приз за {$position}-е место в турнире '{$tournament['name']}'"
                ]);
            }
            
            $results[] = $resultData;
        }
        
        // 6. Обновляем статус турнира
        $stmt = $pdo->prepare("
            UPDATE tournaments 
            SET status = 'completed', 
                end_time = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$tournamentId]);
        
        $pdo->commit();
        
        return [
            'success' => true,
            'tournament_id' => $tournamentId,
            'tournament_name' => $tournament['name'],
            'total_players' => count($players),
            'active_players' => count($players),
            'prize_pool' => $prizePool,
            'results' => $results,
            'message' => 'Турнир успешно рассчитан'
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
?>