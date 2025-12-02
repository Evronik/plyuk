<?php
require_once '../config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$rating = intval($data['rating']);

try {
    // Проверяем существование записи статистики
    $checkStmt = $pdo->prepare("SELECT id FROM user_stats WHERE user_id = ?");
    $checkStmt->execute([$_SESSION['user_id']]);
    
    if ($checkStmt->fetch()) {
        // Обновляем существующую запись
        $stmt = $pdo->prepare("UPDATE user_stats SET rating = ? WHERE user_id = ?");
        $success = $stmt->execute([$rating, $_SESSION['user_id']]);
    } else {
        // Создаем новую запись
        $stmt = $pdo->prepare("INSERT INTO user_stats (user_id, rating) VALUES (?, ?)");
        $success = $stmt->execute([$_SESSION['user_id'], $rating]);
    }
    
    echo json_encode(['success' => $success]);
} catch (PDOException $e) {
    error_log("Update rating error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>