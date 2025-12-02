<?php
/**
 * Финальный скрипт очистки просроченных регистраций
 * Запускается через cron каждый час
 */

// Определяем абсолютный путь
$base_dir = '/home/p/partners1p/plyuk/public_html';

try {
    // Подключаем конфиг
    require_once $base_dir . '/config.php';
    
    // Удаляем просроченные регистрации
    $stmt = $pdo->prepare("DELETE FROM pending_registrations WHERE expires_at < NOW()");
    $stmt->execute();
    $deleted_count = $stmt->rowCount();
    
    // Логируем ВСЕГДА, но кратко
    $log_message = "[" . date('Y-m-d H:i:s') . "] CLEANUP: ";
    
    if ($deleted_count > 0) {
        $log_message .= "Deleted {$deleted_count} expired registrations";
    } else {
        $log_message .= "No expired registrations found";
    }
    
    file_put_contents($base_dir . '/cleanup_log.txt', $log_message . "\n", FILE_APPEND);
    
} catch (Exception $e) {
    $error_message = "[" . date('Y-m-d H:i:s') . "] CLEANUP ERROR: " . $e->getMessage() . "\n";
    file_put_contents($base_dir . '/cleanup_log.txt', $error_message, FILE_APPEND);
}
?>