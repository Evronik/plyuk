<?php
/**
 * Скрипт для очистки просроченных регистраций
 * Запускается ежедневно через cron на sweb.ru
 */

// Логируем запуск
error_log("Sweb.ru cleanup script started at " . date('Y-m-d H:i:s'));

// Подключаем конфиг с АБСОЛЮТНЫМ путем
require_once '/home/p/partners1p/plyuk/public_html/config.php';

try {
    // Удаляем просроченные регистрации (старше 24 часов)
    $stmt = $pdo->prepare("DELETE FROM pending_registrations WHERE expires_at < NOW()");
    $stmt->execute();
    $deleted_count = $stmt->rowCount();
    
    // Логируем результат
    $message = "Очищено {$deleted_count} просроченных регистраций";
    error_log($message);
    
    // Пишем в файл для отслеживания
    file_put_contents('/home/p/partners1p/plyuk/public_html/cleanup_log.txt', 
        date('Y-m-d H:i:s') . " - {$message}\n", 
        FILE_APPEND
    );
    
    echo $message . "\n";
    
} catch (PDOException $e) {
    $error_message = "Ошибка очистки: " . $e->getMessage();
    error_log($error_message);
    file_put_contents('/home/p/partners1p/plyuk/public_html/cleanup_log.txt', 
        date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . "\n", 
        FILE_APPEND
    );
    echo $error_message . "\n";
    exit(1);
}

echo "Очистка завершена успешно\n";
?>