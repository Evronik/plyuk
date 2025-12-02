<?php
require_once 'config.php';

header('Content-Type: text/plain');

try {
    $stmt = $pdo->query("SELECT id, user_id, game_start_time, seconds, was_solved FROM user_games");
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Всего записей: " . count($games) . "\n\n";
    
    foreach ($games as $game) {
        echo "ID: " . $game['id'] . "\n";
        echo "User ID: " . $game['user_id'] . "\n";
        echo "Game Start Time: " . $game['game_start_time'] . "\n";
        echo "Seconds: " . $game['seconds'] . "\n";
        echo "Was Solved: " . $game['was_solved'] . "\n";
        
        if ($game['game_start_time']) {
            $readable_time = date('Y-m-d H:i:s', $game['game_start_time'] / 1000);
            echo "Readable Time: " . $readable_time . "\n";
        }
        echo "------------------------\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>