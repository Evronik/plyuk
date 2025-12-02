<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$amount = intval($input['amount'] ?? 0);

if ($amount <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid amount']);
    exit();
}

try {
    // Обновление баланса пользователя
    $stmt = $pdo->prepare("UPDATE user_stats SET total_points = total_points + ? WHERE user_id = ?");
    $stmt->execute([$amount, $_SESSION['user_id']]);
    
    // Запись в историю платежей
    $stmt = $pdo->prepare("INSERT INTO payment_history (user_id, amount, method, status) VALUES (?, ?, 'donationalerts', 'completed')");
    $stmt->execute([$_SESSION['user_id'], $amount]);
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    error_log("Update balance error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>