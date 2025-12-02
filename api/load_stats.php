<?php
require_once '../config.php';
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(null);
    exit();
}

$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT stats_data FROM user_stats WHERE user_id = ?");
    $stmt->execute([$userId]);
    $data = $stmt->fetch();
    
    echo $data ? $data['stats_data'] : json_encode(null);
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>