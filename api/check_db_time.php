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
    // Получаем все данные о времени
    $stmt = $pdo->prepare("SELECT 
        seconds, 
        was_solved, 
        game_lost,
        board,
        LENGTH(board) as board_length,
        board LIKE '%0,%' as has_zeros
    FROM user_games WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $game = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($game) {
        echo json_encode([
            'success' => true,
            'user_id' => $user_id,
            'seconds_in_db' => (int)$game['seconds'],
            'was_solved' => (bool)$game['was_solved'],
            'game_lost' => (bool)$game['game_lost'],
            'board_length' => $game['board_length'],
            'has_zeros_in_board' => (bool)$game['has_zeros'],
            'board_preview' => substr($game['board'], 0, 100)
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Game not found']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>