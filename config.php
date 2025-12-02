<?php
// В начале config.php добавить:
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
// ==================== НАСТРОЙКА ЛОГИРОВАНИЯ ОШИБОК ====================

// Включаем логирование ошибок
ini_set('log_errors', 1);

// Указываем файл для логов ошибок
ini_set('error_log', __DIR__ . '/php-errors.log');

// Настройка уровня ошибок для разных режимов
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    // В режиме отладки - все ошибки
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    // В продакшн режиме - только критические ошибки
    ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

// ==================== КОНЕЦ НАСТРОЙКИ ЛОГИРОВАНИЯ ====================

// Настройки безопасности сессии ДО session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Измените на 1 если используете HTTPS
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_name('SUDOKU_SESSION');

// Определяем базовый путь для API
define('API_BASE_PATH', __DIR__ . '/api/');

// Начало сессии с улучшенными параметрами безопасности
session_start([
    'cookie_lifetime' => 86400, // 1 день
    'gc_maxlifetime'  => 86400,
    'cookie_httponly' => 1,
    'cookie_secure' => 0,
    'cookie_samesite' => 'Strict'
]);

// Настройки базы данных
define('DB_HOST', 'localhost');
define('DB_USER', 'partners1p');
define('DB_PASS', 'Sudoku235');
define('DB_NAME', 'partners1p');
define('DEBUG_MODE', true); // Временно true для отладки

// Настройки приложения
define('APP_NAME', 'ПлюкСудоку');
define('BASE_URL', 'https://plyuk.site/');
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 300); // 5 минут в секундах
define('REMEMBER_ME_EXPIRE', 60 * 60 * 24 * 30); // 30 дней

// ★★★ НОВЫЕ КОНСТАНТЫ ДЛЯ ТУРНИРОВ ★★★
define('TOURNAMENT_ENTRY_FEE', 0); // Взнос по умолчанию
define('TOURNAMENT_MAX_PLAYERS', 8);
define('TOURNAMENT_DEFAULT_PRIZE_POOL', 100);
define('TOURNAMENT_REGISTRATION_HOURS', 24); // Время регистрации
define('TOURNAMENT_DURATION_HOURS', 2); // Длительность турнира

// Конфигурация писем через почту
define('FROM_EMAIL', 'admin@plyuk.site'); // Ваш почтовый ящик
define('FROM_NAME', 'ПлюкСудоку');

// Подключение к базе данных
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    );
} catch (PDOException $e) {
    error_log('Database connection error: ' . $e->getMessage());
    if (DEBUG_MODE) {
        // В режиме отладки показываем ошибку
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
        exit();
    } else {
        // В продакшене - общая ошибка
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Database connection failed']);
        exit();
    }
}

// Функция для проверки существования таблицы
function table_exists($pdo, $table_name) {
    try {
        $result = $pdo->query("SHOW TABLES LIKE '$table_name'");
        return $result->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Table check error for $table_name: " . $e->getMessage());
        return false;
    }
}

// Проверка remember me cookie (должно быть ДО любой проверки сессии)
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token']) && isset($_COOKIE['user_id'])) {
    try {
        // Проверяем существование таблицы remember_tokens
        if (table_exists($pdo, 'remember_tokens')) {
            $stmt = $pdo->prepare("SELECT u.id, u.username, u.email 
                                  FROM users u 
                                  INNER JOIN remember_tokens rt ON u.id = rt.user_id 
                                  WHERE u.id = ? AND rt.token = ? AND rt.expires_at > NOW()");
            $stmt->execute([$_COOKIE['user_id'], $_COOKIE['remember_token']]);
            $user = $stmt->fetch();
            
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                
                // Обновляем срок действия токена
                $new_expires = time() + REMEMBER_ME_EXPIRE;
                $stmt = $pdo->prepare("UPDATE remember_tokens SET expires_at = ? WHERE token = ?");
                $stmt->execute([date('Y-m-d H:i:s', $new_expires), $_COOKIE['remember_token']]);
                
                // Обновляем cookies
                setcookie('remember_token', $_COOKIE['remember_token'], [
                    'expires' => $new_expires,
                    'path' => '/',
                    'domain' => $_SERVER['HTTP_HOST'],
                    'secure' => isset($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
                
                setcookie('user_id', $user['id'], [
                    'expires' => $new_expires,
                    'path' => '/',
                    'domain' => $_SERVER['HTTP_HOST'],
                    'secure' => isset($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            } else {
                // Невалидные cookies - удаляем
                setcookie('remember_token', '', time() - 3600, '/', $_SERVER['HTTP_HOST'], true, true);
                setcookie('user_id', '', time() - 3600, '/', $_SERVER['HTTP_HOST'], true, true);
            }
        }
    } catch (PDOException $e) {
        error_log('Remember me error: ' . $e->getMessage());
    }
}

// Генерация CSRF-токена если не существует
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Очистка старых remember tokens (раз в 100 запросов)
if (rand(1, 100) === 1 && table_exists($pdo, 'remember_tokens')) {
    try {
        $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE expires_at < NOW()");
        $stmt->execute();
        error_log('Cleaned up expired remember tokens');
    } catch (PDOException $e) {
        error_log('Cleanup old tokens error: ' . $e->getMessage());
    }
}

/**
 * Проверка CSRF-токена
 */
function verify_csrf_token() {
    if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token'])) {
        throw new Exception("Неверный CSRF-токен. Пожалуйста, обновите страницу и попробуйте снова.");
    }
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        throw new Exception("Неверный CSRF-токен. Пожалуйста, обновите страницу и попробуйте снова.");
    }
}

/**
 * Перенаправление на указанный URL
 */
function redirect(string $url): void {
    header("Location: " . $url);
    exit();
}

/**
 * Проверка авторизации пользователя
 */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Создание remember me token
 */
function create_remember_token(int $user_id): ?string {
    global $pdo;
    
    try {
        // Проверяем существование таблицы
        if (!table_exists($pdo, 'remember_tokens')) {
            return null;
        }
        
        $token = bin2hex(random_bytes(32));
        $expires = time() + REMEMBER_ME_EXPIRE;
        
        $stmt = $pdo->prepare("
            INSERT INTO remember_tokens (user_id, token, expires_at) 
            VALUES (?, ?, ?)
        ");
        
        if ($stmt->execute([$user_id, $token, date('Y-m-d H:i:s', $expires)])) {
            return $token;
        }
        
        return null;
    } catch (PDOException $e) {
        error_log('Error creating remember token: ' . $e->getMessage());
        return null;
    }
}

/**
 * Удаление remember me token
 */
function delete_remember_token(string $token): bool {
    global $pdo;
    
    try {
        if (!table_exists($pdo, 'remember_tokens')) {
            return true;
        }
        
        $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE token = ?");
        return $stmt->execute([$token]);
    } catch (PDOException $e) {
        error_log('Error deleting remember token: ' . $e->getMessage());
        return false;
    }
}

/**
 * Получение данных текущего пользователя
 */
function get_current_user_data(): ?array {
    global $pdo;
    
    if (!is_logged_in()) {
        return null;
    }
    
    try {
        if (!table_exists($pdo, 'users')) {
            return null;
        }
        
        $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        error_log('Error fetching current user: ' . $e->getMessage());
        return null;
    }
}

/**
 * Проверка лимита попыток входа
 */
function is_login_blocked(string $email): bool {
    global $pdo;
    
    try {
        if (!table_exists($pdo, 'login_attempts')) {
            return false;
        }
        
        $stmt = $pdo->prepare("SELECT attempts, last_attempt FROM login_attempts WHERE email = ?");
        $stmt->execute([$email]);
        $data = $stmt->fetch();
        
        if ($data && $data['attempts'] >= MAX_LOGIN_ATTEMPTS) {
            $timeout = strtotime($data['last_attempt']) + LOGIN_TIMEOUT;
            return time() < $timeout;
        }
        return false;
    } catch (PDOException $e) {
        error_log('Login attempt check error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Логирование попытки входа
 */
function log_login_attempt(string $email, bool $success): void {
    global $pdo;
    
    try {
        if (!table_exists($pdo, 'login_attempts')) {
            return;
        }
        
        if ($success) {
            $pdo->prepare("DELETE FROM login_attempts WHERE email = ?")->execute([$email]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO login_attempts (email, attempts, last_attempt) 
                VALUES (?, 1, NOW()) 
                ON DUPLICATE KEY UPDATE 
                    attempts = attempts + 1, 
                    last_attempt = NOW()
            ");
            $stmt->execute([$email]);
        }
    } catch (PDOException $e) {
        error_log('Login attempt logging error: ' . $e->getMessage());
    }
}

// Функция для получения статистики пользователя с проверкой таблицы
function get_user_stats($user_id) {
    global $pdo;
    
    try {
        if (!table_exists($pdo, 'user_stats')) {
            return [
                'totalGames' => 0,
                'gamesWon' => 0,
                'totalPoints' => 0,
                'rating' => 0,
                'bestTimes' => [
                    'easy' => null,
                    'medium' => null,
                    'hard' => null
                ]
            ];
        }
        
        $stmt = $pdo->prepare("
            SELECT 
                total_games,
                games_won, 
                total_points,
                best_time_easy,
                best_time_medium, 
                best_time_hard
            FROM user_stats 
            WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($stats) {
            return [
                'totalGames' => (int)$stats['total_games'],
                'gamesWon' => (int)$stats['games_won'],
                'totalPoints' => (int)($stats['total_points'] ?? 0),
                'rating' => (int)($stats['total_points'] ?? 0), // Рейтинг = общим чатлам
                'bestTimes' => [
                    'easy' => $stats['best_time_easy'] ? (int)$stats['best_time_easy'] : null,
                    'medium' => $stats['best_time_medium'] ? (int)$stats['best_time_medium'] : null,
                    'hard' => $stats['best_time_hard'] ? (int)$stats['best_time_hard'] : null
                ]
            ];
        }
        
        // Возвращаем статистику по умолчанию если записи нет
        return [
            'totalGames' => 0,
            'gamesWon' => 0,
            'totalPoints' => 0,
            'rating' => 0,
            'bestTimes' => [
                'easy' => null,
                'medium' => null,
                'hard' => null
            ]
        ];
    } catch (PDOException $e) {
        error_log("Error getting user stats: " . $e->getMessage());
        return [
            'totalGames' => 0,
            'gamesWon' => 0,
            'totalPoints' => 0,
            'rating' => 0,
            'bestTimes' => [
                'easy' => null,
                'medium' => null,
                'hard' => null
            ]
        ];
    }
}

/**
 * Получение достижений пользователя с проверкой таблицы
 */
function get_user_achievements($user_id) {
    global $pdo;
    
    try {
        // Если таблицы не существует, возвращаем пустой массив
        if (!table_exists($pdo, 'user_achievements')) {
            return [];
        }
        
        $stmt = $pdo->prepare("
            SELECT achievement_id as id, unlocked, unlocked_at as unlockedAt, progress 
            FROM user_achievements 
            WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Error getting user achievements: " . $e->getMessage());
        return [];
    }
}

/**
 * Сохранение достижений пользователя с проверкой таблицы
 */
function save_user_achievements($user_id, $achievements) {
    global $pdo;
    
    try {
        // Если таблицы не существует, просто возвращаем true
        if (!table_exists($pdo, 'user_achievements')) {
            error_log("user_achievements table does not exist - skipping save");
            return true;
        }
        
        // Удаляем старые достижения пользователя
        $stmt = $pdo->prepare("DELETE FROM user_achievements WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Сохраняем новые достижения
        $stmt = $pdo->prepare("
            INSERT INTO user_achievements 
            (user_id, achievement_id, unlocked, unlocked_at, progress, points) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($achievements as $achievement) {
            if (!isset($achievement['id'])) continue;
            
            $unlocked = isset($achievement['unlocked']) ? (int)$achievement['unlocked'] : 0;
            $unlockedAt = isset($achievement['unlockedAt']) ? $achievement['unlockedAt'] : null;
            $progress = isset($achievement['progress']) ? (int)$achievement['progress'] : 0;
            $points = isset($achievement['points']) ? (int)$achievement['points'] : 0;
            
            $stmt->execute([
                $user_id,
                $achievement['id'],
                $unlocked,
                $unlockedAt,
                $progress,
                $points
            ]);
        }
        
        return true;
        
    } catch (PDOException $e) {
        error_log("Error saving user achievements: " . $e->getMessage());
        return false;
    }
}

// Автозагрузка классов (если используется ООП)
spl_autoload_register(function ($class_name) {
    $file = __DIR__ . '/classes/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Установка часового пояса
date_default_timezone_set('Europe/Moscow');

// Защита от XSS для выводимых данных
function e($string): string {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Функция для безопасного JSON ответа в API
function json_response($data) {
    // Очищаем буфер вывода
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Проверка и создание таблицы pending_registrations
 */
function check_pending_registrations_table() {
    global $pdo;
    
    try {
        // Проверяем существование таблицы
        $pdo->query("SELECT 1 FROM pending_registrations LIMIT 1");
    } catch (PDOException $e) {
        // Таблица не существует, создаем
        $pdo->exec("
            CREATE TABLE pending_registrations (
                id INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                confirmation_code VARCHAR(100) NOT NULL UNIQUE,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                expires_at DATETIME NOT NULL,
                INDEX idx_confirmation_code (confirmation_code),
                INDEX idx_expires_at (expires_at),
                INDEX idx_email (email)
            )
        ");
        error_log("Created pending_registrations table");
    }
}

// Проверяем таблицу при загрузке конфига
check_pending_registrations_table();

// Функция для создания таблиц турниров
function create_tournament_tables() {
    global $pdo;
    
    $tables = [
        "CREATE TABLE IF NOT EXISTS tournaments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            entry_fee DECIMAL(10,2) DEFAULT 0,
            prize_pool DECIMAL(10,2) DEFAULT 0,
            max_players INT DEFAULT 8,
            difficulty ENUM('easy', 'medium', 'hard', 'tournament') DEFAULT 'medium',
            status ENUM('registration', 'active', 'completed', 'cancelled') DEFAULT 'registration',
            start_time DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS tournament_registrations (
            id INT PRIMARY KEY AUTO_INCREMENT,
            tournament_id INT,
            user_id INT,
            registered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            status ENUM('registered', 'playing', 'completed', 'disqualified') DEFAULT 'registered',
            FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_registration (tournament_id, user_id)
        )",
        
        "CREATE TABLE IF NOT EXISTS tournament_results (
            id INT PRIMARY KEY AUTO_INCREMENT,
            tournament_id INT,
            user_id INT,
            position INT,
            score INT DEFAULT 0,
            prize DECIMAL(10,2) DEFAULT 0,
            completed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            games_played INT DEFAULT 0,
            games_won INT DEFAULT 0,
            best_time INT DEFAULT NULL,
            win_rate DECIMAL(5,2) DEFAULT 0,
            FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_tournament_user (tournament_id, user_id)
        )",
        
        "CREATE TABLE IF NOT EXISTS tournament_games (
            id INT PRIMARY KEY AUTO_INCREMENT,
            tournament_id INT,
            game_id VARCHAR(100),
            player1_id INT,
            player2_id INT,
            board_data JSON,
            status ENUM('pending', 'active', 'completed') DEFAULT 'pending',
            player1_score INT DEFAULT 0,
            player2_score INT DEFAULT 0,
            winner_id INT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
            FOREIGN KEY (player1_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (player2_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        
        "CREATE TABLE IF NOT EXISTS payment_history (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT,
            amount DECIMAL(10,2),
            method VARCHAR(50),
            status VARCHAR(50),
            external_id VARCHAR(100),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        
        "CREATE TABLE IF NOT EXISTS tournament_seen (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT,
            tournament_id INT,
            seen_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
            UNIQUE KEY unique_seen (user_id, tournament_id)
        )"
    ];
    
    foreach ($tables as $tableSql) {
        try {
            $pdo->exec($tableSql);
        } catch (PDOException $e) {
            error_log("Table creation error: " . $e->getMessage());
        }
    }
    
    error_log("Tournament tables checked/created successfully");
}

function initialize_demo_tournaments() {
    // Пустая функция - ничего не делает
    // Демо-турниры больше не создаются автоматически
    return;
}

// ★★★ ВЫЗВАТЬ ОБЕ ФУНКЦИИ В КОНЦЕ ★★★
create_tournament_tables();
// initialize_demo_tournaments();

/**
 * Отправка email с подтверждением через встроенную почту хостинга
 */
function send_confirmation_email($email, $username, $confirmation_link) {
    $to = $email;
    $subject = "🎯 Подтверждение регистрации";
    
    // Красивый HTML шаблон
$html_message = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение регистрации</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: "Inter", "Arial", sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0; 
            padding: 20px;
            min-height: 100vh;
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            background: white; 
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header { 
            background: linear-gradient(135deg, #FFDD2D 0%, #FFC107 100%);
            padding: 30px 20px;
            text-align: center;
            position: relative;
        }
        .header-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: inherit;
            gap: 20px;
            transition: transform 0.3s ease;
            max-width: 100%;
        }
        .header-logo {
            width: 100px;
            height: 80px;
            object-fit: contain;
        }
        .header-text {
            text-align: left;
        }
        .title { 
            font-size: 32px; 
            color: #333;
            font-weight: 600;
            margin-bottom: 3px;
        }
        .subtitle {
            font-size: 16px;
            color: #000000;
            opacity: 0.9;
        }
        .content { 
            padding: 40px 30px; 
            background: #f8f9fa;
        }
        .greeting {
            font-size: 18px;
            color: #333;
            margin-bottom: 25px;
            line-height: 1.6;
            text-align: center;
        }
        .steps {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin: 10px 0;
            align-items: center;
        }
        .step {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 5px;
        }
        .step-icon {
            color: #4CAF50;
            font-size: 18px;
            margin-right: 10px;
            flex-shrink: 0;
            width: 30px;
            text-align: center;
            display: flex;
            align-items: center;
        }
        .step-text {
            font-size: 16px;
            color: #333;
            line-height: 1.5;
            display: flex;
            align-items: center;
        }
        .button { 
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 40px; 
            background: linear-gradient(135deg, #418344 0%, #34bb3a 100%);
            color: #ffffff !important;
            text-decoration: none; 
            border-radius: 50px;
            font-size: 18px;
            font-weight: 600;
            margin: 25px 0;
            text-align: center;
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.3);
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(76, 175, 80, 0.4);
            color: #f8f9fa !important;
        }
        .button-icon {
            font-size: 20px;
        }
        .link-box { 
            background: white; 
            padding: 20px; 
            border-radius: 10px;
            border: 2px dashed #e0e0e0;
            margin: 20px 0;
            word-break: break-all;
            font-family: "Courier New", monospace;
            font-size: 14px;
            color: #555;
        }
        .warning { 
            background: #fff3cd; 
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 2px 0;
            border-radius: 4px;
            color: #856404;
        }
        .footer { 
            text-align: center; 
            padding: 20px;
            background: white;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }
        .footer-links {
            margin-bottom: 20px;
            text-align: center;
        }
        .footer-link {
            color: #666;
            text-decoration: none;
            margin: 0 10px;
            font-size: 12px;
        }
        .footer-link:hover {
            text-decoration: underline;
        }
        .age-rating {
            display: inline-flex;
            align-items: center;
            margin-left: 15px;
        }
        .age-badge {
            background: linear-gradient(135deg, #ededed, #ffffff);
            color: #333;
            padding: 4px 8px;
            border-radius: 50px;
            font-size: 0.8rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .social {
            margin: 20px 0;
        }
        .social a {
            color: #666;
            text-decoration: none;
            margin: 0 10px;
        }
        @media (max-width: 600px) {
            .container { margin: 10px; }
            .header { padding: 25px 15px; }
            .header-link {
                flex-direction: column;
                gap: 15px;
            }
            .header-logo {
                width: 100px;
                height: 80px;
            }
            .title {
                font-size: 28px;
            }
            .content { padding: 30px 20px; }
            .button { 
                padding: 14px 30px; 
                font-size: 16px; 
            }
            .step {
                margin-bottom: 15px;
            }
            .footer-links {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            .age-rating {
                margin-left: 0;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="https://plyuk.site/" class="header-link">
                <img src="https://plyuk.site/img/KU.png" alt="ПлюкСудоку" class="header-logo">
                <div class="header-text">
                    <div class="title">ПлюкСудоку</div>
                    <div class="subtitle">Умная игра для гениев</div>
                </div>
            </a>
        </div>
            
            <div class="content">
            <div class="greeting">
                <strong>Приветствуем, ' . htmlspecialchars($username) . '!</strong><br>
                Почти готово! Остался всего один шаг для начала игры.
            </div>
            
            <div class="steps">
                <div class="step">
                    <div class="step-icon">✅</div>
                    <div class="step-text">Вы успешно заполнили форму регистрации</div>
                </div>
                <div class="step">
                    <div class="step-icon">✅</div>
                    <div class="step-text">Нажмите кнопку ниже для подтверждения email</div>
                </div>
                <div class="step">
                    <div class="step-icon">✅</div>
                    <div class="step-text">Начните играть и покоряйте рейтинги!</div>
                </div>
            </div>
            
            <div style="text-align: center;">
                <a href="' . $confirmation_link . '" class="button">
                    Подтвердить регистрацию
                </a>
            </div>
                
                <div class="link-box">
                    ' . $confirmation_link . '
                </div>
                
                <div style="text-align: center;">
                🏆 Играйте в судоку, зарабатывайте чатлы, улучшайте свой статус.
                Чем больше чатлов, тем больше шансов выиграть призы.
                </div>
                <div class="social" style="text-align: center;">
                    С Уважением, команда <strong>ПлюкСудоку</strong> 🎯
                </div>
                
                <div class="warning">
                    <strong>⚠️ Ссылка действительна 24 часа</strong><br>
                    Если Вы не регистрировались в нашей игре, просто проигнорируйте это письмо.
                </div>
            </div>
            <div class="footer">
            <div class="footer-links">
                <a href="https://plyuk.site/privacy.php" class="footer-link">
                    Политика конфиденциальности
                </a>
                <a href="https://plyuk.site/terms.php" class="footer-link">
                    Условия использования
                </a>
                <div class="age-rating">
                    <span class="age-badge">+16</span>
                </div>
            </div>
            <div>© 2024 ПлюкСудоку. Все права защищены.</div>
            <div style="margin-top: 10px; font-size: 12px; color: #999;">
                Это автоматическое письмо, пожалуйста, не отвечайте на него
            </div>
        </div>
    </div>
</body>
</html>
    ';
    
    // Текстовая версия для почтовых клиентов
    $text_message = "
    ПлюкСудоку - Подтверждение регистрации
    
    Здравствуйте, " . htmlspecialchars($username) . "!
    
    Для завершения регистрации перейдите по ссылке:
    " . $confirmation_link . "
    
    Ссылка действительна в течение 24 часов.
    
    Если вы не регистрировались на ПлюкСудоку, проигнорируйте это письмо.
    
    С уважением,
    Команда ПлюкСудоку
    ";
    
    // Заголовки письма
    $headers = [
        'From: ' . FROM_NAME . ' <' . FROM_EMAIL . '>',
        'Reply-To: ' . FROM_EMAIL,
        'Content-Type: text/html; charset=UTF-8',
        'X-Mailer: PHP/' . phpversion()
    ];

    // Отправка письма
    $mail_sent = mail($to, $subject, $html_message, implode("\r\n", $headers));
    
    // Логируем результат
    $log_message = date('Y-m-d H:i:s') . " - To: $email - Sent: " . ($mail_sent ? 'YES' : 'NO');
    file_put_contents('email_log.txt', $log_message . PHP_EOL, FILE_APPEND);
    
    return $mail_sent;
}

/**
 * Создание временной регистрации
 */
function create_pending_registration($username, $email, $password_hash) {
    global $pdo;
    
    try {
        $confirmation_code = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', time() + 24 * 60 * 60); // 24 часа
        
        $stmt = $pdo->prepare("
            INSERT INTO pending_registrations 
            (username, email, password_hash, confirmation_code, expires_at) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([$username, $email, $password_hash, $confirmation_code, $expires_at]) 
            ? $confirmation_code 
            : false;
        
    } catch (PDOException $e) {
        error_log('Error creating pending registration: ' . $e->getMessage());
        return false;
    }
}

/**
 * Подтверждение регистрации
 */
function confirm_registration($confirmation_code) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Находим ожидающую регистрацию
        $stmt = $pdo->prepare("
            SELECT * FROM pending_registrations 
            WHERE confirmation_code = ? AND expires_at > NOW()
        ");
        $stmt->execute([$confirmation_code]);
        $pending = $stmt->fetch();
        
        if (!$pending) {
            $pdo->rollBack();
            return false;
        }
        
        // Проверяем, не существует ли уже пользователь
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$pending['username'], $pending['email']]);
        if ($stmt->fetch()) {
            $pdo->rollBack();
            return false;
        }
        
        // Создаем пользователя
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$pending['username'], $pending['email'], $pending['password_hash']]);
        $user_id = $pdo->lastInsertId();
        
        // Создаем статистику
        $stmt = $pdo->prepare("INSERT INTO user_stats (user_id) VALUES (?)");
        $stmt->execute([$user_id]);
        
        // Удаляем из ожидающих
        $stmt = $pdo->prepare("DELETE FROM pending_registrations WHERE id = ?");
        $stmt->execute([$pending['id']]);
        
        $pdo->commit();
        
        return [
            'id' => $user_id,
            'username' => $pending['username'],
            'email' => $pending['email']
        ];
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Error confirming registration: ' . $e->getMessage());
        return false;
    }
}

/**
 * Проверка существования ожидающей регистрации
 */
function has_pending_registration($username, $email) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT id FROM pending_registrations 
            WHERE (username = ? OR email = ?) AND expires_at > NOW()
        ");
        $stmt->execute([$username, $email]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log('Error checking pending registration: ' . $e->getMessage());
        return false;
    }
}

/**
 * Проверяем, является ли запрос API запросом
 */
function is_api_request() {
    // Получаем текущий скрипт
    $current_script = $_SERVER['SCRIPT_NAME'] ?? '';
    $current_uri = $_SERVER['REQUEST_URI'] ?? '';
    
    // Список API скриптов
    $api_scripts = [
        'websocket_proxy.php',
        'api/get_tournaments.php',
        'api/check_auth.php',
        'api/get_stats.php',
        'api/save_stats.php',
        'api/get_game.php',
        'api/save_game.php',
        'api/clear_game.php',
        'api/get_achievements.php',
        'api/save_achievements.php',
        'api/update_stats.php',
        'api/reset_was_solved.php'
    ];
    
    // Проверяем по имени скрипта
    foreach ($api_scripts as $api_script) {
        if (strpos($current_script, $api_script) !== false || 
            strpos($current_uri, $api_script) !== false) {
            return true;
        }
    }
    
    // Проверяем по пути
    if (strpos($current_script, '/api/') !== false || 
        strpos($current_uri, '/api/') !== false) {
        return true;
    }
    
    return false;
}

// Выводим JavaScript переменные ТОЛЬКО если это не API запрос
if (!is_api_request() && !defined('NO_JS_OUTPUT')) {
    // Устанавливаем флаг, что это не API запрос
    define('IS_HTML_REQUEST', true);
    
    echo "<script>
    window.isLoggedIn = " . (is_logged_in() ? 'true' : 'false') . ";
    window.userId = " . (is_logged_in() ? $_SESSION['user_id'] : 'null') . ";
    window.username = " . (is_logged_in() ? json_encode($_SESSION['username']) : 'null') . ";
    </script>";
} else {
    // Устанавливаем флаг, что это API запрос
    define('IS_API_REQUEST', true);
}

function update_tournament_statuses() {
    global $pdo;
    try {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] 🔄 CONFIG: Updating tournament statuses\n", FILE_APPEND);
        
        // Регистрация -> Активный
        $activeUpdated = $pdo->exec("
            UPDATE tournaments 
            SET status = 'active', updated_at = NOW() 
            WHERE status = 'registration' AND start_time <= NOW()
        ");
        
        // Активный -> Завершен (турниры, которые начались более 2 часов назад)
        $stmt = $pdo->query("
            SELECT id FROM tournaments 
            WHERE status = 'active' AND start_time <= DATE_SUB(NOW(), INTERVAL 2 HOUR)
        ");
        $completedTournaments = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $completedUpdated = 0;
        foreach ($completedTournaments as $tournamentId) {
            if (calculateTournamentResults($tournamentId)) {
                $completedUpdated++;
            }
        }
        
        file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] ✅ CONFIG: Statuses updated - Active: {$activeUpdated}, Completed: {$completedUpdated}\n", FILE_APPEND);
        
        return ['active' => $activeUpdated, 'completed' => $completedUpdated];
        
    } catch (PDOException $e) {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] ❌ CONFIG: Tournament status update error: " . $e->getMessage() . "\n", FILE_APPEND);
        return ['active' => 0, 'completed' => 0];
    }
}

function calculate_tournament_results() {
    // Эта функция может вызывать API или выполнять расчет напрямую
    // Пока просто логируем
    error_log("Tournaments completed - results should be calculated");
    
    // В реальной реализации здесь будет вызов calculate_tournament_results.php
    // или непосредственный расчет результатов
}

// Вызывайте эту функцию при загрузке страницы или по cron
update_tournament_statuses();

function is_admin() {
    if (!is_logged_in()) return false;
    
    // Временно разрешаем всем авторизованным пользователям
    // В будущем можно добавить реальную проверку прав
    return true;
    
    /*
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        return $user && $user['is_admin'];
    } catch (PDOException $e) {
        error_log("Admin check error: " . $e->getMessage());
        return false;
    }
    */
}
/**
 * Функция расчета результатов турнира
 */
function calculateTournamentWinners($tournamentId) {
    global $pdo;
    
    try {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] 🎯 Starting calculation for tournament #{$tournamentId}\n", FILE_APPEND);
        
        $pdo->beginTransaction();

        // 1. ОБНОВЛЯЕМ СТАТУСЫ РЕГИСТРАЦИЙ
        $stmt = $pdo->prepare("
            UPDATE tournament_registrations 
            SET status = 'completed' 
            WHERE tournament_id = ? 
            AND status IN ('registered', 'playing')
        ");
        $stmt->execute([$tournamentId]);
        $updatedRegistrations = $stmt->rowCount();
        
        file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] 📊 Tournament #{$tournamentId}: Updated {$updatedRegistrations} registrations\n", FILE_APPEND);

        // 2. Получаем турнир
        $stmt = $pdo->prepare("SELECT * FROM tournaments WHERE id = ?");
        $stmt->execute([$tournamentId]);
        $tournament = $stmt->fetch();

        if (!$tournament) {
            $pdo->rollBack();
            file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] ❌ Tournament #{$tournamentId} not found\n", FILE_APPEND);
            return false;
        }

        file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] 📊 Tournament #{$tournamentId}: '{$tournament['name']}' found, prize pool: {$tournament['prize_pool']}\n", FILE_APPEND);

        // 3. Получаем участников
        $stmt = $pdo->prepare("
            SELECT tr.user_id, u.username
            FROM tournament_registrations tr
            INNER JOIN users u ON tr.user_id = u.id
            WHERE tr.tournament_id = ? AND tr.status = 'completed'
        ");
        $stmt->execute([$tournamentId]);
        $participants = $stmt->fetchAll();

        file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] 📊 Tournament #{$tournamentId}: Found " . count($participants) . " participants\n", FILE_APPEND);

        if (empty($participants)) {
            $pdo->rollBack();
            file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] ❌ Tournament #{$tournamentId}: No participants found\n", FILE_APPEND);
            return false;
        }

        // Выводим список участников для отладки
        foreach ($participants as $participant) {
            file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] 👤 Participant: ID {$participant['user_id']}, {$participant['username']}\n", FILE_APPEND);
        }

        // 4. Собираем статистику (ВРЕМЕННО - случайные очки)
        $playerStats = [];
        foreach ($participants as $participant) {
            $score = rand(500, 1500);
            $playerStats[] = [
                'user_id' => $participant['user_id'],
                'username' => $participant['username'],
                'score' => $score
            ];
            file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] 📊 User {$participant['user_id']} score: {$score}\n", FILE_APPEND);
        }

        // 5. Сортируем по очкам
        usort($playerStats, function($a, $b) {
            return $b['score'] - $a['score'];
        });

        // 6. Распределение призов
        $prizeDistribution = [1 => 0.5, 2 => 0.3, 3 => 0.2];
        $totalPrizeGiven = 0;

        file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] 💰 Starting prize distribution for " . count($playerStats) . " players\n", FILE_APPEND);

        // 7. Записываем результаты
        foreach ($playerStats as $position => $player) {
            $actualPosition = $position + 1;
            $prize = isset($prizeDistribution[$actualPosition]) ? 
                     round($tournament['prize_pool'] * $prizeDistribution[$actualPosition]) : 0;
            $totalPrizeGiven += $prize;
            
            file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] 🏆 Position {$actualPosition}: User {$player['user_id']} - Score: {$player['score']}, Prize: {$prize}\n", FILE_APPEND);
            
            // Начисляем чатлы победителям
            if ($prize > 0) {
                $stmt = $pdo->prepare("UPDATE user_stats SET total_points = total_points + ? WHERE user_id = ?");
                $stmt->execute([$prize, $player['user_id']]);
                file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] 💰 Added {$prize} points to user {$player['user_id']}\n", FILE_APPEND);
            }

            // Сохраняем результат
            $stmt = $pdo->prepare("
                INSERT INTO tournament_results (tournament_id, user_id, position, score, prize) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $tournamentId, 
                $player['user_id'], 
                $actualPosition, 
                $player['score'], 
                $prize
            ]);
            
            file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] ✅ Saved result for user {$player['user_id']} in position {$actualPosition}\n", FILE_APPEND);
        }

        $pdo->commit();
        
        file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] ✅ Tournament #{$tournamentId}: Successfully calculated. Total prize given: {$totalPrizeGiven}\n", FILE_APPEND);
        return true;

    } catch (PDOException $e) {
        $pdo->rollBack();
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] ❌ Tournament #{$tournamentId}: Database error - " . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    } catch (Exception $e) {
        $pdo->rollBack();
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] ❌ Tournament #{$tournamentId}: General error - " . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    }
}

/**
 * Функция расчета результатов турнира на основе статистики игроков
 */
function calculateTournamentResults($tournamentId) {
    global $pdo;
    
    try {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] 🎯 Starting results calculation for tournament #{$tournamentId}\n", FILE_APPEND);
        
        $pdo->beginTransaction();

        // 1. Получаем турнир
        $stmt = $pdo->prepare("SELECT * FROM tournaments WHERE id = ?");
        $stmt->execute([$tournamentId]);
        $tournament = $stmt->fetch();

        if (!$tournament) {
            $pdo->rollBack();
            file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] ❌ Tournament #{$tournamentId} not found\n", FILE_APPEND);
            return false;
        }

        // 2. Получаем зарегистрированных игроков
        $stmt = $pdo->prepare("
            SELECT tr.user_id, u.username 
            FROM tournament_registrations tr 
            INNER JOIN users u ON tr.user_id = u.id 
            WHERE tr.tournament_id = ? AND tr.status = 'registered'
        ");
        $stmt->execute([$tournamentId]);
        $participants = $stmt->fetchAll();

        if (empty($participants)) {
            $pdo->rollBack();
            file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] ❌ Tournament #{$tournamentId}: No participants found\n", FILE_APPEND);
            return false;
        }

        $playerStats = [];
        
        // 3. Собираем статистику для каждого игрока
        foreach ($participants as $participant) {
            $userId = $participant['user_id'];
            
            // Получаем статистику игрока за период турнира
            $stmt = $pdo->prepare("
                SELECT 
                    COALESCE(SUM(us.total_points), 0) as total_points,
                    COALESCE(SUM(us.games_won), 0) as games_won,
                    COALESCE(MIN(CASE WHEN us.best_time_easy > 0 THEN us.best_time_easy END), 999999) as best_time_easy,
                    COALESCE(MIN(CASE WHEN us.best_time_medium > 0 THEN us.best_time_medium END), 999999) as best_time_medium,
                    COALESCE(MIN(CASE WHEN us.best_time_hard > 0 THEN us.best_time_hard END), 999999) as best_time_hard,
                    COUNT(us.user_id) as games_played
                FROM user_stats us 
                WHERE us.user_id = ? 
                AND us.updated_at >= ?
            ");
            
            $stmt->execute([$userId, $tournament['start_time']]);
            $stats = $stmt->fetch();
            
            // Рассчитываем процент побед
            $winRate = $stats['games_played'] > 0 ? 
                ($stats['games_won'] / $stats['games_played']) * 100 : 0;
            
            // Определяем лучшее время в зависимости от сложности турнира
            $bestTime = 999999;
            switch($tournament['difficulty']) {
                case 'easy':
                    $bestTime = $stats['best_time_easy'];
                    break;
                case 'medium':
                    $bestTime = $stats['best_time_medium'];
                    break;
                case 'hard':
                    $bestTime = $stats['best_time_hard'];
                    break;
            }
            
            $playerStats[] = [
                'user_id' => $userId,
                'username' => $participant['username'],
                'total_points' => (int)$stats['total_points'],
                'games_won' => (int)$stats['games_won'],
                'games_played' => (int)$stats['games_played'],
                'best_time' => $bestTime < 999999 ? $bestTime : null,
                'win_rate' => round($winRate, 2)
            ];
        }

        // 4. Сортируем игроков по правилам турнира
        usort($playerStats, function($a, $b) {
            // 1-й приоритет: чатлы (по убыванию)
            if ($b['total_points'] !== $a['total_points']) {
                return $b['total_points'] - $a['total_points'];
            }
            // 2-й приоритет: время (по возрастанию) - меньше время = лучше
            if ($a['best_time'] !== $b['best_time']) {
                return ($a['best_time'] ?? 999999) - ($b['best_time'] ?? 999999);
            }
            // 3-й приоритет: победы (по убыванию)
            if ($b['games_won'] !== $a['games_won']) {
                return $b['games_won'] - $a['games_won'];
            }
            // 4-й приоритет: процент побед (по убыванию)
            return ($b['win_rate'] ?? 0) - ($a['win_rate'] ?? 0);
        });

        // 5. Распределение призов
        $prizeDistribution = [1 => 0.5, 2 => 0.3, 3 => 0.2]; // 50%, 30%, 20%
        $totalPrizeGiven = 0;

        // 6. Записываем результаты и начисляем призы
        foreach ($playerStats as $position => $player) {
            $actualPosition = $position + 1;
            $prize = isset($prizeDistribution[$actualPosition]) ? 
                     round($tournament['prize_pool'] * $prizeDistribution[$actualPosition]) : 0;
            $totalPrizeGiven += $prize;
            
            // Начисляем чатлы победителям
            if ($prize > 0) {
                $stmt = $pdo->prepare("
                    UPDATE user_stats 
                    SET total_points = total_points + ? 
                    WHERE user_id = ?
                ");
                $stmt->execute([$prize, $player['user_id']]);
            }

            // Сохраняем результат
            $stmt = $pdo->prepare("
                INSERT INTO tournament_results (tournament_id, user_id, position, score, prize) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $tournamentId, 
                $player['user_id'], 
                $actualPosition, 
                $player['total_points'], 
                $prize
            ]);
        }

        // 7. Обновляем статус турнира
        $stmt = $pdo->prepare("
            UPDATE tournaments 
            SET status = 'completed', updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$tournamentId]);

        $pdo->commit();
        
        file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] ✅ Tournament #{$tournamentId}: Successfully calculated. Total prize given: {$totalPrizeGiven}\n", FILE_APPEND);
        return true;

    } catch (PDOException $e) {
        $pdo->rollBack();
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] ❌ Tournament #{$tournamentId}: Database error - " . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    }
}

/**
 * Получение завершенных турниров с результатами пользователя
 */
function getCompletedTournamentsWithResults($userId = null) {
    global $pdo;
    
    try {
        $query = "
            SELECT 
                t.*,
                tr.position,
                tr.score,
                tr.prize,
                tr.completed_at
            FROM tournaments t
            LEFT JOIN tournament_results tr ON t.id = tr.tournament_id 
        ";
        
        if ($userId) {
            $query .= " AND tr.user_id = ?";
        }
        
        $query .= " WHERE t.status = 'completed' ORDER BY t.completed_at DESC";
        
        $stmt = $pdo->prepare($query);
        
        if ($userId) {
            $stmt->execute([$userId]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting completed tournaments: " . $e->getMessage());
        return [];
    }
}

/**
 * Получение статистики турнира для конкретного пользователя
 */
function getUserTournamentStats($userId, $tournamentId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                tr.position,
                tr.score,
                tr.prize,
                tr.completed_at,
                t.name as tournament_name,
                t.prize_pool
            FROM tournament_results tr
            INNER JOIN tournaments t ON tr.tournament_id = t.id
            WHERE tr.user_id = ? AND tr.tournament_id = ?
        ");
        $stmt->execute([$userId, $tournamentId]);
        
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        error_log("Error getting user tournament stats: " . $e->getMessage());
        return null;
    }
}

function auto_update_tournament_statuses() {
    // Проверяем, не выполнялось ли уже обновление в этом запросе
    if (defined('TOURNAMENT_STATUS_UPDATED')) {
        return;
    }
    
    // 10% вероятность выполнения (1 из 10 запросов)
    if (rand(1, 10) === 1) {
        // Логируем начало обновления
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] 🎯 AUTO: Starting automatic tournament status update\n", FILE_APPEND);
        
        // Выполняем обновление статусов
        $result = update_tournament_statuses();
        
        // Логируем результат
        file_put_contents(__DIR__ . '/tournament_cron.log', "[{$timestamp}] ✅ AUTO: Tournament status update completed - Active: {$result['active']}, Completed: {$result['completed']}\n", FILE_APPEND);
        
        // Помечаем, что обновление выполнено в этом запросе
        define('TOURNAMENT_STATUS_UPDATED', true);
    }
}

// ★★★ ВЫЗОВ АВТОМАТИЧЕСКОГО ОБНОВЛЕНИЯ ★★★
// Выполняется только для не-API запросов чтобы не замедлять AJAX запросы
if (!is_api_request() && !defined('NO_AUTO_UPDATE')) {
    auto_update_tournament_statuses();
}

?>