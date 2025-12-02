<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(null);
    exit();
}

$userId = $_SESSION['user_id'];

// Загрузите данные из базы данных
$stmt = $pdo->prepare("SELECT game_data FROM user_games WHERE user_id = ?");
$stmt->execute([$userId]);
$data = $stmt->fetch();

echo $data ? $data['game_data'] : json_encode(null);
?>