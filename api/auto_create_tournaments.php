<?php
// api/auto_create_tournaments.php - Автоматическое создание турниров

// Включаем вывод ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Определяем корневую директорию
define('ROOT_PATH', dirname(dirname(__FILE__)));

// Подключаем config.php
require_once ROOT_PATH . '/config.php';

// Функция логирования
function log_message($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] {$message}\n";
    file_put_contents(ROOT_PATH . '/tournament_creation.log', $logEntry, FILE_APPEND);
    
    if (isset($_SERVER['HTTP_HOST'])) {
        echo $logEntry;
    }
    
    return $logEntry;
}

try {
    log_message("🎯 AUTO: Starting automatic tournament creation");

    // 1. Создаем турниры на следующие сутки
    $createdCount = createDailyTournaments();
    log_message("✅ Created {$createdCount} tournaments for tomorrow");

    // 2. Удаляем старые завершенные турниры (старше 2 дней)
    $deletedCount = cleanupOldTournaments();
    log_message("🗑️  Cleaned up {$deletedCount} old tournaments");

    log_message("🎯 AUTO: Tournament creation completed");

    // Для HTTP выводим JSON
    if (isset($_SERVER['HTTP_HOST'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => "Created: {$createdCount}, Cleaned: {$deletedCount}",
            'created' => $createdCount,
            'cleaned' => $deletedCount
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    $errorMessage = "💥 AUTO: Critical error: " . $e->getMessage();
    log_message($errorMessage);
    
    if (isset($_SERVER['HTTP_HOST'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    exit(1);
}

/**
 * Создание ежедневных турниров
 */
function createDailyTournaments() {
    global $pdo;
    
    $tournaments = [
        [
            'name' => 'Пацак и ночью работает',
            'description' => 'Вложи 10 - получи больше',
            'entry_fee' => 100,
            'prize_pool' => 1000,
            'max_players' => 10,
            'difficulty' => 'easy',
            'start_hour' => 0 // 00:00
        ],
        [
            'name' => 'КУ, Чатланин!',
            'description' => 'Раздаём чатлы',
            'entry_fee' => 100,
            'prize_pool' => 1000,
            'max_players' => 10,
            'difficulty' => 'easy',
            'start_hour' => 2 // 02:00
        ],
        [
            'name' => 'Доброе утро, Плюк',
            'description' => 'Ранний заработок - весь день кормит',
            'entry_fee' => 100,
            'prize_pool' => 1000,
            'max_players' => 10,
            'difficulty' => 'easy',
            'start_hour' => 4 // 04:00
        ],
        [
            'name' => 'Затяни цапу',
            'description' => 'Увеличь свой доход',
            'entry_fee' => 200,
            'prize_pool' => 2000,
            'max_players' => 10,
            'difficulty' => 'easy',
            'start_hour' => 6 // 06:00
        ],
        [
            'name' => 'Луц колонка',
            'description' => '20%-30%-50%',
            'entry_fee' => 200,
            'prize_pool' => 2000,
            'max_players' => 10,
            'difficulty' => 'easy',
            'start_hour' => 8 // 08:00
        ],
        [
            'name' => 'Заправляемся чатлами',
            'description' => 'Можно заработать до 100 чатлов',
            'entry_fee' => 200,
            'prize_pool' => 2000,
            'max_players' => 10,
            'difficulty' => 'easy',
            'start_hour' => 10 // 10:00
        ],
        [
            'name' => 'Это Вам не пластиковая каша',
            'description' => 'Утраиваем призы',
            'entry_fee' => 300,
            'prize_pool' => 3000,
            'max_players' => 10,
            'difficulty' => 'easy',
            'start_hour' => 12 // 12:00
        ],
        [
            'name' => 'Долетят только трое',
            'description' => 'Чатлы делим на троих',
            'entry_fee' => 300,
            'prize_pool' => 3000,
            'max_players' => 10,
            'difficulty' => 'easy',
            'start_hour' => 14 // 14:00
        ],
        [
            'name' => 'Дневной забег',
            'description' => 'Стань первым за 10 минут',
            'entry_fee' => 300,
            'prize_pool' => 3000,
            'max_players' => 10,
            'difficulty' => 'easy',
            'start_hour' => 16 // 16:00
        ],
        [
            'name' => 'А у Вас какая система?',
            'description' => 'Хорошая стратегия, - большой доход',
            'entry_fee' => 400,
            'prize_pool' => 4000,
            'max_players' => 10,
            'difficulty' => 'easy',
            'start_hour' => 18 // 18:00
        ],
        [
            'name' => 'Цветовая дифференциация штанов',
            'description' => 'Выше статус, - выше заработок',
            'entry_fee' => 400,
            'prize_pool' => 4000,
            'max_players' => 10,
            'difficulty' => 'easy',
            'start_hour' => 20 // 20:00
        ],
        [
            'name' => 'Плюкане любят скорость',
            'description' => 'Быстрей решишь, - быстрей разбогатеешь',
            'entry_fee' => 400,
            'prize_pool' => 4000,
            'max_players' => 10,
            'difficulty' => 'easy',
            'start_hour' => 22 // 22:00
        ]
    ];

    $createdCount = 0;
    $tomorrow = date('Y-m-d', strtotime('+1 day'));

    foreach ($tournaments as $tournament) {
        // Устанавливаем время начала на завтра
        $start_time = $tomorrow . ' ' . str_pad($tournament['start_hour'], 2, '0', STR_PAD_LEFT) . ':00:00';
        
        // Проверяем, не существует ли уже такой турнир на эту дату
        $stmt = $pdo->prepare("
            SELECT id FROM tournaments 
            WHERE name = ? AND DATE(start_time) = ?
        ");
        $stmt->execute([$tournament['name'], $tomorrow]);
        
        if ($stmt->fetch()) {
            log_message("ℹ️ Tournament '{$tournament['name']}' already exists for {$tomorrow}");
            continue;
        }

        // Создаем турнир
        $stmt = $pdo->prepare("
            INSERT INTO tournaments 
            (name, description, entry_fee, prize_pool, max_players, difficulty, status, start_time, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, 'registration', ?, NOW())
        ");
        
        $success = $stmt->execute([
            $tournament['name'],
            $tournament['description'],
            $tournament['entry_fee'],
            $tournament['prize_pool'],
            $tournament['max_players'],
            $tournament['difficulty'],
            $start_time
        ]);

        if ($success) {
            $createdCount++;
            log_message("✅ Created tournament: {$tournament['name']} at {$start_time}");
        } else {
            log_message("❌ Failed to create tournament: {$tournament['name']}");
        }
    }

    return $createdCount;
}

/**
 * Очистка старых завершенных турниров
 */
function cleanupOldTournaments() {
    global $pdo;
    
    // Удаляем завершенные турниры старше 2 дней
    $stmt = $pdo->prepare("
        DELETE FROM tournaments 
        WHERE status = 'completed' 
        AND end_time < DATE_SUB(NOW(), INTERVAL 2 DAY)
    ");
    
    $stmt->execute();
    $deletedCount = $stmt->rowCount();
    
    return $deletedCount;
}
?>