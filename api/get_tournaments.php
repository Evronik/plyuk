<?php
require_once '../config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Получаем ВСЕ турниры, а не только registration/active
    $stmt = $pdo->query("
        SELECT t.*, 
               COUNT(tr.id) as current_players
        FROM tournaments t
        LEFT JOIN tournament_registrations tr ON t.id = tr.tournament_id AND tr.status = 'registered'
        GROUP BY t.id
        ORDER BY 
            CASE t.status 
                WHEN 'active' THEN 1
                WHEN 'registration' THEN 2
                WHEN 'completed' THEN 3
                ELSE 4
            END,
            t.start_time ASC
    ");
    
    $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $formattedTournaments = [];
    foreach ($tournaments as $tournament) {
        $formattedTournaments[] = [
            'id' => (int)$tournament['id'],
            'name' => $tournament['name'],
            'description' => $tournament['description'],
            'entry_fee' => (float)$tournament['entry_fee'],
            'prize_pool' => (float)$tournament['prize_pool'],
            'max_players' => (int)$tournament['max_players'],
            'current_players' => (int)$tournament['current_players'],
            'difficulty' => $tournament['difficulty'],
            'status' => $tournament['status'],
            'start_time' => $tournament['start_time']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'tournaments' => $formattedTournaments,
        'total' => count($formattedTournaments)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    error_log("Database error in get_tournaments: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка загрузки турниров',
        'tournaments' => []
    ], JSON_UNESCAPED_UNICODE);
}
?>