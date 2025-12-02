<?php
require_once '../config.php';
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['exists' => false]);
    exit();
}

$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_games WHERE user_id = ?");
    $stmt->execute([$userId]);
    $count = $stmt->fetchColumn();
    
    echo json_encode(['exists' => $count > 0]);
} catch (PDOException $e) {
    echo json_encode([
        'exists' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>