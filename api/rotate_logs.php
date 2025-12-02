<?php
define('ROOT_PATH', dirname(dirname(__FILE__)));

$logs_to_rotate = [
    ROOT_PATH . '/tournament_cron.log',
    ROOT_PATH . '/php-errors.log', 
    ROOT_PATH . '/websocket.log'
];

$max_size = 10 * 1024 * 1024; // 10 MB
$backup_count = 5; // Хранить 5 backup файлов

function rotate_log($log_file, $max_size, $backup_count) {
    if (!file_exists($log_file)) {
        return;
    }
    
    // Проверяем размер файла
    if (filesize($log_file) < $max_size) {
        return;
    }
    
    // Ротируем логи
    for ($i = $backup_count - 1; $i >= 0; $i--) {
        $old_file = $log_file . '.' . $i;
        $new_file = $log_file . '.' . ($i + 1);
        
        if (file_exists($old_file)) {
            if ($i === $backup_count - 1) {
                // Удаляем самый старый backup
                unlink($old_file);
            } else {
                rename($old_file, $new_file);
            }
        }
    }
    
    // Переименовываем текущий лог
    rename($log_file, $log_file . '.0');
    
    // Создаем новый пустой лог файл
    touch($log_file);
    chmod($log_file, 0666);
    
    error_log("✅ Log rotated: " . $log_file . " - " . date('Y-m-d H:i:s'));
}

// Выполняем ротацию для всех логов
foreach ($logs_to_rotate as $log_file) {
    rotate_log($log_file, $max_size, $backup_count);
}

echo "All logs rotation completed\n";
?>