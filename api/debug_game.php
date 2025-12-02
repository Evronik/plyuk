<?php
require_once '../config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM user_games WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $game = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($game) {
        $board = json_decode($game['board'], true);
        
        // Анализ заполненности
        $emptyCells = 0;
        $totalCells = 0;
        
        if (is_array($board)) {
            foreach ($board as $row) {
                if (is_array($row)) {
                    foreach ($row as $cell) {
                        $totalCells++;
                        if ($cell === 0 || $cell === null || $cell === '') {
                            $emptyCells++;
                        }
                    }
                }
            }
        }
        
        echo json_encode([
            'user_id' => $user_id,
            'seconds' => $game['seconds'],
            'was_solved' => (bool)$game['was_solved'],
            'game_lost' => (bool)$game['game_lost'],
            'total_cells' => $totalCells,
            'empty_cells' => $emptyCells,
            'is_filled' => ($emptyCells === 0 && $totalCells === 81),
            'board_preview' => substr($game['board'], 0, 200)
        ]);
    } else {
        echo json_encode(['error' => 'No game found']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>