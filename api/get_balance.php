<?php
require_once '../config.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT total_points, rating 
        FROM user_stats 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($stats) {
        echo json_encode([
            'success' => true,
            'total_points' => (int)$stats['total_points'],
            'rating' => (int)$stats['rating'],
            'balance' => (int)$stats['total_points'] // для совместимости
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'total_points' => 0,
            'rating' => 0,
            'balance' => 0
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Get balance error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error'
    ]);
}
?>