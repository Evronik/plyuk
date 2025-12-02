<?php
// test_load.php
require_once '../config.php';

file_put_contents('debug_load.txt', "=== TEST LOAD ===\n", FILE_APPEND);
file_put_contents('debug_load.txt', "Time: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

if (!isset($_SESSION['user_id'])) {
    file_put_contents('debug_load.txt', "User not authorized\n", FILE_APPEND);
    echo json_encode(['error' => 'Not authorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
file_put_contents('debug_load.txt', "User ID: $user_id\n", FILE_APPEND);

try {
    $stmt = $pdo->prepare("SELECT seconds, was_solved, game_lost, board FROM user_games WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $game = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($game) {
        file_put_contents('debug_load.txt', "DB seconds: " . $game['seconds'] . "\n", FILE_APPEND);
        file_put_contents('debug_load.txt', "was_solved: " . $game['was_solved'] . "\n", FILE_APPEND);
        file_put_contents('debug_load.txt', "game_lost: " . $game['game_lost'] . "\n", FILE_APPEND);
        
        // Проверяем заполненность доски
        $board = json_decode($game['board'], true);
        $isFilled = true;
        if ($board && is_array($board)) {
            foreach ($board as $row) {
                foreach ($row as $cell) {
                    if ($cell === 0 || $cell === null || $cell === '') {
                        $isFilled = false;
                        break 2;
                    }
                }
            }
        }
        file_put_contents('debug_load.txt', "Board filled: " . ($isFilled ? 'yes' : 'no') . "\n", FILE_APPEND);
        
        echo json_encode([
            'success' => true,
            'seconds' => (int)$game['seconds'],
            'wasSolved' => (bool)$game['was_solved'],
            'gameLost' => (bool)$game['game_lost'],
            'boardFilled' => $isFilled
        ]);
    } else {
        file_put_contents('debug_load.txt', "No game found\n", FILE_APPEND);
        echo json_encode(['success' => false, 'error' => 'No game']);
    }
} catch (Exception $e) {
    file_put_contents('debug_load.txt', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

file_put_contents('debug_load.txt', "================\n\n", FILE_APPEND);
?>