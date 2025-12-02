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
    // ПРОВЕРЯЕМ СОХРАНЕННУЮ ИГРУ
    $stmt = $pdo->prepare("SELECT * FROM user_games WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $game = $stmt->fetch(PDO::FETCH_ASSOC);

    // Используем новые колонки board_data и solution_data
    if ($game && !empty($game['board_data']) && $game['board_data'] !== 'null') {
        // Есть сохраненная игра - возвращаем данные с новыми именами полей
        $board_data = json_decode($game['board_data'], true);
        $solution_data = json_decode($game['solution_data'], true);
        $fixed_cells = json_decode($game['fixed_cells'], true);
        
        // Проверяем корректность данных
        if ($board_data && $solution_data) {
            echo json_encode([
                'success' => true,
                'gameExists' => true,
                'board' => $board_data,
                'solution' => $solution_data,
                'fixedCells' => $fixed_cells,
                'difficulty' => $game['difficulty'],
                'seconds' => (int)$game['seconds'],
                'mistakes' => (int)$game['mistakes'],
                'hintsUsed' => (int)$game['hints_used'],
                'hintsLeft' => (int)$game['hints_left'],
                'wasSolved' => (bool)$game['was_solved'],
                'gameLost' => (bool)$game['game_lost'],
                'debug' => [
                    'seconds' => (int)$game['seconds'],
                    'board_cells' => count($board_data) > 0 ? 'valid' : 'empty',
                    'updated_at' => $game['updated_at'],
                    'data_source' => 'board_data/solution_data'
                ]
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'gameExists' => false,
                'debug' => 'Invalid game data in database'
            ]);
        }
    } else {
        // Нет сохраненной игры или данные некорректны
        echo json_encode([
            'success' => true,
            'gameExists' => false,
            'debug' => $game ? 'Empty or invalid game data' : 'No game record found'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Get game error: " . $e->getMessage());
    echo json_encode([
        'success' => true,
        'gameExists' => false,
        'debug' => 'Database error: ' . $e->getMessage()
    ]);
}
?>