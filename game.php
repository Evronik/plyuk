<?php
// Включаем буферизацию вывода для предотвращения частичной загрузки
ob_start();
// Проверяем наличие новых достижений в cookie
$newAchievements = [];
if (isset($_COOKIE['sudoku_new_achievements'])) {
    $newAchievements = json_decode(urldecode($_COOKIE['sudoku_new_achievements']), true);
    if (!is_array($newAchievements)) {
        $newAchievements = [];
    }
}

ini_set('display_errors', 0); // Не показывать ошибки пользователям
ini_set('log_errors', 1); // Логировать ошибки
ini_set('error_log', __DIR__ . '/php-errors.log'); // Куда писать логи

require_once 'config.php';

// Проверяем авторизацию или гостевой режим
session_start();
$isGuest = !isset($_SESSION['user_id']);

if ($isGuest && !isset($_GET['guest'])) {
    header('Location: index.php');
    exit();
}

// ★★★ ОЧИСТКА ДАННЫХ АВТОРИЗОВАННОГО ПОЛЬЗОВАТЕЛЯ ПРИ ВХОДЕ В ГОСТЕВОЙ РЕЖИМ ★★★
if ($isGuest && isset($_GET['guest'])) {
    // Очищаем возможные остатки данных авторизованного пользователя из localStorage
    echo '<script>
        if (typeof localStorage !== "undefined") {
            // Удаляем данные авторизованного пользователя
            localStorage.removeItem("pluk_sudoku_stats");
            localStorage.removeItem("pluk_sudoku_achievements");
            localStorage.removeItem("pluk_sudoku_game");
            
            // Инициализируем чистые гостевые данные
            const defaultStats = {
                totalGames: 0,
                gamesWon: 0,
                totalPoints: 0,
                rating: 0,
                bestTimes: { easy: null, medium: null, hard: null }
            };
            
            const defaultAchievements = ' . json_encode(getDefaultAchievements()) . ';
            
            localStorage.setItem("pluk_sudoku_guest_stats", JSON.stringify(defaultStats));
            localStorage.setItem("pluk_sudoku_guest_achievements", JSON.stringify(defaultAchievements));
            console.log("✅ Гостевые данные инициализированы");
        }
    </script>';
}

// Получаем данные для таблицы лидеров
$leaderboard = [];
try {
    $stmt = $pdo->query("
    SELECT 
        u.username, 
        COALESCE(us.games_won, 0) as games_won,
        COALESCE(us.total_games, 0) as total_games,
        COALESCE(
            CASE 
                WHEN us.total_games > 0 THEN ROUND((us.games_won / us.total_games) * 100)
                ELSE 0 
            END, 0
        ) as win_rate,
        COALESCE(us.best_time_easy, 0) as best_time_easy,
        COALESCE(us.best_time_medium, 0) as best_time_medium,
        COALESCE(us.best_time_hard, 0) as best_time_hard,
        COALESCE(us.total_points, 0) as total_points
    FROM users u
    LEFT JOIN user_stats us ON u.id = us.user_id
    WHERE (us.games_won > 0 OR us.total_games > 0 OR us.total_points > 0)
      AND u.username IS NOT NULL
    ORDER BY 
        total_points DESC,
        us.games_won DESC, 
        win_rate DESC,
        us.total_games DESC
    LIMIT 100
");
    $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Leaderboard error: " . $e->getMessage());
    $leaderboard = [];
    
    // Покажите сообщение об ошибке для отладки
    echo "<!-- Leaderboard error: " . htmlspecialchars($e->getMessage()) . " -->";
}

// Обработка выхода
if (isset($_GET['logout'])) {
    // Удаляем все данные сессии
    $_SESSION = array();
    
    // Если требуется уничтожить сессию, также удаляем сессионные cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Уничтожаем сессию
    session_destroy();
    
    // Перенаправляем на главную страницу
    header('Location: index.php');
    exit();
}

// Получаем данные пользователя
if ($isGuest) {
    $user = [
        'username' => 'Гость',
        'guest' => true
    ];
} else {
    $user = get_current_user_data();
}

// Функции для работы с данными (гости vs авторизованные)
function saveGameData($data) {
    global $isGuest;
    
    if ($isGuest) {
        // Для гостей сохраняем только через JavaScript
        return true;
    } else {
        // Для авторизованных пользователей сохраняем в БД
        return save_user_game($_SESSION['user_id'], $data);
    }
}

function loadGameData() {
    global $isGuest;
    
    if ($isGuest) {
        // УПРОЩЕННАЯ ВЕРСИЯ - только для гостей
        // Для гостей вся логика загрузки будет в JavaScript
        return null;
    } else {
        // Для авторизованных пользователей загружаем с сервера
        try {
            $gameData = get_user_game($_SESSION['user_id']);
            if ($gameData && isset($gameData['game_data'])) {
                $data = json_decode($gameData['game_data'], true);
                // Добавляем информацию о проигрыше из БД
                $data['gameLost'] = isset($gameData['game_lost']) && $gameData['game_lost'];
                return $data;
            }
            return null;
        } catch (Exception $e) {
            error_log("Error loading game data: " . $e->getMessage());
            return null;
        }
    }
}

function saveStats($stats) {
    global $isGuest;
    
    if ($isGuest) {
        // Для гостей сохраняем в cookies с другим именем
        $statsData = json_encode(validateStats($stats));
        setcookie('sudoku_guest_stats', urlencode($statsData), time() + (365 * 24 * 60 * 60), '/'); // ИЗМЕНИТЬ НАЗВАНИЕ
    } else {
        // Для авторизованных пользователей сохраняем в БД
        save_user_stats($_SESSION['user_id'], validateStats($stats));
    }
}

function loadStats() {
    global $isGuest;
    
    if ($isGuest) {
        // Для гостей загружаем из localStorage через cookies
        if (isset($_COOKIE['sudoku_guest_stats'])) { // ИЗМЕНИТЬ НАЗВАНИЕ КУКИ
            $stats = json_decode(urldecode($_COOKIE['sudoku_guest_stats']), true);
            if ($stats && is_array($stats)) {
                return validateStats($stats);
            }
        }
        
        return validateStats([
            'totalGames' => 0,
            'gamesWon' => 0,
            'totalPoints' => 0,
            'rating' => 0,
            'bestTimes' => [
                'easy' => null,
                'medium' => null,
                'hard' => null
            ]
        ]);
    } else {
        // Для авторизованных пользователей загружаем из БД
        $stats = get_user_stats($_SESSION['user_id']);
        return validateStats($stats ?: [
            'totalGames' => 0,
            'gamesWon' => 0,
            'totalPoints' => 0,
            'rating' => 0,
            'bestTimes' => [
                'easy' => null,
                'medium' => null,
                'hard' => null
            ]
        ]);
    }
}

function saveAchievements($achievements) {
    global $isGuest;
    
    if ($isGuest) {
        return true;
    } else {
        // Для авторизованных пользователей сохраняем в БД
        return save_user_achievements($_SESSION['user_id'], $achievements);
    }
}

function loadAchievements() {
    global $isGuest, $pdo;
    
    if ($isGuest) {
        // Для гостей загружаем из localStorage через cookies с другим именем
        if (isset($_COOKIE['sudoku_guest_achievements'])) { // ИЗМЕНИТЬ НАЗВАНИЕ
            $achievements = json_decode(urldecode($_COOKIE['sudoku_guest_achievements']), true);
            if ($achievements && is_array($achievements)) {
                return $achievements;
            }
        }
        
        return getDefaultAchievements();
    } else {
        // Для авторизованных пользователей загружаем из БД
        $achievements = get_user_achievements($_SESSION['user_id']);
        if (!$achievements || !is_array($achievements)) {
            return getDefaultAchievements();
        }
        return $achievements;
    }
}

function getDefaultAchievements() {
    return [
        [
            'id' => 'first_win',
            'name' => 'Привет, Плюк!', 
            'description' => 'Решите Ваше первое судоку',
            'unlocked' => false,
            'icon' => 'fa-meteor',
            'color' => '#FFB800',
            'rare' => false,
            'progress' => 0,
            'progressMax' => 1,
            'points' => 5 // ★ Добавлено
        ],
        [
            'id' => 'no_mistakes',
            'name' => 'Без ошибок',
            'description' => 'Решите судоку без единой ошибки',
            'unlocked' => false,
            'icon' => 'fa-check-circle',
            'color' => '#fd4d00',
            'rare' => false,
            'progress' => 0,
            'progressMax' => 1,
            'points' => 2 // ★ Добавлено
        ],
        [
            'id' => 'no_hints',
            'name' => 'Без подсказок', 
            'description' => 'Решите судоку без использования подсказок',
            'unlocked' => false,
            'icon' => 'fa-lightbulb',
            'color' => '#c9a5df',
            'rare' => false,
            'progress' => 0,
            'progressMax' => 1,
            'points' => 2 // ★ Добавлено
        ],
        [
            'id' => 'perfectionist',
            'name' => 'Последний выдох',
            'description' => 'Решите судоку без ошибок и подсказок',
            'unlocked' => false,
            'icon' => 'fa-cloud-meatball',
            'color' => '#b2eaf5',
            'rare' => false,
            'progress' => 0,
            'progressMax' => 1,
            'points' => 5 // ★ Добавлено
        ],
        [
            'id' => 'speedster_easy',
            'name' => 'Зелёные штаны',
            'description' => 'Решите легкое судоку менее чем за 5 минут',
            'unlocked' => false,
            'icon' => 'fa-universal-access',
            'color' => '#52ff30',
            'rare' => false,
            'progress' => 0,
            'progressMax' => 300,
            'points' => 5 // ★ Добавлено
        ],
        [
            'id' => 'speedster_medium', 
            'name' => 'Сиреневые штаны',
            'description' => 'Решите среднее судоку менее чем за 10 минут',
            'unlocked' => false,
            'icon' => 'fa-universal-access',
            'color' => '#af52de',
            'rare' => false,
            'progress' => 0,
            'progressMax' => 600,
            'points' => 10 // ★ Добавлено
        ],
        [
            'id' => 'speedster_hard',
            'name' => 'Жёлтые штаны',
            'description' => 'Решите сложное судоку менее чем за 15 минут', 
            'unlocked' => false,
            'icon' => 'fa-universal-access',
            'color' => '#FFD700',
            'rare' => false,
            'progress' => 0,
            'progressMax' => 900,
            'points' => 15 // ★ Добавлено
        ],
        [
            'id' => 'veteran',
            'name' => 'Чатланин',
            'description' => 'Решите 100 судоку',
            'unlocked' => false,
            'icon' => 'fa-user-tie', 
            'color' => '#d5a582',
            'rare' => true,
            'progress' => 0,
            'progressMax' => 100,
            'points' => 100 // ★ Добавлено
        ],
        [
            'id' => 'master',
            'name' => 'Эцилопп',
            'description' => 'Решите 500 судоку',
            'unlocked' => false,
            'icon' => 'fa-user-ninja',
            'color' => '#af52de',
            'rare' => true,
            'progress' => 0,
            'progressMax' => 500,
            'points' => 500 // ★ Добавлено
        ],
        [
            'id' => 'professional',
            'name' => 'Господин ПЖ',
            'description' => 'Решите 1000 судоку',
            'unlocked' => false,
            'icon' => 'fa-crown',
            'color' => '#30dbff',
            'rare' => true,
            'progress' => 0,
            'progressMax' => 1000,
            'points' => 1000 // ★ Добавлено
        ]
    ];
}

// Функция для получения статистики по умолчанию
function getDefaultStats() {
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

// Функция для валидации и очистки статистики
function validateStats($stats) {
    if (!is_array($stats)) {
        return getDefaultStats();
    }
    
    // Базовые проверки
    $validated = [
        'totalGames' => max(0, intval($stats['totalGames'] ?? 0)),
        'gamesWon' => max(0, intval($stats['gamesWon'] ?? 0)),
        'totalPoints' => max(0, intval($stats['totalPoints'] ?? 0)),
        'rating' => max(0, intval($stats['rating'] ?? 0)),
        'bestTimes' => [
            'easy' => isset($stats['bestTimes']['easy']) ? max(0, intval($stats['bestTimes']['easy'])) : null,
            'medium' => isset($stats['bestTimes']['medium']) ? max(0, intval($stats['bestTimes']['medium'])) : null,
            'hard' => isset($stats['bestTimes']['hard']) ? max(0, intval($stats['bestTimes']['hard'])) : null
        ]
    ];
    
    // Проверка целостности: количество побед не может превышать общее количество игр
    if ($validated['gamesWon'] > $validated['totalGames']) {
        $validated['totalGames'] = max($validated['totalGames'], $validated['gamesWon']);
    }
    
    // Рейтинг должен равняться общим чатлам
    $validated['rating'] = $validated['totalPoints'];
    
    return $validated;
}

// Загружаем статистику для отображения на странице
$userStats = loadStats();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<style>
        /* Предотвращение FOUC (Flash of Unstyled Content) */
        .header {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .game-container {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        /* Скрываем элементы до полной загрузки */
        body.loading .header,
        body.loading .game-container {
            visibility: hidden;
        }
        
        body.loaded .header,
        body.loaded .game-container {
            opacity: 1;
            visibility: visible;
        }
        
        /* Гарантируем, что header всегда на месте */
        .header {
            position: relative;
            z-index: 1000;
            background: var(--header-bg, #fff);
            will-change: transform;
        }
        
        /* Предотвращаем смещение контента */
        .game-container {
            min-height: calc(100vh - 80px);
            position: relative;
        }
</style>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ПлюкСудоку</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/font-awesome/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="/css/game.css">
<link rel="stylesheet" href="/css/logo.css">
<link rel="stylesheet" href="/css/zvuk.css">
<link rel="stylesheet" href="/css/sgame.css">
<link rel="stylesheet" href="/css/style.css">
<link rel="stylesheet" href="/css/vvod.css">
<link rel="stylesheet" href="/css/lk.css">
<style>
/* Стили для результатов турниров */
.tournament-result-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 15px;
    border-left: 5px solid #ddd;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.tournament-result-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.result-gold {
    border-left-color: #FFD700;
    background: linear-gradient(135deg, #fff8e1, #ffffff);
}

.result-silver {
    border-left-color: #C0C0C0;
    background: linear-gradient(135deg, #f5f5f5, #ffffff);
}

.result-bronze {
    border-left-color: #CD7F32;
    background: linear-gradient(135deg, #f3e5d7, #ffffff);
}

.result-top10 {
    border-left-color: #4CAF50;
}

.result-other {
    border-left-color: #9E9E9E;
    opacity: 0.8;
}

.result-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.tournament-name {
    font-size: 18px;
    font-weight: 600;
    color: #333;
}

.result-date {
    font-size: 14px;
    color: #666;
}

.result-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.result-position {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 120px;
}

.position-text {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin-top: 5px;
    text-align: center;
}

.result-stats {
    flex: 1;
    margin-left: 30px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.stat-item i {
    width: 20px;
    color: #667eea;
}

/* Медали */
.medal-gold { color: #FFD700; }
.medal-silver { color: #C0C0C0; }
.medal-bronze { color: #CD7F32; }

/* Фильтры */
.results-filters-compact {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.filter-btn-compact {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    background: #f0f0f0;
    color: #666;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
}

.filter-btn-compact:hover {
    background: #e0e0e0;
}

.filter-btn-compact.active {
    background: #667eea;
    color: white;
}

/* Статистика */
.stats-grid-compact {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-item-compact {
    background: white;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.stat-label {
    font-size: 14px;
    color: #666;
    margin-bottom: 5px;
}

.stat-number {
    font-size: 24px;
    font-weight: 700;
    color: #667eea;
}

/* Адаптивность */
@media (max-width: 768px) {
    .result-details {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .result-position {
        flex-direction: row;
        margin-bottom: 15px;
        min-width: auto;
    }
    
    .position-text {
        margin-top: 0;
        margin-left: 10px;
    }
    
    .result-stats {
        margin-left: 0;
        width: 100%;
    }
    
    .stats-grid-compact {
        grid-template-columns: 1fr;
    }
}
</style>
</head>
<body class="loading">
<div class="container">
    <!-- ХЕДЕР - СОХРАНЯЕМ ВСЮ СУЩЕСТВУЮЩУЮ СТРУКТУРУ И ФУНКЦИОНАЛ -->
    <header class="header">
        <div class="header-content">
            <div class="logo-with-button">
                <div class="logo">
                    <a href="index.php" <?= $isGuest ? 'onclick="return handleGuestLogoClick(event)"' : '' ?>>
                        <svg viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 2.99976H29V17.5847C29 21.3236 27.0054 24.7786 23.7676 26.6483L14.5 31.9998L5.23246 26.6483C1.99458 24.7786 3.32377e-06 21.3236 3.32377e-06 17.5847L0 2.99976Z" fill="#FFDD2D"/>
                            <path xmlns="http://www.w3.org/2000/svg" d="M0 0 C5.28 0 10.56 0 16 0 C16 0.66 16 1.32 16 2 C15.34 2 14.68 2 14 2 C14 5.3 14 8.6 14 12 C14.66 12 15.32 12 16 12 C16 12.66 16 13.32 16 14 C13.69 14 11.38 14 9 14 C9 13.34 9 12.68 9 12 C9.66 12 10.32 12 11 12 C11 8.7 11 5.4 11 2 C9.02 2 7.04 2 5 2 C5 5.3 5 8.6 5 12 C5.66 12 6.32 12 7 12 C7 12.66 7 13.32 7 14 C4.69 14 2.38 14 0 14 C0 13.34 0 12.68 0 12 C0.66 12 1.32 12 2 12 C2 8.7 2 5.4 2 2 C1.34 2 0.68 2 0 2 C0 1.34 0 0.68 0 0 Z " fill="#333333" transform="translate(7,9)"/>
                        </svg>
                    </a>
                    <a href="index.php" class="logo-text" <?= $isGuest ? 'onclick="return handleGuestLogoClick(event)"' : '' ?>>
                        <h1>
                            <span class="word">Плюк<span class="superscript">s</span> </span>
                            <span class="word sudoku-animated">Судоку</span>
                        </h1>
                    </a>              
                </div>
                <!-- Контейнер с пользователем и рейтингом -->
                <div id="user-nick-btn" class="user-info-container">
    <div class="bad user-nick-btn">
        <span class="guest-badge"><?= htmlspecialchars($user['username']) ?> <i class="fa-solid fa-user-shield"></i>&nbsp; ТУРНИРЫ</span>
        <?php if($isGuest): ?><?php endif; ?>
    </div>
    <div class="user-rating">
        <i class="fa-solid fa-money-bill-1-wave fa-beat"></i>
        <span id="user-rating">0</span> чатлов
    </div>
</div>
                
                <div class="header-button">
                    <?php if($isGuest): ?>
                        <a href="login.php" class="btn btn-primary login-btn" style="text-decoration: none;">
                            <i class="fas fa-sign-in-alt"></i> Войти
                        </a>
                    <?php else: ?>
                        <a href="logout.php" class="btn btn-danger logout-btn" style="text-decoration: none;">
                            <i class="fas fa-sign-out-alt"></i> Выход
                        </a>
                    <?php endif; ?>
                    
                    <!-- Кнопка Правила - БЕЗ ИЗМЕНЕНИЙ -->
                    <div class="rules-button-container">
                        <button id="instructions-btn" class="btn btn-info instructions-btn" title="Правила игры">
                            <i class="fas fa-book"></i> Правила
                        </button>
                    </div>
                    
                </div>
            </div>
        </div>
    </header>
    <main class="game-container">
        <!-- Весь существующий игровой контент остается БЕЗ ИЗМЕНЕНИЙ -->
        <div class="action-buttons">
            <button id="leaderboard-btn" class="btn btn-secondary">
                <i class="fas fa-crown"></i> Лидеры
            </button>
            <button id="stats-btn" class="btn btn-secondary">
                <i class="fas fa-chart-bar"></i> Статистика
            </button>
            <button id="achievements-btn" class="btn btn-secondary">
                <i class="fas fa-trophy"></i> Достижения
            </button>
        </div>
        
        <div class="difficulty-selector">
            <button id="easy-btn" class="difficulty-btn active">
               Легкий +5 <i class="fa-solid fa-money-bill-1-wave"></i><br>за 10 мин.
            </button>
            <button id="medium-btn" class="difficulty-btn">
               Средний +10 <i class="fa-solid fa-money-bill-1-wave"></i><br>за 15 мин.
            </button>
            <button id="hard-btn" class="difficulty-btn">
               Трудный +20 <i class="fa-solid fa-money-bill-1-wave"></i><br>за 20 мин.
            </button>
        </div>
    
<div class="game-layout">
    <!-- Панель статистики слева -->
    <div class="stats-panel-left">
        <div class="stat-item-left">
            <div class="stat-value-left">
                <i class="fas fa-clock"></i> <span id="timer">00:00</span>
            </div>
            <div class="stat-label-left">Время</div>
        </div>
        <div class="stat-item-left">
            <div class="stat-value-left">
                <i class="fas fa-times-circle"></i> <span id="mistakes">0/3</span>
            </div>
            <div class="stat-label-left">Ошибки</div>
        </div>
        <div class="stat-item-left">
            <div class="stat-value-left">
                <i class="fas fa-lightbulb"></i> <span id="hints-counter">0/3</span>
            </div>
            <div class="stat-label-left">Подсказки</div>
        </div>
    </div>
    
    <!-- Центральная часть с игровым полем -->
    <div class="game-content">
        <div class="game-board" id="board"></div>
        
        <!-- Мобильная версия кнопок ввода (только для мобильных) -->
        <div class="number-pad-mobile">
            <button class="number-btn" data-number="1">1</button>
            <button class="number-btn" data-number="2">2</button>
            <button class="number-btn" data-number="3">3</button>
            <button class="number-btn" data-number="4">4</button>
            <button class="number-btn" data-number="5">5</button>
            <button class="number-btn" data-number="6">6</button>
            <button class="number-btn" data-number="7">7</button>
            <button class="number-btn" data-number="8">8</button>
            <button class="number-btn" data-number="9">9</button>
            <button class="number-btn erase" data-number="0">
                <i class="fas fa-eraser"></i>
            </button>
        </div>
        
        <div class="game-bottom-section">
            <div class="controls">
                <button id="new-game-btn" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Новая игра
                </button>
                <button id="hint-btn" class="btn btn-warning">
                    <i class="fas fa-lightbulb"></i> Подсказка
                    <span class="badge badge-danger" id="hint-badge">0/3</span>
                </button>
                <button id="check-btn" class="btn btn-secondary-error">
                    <i class="fas fa-check-circle"></i> Проверить
                </button>
                <button id="solve-btn" class="btn btn-danger">
                    <i class="fas fa-question-circle"></i> Решить
                </button>
            </div>
        </div>            
            <!-- Контролы звука и фона -->
            <div class="game-bottom-controls">
                <div class="control-group left-controls">
                    <!-- Сюда добавится кнопка смены фона -->
                </div>
                <div class="control-group right-controls">
                    <!-- Существующие контролы -->
                    <div class="sound-control game-sound-control" id="soundControl">
                        <span class="sound-icon">♪</span>
                        <div class="volume-control">
                            <div class="volume-slider-container">
                                <span class="volume-icon">🔊</span>
                                <input type="range" 
                                       class="volume-slider" 
                                       id="volumeSlider"
                                       min="0" 
                                       max="100" 
                                       value="40">
                            </div>
                            <div class="volume-value" id="volumeValue">40%</div>
                        </div>
                    </div>
                </div>
            </div>
        
    </div>
    
    <!-- Панель цифровых кнопок справа (только для десктопа) -->
    <div class="number-pad-right">
        <div class="number-pad">
            <button class="number-btn" data-number="1">1</button>
            <button class="number-btn" data-number="2">2</button>
            <button class="number-btn" data-number="3">3</button>
            <button class="number-btn" data-number="4">4</button>
            <button class="number-btn" data-number="5">5</button>
            <button class="number-btn" data-number="6">6</button>
            <button class="number-btn" data-number="7">7</button>
            <button class="number-btn" data-number="8">8</button>
            <button class="number-btn" data-number="9">9</button>
            <button class="number-btn erase" data-number="0">
                <i class="fas fa-eraser"></i>
            </button>
        </div>
    </div>
</div>
    </main>
    </div>
    
    <!-- Модальное окно таблицы лидеров -->
<div class="modal leaderboard-modal" id="leaderboard-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="fas fa-crown"></i> Таблица лидеров
                <button id="refresh-leaderboard" class="refresh-btn" title="Обновить">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </h2>
            <button class="modal-close" id="close-leaderboard-modal"><i class="fas fa-times"></i></button>
        </div>
        
        <div class="modal-body">
            <div class="leaderboard-search">
                <div class="search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" id="leaderboard-search" placeholder="Поиск игроков...">
                    <button id="clear-search" class="clear-search-btn" style="display: none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <div id="leaderboard-content">
                <?php if (!empty($leaderboard)): ?>
                <div class="leaderboard-container">
                    <table class="leaderboard-table">
                        <thead>
                            <tr>
                                <th class="leaderboard-rank">#</th>
                                <th>Игрок</th>
                                <th class="leaderboard-stats">Рейтинг</th>
                                <th class="leaderboard-stats">Победы</th>
                                <th class="leaderboard-stats">Процент</th>
                                <th class="leaderboard-stats">Время</th>
                            </tr>
                        </thead>
                        <tbody id="leaderboard-body">
                            <?php 
                            // Разбиваем на группы (исключая текущего пользователя)
                            $otherPlayers = [];
                            $currentUserData = null;
                            
                            foreach ($leaderboard as $player) {
                                if ($player['username'] === $user['username']) {
                                    $currentUserData = $player;
                                } else {
                                    $otherPlayers[] = $player;
                                }
                            }
                            
                            $goldGroup = array_slice($otherPlayers, 0, 10);
                            $silverGroup = array_slice($otherPlayers, 10, 10);
                            $bronzeGroup = array_slice($otherPlayers, 20, 10);
                            $remainingPlayers = array_slice($otherPlayers, 30);
                            
                            // Функция для форматирования времени
                            function formatBestTime($time) {
                                if (!$time || $time == 0) return '-';
                                $mins = floor($time / 60);
                                $secs = $time % 60;
                                return "{$mins}:" . sprintf('%02d', $secs);
                            }
                            ?>
                            
                            <!-- Золотая группа -->
                            <?php if (!empty($goldGroup)): ?>
                            <tr class="group-header gold-group">
                                <td colspan="6">
                                    <div class="group-title">
                                        <i class="fas fa-trophy medal-gold"></i>
                                        Золотые призёры (Топ-10)
                                    </div>
                                </td>
                            </tr>
                            <?php foreach ($goldGroup as $index => $player): ?>
                            <tr class="gold-group-row">
                                <td class="leaderboard-rank">
                                    <?php if ($index == 0): ?>
                                        <i class="fas fa-trophy medal-gold"></i>
                                    <?php elseif ($index == 1): ?>
                                        <i class="fas fa-trophy medal-silver"></i>
                                    <?php elseif ($index == 2): ?>
                                        <i class="fas fa-trophy medal-bronze"></i>
                                    <?php else: ?>
                                        <?= $index + 1 ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="leaderboard-user">
                                        <div class="leaderboard-avatar">
                                            <?= mb_substr($player['username'], 0, 1, 'UTF-8') ?>
                                        </div>
                                        <?= htmlspecialchars($player['username']) ?>
                                    </div>
                                </td>
                                <td class="leaderboard-stats">
                                    <span class="leaderboard-rating"><?= $player['total_points'] ?></span>
                                </td>
                                <td class="leaderboard-stats">
                                    <span class="leaderboard-wins"><?= $player['games_won'] ?></span>
                                </td>
                                <td class="leaderboard-stats">
                                    <span class="leaderboard-rate"><?= round($player['win_rate']) ?>%</span>
                                </td>
                                <td class="leaderboard-stats">
                                    <div class="leaderboard-time">
                                        <?php if ($player['best_time_easy'] && $player['best_time_easy'] > 0): ?>
                                            Л: <?= formatBestTime($player['best_time_easy']) ?>
                                            <?php if ($player['best_time_medium'] > 0 || $player['best_time_hard'] > 0): ?><br><?php endif; ?>
                                        <?php endif; ?>
                                        <?php if ($player['best_time_medium'] && $player['best_time_medium'] > 0): ?>
                                            С: <?= formatBestTime($player['best_time_medium']) ?>
                                            <?php if ($player['best_time_hard'] > 0): ?><br><?php endif; ?>
                                        <?php endif; ?>
                                        <?php if ($player['best_time_hard'] && $player['best_time_hard'] > 0): ?>
                                            Т: <?= formatBestTime($player['best_time_hard']) ?>
                                        <?php endif; ?>
                                        <?php if (!$player['best_time_easy'] && !$player['best_time_medium'] && !$player['best_time_hard']): ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <!-- Серебряная группа -->
                            <?php if (!empty($silverGroup)): ?>
                            <tr class="group-header silver-group">
                                <td colspan="6">
                                    <div class="group-title">
                                        <i class="fas fa-trophy medal-silver"></i>
                                        Серебряные призёры (11-20)
                                    </div>
                                </td>
                            </tr>
                            <?php foreach ($silverGroup as $index => $player): ?>
                            <?php $globalIndex = 10 + $index; ?>
                            <tr class="silver-group-row">
                                <td class="leaderboard-rank"><?= $globalIndex + 1 ?></td>
                                <td>
                                    <div class="leaderboard-user">
                                        <div class="leaderboard-avatar">
                                            <?= mb_substr($player['username'], 0, 1, 'UTF-8') ?>
                                        </div>
                                        <?= htmlspecialchars($player['username']) ?>
                                    </div>
                                </td>
                                <td class="leaderboard-stats">
                                    <span class="leaderboard-rating"><?= $player['total_points'] ?></span>
                                </td>
                                <td class="leaderboard-stats">
                                    <span class="leaderboard-wins"><?= $player['games_won'] ?></span>
                                </td>
                                <td class="leaderboard-stats">
                                    <span class="leaderboard-rate"><?= round($player['win_rate']) ?>%</span>
                                </td>
                                <td class="leaderboard-stats">
                                    <div class="leaderboard-time">
                                        <?php if ($player['best_time_easy'] && $player['best_time_easy'] > 0): ?>
                                            Л: <?= formatBestTime($player['best_time_easy']) ?>
                                            <?php if ($player['best_time_medium'] > 0 || $player['best_time_hard'] > 0): ?><br><?php endif; ?>
                                        <?php endif; ?>
                                        <?php if ($player['best_time_medium'] && $player['best_time_medium'] > 0): ?>
                                            С: <?= formatBestTime($player['best_time_medium']) ?>
                                            <?php if ($player['best_time_hard'] > 0): ?><br><?php endif; ?>
                                        <?php endif; ?>
                                        <?php if ($player['best_time_hard'] && $player['best_time_hard'] > 0): ?>
                                            Т: <?= formatBestTime($player['best_time_hard']) ?>
                                        <?php endif; ?>
                                        <?php if (!$player['best_time_easy'] && !$player['best_time_medium'] && !$player['best_time_hard']): ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <!-- Бронзовая группа -->
                            <?php if (!empty($bronzeGroup)): ?>
                            <tr class="group-header bronze-group">
                                <td colspan="6">
                                    <div class="group-title">
                                        <i class="fas fa-trophy medal-bronze"></i>
                                        Бронзовые призёры (21-30)
                                    </div>
                                </td>
                            </tr>
                            <?php foreach ($bronzeGroup as $index => $player): ?>
                            <?php $globalIndex = 20 + $index; ?>
                            <tr class="bronze-group-row">
                                <td class="leaderboard-rank"><?= $globalIndex + 1 ?></td>
                                <td>
                                    <div class="leaderboard-user">
                                        <div class="leaderboard-avatar">
                                            <?= mb_substr($player['username'], 0, 1, 'UTF-8') ?>
                                        </div>
                                        <?= htmlspecialchars($player['username']) ?>
                                    </div>
                                </td>
                                <td class="leaderboard-stats">
                                    <span class="leaderboard-rating"><?= $player['total_points'] ?></span>
                                </td>
                                <td class="leaderboard-stats">
                                    <span class="leaderboard-wins"><?= $player['games_won'] ?></span>
                                </td>
                                <td class="leaderboard-stats">
                                    <span class="leaderboard-rate"><?= round($player['win_rate']) ?>%</span>
                                </td>
                                <td class="leaderboard-stats">
                                    <div class="leaderboard-time">
                                        <?php if ($player['best_time_easy'] && $player['best_time_easy'] > 0): ?>
                                            Л: <?= formatBestTime($player['best_time_easy']) ?>
                                            <?php if ($player['best_time_medium'] > 0 || $player['best_time_hard'] > 0): ?><br><?php endif; ?>
                                        <?php endif; ?>
                                        <?php if ($player['best_time_medium'] && $player['best_time_medium'] > 0): ?>
                                            С: <?= formatBestTime($player['best_time_medium']) ?>
                                            <?php if ($player['best_time_hard'] > 0): ?><br><?php endif; ?>
                                        <?php endif; ?>
                                        <?php if ($player['best_time_hard'] && $player['best_time_hard'] > 0): ?>
                                            Т: <?= formatBestTime($player['best_time_hard']) ?>
                                        <?php endif; ?>
                                        <?php if (!$player['best_time_easy'] && !$player['best_time_medium'] && !$player['best_time_hard']): ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <!-- Остальные игроки -->
                            <?php if (!empty($remainingPlayers)): ?>
                            <tr class="group-header other-group">
                                <td colspan="6">
                                    <div class="group-title">
                                        <i class="fas fa-users"></i>
                                        Остальные игроки
                                    </div>
                                </td>
                            </tr>
                            <?php foreach ($remainingPlayers as $index => $player): ?>
                            <?php $globalIndex = 30 + $index; ?>
                            <tr class="other-group-row">
                                <td class="leaderboard-rank"><?= $globalIndex + 1 ?></td>
                                <td>
                                    <div class="leaderboard-user">
                                        <div class="leaderboard-avatar">
                                            <?= mb_substr($player['username'], 0, 1, 'UTF-8') ?>
                                        </div>
                                        <?= htmlspecialchars($player['username']) ?>
                                    </div>
                                </td>
                                <td class="leaderboard-stats">
                                    <span class="leaderboard-rating"><?= $player['total_points'] ?></span>
                                </td>
                                <td class="leaderboard-stats">
                                    <span class="leaderboard-wins"><?= $player['games_won'] ?></span>
                                </td>
                                <td class="leaderboard-stats">
                                    <span class="leaderboard-rate"><?= round($player['win_rate']) ?>%</span>
                                </td>
                                <td class="leaderboard-stats">
                                    <div class="leaderboard-time">
                                        <?php if ($player['best_time_easy'] && $player['best_time_easy'] > 0): ?>
                                            Л: <?= formatBestTime($player['best_time_easy']) ?>
                                            <?php if ($player['best_time_medium'] > 0 || $player['best_time_hard'] > 0): ?><br><?php endif; ?>
                                        <?php endif; ?>
                                        <?php if ($player['best_time_medium'] && $player['best_time_medium'] > 0): ?>
                                            С: <?= formatBestTime($player['best_time_medium']) ?>
                                            <?php if ($player['best_time_hard'] > 0): ?><br><?php endif; ?>
                                        <?php endif; ?>
                                        <?php if ($player['best_time_hard'] && $player['best_time_hard'] > 0): ?>
                                            Т: <?= formatBestTime($player['best_time_hard']) ?>
                                        <?php endif; ?>
                                        <?php if (!$player['best_time_easy'] && !$player['best_time_medium'] && !$player['best_time_hard']): ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>Пока пусто</h3>
                    <p>Станьте первым в таблице лидеров!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="modal-footer">
            <div class="leaderboard-info">
                <span>Обновлено: <?= date('H:i') ?></span>
                <?php if (isset($userPosition)): ?>
                <span class="user-position">Ваша позиция: <?= $userPosition ?> из <?= count($leaderboard) ?></span>
                <?php endif; ?>
            </div>
            <button class="btn btn-primary" id="close-leaderboard-btn">
                <i class="fas fa-check"></i> Закрыть
            </button>
        </div>
    </div>
</div>
    
   <!-- Модальное окно статистики -->
<div class="modal stats-modal" id="stats-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="fas fa-chart-bar"></i> Ваша статистика
            </h2>
            <button class="modal-close" id="close-stats-modal"><i class="fas fa-times"></i></button>
        </div>
        
        <div class="modal-body">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <div class="stat-value" id="total-games">0</div>
                    <div class="stat-label">Завершено игр</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-value" id="games-won">0</div>
                    <div class="stat-label">Побед</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-value" id="win-rate">0%</div>
                    <div class="stat-label">Процент побед</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-money-bill-1-wave"></i>
                    </div>
                    <div class="stat-value" id="total-points">0</div>
                    <div class="stat-label">Всего чатлов</div>
                </div>
            </div>
            
            <div class="section-title">
                <i class="fas fa-stopwatch"></i> Лучшее время
            </div>
            
            <div class="time-stats">
                <div class="time-stat">
                    <div class="difficulty-badge easy">
                        <i class="fas fa-smile"></i> Легкий
                    </div>
                    <div class="time-value" id="best-time-easy">--:--</div>
                </div>
                
                <div class="time-stat">
                    <div class="difficulty-badge medium">
                        <i class="fas fa-meh"></i> Средний
                    </div>
                    <div class="time-value" id="best-time-medium">--:--</div>
                </div>
                
                <div class="time-stat">
                    <div class="difficulty-badge hard">
                        <i class="fas fa-frown"></i> Трудный
                    </div>
                    <div class="time-value" id="best-time-hard">--:--</div>
                </div>
            </div>
            
            <?php if($isGuest): ?>
            <div class="guest-info">
                <i class="fas fa-info-circle"></i>
                <strong>Гостевой режим</strong>
                <p>Статистика не сохраняется на сервере!<br> Ваши победы не участвуют в игровом рейтинге!</p>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="modal-footer">
            <button class="btn btn-primary" id="close-stats-btn">
                <i class="fas fa-check"></i> Закрыть
            </button>
        </div>
    </div>
</div>
    
    <!-- Модальное окно достижений -->
    <div class="modal achievements-modal" id="achievements-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="fas fa-trophy"></i> Ваши достижения
                <span class="achievements-count" id="achievements-count">0/9</span>
            </h2>
            <button class="modal-close" id="close-achievements-modal"><i class="fas fa-times"></i></button>
        </div>
        
        <div class="modal-body">
            <div class="achievements-progress">
                <div class="progress-bar">
                    <div class="progress-fill" id="achievements-progress"></div>
                </div>
                <div class="progress-text">Завершено: <span id="progress-percent">0%</span></div>
            </div>
            
            <div class="achievements-filter">
                <button class="filter-btn active" data-filter="all">Все</button>
                <button class="filter-btn" data-filter="unlocked">Полученные</button>
                <button class="filter-btn" data-filter="locked">Не полученные</button>
            </div>
            
            <div class="achievements-grid" id="achievements-container">
                <!-- Достижения будут добавлены через JavaScript -->
            </div>
        </div>
        
        <div class="modal-footer">
            <div class="achievements-stats">
                <div class="stat">
                    <i class="fas fa-trophy"></i>
                    <span id="total-achievements">0</span> достижений
                </div>
                <div class="stat">
                    <i class="fas fa-medal"></i>
                    <span id="rare-achievements">0</span> редких
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Модальное окно победы -->
<div class="modal win-modal" id="win-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="fas fa-trophy"></i> Поздравляем!
            </h2>
            <!-- Добавляем кнопку закрытия -->
            <button class="modal-close" id="close-win-modal"><i class="fas fa-times"></i></button>
        </div>
        
        <div class="modal-body">
            <div class="win-celebration">
                <div class="confetti"></div>
                <div class="confetti"></div>
                <div class="confetti"></div>
            </div>
            
            <p class="win-message">Вы успешно решили судоку!</p>
            
            <div class="win-stats-grid">
                <div class="win-stat">
                    <div class="win-stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="win-stat-value" id="win-time">00:00</div>
                    <div class="win-stat-label">Время</div>
                </div>
                
                <div class="win-stat">
                    <div class="win-stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="win-stat-value" id="win-mistakes">0/3</div> <!-- ОБНОВЛЕНО: формат 0/3 -->
                    <div class="win-stat-label">Ошибки</div>
                </div>
                
                <div class="win-stat">
                    <div class="win-stat-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <div class="win-stat-value" id="win-hints">0/3</div>
                    <div class="win-stat-label">Подсказки</div>
                </div>
            </div>
            
            <div id="new-achievements-container" style="display: none;">
                <div class="section-title">
                    <i class="fas fa-medal"></i> Новые достижения
                </div>
                <div id="new-achievements-list">
                    <!-- Новые достижения будут здесь -->
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button class="btn btn-secondary-warning" id="cancel-solve">
                <i class="fas fa-times"></i> Назад
            </button>
            <button class="btn btn-primary-warning" id="new-game-win-btn">
                <i class="fas fa-check"></i> Новая игра
            </button>
        </div>
    </div>
</div>
    
    <!-- Модальное окно предупреждения о смене сложности -->
<div class="modal" id="difficulty-warning-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="fas fa-exclamation-triangle"></i> Смена уровня сложности!
            </h2>
            <button class="modal-close" id="close-difficulty-warning-modal"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p style="margin-bottom: 10px;">Вы собираетесь изменить уровень сложности во время активной игры!</p>
            <p>Это приведет к завершению текущей игры с автоматическим решением и уменьшению Вашего общего процента побед.</p>
            <p>Хотите сменить сложность?</p>
            <br>
            <p style="font-size: 12px;"><em>* Для смены сложности без потери процента побед, решите самостоятельно текущую игру</em></p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary-warning" id="cancel-difficulty-change">
                <i class="fas fa-times"></i> Отмена
            </button>
            <button class="btn btn-primary-warning" id="confirm-difficulty-change">
                <i class="fas fa-check"></i> Сменить сложность
            </button>
        </div>
    </div>
</div>
    
<!-- Модальное окно подтверждения новой игры -->
<div class="modal confirm-modal" id="new-game-confirm-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="fas fa-exclamation-triangle"></i> Начало новой игры!
            </h2>
            <button class="modal-close" id="close-new-game-confirm-modal"><i class="fas fa-times"></i></button>
        </div>
        
        <div class="modal-body">
            <p style="margin-bottom: 10px;">Вы собираетесь начать новую игру во время активной!</p>
            <p>Это приведет к завершению текущей игры с автоматическим решением и уменьшению Вашего общего процента побед.</p>
            <p>Хотите начать новую игру?</p>
            <br>
            <p style="font-size: 12px;"><em>* Для начала новой игры без потери процента побед, решите самостоятельно текущую игру</em></p>
        </div>
        
        <div class="modal-footer">
            <button class="btn btn-secondary-warning" id="cancel-new-game">
                <i class="fas fa-times"></i> Отмена
            </button>
            <button class="btn btn-primary-warning" id="confirm-new-game">
                <i class="fas fa-check"></i> Новая игра
            </button>
        </div>
    </div>
</div>

<!-- Модальное окно для перехода на главную страницу -->
<div class="modal lose-modal" id="homepage-warning-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="fas fa-exclamation-triangle"></i> Переход на главную страницу!
            </h2>
            <button class="modal-close" id="close-homepage-modal"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p style="margin-bottom: 10px;">Вы собираетесь перейти на главную страницу во время активной игры!</p>
            <p>Это приведет к завершению текущей игры с автоматическим решением и уменьшению Вашего общего процента побед.</p>
            <p>Хотите продолжить?</p>
            <br>
            <p style="font-size: 12px;"><em>* Для перехода без потери процента побед, решите самостоятельно текущую игру</em></p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary-warning" id="cancel-homepage">
                <i class="fas fa-times"></i> Отмена
            </button>
            <button class="btn btn-primary-warning" id="confirm-homepage">
                <i class="fas fa-check"></i> Перейти
            </button>
        </div>
    </div>
</div>

<!-- Модальное окно для перехода на страницу авторизации -->
<div class="modal lose-modal" id="login-warning-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="fas fa-exclamation-triangle"></i> Переход на страницу авторизации!
            </h2>
            <button class="modal-close" id="close-login-modal"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p style="margin-bottom: 10px;">Вы собираетесь перейти на страницу авторизации во время активной игры!</p>
            <p>Это приведет к завершению текущей игры с автоматическим решением и уменьшению Вашего общего процента побед.</p>
            <p>Хотите продолжить?</p>
            <br>
            <p style="font-size: 12px;"><em>* Для перехода без потери процента побед, решите самостоятельно текущую игру</em></p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary-warning" id="cancel-login">
                <i class="fas fa-times"></i> Отмена
            </button>
            <button class="btn btn-primary-warning" id="confirm-login">
                <i class="fas fa-check"></i> Перейти
            </button>
        </div>
    </div>
</div>

<!-- Модальное окно для решения игры -->
<div class="modal lose-modal" id="solve-warning-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="fas fa-exclamation-triangle"></i> Автоматическое решение игры!
            </h2>
            <button class="modal-close" id="close-solve-modal"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p style="margin-bottom: 10px;">Вы собираетесь автоматически решить головоломку!</p>
            <p>Это приведет к завершению текущей игры с автоматическим решением и уменьшению Вашего общего процента побед.</p>
            <p>Хотите продолжить?</p>
            <br>
            <p style="font-size: 12px;"><em>* Для решения без потери процента побед, решите самостоятельно текущую игру</em></p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary-warning" id="cancel-sol">
                <i class="fas fa-times"></i> Отмена
            </button>
            <button class="btn btn-primary-warning" id="confirm-solve">
                <i class="fas fa-check"></i> Решить
            </button>
        </div>
    </div>
</div>

<!-- Модальное окно для выхода из системы -->
<div class="modal lose-modal" id="logout-warning-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="fas fa-exclamation-triangle"></i> Выход из игры!
            </h2>
            <button class="modal-close" id="close-logout-modal"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p style="margin-bottom: 10px;">Вы собираетесь выйти во время активной игры!</p>
            <p>Это приведет к завершению текущей игры с автоматическим решением и уменьшению Вашего общего процента побед.</p>
            <p>Хотите продолжить?</p>
            <br>
            <p style="font-size: 12px;"><em>* Для выхода без потери процента побед, решите самостоятельно текущую игру</em></p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary-warning" id="cancel-logout">
                <i class="fas fa-times"></i> Отмена
            </button>
            <button class="btn btn-primary-warning" id="confirm-logout">
                <i class="fas fa-check"></i> Выйти
            </button>
        </div>
    </div>
</div>

<!-- Модальное окно проигрыша при 3 ошибках -->
<div class="modal lose-game-modal" id="lose-game-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="fas fa-bolt"></i> Вы проиграли!
            </h2>
            <button class="modal-close" id="close-lose-game-modal"><i class="fas fa-times"></i></button>
        </div>
        
        <div class="modal-body">
            <div class="lose-message">
                <i class="fas fa-sad-tear"></i>
                <h3>Вы допустили 3 ошибки!</h3>
                <p>Игра завершена. Вот решение головоломки:</p>
            </div>
            
            <div class="solved-board-container">
                <div class="solved-board" id="solved-board"></div>
            </div>
            
            <div class="game-stats-summary">
                <div class="stat-summary">
                    <i class="fas fa-clock"></i>
                    <span>Время: <strong id="lose-time">00:00</strong></span>
                </div>
                <div class="stat-summary">
                    <i class="fas fa-times-circle"></i>
                    <span>Ошибки: <strong id="lose-mistakes">0/3</strong></span> <!-- ОБНОВЛЕНО: формат 0/3 -->
                </div>
                <div class="stat-summary">
                    <i class="fas fa-lightbulb"></i>
                    <span>Подсказки: <strong id="lose-hints">0/3</strong></span>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button class="btn btn-secondary-war" id="fill-board-btn">
                <i class="fas fa-edit"></i> Посмотреть решение
            </button>
            <button class="btn btn-secondary-err" id="new-game-after-lose-btn">
                <i class="fas fa-check"></i> Новая игра
            </button>
        </div>
    </div>
</div>

<!-- Модальное окно инструкции -->
<div class="modal instructions-modal" id="instructions-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="fas fa-book"></i> Правила игры и начисления чатлов
            </h2>
            <button class="modal-close" id="close-instructions-modal"><i class="fas fa-times"></i></button>
        </div>
        
        <div class="modal-body">
            <div class="instructions-content">
                <!-- Раздел 1: Основные правила -->
                <div class="instruction-section">
                    <h3><i class="fas fa-puzzle-piece"></i> Основные правила Судоку</h3>
                    <div class="instruction-text">
                        <p><strong>Цель игры:</strong> Заполнить сетку 9×9 цифрами от 1 до 9 так, чтобы:</p>
                        <ul>
                            <li>Каждая строка содержала все цифры от 1 до 9 без повторений</li>
                            <li>Каждый столбец содержал все цифры от 1 до 9 без повторений</li>
                            <li>Каждый блок 3×3 содержал все цифры от 1 до 9 без повторений</li>
                        </ul>
                    </div>
                </div>

                <!-- Раздел 2: Управление игрой -->
                <div class="instruction-section">
                    <h3><i class="fas fa-gamepad"></i> Управление игрой</h3>
                    <div class="instruction-text">
                        <div class="control-item">
                            <span class="control-icon"><i class="fas fa-mouse-pointer"></i></span>
                            <span class="control-desc"><strong>Выбор ячейки:</strong> Кликните по пустой ячейке</span>
                        </div>
                        <div class="control-item">
                            <span class="control-icon"><i class="fas fa-keyboard"></i></span>
                            <span class="control-desc"><strong>Ввод цифр:</strong> Нажмите цифру 1-9 на клавиатуре или панели</span>
                        </div>
                        <div class="control-item">
                            <span class="control-icon"><i class="fas fa-eraser"></i></span>
                            <span class="control-desc"><strong>Очистка:</strong> Кнопка с ластиком или клавиша 0/Backspace</span>
                        </div>
                        <div class="control-item">
                            <span class="control-icon"><i class="fas fa-lightbulb"></i></span>
                            <span class="control-desc"><strong>Подсказка:</strong> До 3 подсказок за игру</span>
                        </div>
                    </div>
                </div>

                <!-- Раздел 3: Система начисления чатлов -->
                <div class="instruction-section">
                    <h3><i class="fas fa-star"></i> Система начисления чатлов</h3>
                    <div class="points-system">
                        <!-- Базовые начисления чатлов за сложность -->
                        <div class="points-category">
                            <h4><i class="fas fa-layer-group"></i> Чатлы за сложность уровня:</h4>
                            <div class="points-grid">
                                <div class="points-item">
                                    <span class="time-badge"><i class="fa-alarm-clock"></i> "Легкий" - (выдается всегда)</span>
                                    <span class="points-value">5 чатлов</span>
                                </div>
                                <div class="points-item">
                                    <span class="time-badge">"Средний" - (выдается всегда)</span>
                                    <span class="points-value">10 чатлов</span>
                                </div>
                                <div class="points-item">
                                    <span class="time-badge">"Трудный" - (выдается всегда)</span>
                                    <span class="points-value">20 чатлов</span>
                                </div>
                            </div>
                        </div>

                        <!-- Чатлы за скорость -->
                        <div class="points-category">
                            <h4><i class="fas fa-bolt"></i> Чатлы за скорость в уровне:</h4>
                            <div class="points-grid">
                                <div class="points-item">
                                    <span class="time-badge"><i class="fas fa-bolt" style="color: #FFD700;"></i> "Менее 5 минут (Легкий)" - (выдается всегда)</span>
                                    <span class="points-value">5 чатлов</span>
                                </div>
                                <div class="points-item">
                                    <span class="time-badge"><i class="fas fa-bolt" style="color: #72e50c;"></i> "Менее 10 минут (Средний)" - (выдается всегда)</span>
                                    <span class="points-value">10 чатлов</span>
                                </div>
                                <div class="points-item">
                                    <span class="time-badge"><i class="fas fa-bolt" style="color: #f74318;"></i> "Менее 15 минут (Трудный)" - (выдается всегда)</span>
                                    <span class="points-value">15 чатлов</span>
                                </div>
                            </div>
                        </div>

                        <!-- Чатлы за качество -->
                        <div class="points-category">
                            <h4><i class="fas fa-trophy"></i> Чатлы за качество игры:</h4>
                            <div class="points-grid">
                                <div class="points-item">
                                    <span class="time-badge"><i class="fas fa-check-circle" style="color: #b3832a;"></i> "Без ошибок" - (выдается всегда)</span>
                                    <span class="points-value">2 чатла</span>
                                </div>
                                <div class="points-item">
                                    <span class="time-badge"><i class="fas fa-lightbulb" style="color: #c9a5df;"></i> "Без подсказок" - (выдается всегда)</span>
                                    <span class="points-value">2 чатла</span>
                                </div>
                            </div>
                        </div>

                        <!-- Достижения -->
                        <div class="points-category">
                            <h4><i class="fas fa-medal"></i> Чатлы за достижения:</h4>
                            <div class="achievements-list">
                                <div class="achievement-item">
                                    <span class="time-badge"><i class="fa-solid fa-meteor" style="color: #FFB800;"></i> "Привет, Плюк!" - (выдается один раз)</span>
                                    <span class="points-value">5 чатлов</span>
                                </div>
                                <div class="achievement-item">
                                    <span class="time-badge"><i class="fa-solid fa-cloud-meatball" style="color: #b2eaf5;"></i> "Последний выдох" - (выдается один раз)</span>
                                    <span class="points-value">5 чатлов</span>
                                </div>
                                <div class="achievement-item">
                                    <span class="time-badge"><i class="fa-solid fa-universal-access" style="color: #52ff30;"></i> "Зелёные штаны" - (выдается один раз)</span>
                                    <span class="points-value">5 чатлов</span>
                                </div>
                                <div class="achievement-item">
                                    <span class="time-badge"><i class="fa-solid fa-universal-access" style="color: #af52de;"></i> "Сиреневые штаны" - (выдается один раз)</span>
                                    <span class="points-value">10 чатлов</span>
                                </div>
                                <div class="achievement-item">
                                    <span class="time-badge"><i class="fa-solid fa-universal-access" style="color: #FFD700;"></i> "Жёлтые штаны" - (выдается один раз)</span>
                                    <span class="points-value">15 чатлов</span>
                                </div>
                                <div class="achievement-item">
                                    <span class="time-badge"><i class="fa-solid fa-user-tie" style="color: #d5a582;"></i> "Чатланин" - (выдается один раз)</span>
                                    <span class="points-value">100 чатлов</span>
                                </div>
                                <div class="achievement-item">
                                    <span class="time-badge"><i class="fa-solid fa-user-ninja" style="color: #af52de;"></i> "Эцилопп" - (выдается один раз)</span>
                                    <span class="points-value">500 чатлов</span>
                                </div>
                                <div class="achievement-item">
                                    <span class="time-badge"><i class="fas fa-crown" style="color: #30dbff;"></i> "Господин ПЖ" - (выдается один раз)</span>
                                    <span class="points-value">1000 чатлов</span>
                                </div>
                            </div>
                        </div>

                        <!-- Чатлы за статус -->
                        <div class="points-category">
                            <h4><i class="fas fa-crown"></i> Чатлы за статус:</h4>
                            <div class="points-grid">
                                <div class="points-item">
                                    <span class="time-badge"><i class="fa-solid fa-universal-access" style="color: #52ff30;"></i> "Зелёные штаны" - (при каждой победе)</span>
                                    <span class="points-value">1 чатл</span>
                                </div>
                                <div class="points-item">
                                    <span class="time-badge"><i class="fa-solid fa-universal-access" style="color: #af52de;"></i> "Сиреневые штаны" - (при каждой победе)</span>
                                    <span class="points-value">2 чатла</span>
                                </div>
                                <div class="points-item">
                                    <span class="time-badge"><i class="fa-solid fa-universal-access" style="color: #FFD700;"></i> "Жёлтые штаны" - (при каждой победе)</span>
                                    <span class="points-value">3 чатла</span>
                                </div>
                                <div class="points-item">
                                    <span class="time-badge"><i class="fa-solid fa-user-tie" style="color: #d5a582;"></i> "Чатланин" - (при каждой победе)</span>
                                    <span class="points-value">10 чатлов</span>
                                </div>
                                <div class="points-item">
                                    <span class="time-badge"><i class="fa-solid fa-user-ninja" style="color: #af52de;"></i> "Эцилопп" - (при каждой победе)</span>
                                    <span class="points-value">50 чатлов</span>
                                </div>
                                <div class="points-item">
                                    <span class="time-badge"><i class="fas fa-crown" style="color: #30dbff;"></i> "Господин ПЖ" - (при каждой победе)</span>
                                    <span class="points-value">100 чатлов</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Раздел 4: Ограничения -->
                <div class="instruction-section">
                    <h3><i class="fas fa-exclamation-triangle"></i> Ограничения и правила</h3>
                    <div class="instruction-text">
                        <ul>
                            <li><strong>Ошибки:</strong> Максимум 3 ошибки - после этого игра завершается. При 3-х ошибках засчитывается проигрыш, до 2-х ошибок в зачёт не идёт. 3 ошибки влияют на рейтинг в общем зачёте при равных позициях соседних игроков.</li>
                            <li><strong>Подсказки:</strong> До 3 подсказок за игру. На позицию в рейтинге общего зачёта не влияют - отображаются информативно.</li>
                            <li><strong>Время:</strong> Ограничение по времени для каждого уровня сложности. Для "Лёгкого" уровня - 10 мин., для "Среднего" - 15 мин., для "Трудного" - 20 мин. Если не уложились в установленное время, то засчитывается проигрыш. Влияет на рейтинг в общем зачёте при равных позициях соседних игроков.</li>
                            <li><strong>Авторешение:</strong> Использование кнопки "Решить" засчитывается как проигрыш. Влияет на рейтинг в общем зачёте при равных позициях соседних игроков.</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Раздел 5: Правило подсчета рейтинга -->
                <div class="instruction-section">
                    <h3><i class="fas fa-exclamation-triangle"></i> Правило подсчета рейтинга</h3>
                    <div class="instruction-text">
                        <ul>
                            <li><strong>Приоритет рейтинга:</strong> Устанавливается по количеству заработанных чатлов. Чем больше заработано чатлов, тем выше Ваш рейтинг в общем зачёте.</li>
                            <li><strong>Лучшее время:</strong> Сортировка в рейтинге так же идёт по времени игры. Чем меньше времени затрачено на одну игру, тем выше Ваш рейтинг в общем зачёте.</li>
                            <li><strong>Больше побед:</strong> На рейтинг влияет общее количество побед. Чем больше произведено побед, тем выше Ваш рейтинг в общем зачёте.</li>
                            <li><strong>Больше процент побед:</strong> Процент побед тоже влияет на рейтинг зачёта. Чем больше процент, тем выше Ваша позиция в общем зачёте игроков.</li>
                            <li><strong>Участие в рейтинге:</strong><br> 1. Используется накопительная система учета и сравнения заработка чатлов за игру.<br> 2. Используется подсчёт и сравнение времени игры игроков.<br> 3. Используется подсчёт и сравнение количества побед игроков.<br> 4. Используется подсчёт и сравнение % побед игроков.</li>
                            <li><strong>Логика приоритетов:</strong> Если у игроков одинаковые, или схожие позиции статистики игры, то применяется приоритет подсчёта:<br>
                                1-й приоритет: (больше чатлов = выше рейтинг)<br>
                                2-й приоритет: (меньше время = выше рейтинг)<br>
                                3-й приоритет: (больше побед = выше рейтинг)<br>
                                4-й приоритет: (больше процент побед = выше рейтинг)</li>
                        </ul>
                    </div>
                </div>                

                <!-- Раздел 6: Советы -->
                <div class="instruction-section">
                    <h3><i class="fas fa-graduation-cap"></i> Советы для успеха</h3>
                    <div class="instruction-text">
                        <div class="tip-item">
                            <span class="tip-icon">💡</span>
                            <span>Начинайте с поиска очевидных чисел</span>
                        </div>
                        <div class="tip-item">
                            <span class="tip-icon">🔍</span>
                            <span>Ищите уникальные позиции для чисел в блоках 3×3</span>
                        </div>
                        <div class="tip-item">
                            <span class="tip-icon">⏱️</span>
                            <span>Следите за временем для получения чатлов за скорость</span>
                        </div>
                        <div class="tip-item">
                            <span class="tip-icon">🎯</span>
                            <span>Старайтесь играть без ошибок и подсказок для максимального заработка чатлов</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button class="btn btn-primary" id="close-instructions-btn">
                <i class="fas fa-check"></i> Понятно
            </button>
        </div>
    </div>
</div>

<!-- Модальное окно личного кабинета -->
<div class="modal cabinet-modal" id="user-cabinet-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="fas fa-user-circle"></i> Личный кабинет
            </h2>
            <button class="modal-close" id="close-cabinet-modal"><i class="fas fa-times"></i></button>
        </div>
        
        <div class="modal-body">
            <div class="cabinet-tabs">
                <button class="tab-btn active" data-tab="tournaments">Турниры</button>
                <button class="tab-btn" data-tab="tournament-results">Итоги турниров</button>
                <button class="tab-btn" data-tab="payments">Платежи</button>
                <button class="tab-btn" data-tab="profile">Профиль</button>
            </div>
            
            <div class="tab-content">
                <!-- Вкладка профиля -->
                <div class="tab-pane" id="profile-tab">
                    <div class="profile-info">
                        <div class="profile-avatar">
                            <div class="avatar-placeholder">
                                <?= mb_substr($user['username'], 0, 1, 'UTF-8') ?>
                            </div>
                        </div>
                        <div class="profile-details">
                          <h3><?= htmlspecialchars($user['username']) ?></h3>
                          <p>Email: <?= htmlspecialchars($user['email']) ?></p>
                          <?php if(!$isGuest): ?>
                          <p>Зарегистрирован: <?= date('d.m.Y', strtotime($user['created_at'])) ?></p>
                          <?php endif; ?>
                        </div>
                    </div>
                    <div class="profile-stats-grid">
                        <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-gamepad"></i>
                        </div>
                            <div class="stat-value"><?= $userStats['totalGames'] ?></div>
                            <div class="stat-label">Всего игр</div>
                        </div>
                        <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                            <div class="stat-value"><?= $userStats['gamesWon'] ?></div>
                            <div class="stat-label">Побед</div>
                        </div>
                        <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fa-solid fa-money-bill-1-wave"></i>
                        </div>
                            <div class="stat-value"><?= $userStats['totalPoints'] ?></div>
                            <div class="stat-label">Чатлов</div>
                        </div>
                        <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                            <div class="stat-value"><?= $userStats['rating'] ?></div>
                            <div class="stat-label">Рейтинг</div>
                        </div>
                    </div>
                </div>
                
                <!-- Вкладка турниров -->
                <div class="tab-pane active" id="tournaments-tab">
                    <div class="tournaments-section">
                        <h3>Активные турниры:</h3>
                        <div id="active-tournaments-list" class="tournaments-list">
                            <!-- Список активных турниров -->
                        </div>
                        
                        <h3>Предстоящие турниры:</h3>
                        <div id="upcoming-tournaments-list" class="tournaments-list">
                            <!-- Список предстоящих турниров -->
                        </div>
                        
                        <h3>Завершенные турниры:</h3>
                        <div id="completed-tournaments-list" class="tournaments-list">
                            <!-- Список завершенных турниров -->
                        </div>
                    </div>
                </div>
                
                                <!-- НОВАЯ ВКЛАДКА: Итоги турниров -->
                <div class="tab-pane" id="tournament-results-tab">
    <div class="tournament-results-section">
        <div class="section-header">
            <h3><i class="fas fa-trophy"></i> История турниров</h3>
            <p>Ваши результаты в завершенных турнирах:</p>
        </div>
        
        <!-- Общая статистика -->
        <div class="tournament-stats-overview" id="tournament-stats-overview">
            <div class="stats-grid-compact">
                <div class="stat-item-compact">
                    <div class="stat-number">0</div>
                    <div class="stat-label">Участий</div>
                </div>
                <div class="stat-item-compact">
                    <div class="stat-number">0</div>
                    <div class="stat-label">Побед</div>
                </div>
                <div class="stat-item-compact">
                    <div class="stat-number">0</div>
                    <div class="stat-label">Топ-3</div>
                </div>
                <div class="stat-item-compact">
                    <div class="stat-number">0</div>
                    <div class="stat-label">Чатлов</div>
                </div>
            </div>
        </div>
                        
                        <!-- Фильтры -->
        <div class="results-filters-compact">
            <button class="filter-btn-compact active" data-filter="all">Все</button>
            <button class="filter-btn-compact" data-filter="prize">С выигрышем</button>
            <button class="filter-btn-compact" data-filter="no-prize">Без выигрыша</button>
        </div>
                        
                        <!-- Список результатов -->
        <div class="tournament-results-list-compact" id="tournament-results-list">
            <div class="loading-results">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Загрузка результатов...</p>
            </div>
        </div>
                    </div>
                </div>
                
                <!-- Вкладка платежей -->
                <div class="tab-pane" id="payments-tab">
                    <div class="payments-section">
                        <div class="balance-info">
                            <h3>Баланс чатлов:</h3>
                            <div class="balance-amount"><?= $userStats['totalPoints'] ?> <i class="fa-solid fa-money-bill-1-wave fa-beat"></i></div>
                        </div>
                        
                        <div class="payment-methods">
                            <h4>Пополнить баланс:</h4>
                            <button class="payment-btn btn" data-method="donationalerts">
                                <i class="fa-solid fa-credit-card"></i>
                                DonationAlerts
                            </button>
                            <!-- Можно добавить другие методы оплаты -->
                        </div>
                        
                        <div class="payment-history">
                            <h4>История операций:</h4>
                            <div id="payment-history-list">
                                <!-- История платежей -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button class="btn btn-primary" id="close-cabinet-btn">
                <i class="fas fa-check"></i> Закрыть
            </button>
        </div>
    </div>
</div>

<script>
// МИНИМАЛЬНЫЙ JS ФИКС
document.addEventListener('DOMContentLoaded', function() {
    // Добавляем класс при открытии модальных окон
    document.addEventListener('click', function(e) {
        if (e.target.closest('#achievements-btn') || 
            e.target.closest('#user-nick-btn') ||
            e.target.closest('#leaderboard-btn') ||
            e.target.closest('#stats-btn') ||
            e.target.closest('#instructions-btn')) {
            document.body.classList.add('modal-open');
        }
    });
    
    // Убираем класс при закрытии
    document.addEventListener('click', function(e) {
        if (e.target.closest('.modal-close') || 
            e.target.classList.contains('modal') ||
            (e.target.id && e.target.id.includes('close-'))) {
            document.body.classList.remove('modal-open');
        }
    });
    
    // Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.body.classList.remove('modal-open');
        }
    });
});
</script>

    <script src="/js/main.js"></script>
    <script>

// Создаем аудио элемент для фоновой музыки
const backgroundMusic = new Audio();

// Используем успокаивающую музыку для судоку
backgroundMusic.src = '/media/trek10.mp3';

// Настройки аудио
backgroundMusic.loop = true;
backgroundMusic.volume = 0.4; // Уровень громкости от 0 до 1

// Элементы управления
const soundControl = document.getElementById('soundControl');
const soundIcon = soundControl.querySelector('.sound-icon');
const volumeSlider = document.getElementById('volumeSlider');
const volumeValue = document.getElementById('volumeValue');

// Состояние звука
let isMuted = false;
let lastVolume = 0.4; // Запоминаем последнюю громкость для unmute
let wasPlayingBeforeWin = false; // Флаг, был ли звук включен до победы

// Функция для обновления отображения громкости
function updateVolumeDisplay() {
    const volumePercent = Math.round(backgroundMusic.volume * 100);
    volumeValue.textContent = `${volumePercent}%`;
    volumeSlider.value = volumePercent;
    
    // Обновляем иконку в зависимости от уровня громкости
    const volumeIcon = document.querySelector('.volume-icon');
    if (volumeIcon) {
        if (backgroundMusic.volume === 0) {
            volumeIcon.textContent = '🔇';
        } else if (backgroundMusic.volume < 0.3) {
            volumeIcon.textContent = '🔈';
        } else if (backgroundMusic.volume < 0.7) {
            volumeIcon.textContent = '🔉';
        } else {
            volumeIcon.textContent = '🔊';
        }
    }
}

// Функция для плавного изменения громкости
function fadeVolume(targetVolume, duration = 1000) {
    if (!backgroundMusic) return;
    
    const startVolume = backgroundMusic.volume;
    const startTime = performance.now();
    
    // Если изменение мгновенное или громкость уже целевая
    if (duration <= 0 || Math.abs(targetVolume - startVolume) < 0.01) {
        backgroundMusic.volume = targetVolume;
        updateVolumeDisplay();
        return;
    }
    
    function animateVolume(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Квадратичное easing для плавности
        const easeProgress = progress < 0.5 
            ? 2 * progress * progress 
            : -1 + (4 - 2 * progress) * progress;
            
        backgroundMusic.volume = startVolume + (targetVolume - startVolume) * easeProgress;
        
        if (progress < 1) {
            requestAnimationFrame(animateVolume);
        } else {
            // Гарантируем точное значение в конце
            backgroundMusic.volume = targetVolume;
            updateVolumeDisplay();
        }
    }
    
    requestAnimationFrame(animateVolume);
}

// Функция для установки громкости
function setVolume(volume) {
    // Преобразуем значение слайдера (0-100) в диапазон громкости (0-1)
    const newVolume = volume / 100;
    backgroundMusic.volume = newVolume;
    
    // Если включаем звук после mute, обновляем состояние
    if (isMuted && newVolume > 0) {
        isMuted = false;
        soundControl.classList.remove('muted');
        soundIcon.textContent = '♪';
        localStorage.setItem('sudokuSoundEnabled', 'true');
    }
    
    // Сохраняем громкость
    localStorage.setItem('sudokuVolume', newVolume.toString());
    updateVolumeDisplay();
}

// Функция для переключения звука
function toggleSound() {
    if (isMuted) {
        // Включаем звук с последней сохраненной громкостью
        backgroundMusic.volume = lastVolume;
        backgroundMusic.play().catch(e => {});
        soundControl.classList.remove('muted');
        soundIcon.textContent = '♪';
        isMuted = false;
        
        // Сохраняем настройку в localStorage
        localStorage.setItem('sudokuSoundEnabled', 'true');
    } else {
        // Выключаем звук, запоминаем текущую громкость
        lastVolume = backgroundMusic.volume;
        backgroundMusic.volume = 0;
        backgroundMusic.pause();
        soundControl.classList.add('muted');
        soundIcon.textContent = '🔇';
        isMuted = true;
        
        // Сохраняем настройку в localStorage
        localStorage.setItem('sudokuSoundEnabled', 'false');
    }
    
    updateVolumeDisplay();
}

// Обработчик изменения слайдера громкости
volumeSlider.addEventListener('input', function() {
    setVolume(this.value);
});

// Обработчик клика по кнопке звука
soundControl.addEventListener('click', function(event) {
    // Предотвращаем срабатывание при клике на слайдер
    if (!event.target.closest('.volume-control')) {
        toggleSound();
    }
});

// Проверяем сохраненные настройки звука при загрузке
window.addEventListener('DOMContentLoaded', () => {
    const savedSoundSetting = localStorage.getItem('sudokuSoundEnabled');
    const savedVolume = localStorage.getItem('sudokuVolume');
    
    // Восстанавливаем громкость
    if (savedVolume) {
        backgroundMusic.volume = parseFloat(savedVolume);
    }
    
    // Если настройка сохранена и звук включен, воспроизводим музыку
    if (savedSoundSetting === 'true') {
        isMuted = false;
        soundControl.classList.remove('muted');
        soundIcon.textContent = '♪';
        
        // Пытаемся воспроизвести музыку
        backgroundMusic.play().catch(e => {});
    } else {
        // Если настройка не сохранена или звук выключен
        isMuted = true;
        soundControl.classList.add('muted');
        soundIcon.textContent = '🔇';
        lastVolume = backgroundMusic.volume;
        backgroundMusic.volume = 0;
    }
    
    // Обновляем отображение громкости
    updateVolumeDisplay();
    
    // Добавляем анимацию появления
    soundControl.classList.add('fade-in');
});

// Обработчик для возобновления музыки после паузы (например, при переключении вкладок)
document.addEventListener('visibilitychange', function() {
    if (!document.hidden && !isMuted && backgroundMusic.volume > 0) {
        backgroundMusic.play().catch(e => {});
    }
});

// Интеграция с игровыми событиями
document.addEventListener('DOMContentLoaded', function() {
    // При победе - плавно уменьшаем громкость и запоминаем состояние
    const winModal = document.getElementById('win-modal');
    if (winModal) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    const displayStyle = winModal.style.display;
                    if (displayStyle === 'block' || displayStyle === 'flex') {
    // Победа - запоминаем, был ли включен звук, и ПОЛНОСТЬЮ отключаем
    wasPlayingBeforeWin = !isMuted && backgroundMusic.volume > 0;
    backgroundMusic.volume = 0; // Полностью выключаем звук
    updateVolumeDisplay();
} else {
    // Закрыли окно победы - возвращаем громкость ТОЛЬКО если звук был включен до победы
    if (wasPlayingBeforeWin && !isMuted) {
        const savedVolume = localStorage.getItem('sudokuVolume');
        const targetVolume = savedVolume ? parseFloat(savedVolume) : 0.4;
        backgroundMusic.volume = targetVolume; // Мгновенно возвращаем громкость
        updateVolumeDisplay();
    }
    wasPlayingBeforeWin = false; // Сбрасываем флаг
}
                }
            });
        });
        
        observer.observe(winModal, { attributes: true, attributeFilter: ['style'] });
    }
    
    // При проигрыше - также уменьшаем громкость
    const loseModal = document.getElementById('lose-game-modal');
    if (loseModal) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    const displayStyle = loseModal.style.display;
                    if (displayStyle === 'block' || displayStyle === 'flex') {
                        // Проигрыш - плавно уменьшаем громкость
                        fadeVolume(0.1, 1500);
                    } else {
                        // Закрыли окно проигрыша - возвращаем громкость
                        if (!isMuted) {
                            const savedVolume = localStorage.getItem('sudokuVolume');
                            const targetVolume = savedVolume ? parseFloat(savedVolume) : 0.4;
                            fadeVolume(targetVolume, 1000);
                        }
                    }
                }
            });
        });
        
        observer.observe(loseModal, { attributes: true, attributeFilter: ['style'] });
    }
});

// Закрытие регулятора громкости при клике вне его
document.addEventListener('click', function(event) {
    const volumeControl = document.querySelector('.volume-control');
    const soundControl = document.getElementById('soundControl');
    
    if (volumeControl && 
        !soundControl.contains(event.target) && 
        volumeControl.style.visibility === 'visible') {
        // Не закрываем сразу, даем возможность взаимодействовать со слайдером
        setTimeout(() => {
            if (!soundControl.matches(':hover')) {
                volumeControl.style.opacity = '0';
                volumeControl.style.visibility = 'hidden';
                volumeControl.style.transform = 'translateY(10px)';
            }
        }, 100);
    }
});
</script>
<script>
    // Передаем PHP переменные в JavaScript
    const isGuest = <?= $isGuest ? 'true' : 'false' ?>;
    const userStats = <?= json_encode($userStats) ?>;
    const leaderboardData = <?= json_encode($leaderboard) ?>;
    const user = <?= json_encode($user) ?>;
    
    // ★★★ ДУБЛИРУЕМ В ГЛОБАЛЬНЫЙ ОБЪЕКТ WINDOW ДЛЯ ДОСТУПА ИЗ ФУНКЦИЙ ★★★
    window.user = user;
    window.leaderboardData = leaderboardData;
    window.isGuest = isGuest;
    window.userStats = userStats;
    
    // УПРОЩЕННАЯ ПРОВЕРКА ПРИ ЗАГРУЗКЕ
    window.addEventListener('DOMContentLoaded', function() {
        // Проверяем состояние кнопки "Решить"
        const solveBtnDisabled = localStorage.getItem('solveBtnDisabled');
        const solveBtn = document.getElementById('solve-btn');
        
        if (solveBtnDisabled === 'true' && solveBtn) {
            solveBtn.disabled = true;
            solveBtn.classList.add('disabled');
        }
    });
</script>
<script>
    class BackgroundManager {
    constructor() {
        this.images = [
            '/img/fon1.jpg',
            '/img/fon2.jpg', 
            '/img/fon3.jpg',
            '/img/fon4.jpg',
            '/img/fon5.jpg',
            '/img/fon6.jpg',
            '/img/fon7.jpg',
            '/img/fon8.jpg',
            '/img/fon9.jpg'
        ];
        this.currentIndex = 0;
    }

    init() {
        // Восстанавливаем последний фон
        const savedIndex = localStorage.getItem('sudoku_bg_index');
        if (savedIndex !== null) {
            this.currentIndex = parseInt(savedIndex);
        } else {
            this.currentIndex = Math.floor(Math.random() * this.images.length);
        }
        
        this.setBackground(this.currentIndex);
        this.createBackgroundButton();
    }

    createBackgroundButton() {
        // Ищем контейнер для левых контролов
        let leftControls = document.querySelector('.left-controls');
        
        if (!leftControls) {
            return;
        }

        // Создаем простую кнопку
        const bgButton = document.createElement('button');
        bgButton.className = 'bg-change-btn';
        bgButton.id = 'change-bg-btn';
        bgButton.title = 'Фон';
        bgButton.innerHTML = '<i class="fas fa-image"></i>';

        // Обработчик клика
        bgButton.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.nextBackground();
            
            // Анимация кнопки
            bgButton.style.transform = 'scale(1.2) rotate(180deg)';
            setTimeout(() => {
                bgButton.style.transform = 'scale(1) rotate(0deg)';
            }, 300);
        });

        leftControls.appendChild(bgButton);

        // Горячая клавиша
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'b') {
                e.preventDefault();
                this.nextBackground();
            }
        });
    }

    setBackground(index) {
        if (index >= 0 && index < this.images.length) {
            this.currentIndex = index;
            document.body.style.backgroundImage = `url("${this.images[index]}")`;
            localStorage.setItem('sudoku_bg_index', index.toString());
        }
    }

    nextBackground() {
        this.currentIndex = (this.currentIndex + 1) % this.images.length;
        this.setBackground(this.currentIndex);
    }
}

// Глобальная инициализация
let backgroundManager;

function initializeBackgroundManager() {
    if (!backgroundManager) {
        backgroundManager = new BackgroundManager();
        backgroundManager.init();
    }
}

// Запускаем когда DOM готов
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeBackgroundManager);
} else {
    setTimeout(initializeBackgroundManager, 500);
}

// Функция для принудительной инициализации из консоли
window.initBackgroundManager = initializeBackgroundManager;
</script>
<script>
// Обработчик для логотипа в гостевом режиме
function handleGuestLogoClick(event) {
    if (window.gameState && window.gameState.isGameActive && !window.gameState.isGameCompleted) {
        event.preventDefault();
        document.getElementById('homepage-warning-modal').style.display = 'flex';
        return false;
    }
    return true;
}

// Обработчик для кнопки входа в гостевом режиме
function handleGuestLoginClick(event) {
    if (window.gameState && window.gameState.isGameActive && !window.gameState.isGameCompleted) {
        event.preventDefault();
        document.getElementById('login-warning-modal').style.display = 'flex';
        return false;
    }
    return true;
}

// Инициализация обработчиков
document.addEventListener('DOMContentLoaded', function() {
    // Обработчик для логотипа
    const logoLink = document.querySelector('.logo-text');
    if (logoLink) {
        logoLink.addEventListener('click', function(e) {
            if (<?= $isGuest ? 'true' : 'false' ?>) {
                handleGuestLogoClick(e);
            }
        });
    }
    
    // Обработчик для кнопки входа
    const loginBtn = document.querySelector('a[href="login.php"]');
    if (loginBtn && <?= $isGuest ? 'true' : 'false' ?>) {
        loginBtn.addEventListener('click', handleGuestLoginClick);
    }
});
</script>
<script>
// Обработчик для кнопки ника пользователя
document.addEventListener('DOMContentLoaded', function() {
    const userNickBtn = document.getElementById('user-nick-btn');
    const cabinetModal = document.getElementById('user-cabinet-modal');
    const closeCabinetModal = document.getElementById('close-cabinet-modal');
    const closeCabinetBtn = document.getElementById('close-cabinet-btn');
    
    // Функция для открытия модального окна
    function openCabinetModal() {
    cabinetModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // ★★★ АВТОМАТИЧЕСКИ ОТКРЫВАЕМ ВКЛАДКУ ТУРНИРОВ ★★★
    setTimeout(() => {
        switchTab('tournaments');
    }, 100);
    
    // Инициализируем вкладки кабинета если нужно
    if (typeof initCabinetTabs === 'function') {
        initCabinetTabs();
    }
}
    
    // Функция для закрытия модального окна
    function closeCabinetModalFunc() {
        cabinetModal.style.display = 'none';
        document.body.style.overflow = ''; // Восстанавливаем скролл
    }
    
    if (userNickBtn && cabinetModal) {
        // Открытие по клику на ник
        userNickBtn.addEventListener('click', openCabinetModal);
        
        // Добавляем визуальную обратную связь при наведении
        userNickBtn.style.cursor = 'pointer';
        userNickBtn.title = 'Открыть личный кабинет';
    }
    
    // Закрытие по кнопке закрытия
    if (closeCabinetModal) {
        closeCabinetModal.addEventListener('click', closeCabinetModalFunc);
    }
    
    // Закрытие по кнопке "Закрыть"
    if (closeCabinetBtn) {
        closeCabinetBtn.addEventListener('click', closeCabinetModalFunc);
    }
    
    // Закрытие по клику вне модального окна
    if (cabinetModal) {
        cabinetModal.addEventListener('click', function(e) {
            if (e.target === cabinetModal) {
                closeCabinetModalFunc();
            }
        });
    }
    
    // Закрытие по клавише Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && cabinetModal.style.display === 'flex') {
            closeCabinetModalFunc();
        }
    });
});

// Функция для инициализации вкладок личного кабинета (если её ещё нет)
function initCabinetTabs() {
    const tabBtns = document.querySelectorAll('.cabinet-tabs .tab-btn');
    const tabPanes = document.querySelectorAll('.cabinet-modal .tab-pane');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Убираем активный класс у всех кнопок и вкладок
            tabBtns.forEach(b => b.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            
            // Добавляем активный класс текущей кнопке и соответствующей вкладке
            this.classList.add('active');
            document.getElementById(`${targetTab}-tab`).classList.add('active');
        });
    });
}
</script>
</body>
<?php
// Очищаем буфер и отправляем полную страницу
ob_end_flush();
?>
</html>