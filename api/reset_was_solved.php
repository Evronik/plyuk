<?php
require_once 'config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Проверяем существование таблицы
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'user_games'")->fetch();
    if (!$tableCheck) {
        echo json_encode(['success' => true, 'message' => 'Table not found']);
        exit();
    }
    
    // Сбрасываем флаг was_solved
    $stmt = $pdo->prepare("UPDATE user_games SET was_solved = 0 WHERE user_id = ?");
    $result = $stmt->execute([$user_id]);
    
    if ($result) {
        error_log("was_solved reset for user: $user_id");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to reset']);
    }
    
} catch (PDOException $e) {
    error_log("Reset was_solved error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>