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
    $stmt = $pdo->prepare("SELECT seconds FROM user_games WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $game = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($game) {
        echo json_encode([
            'success' => true,
            'seconds_from_db' => (int)$game['seconds'],
            'user_id' => $user_id
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Game not found']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>