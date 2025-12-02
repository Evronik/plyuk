<?php
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

$tournament_id = $_GET['tournament_id'] ?? 0;

if (!$tournament_id) {
    echo json_encode(['success' => false, 'message' => 'ID турнира не указан']);
    exit;
}

try {
    // Включаем функцию calculateTournamentWinners из calculate_tournament_results.php
    function calculateTournamentWinners($tournamentId) {
        global $pdo;
        
        try {
            $pdo->beginTransaction();

            // 1. ОБНОВЛЯЕМ СТАТУСЫ РЕГИСТРАЦИЙ НА 'completed'
            $stmt = $pdo->prepare("
                UPDATE tournament_registrations 
                SET status = 'completed' 
                WHERE tournament_id = ? 
                AND status IN ('registered', 'playing')
            ");
            $stmt->execute([$tournamentId]);
            $updatedRegistrations = $stmt->rowCount();

            // 2. Получаем турнир
            $stmt = $pdo->prepare("SELECT * FROM tournaments WHERE id = ?");
            $stmt->execute([$tournamentId]);
            $tournament = $stmt->fetch();

            if (!$tournament) {
                $pdo->rollBack();
                return false;
            }

            // 3. Получаем участников
            $stmt = $pdo->prepare("
                SELECT tr.user_id, u.username
                FROM tournament_registrations tr
                INNER JOIN users u ON tr.user_id = u.id
                WHERE tr.tournament_id = ? AND tr.status = 'completed'
            ");
            $stmt->execute([$tournamentId]);
            $participants = $stmt->fetchAll();

            if (empty($participants)) {
                $pdo->rollBack();
                return false;
            }

            // 4. Собираем статистику
            $playerStats = [];
            foreach ($participants as $participant) {
                $playerStats[] = [
                    'user_id' => $participant['user_id'],
                    'username' => $participant['username'],
                    'score' => rand(500, 1500)
                ];
            }

            // 5. Сортируем по очкам
            usort($playerStats, function($a, $b) {
                return $b['score'] - $a['score'];
            });

            // 6. Распределение призов
            $prizeDistribution = [1 => 0.5, 2 => 0.3, 3 => 0.2];

            // 7. Записываем результаты
            foreach ($playerStats as $position => $player) {
                $actualPosition = $position + 1;
                $prize = isset($prizeDistribution[$actualPosition]) ? 
                         round($tournament['prize_pool'] * $prizeDistribution[$actualPosition]) : 0;
                
                if ($prize > 0) {
                    $stmt = $pdo->prepare("UPDATE user_stats SET total_points = total_points + ? WHERE user_id = ?");
                    $stmt->execute([$prize, $player['user_id']]);
                }

                $stmt = $pdo->prepare("
                    INSERT INTO tournament_results (tournament_id, user_id, position, score, prize) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $tournamentId, 
                    $player['user_id'], 
                    $actualPosition, 
                    $player['score'], 
                    $prize
                ]);
            }

            $pdo->commit();
            return true;

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error calculating tournament {$tournamentId}: " . $e->getMessage());
            return false;
        }
    }

    // Вызываем расчет
    $success = calculateTournamentWinners($tournament_id);
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Результаты турнира рассчитаны'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка расчета результатов'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Ошибка: ' . $e->getMessage()
    ]);
}
?>