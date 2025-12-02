<?php
// Детальный скрипт очистки с логированием каждого шага
$debug_file = '/home/p/partners1p/plyuk/public_html/cleanup_detailed.log';

function log_message($message) {
    global $debug_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($debug_file, "[$timestamp] $message\n", FILE_APPEND);
}

log_message("=== CLEANUP SCRIPT STARTED ===");

try {
    // Шаг 1: Подключение config.php
    log_message("Step 1: Loading config.php");
    $config_path = '/home/p/partners1p/plyuk/public_html/config.php';
    
    if (!file_exists($config_path)) {
        throw new Exception("Config file not found: $config_path");
    }
    
    require_once $config_path;
    log_message("Config loaded successfully");
    
    // Шаг 2: Проверка подключения к БД
    log_message("Step 2: Testing DB connection");
    $test = $pdo->query("SELECT 1");
    log_message("DB connection OK");
    
    // Шаг 3: Проверка существования таблицы
    log_message("Step 3: Checking pending_registrations table");
    $stmt = $pdo->query("SHOW TABLES LIKE 'pending_registrations'");
    $table_exists = $stmt->fetch();
    
    if (!$table_exists) {
        throw new Exception("Table pending_registrations does not exist");
    }
    log_message("Table pending_registrations exists");
    
    // Шаг 4: Проверка текущих записей
    log_message("Step 4: Checking current records");
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM pending_registrations");
    $total_count = $stmt->fetch()['count'];
    log_message("Total records in table: " . $total_count);
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM pending_registrations WHERE expires_at < NOW()");
    $expired_count = $stmt->fetch()['count'];
    log_message("Expired records: " . $expired_count);
    
    // Шаг 5: Удаление просроченных записей
    log_message("Step 5: Deleting expired records");
    $stmt = $pdo->prepare("DELETE FROM pending_registrations WHERE expires_at < NOW()");
    $stmt->execute();
    $deleted_count = $stmt->rowCount();
    
    log_message("Successfully deleted records: " . $deleted_count);
    
    // Шаг 6: Финальная проверка
    log_message("Step 6: Final check");
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM pending_registrations");
    $final_count = $stmt->fetch()['count'];
    log_message("Records remaining: " . $final_count);
    
    log_message("=== CLEANUP COMPLETED SUCCESSFULLY ===");
    
} catch (Exception $e) {
    log_message("ERROR: " . $e->getMessage());
    log_message("ERROR DETAILS: " . $e->getFile() . ":" . $e->getLine());
}
?>