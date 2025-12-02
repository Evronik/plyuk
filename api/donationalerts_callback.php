<?php
require_once '../config.php';

// Обработка callback от DonationAlerts
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['type']) && $input['type'] === 'donation') {
    $amount = floatval($input['amount']);
    $username = $input['username'];
    $message = $input['message'] ?? '';
    
    try {
        // Найти пользователя по имени
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Конвертация в чатлы (например, 1 рубль = 1 чатл)
            $chatls = intval($amount);
            
            // Зачисление на баланс
            $stmt = $pdo->prepare("UPDATE user_stats SET total_points = total_points + ? WHERE user_id = ?");
            $stmt->execute([$chatls, $user['id']]);
            
            // Запись в историю
            $stmt = $pdo->prepare("INSERT INTO payment_history (user_id, amount, method, status, external_id) VALUES (?, ?, 'donationalerts', 'completed', ?)");
            $stmt->execute([$user['id'], $chatls, $input['id'] ?? '']);
            
            // Уведомление через WebSocket
            // (здесь можно добавить отправку уведомления)
        }
        
    } catch (PDOException $e) {
        error_log("DonationAlerts callback error: " . $e->getMessage());
    }
}

http_response_code(200);
?>