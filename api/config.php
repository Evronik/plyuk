<?php
// Настройки безопасности сессии ДО session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Измените на 1 если используете HTTPS
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_name('SUDOKU_SESSION');

// Начало сессии с улучшенными параметрами безопасности
session_start([
    'cookie_lifetime' => 86400, // 1 день
    'gc_maxlifetime'  => 86400,
]);

// Настройки базы данных
define('DB_HOST', 'localhost');
define('DB_USER', 'partners1p');
define('DB_PASS', 'Sudoku235');
define('DB_NAME', 'partners1p');
define('DEBUG_MODE', false); // В продакшене должно быть false

// Настройки приложения
define('APP_NAME', 'ПризСудоку');
define('BASE_URL', 'http://partners1p.temp.swtest.ru/');
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 300); // 5 минут в секундах
define('REMEMBER_ME_EXPIRE', 60 * 60 * 24 * 30); // 30 дней

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
        die("Database connection error: " . $e->getMessage());
    } else {
        die("Произошла ошибка подключения к базе данных. Пожалуйста, попробуйте позже.");
    }
}

// Проверка remember me cookie (должно быть ДО любой проверки сессии)
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token']) && isset($_COOKIE['user_id'])) {
    try {
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
    } catch (PDOException $e) {
        error_log('Remember me error: ' . $e->getMessage());
    }
}

// Генерация CSRF-токена если не существует
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Очистка старых remember tokens (раз в 100 запросов)
if (rand(1, 100) === 1) {
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

/**
 * Сохранение игры пользователя
 */
function save_user_game($user_id, $game_data) {
    global $pdo;
    
    try {
        // Извлекаем отдельные поля из данных игры
        $board = json_encode($game_data['board'] ?? []);
        $solution = json_encode($game_data['solution'] ?? []);
        $fixedCells = json_encode($game_data['fixedCells'] ?? []);
        $difficulty = $game_data['difficulty'] ?? 'easy';
        $seconds = $game_data['seconds'] ?? 0;
        $mistakes = $game_data['mistakes'] ?? 0;
        $hintsUsed = $game_data['hintsUsed'] ?? 0;
        $hintsLeft = $game_data['hintsLeft'] ?? 3;
        $wasSolved = isset($game_data['wasSolved']) ? (int)$game_data['wasSolved'] : 0;
        
        // ВАЖНОЕ ИСПРАВЛЕНИЕ: Сохраняем gameStartTime как BIGINT
        $gameStartTime = isset($game_data['gameStartTime']) ? (int)$game_data['gameStartTime'] : null;

// Добавьте проверку на корректность timestamp
if ($gameStartTime && $gameStartTime > 2000000000000) {
    // Слишком большое значение - вероятно ошибка
    error_log("❌ INVALID gameStartTime: $gameStartTime");
    $gameStartTime = null;
}
        
        // ОТЛАДОЧНОЕ ЛОГИРОВАНИЕ
        error_log("💾 SAVE GAME DEBUG - User: $user_id, Seconds: $seconds, GameStartTime: " . ($gameStartTime ?? 'NULL'));
        
        // Проверяем, есть ли уже сохраненная игра
        $stmt = $pdo->prepare("SELECT id FROM user_games WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $existing_game = $stmt->fetch();
        
        if ($existing_game) {
            // Обновляем существующую запись
            $stmt = $pdo->prepare("
                UPDATE user_games 
                SET board = ?, solution = ?, fixed_cells = ?, difficulty = ?, 
                    seconds = ?, mistakes = ?, hints_used = ?, hints_left = ?, 
                    was_solved = ?, game_start_time = ?, updated_at = NOW() 
                WHERE user_id = ?
            ");
            
            $params = [
                $board, $solution, $fixedCells, $difficulty, 
                $seconds, $mistakes, $hintsUsed, $hintsLeft, 
                $wasSolved, $gameStartTime, $user_id
            ];
            
            error_log("💾 UPDATE PARAMS: " . implode(', ', array_map(function($p) {
                return is_null($p) ? 'NULL' : $p;
            }, $params)));
            
            $result = $stmt->execute($params);
            
            error_log("💾 UPDATE GAME RESULT: " . ($result ? 'SUCCESS' : 'FAILED'));
            return $result;
        } else {
            // Создаем новую запись
            $stmt = $pdo->prepare("
                INSERT INTO user_games 
                (user_id, board, solution, fixed_cells, difficulty, seconds, 
                 mistakes, hints_used, hints_left, was_solved, game_start_time, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            $params = [
                $user_id, $board, $solution, $fixedCells, $difficulty, 
                $seconds, $mistakes, $hintsUsed, $hintsLeft, $wasSolved, $gameStartTime
            ];
            
            error_log("💾 INSERT PARAMS: " . implode(', ', array_map(function($p) {
                return is_null($p) ? 'NULL' : $p;
            }, $params)));
            
            $result = $stmt->execute($params);
            
            error_log("💾 INSERT GAME RESULT: " . ($result ? 'SUCCESS' : 'FAILED'));
            return $result;
        }
    } catch (PDOException $e) {
        error_log("❌ Error saving game: " . $e->getMessage());
        return false;
    }
}

/**
 * Загрузка игры пользователя
 */
function get_user_game($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT board, solution, fixed_cells, difficulty, seconds, 
                   mistakes, hints_used, hints_left, was_solved, game_start_time 
            FROM user_games WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);
        $game = $stmt->fetch();
        
        if ($game) {
            return [
                'board' => json_decode($game['board'], true),
                'solution' => json_decode($game['solution'], true),
                'fixedCells' => json_decode($game['fixed_cells'], true),
                'difficulty' => $game['difficulty'],
                'seconds' => (int)$game['seconds'],
                'mistakes' => (int)$game['mistakes'],
                'hintsUsed' => (int)$game['hints_used'],
                'hintsLeft' => (int)$game['hints_left'],
                'wasSolved' => (bool)$game['was_solved'],
                'gameStartTime' => $game['game_start_time'] ? (int)$game['game_start_time'] : null,
                'success' => true
            ];
        }
        return ['success' => false];
    } catch (PDOException $e) {
        error_log("Error loading game: " . $e->getMessage());
        return ['success' => false];
    }
}

// Сброс состояния was_solved для пользователя
function reset_was_solved($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE user_games 
            SET was_solved = 0, updated_at = NOW() 
            WHERE user_id = ?
        ");
        return $stmt->execute([$user_id]);
    } catch (PDOException $e) {
        error_log("Error resetting was_solved: " . $e->getMessage());
        return false;
    }
}

// Проверка состояния was_solved для пользователя
function get_was_solved_state($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT was_solved FROM user_games WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        
        return $result ? (bool)$result['was_solved'] : false;
    } catch (PDOException $e) {
        error_log("Error getting was_solved state: " . $e->getMessage());
        return false;
    }
}

// Функция для сохранения статистики пользователя
function save_user_stats($user_id, $stats) {
    global $pdo;
    
    try {
        // Проверяем, существует ли уже запись
        $stmt = $pdo->prepare("SELECT * FROM user_stats WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Обновляем существующую запись
            $stmt = $pdo->prepare("
                UPDATE user_stats 
                SET total_games = ?, games_won = ?, 
                    best_time_easy = ?, best_time_medium = ?, best_time_hard = ?,
                    updated_at = NOW()
                WHERE user_id = ?
            ");
            
            return $stmt->execute([
                $stats['totalGames'],
                $stats['gamesWon'],
                $stats['bestTimes']['easy'],
                $stats['bestTimes']['medium'],
                $stats['bestTimes']['hard'],
                $user_id
            ]);
        } else {
            // Создаем новую запись
            $stmt = $pdo->prepare("
                INSERT INTO user_stats 
                (user_id, total_games, games_won, best_time_easy, best_time_medium, best_time_hard, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            return $stmt->execute([
                $user_id,
                $stats['totalGames'],
                $stats['gamesWon'],
                $stats['bestTimes']['easy'],
                $stats['bestTimes']['medium'],
                $stats['bestTimes']['hard']
            ]);
        }
    } catch (PDOException $e) {
        error_log("Error saving user stats: " . $e->getMessage());
        return false;
    }
}

// Функция для получения статистики пользователя
function get_user_stats($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM user_stats WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($stats) {
            return [
                'totalGames' => (int)$stats['total_games'],
                'gamesWon' => (int)$stats['games_won'],
                'bestTimes' => [
                    'easy' => $stats['best_time_easy'] ? (int)$stats['best_time_easy'] : null,
                    'medium' => $stats['best_time_medium'] ? (int)$stats['best_time_medium'] : null,
                    'hard' => $stats['best_time_hard'] ? (int)$stats['best_time_hard'] : null
                ]
            ];
        }
        
        return null;
    } catch (PDOException $e) {
        error_log("Error getting user stats: " . $e->getMessage());
        return null;
    }
}

/**
 * Получение достижений пользователя
 */
function get_user_achievements($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT achievement_id as id, unlocked, unlocked_at as unlockedAt, progress 
            FROM user_achievements 
            WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);
        $userAchievements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Базовые достижения
        $defaultAchievements = [
            ['id' => 'first_win', 'name' => 'Привет, Плюк!', 'description' => 'Решите Ваше первое судоку', 'icon' => 'fa-meteor', 'color' => '#FFDD2D', 'rare' => false, 'progressMax' => 1],
            ['id' => 'no_mistakes', 'name' => 'Без ошибок', 'description' => 'Решите судоку без единой ошибки', 'icon' => 'fa-check-circle', 'color' => '#34C759', 'rare' => false, 'progressMax' => 1],
            ['id' => 'no_hints', 'name' => 'Без подсказок', 'description' => 'Решите судоку без использования подсказок', 'icon' => 'fa-lightbulb', 'color' => '#3366FF', 'rare' => false, 'progressMax' => 1],
            ['id' => 'perfectionist', 'name' => 'Последний выдох', 'description' => 'Решите судоку без ошибок и подсказок', 'icon' => 'fa-cloud-meatball', 'color' => '#AF52DE', 'rare' => false, 'progressMax' => 1],
            ['id' => 'speedster_easy', 'name' => 'Зелёные штаны', 'description' => 'Решите легкое судоку менее чем за 5 минут', 'icon' => 'fa-universal-access', 'color' => '#AF52DE', 'rare' => false, 'progressMax' => 300],
            ['id' => 'speedster_medium', 'name' => 'Сиреневые штаны', 'description' => 'Решите среднее судоку менее чем за 10 минут', 'icon' => 'fa-universal-access', 'color' => '#AF52DE', 'rare' => false, 'progressMax' => 600],
            ['id' => 'speedster_hard', 'name' => 'Жёлтые штаны', 'description' => 'Решите сложное судоку менее чем за 15 минут', 'icon' => 'fa-universal-access', 'color' => '#FF2D55', 'rare' => true, 'progressMax' => 900],
            ['id' => 'veteran', 'name' => 'Чатланин', 'description' => 'Решите 100 судоку за любое время', 'icon' => 'fa-user-tie', 'color' => '#5856D6', 'rare' => false, 'progressMax' => 100],
            ['id' => 'master', 'name' => 'Эцилопп', 'description' => 'Решите 500 судоку за любое время', 'icon' => 'fa-user-ninja', 'color' => '#FF9500', 'rare' => true, 'progressMax' => 500],
            ['id' => 'professional', 'name' => 'Господин ПЖ', 'description' => 'Решите 1000 судоку за любое время', 'icon' => 'fa-crown', 'color' => '#FF9500', 'rare' => true, 'progressMax' => 1000]
        ];
        
        // Объединяем данные
        $achievements = [];
        foreach ($defaultAchievements as $default) {
            $userData = array_filter($userAchievements, function($a) use ($default) {
                return $a['id'] === $default['id'];
            });
            
            if (!empty($userData)) {
                $userData = reset($userData);
                $achievements[] = array_merge($default, [
                    'unlocked' => (bool)$userData['unlocked'],
                    'unlockedAt' => $userData['unlockedAt'],
                    'progress' => (int)$userData['progress']
                ]);
            } else {
                $achievements[] = array_merge($default, [
                    'unlocked' => false,
                    'unlockedAt' => null,
                    'progress' => 0
                ]);
            }
        }
        
        return $achievements;
        
    } catch (PDOException $e) {
        error_log("Error getting user achievements: " . $e->getMessage());
        return null;
    }
}

/**
 * Сохранение достижений пользователя
 */
function save_user_achievements($user_id, $achievements) {
    global $pdo;
    
    try {
        // Удаляем старые достижения
        $stmt = $pdo->prepare("DELETE FROM user_achievements WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Сохраняем новые достижения
        $stmt = $pdo->prepare("
            INSERT INTO user_achievements (user_id, achievement_id, unlocked, unlocked_at, progress) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($achievements as $achievement) {
            $unlocked = isset($achievement['unlocked']) ? (int)$achievement['unlocked'] : 0;
            $unlockedAt = isset($achievement['unlockedAt']) ? $achievement['unlockedAt'] : null;
            $progress = isset($achievement['progress']) ? (int)$achievement['progress'] : 0;
            
            $stmt->execute([
                $user_id,
                $achievement['id'],
                $unlocked,
                $unlockedAt,
                $progress
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
?>