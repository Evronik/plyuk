<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // ★★★ ВАЖНО: Получаем только РЕАЛЬНЫЕ участия в турнирах ★★★
    // Где пользователь либо набрал чатлы, либо получил приз
    $stmt = $pdo->prepare("
    SELECT 
        tr.*,
        t.name as tournament_name,
        t.entry_fee,
        t.prize_pool,
        t.difficulty,
        t.status as tournament_status,
        t.end_time as tournament_completed_at,
        u.username
    FROM tournament_results tr
    JOIN tournaments t ON tr.tournament_id = t.id
    JOIN users u ON tr.user_id = u.id
    WHERE tr.user_id = ?
    AND t.status = 'completed'
    AND (tr.score > 0 OR tr.prize > 0 OR tr.games_won > 0)
    ORDER BY t.end_time DESC, tr.position ASC
");
    
    $stmt->execute([$user_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ★★★ ДОПОЛНИТЕЛЬНАЯ ФИЛЬТРАЦИЯ на случай проблем с SQL ★★★
    $filteredResults = array_filter($results, function($result) {
        // Проверяем, что есть хоть какие-то значимые данные
        $hasPoints = isset($result['total_points']) && $result['total_points'] > 0;
        $hasScore = isset($result['score']) && $result['score'] > 0;
        $hasPrize = isset($result['prize']) && $result['prize'] > 0;
        $hasGamesWon = isset($result['games_won']) && $result['games_won'] > 0;
        
        return $hasPoints || $hasScore || $hasPrize || $hasGamesWon;
    });
    
    // Преобразуем обратно в массив с индексами
    $filteredResults = array_values($filteredResults);
    
    // ★★★ ЛОГИРОВАНИЕ для отладки ★★★
    error_log("Tournament results for user {$user_id}: " . count($filteredResults) . " valid results out of " . count($results) . " total");
    
    // ★★★ ДОБАВЛЯЕМ ДОПОЛНИТЕЛЬНЫЕ ПОЛЯ для фронтенда ★★★
    foreach ($filteredResults as &$result) {
        // Форматируем лучшее время
        $result['best_time_formatted'] = formatTimeForDisplay($result['best_time'] ?? 0);
        
        // Рассчитываем общий выигрыш (очки + приз)
        $result['total_earned'] = ($result['total_points'] ?? 0) + ($result['prize'] ?? 0);
        
        // Форматируем дату
        if (!empty($result['tournament_completed_at'])) {
            $date = new DateTime($result['tournament_completed_at']);
            $result['date_formatted'] = $date->format('d.m.Y H:i');
        } else {
            $result['date_formatted'] = 'Дата неизвестна';
        }
        
        // Определяем цвет медали
        $result['medal_color'] = getMedalColor($result['position'] ?? 999);
    }
    unset($result); // Важно: разрываем ссылку

    echo json_encode([
        'success' => true,
        'results' => $filteredResults,
        'count' => count($filteredResults),
        'stats' => [
            'total_participations' => count($filteredResults),
            'total_points' => array_sum(array_column($filteredResults, 'total_points')),
            'total_prizes' => array_sum(array_column($filteredResults, 'prize')),
            'wins_count' => count(array_filter($filteredResults, function($r) {
                return ($r['position'] ?? 999) === 1;
            })),
            'top3_count' => count(array_filter($filteredResults, function($r) {
                $pos = $r['position'] ?? 999;
                return $pos >= 1 && $pos <= 3;
            }))
        ]
    ]);

} catch (PDOException $e) {
    error_log("Database error in get_tournament_results.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Ошибка загрузки результатов турниров',
        'error' => $e->getMessage()
    ]);
}

/**
 * Форматирование времени для отображения
 */
function formatTimeForDisplay($seconds) {
    if (!$seconds || $seconds == 0) {
        return '-';
    }
    
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;
    
    if ($hours > 0) {
        return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
    } else {
        return sprintf('%d:%02d', $minutes, $seconds);
    }
}

/**
 * Получение цвета медали по позиции
 */
function getMedalColor($position) {
    switch ($position) {
        case 1: return '#FFD700'; // Золото
        case 2: return '#C0C0C0'; // Серебро
        case 3: return '#CD7F32'; // Бронза
        default: return '#667eea'; // Синий для остальных
    }
}
?>