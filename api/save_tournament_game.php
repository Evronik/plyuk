<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['tournament_id'])) {
    echo json_encode(['success' => false, 'message' => 'Некорректные данные']);
    exit;
}

$userId = $_SESSION['user_id'];
$tournamentId = (int)$data['tournament_id'];
$gameId = $data['game_id'] ?? uniqid('game_', true);
$chatlsEarned = (int)($data['chatls_earned'] ?? 0);
$timeSeconds = (int)($data['time_seconds'] ?? 0);
$mistakes = (int)($data['mistakes'] ?? 0);
$hintsUsed = (int)($data['hints_used'] ?? 0);
$wonGame = isset($data['won_game']) ? (int)$data['won_game'] : 0;

try {
    // Проверяем, зарегистрирован ли пользователь в турнире
    $stmt = $pdo->prepare("SELECT id FROM tournament_registrations WHERE tournament_id = ? AND user_id = ? AND status = 'registered'");
    $stmt->execute([$tournamentId, $userId]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Вы не зарегистрированы в этом турнире']);
        exit;
    }
    
    // Сохраняем статистику игры
    $stmt = $pdo->prepare("
        INSERT INTO tournament_game_stats 
        (tournament_id, user_id, game_id, chatls_earned, time_seconds, mistakes, hints_used, won_game, played_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
        chatls_earned = VALUES(chatls_earned),
        time_seconds = VALUES(time_seconds),
        mistakes = VALUES(mistakes),
        hints_used = VALUES(hints_used),
        won_game = VALUES(won_game)
    ");
    
    $stmt->execute([$tournamentId, $userId, $gameId, $chatlsEarned, $timeSeconds, $mistakes, $hintsUsed, $wonGame]);
    
    echo json_encode(['success' => true, 'message' => 'Статистика игры сохранена']);
    
} catch (PDOException $e) {
    error_log("Tournament game save error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка сохранения']);
}
?>