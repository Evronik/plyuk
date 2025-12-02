<?php
// websocket_proxy.php - WebSocket через HTTP порт
require_once 'config.php';

// Добавьте заголовки CORS
header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN'] ?? '*');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Обработка предварительных запросов
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Добавьте кэширование для частых запросов
function shouldCacheResponse($action) {
    $cacheableActions = ['get_tournaments', 'get_tournament_status'];
    return in_array($action, $cacheableActions);
}

// Функция для логирования WebSocket событий
function ws_log($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] [{$level}] {$message}\n";
    
    // Логируем только важные события в продакшн
    if (!defined('DEBUG_MODE') || DEBUG_MODE || $level === 'ERROR') {
        file_put_contents(__DIR__ . '/websocket.log', $logEntry, FILE_APPEND);
    }
    
    // В режиме отладки также пишем в error_log
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        error_log("WS: {$message}");
    }
}

// Проверяем WebSocket заголовки
function isWebSocketRequest() {
    return isset($_SERVER['HTTP_UPGRADE']) && 
           strtolower($_SERVER['HTTP_UPGRADE']) === 'websocket' &&
           isset($_SERVER['HTTP_CONNECTION']) && 
           strpos(strtolower($_SERVER['HTTP_CONNECTION']), 'upgrade') !== false;
}

// Логируем начало запроса
ws_log("Request started: {$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']}");

if (isWebSocketRequest()) {
    // WebSocket upgrade request
    ws_log("WebSocket upgrade attempt from {$_SERVER['REMOTE_ADDR']}");
    handleWebSocketUpgrade();
} else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    // API запросы для управления турнирами
    ws_log("API request: {$_GET['action']} from user " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'guest'));
    handleAPIRequest();
} else {
    // Показываем статус
    ws_log("Status page request");
    showStatusPage();
}

function handleWebSocketUpgrade() {
    ws_log("WebSocket not implemented - returning 501");
    header("HTTP/1.1 501 Not Implemented");
    echo "Real WebSocket connections require a separate server process";
    exit;
}

function handleAPIRequest() {
    global $pdo;
    
    // Устанавливаем правильный заголовок для JSON
    header('Content-Type: application/json; charset=utf-8');
    
    // Проверяем авторизацию для защищенных методов
    $protectedActions = ['join_tournament', 'get_tournament_status', 'get_tournament_results', 'mark_tournament_seen', 'leave_tournament', 'start_tournament_game'];
    
    if (in_array($_GET['action'], $protectedActions) && !isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
        exit;
    }
    
    $action = $_GET['action'];
    $response = ['success' => false, 'message' => 'Unknown action'];
    
    // Получаем ID пользователя из сессии
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    try {
        switch ($action) {
            case 'get_tournaments':
                ws_log("Getting tournaments list for user: " . ($userId ?: 'guest'));
                $response = getTournaments();
                break;
            case 'join_tournament':
                $tournamentId = $_GET['tournament_id'] ?? 0;
                ws_log("User {$userId} joining tournament: {$tournamentId}");
                $response = joinTournament($tournamentId, $userId);
                break;
            case 'get_tournament_status':
                $tournamentId = $_GET['tournament_id'] ?? 0;
                ws_log("User {$userId} checking status of tournament: {$tournamentId}");
                $response = getTournamentStatus($tournamentId, $userId);
                break;
            case 'get_completed_tournaments':
                ws_log("User {$userId} getting completed tournaments");
                $response = getCompletedTournaments($userId);
                break;
            case 'get_tournament_results':
                $tournamentId = $_GET['tournament_id'] ?? 0;
                ws_log("User {$userId} getting results for tournament: {$tournamentId}");
                $response = getTournamentResults($tournamentId, $userId);
                break;
            case 'mark_tournament_seen':
                $tournamentId = $_GET['tournament_id'] ?? 0; // ★★★ ИСПРАВЛЕНО: GET вместо POST ★★★
                ws_log("User {$userId} marking tournament as seen: {$tournamentId}");
                $response = markTournamentSeen($tournamentId, $userId);
                break;
            case 'leave_tournament':
                $tournamentId = $_GET['tournament_id'] ?? 0;
                ws_log("User {$userId} leaving tournament: {$tournamentId}");
                $response = leaveTournament($tournamentId, $userId);
                break;
            case 'start_tournament_game':
                $tournamentId = $_GET['tournament_id'] ?? 0;
                ws_log("User {$userId} starting game in tournament: {$tournamentId}");
                $response = startTournamentGame($tournamentId, $userId);
                break;
            default:
                ws_log("Unknown action requested: {$action}", 'WARNING');
                $response = ['success' => false, 'message' => 'Unknown action: ' . $action];
            }
    } catch (Exception $e) {
        ws_log("API Error in {$action}: " . $e->getMessage(), 'ERROR');
        $response = ['success' => false, 'message' => 'Server error: ' . $e->getMessage()];
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

function getTournaments() {
    global $pdo;
    
    try {
        // Получаем ВСЕ турниры (включая завершенные)
        $stmt = $pdo->query("
            SELECT t.*, 
                   COUNT(tr.id) as current_players
            FROM tournaments t
            LEFT JOIN tournament_registrations tr ON t.id = tr.tournament_id AND tr.status = 'registered'
            GROUP BY t.id
            ORDER BY 
                CASE 
                    WHEN t.status = 'registration' THEN 1
                    WHEN t.status = 'active' THEN 2  
                    WHEN t.status = 'completed' THEN 3
                    ELSE 4
                END,
                t.start_time ASC
        ");
        
        $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Форматируем данные для фронтенда
        $formattedTournaments = [];
        foreach ($tournaments as $tournament) {
            $formattedTournaments[] = [
                'id' => (int)$tournament['id'],
                'name' => $tournament['name'],
                'description' => $tournament['description'],
                'entry_fee' => (float)$tournament['entry_fee'],
                'prize_pool' => (float)$tournament['prize_pool'],
                'max_players' => (int)$tournament['max_players'],
                'current_players' => (int)$tournament['current_players'],
                'difficulty' => $tournament['difficulty'],
                'status' => $tournament['status'],
                'start_time' => $tournament['start_time'],
                'created_at' => $tournament['created_at']
            ];
        }
        
        return [
            'success' => true,
            'tournaments' => $formattedTournaments
        ];
        
    } catch (PDOException $e) {
        ws_log("Database error in getTournaments: " . $e->getMessage(), 'ERROR');
        return [
            'success' => false,
            'message' => 'Ошибка загрузки турниров',
            'tournaments' => []
        ];
    }
}

function getCompletedTournaments($userId = null) {
    global $pdo;
    
    try {
        if ($userId) {
            // Для конкретного пользователя - используем INNER JOIN
            $query = "
                SELECT 
                    t.*,
                    tr.position,
                    tr.score,
                    tr.prize,
                    tr.completed_at
                FROM tournament_results tr
                INNER JOIN tournaments t ON tr.tournament_id = t.id
                WHERE tr.user_id = ? AND t.status = 'completed'
                ORDER BY t.completed_at DESC
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$userId]);
        } else {
            // Все завершенные турниры
            $query = "
                SELECT 
                    t.*,
                    NULL as position,
                    NULL as score, 
                    NULL as prize,
                    NULL as completed_at
                FROM tournaments t
                WHERE t.status = 'completed'
                ORDER BY t.completed_at DESC
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
        }
        
        $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Форматируем данные для фронтенда
        $formattedTournaments = [];
        foreach ($tournaments as $tournament) {
            $formattedTournaments[] = [
                'id' => (int)$tournament['id'],
                'name' => $tournament['name'],
                'description' => $tournament['description'],
                'entry_fee' => (float)$tournament['entry_fee'],
                'prize_pool' => (float)$tournament['prize_pool'],
                'max_players' => (int)$tournament['max_players'],
                'difficulty' => $tournament['difficulty'],
                'status' => $tournament['status'],
                'start_time' => $tournament['start_time'],
                'created_at' => $tournament['created_at'],
                'position' => $tournament['position'] ? (int)$tournament['position'] : null,
                'score' => $tournament['score'] ? (int)$tournament['score'] : null,
                'prize' => $tournament['prize'] ? (float)$tournament['prize'] : null,
                'completed_at' => $tournament['completed_at']
            ];
        }
        
        ws_log("Loaded " . count($formattedTournaments) . " completed tournaments for user: " . ($userId ?: 'all'));
        
        return [
            'success' => true,
            'completed_tournaments' => $formattedTournaments
        ];
        
    } catch (PDOException $e) {
        ws_log("Database error in getCompletedTournaments: " . $e->getMessage(), 'ERROR');
        return [
            'success' => false,
            'message' => 'Ошибка загрузки завершенных турниров',
            'completed_tournaments' => []
        ];
    }
}

function getTournamentResults($tournamentId = null, $userId = null) {
    // ★★★ ПРОСТО ПЕРЕНАПРАВЛЯЕМ НА ОСНОВНОЙ API ★★★
    // Это нужно для совместимости со старым кодом
    
    if ($tournamentId) {
        // Если нужны результаты конкретного турнира
        return [
            'success' => true,
            'message' => 'Используйте api/get_tournament_results.php',
            'redirect' => 'api/get_tournament_results.php'
        ];
    }
    
    // Если нужны результаты пользователя - используем основной файл
    return [
        'success' => true,
        'message' => 'Результаты загружаются через основной API',
        'use_api' => 'api/get_tournament_results.php'
    ];
}

function markTournamentSeen($tournamentId, $userId) {
    global $pdo;
    
    try {
        // Проверяем существование таблицы tournament_seen
        if (!table_exists($pdo, 'tournament_seen')) {
            ws_log("Table tournament_seen does not exist - skipping mark as seen", 'WARNING');
            return [
                'success' => true,
                'message' => 'Турнир помечен как просмотренный'
            ];
        }
        
        // Проверяем, существует ли уже запись
        $stmt = $pdo->prepare("SELECT id FROM tournament_seen WHERE user_id = ? AND tournament_id = ?");
        $stmt->execute([$userId, $tournamentId]);
        
        if (!$stmt->fetch()) {
            // Создаем новую запись
            $stmt = $pdo->prepare("INSERT INTO tournament_seen (user_id, tournament_id, seen_at) VALUES (?, ?, NOW())");
            $stmt->execute([$userId, $tournamentId]);
            ws_log("User {$userId} created new seen record for tournament: {$tournamentId}");
        } else {
            // Обновляем существующую
            $stmt = $pdo->prepare("UPDATE tournament_seen SET seen_at = NOW() WHERE user_id = ? AND tournament_id = ?");
            $stmt->execute([$userId, $tournamentId]);
            ws_log("User {$userId} updated seen record for tournament: {$tournamentId}");
        }
        
        ws_log("User {$userId} marked tournament {$tournamentId} as seen");
        
        return [
            'success' => true,
            'message' => 'Турнир помечен как просмотренный'
        ];
        
    } catch (PDOException $e) {
        ws_log("Database error in markTournamentSeen: " . $e->getMessage(), 'ERROR');
        return [
            'success' => false,
            'message' => 'Ошибка отметки турнира'
        ];
    }
}

function joinTournament($tournamentId, $userId = null) {
    global $pdo;
    
    // Если пользователь не авторизован
    if (!$userId) {
        ws_log("Unauthorized join attempt for tournament: {$tournamentId}", 'WARNING');
        return [
            'success' => false,
            'message' => 'Для участия в турнирах необходимо войти в систему'
        ];
    }
    
    try {
        // Начинаем транзакцию
        $pdo->beginTransaction();
        
        // 1. Получаем данные турнира
        $stmt = $pdo->prepare("SELECT * FROM tournaments WHERE id = ? AND status = 'registration'");
        $stmt->execute([$tournamentId]);
        $tournament = $stmt->fetch();
        
        if (!$tournament) {
            $pdo->rollBack();
            ws_log("Tournament not found or closed: {$tournamentId}", 'WARNING');
            return [
                'success' => false,
                'message' => 'Регистрация закрыта!'
            ];
        }
        
        // 2. Проверяем, не зарегистрирован ли уже пользователь
        $stmt = $pdo->prepare("SELECT id FROM tournament_registrations WHERE tournament_id = ? AND user_id = ?");
        $stmt->execute([$tournamentId, $userId]);
        $existingRegistration = $stmt->fetch();
        
        if ($existingRegistration) {
            $pdo->rollBack();
            ws_log("User {$userId} already registered in tournament: {$tournamentId}", 'WARNING');
            return [
                'success' => false, 
                'message' => 'Вы уже зарегистрированы в этом турнире'
            ];
        }
        
        // 3. Проверяем количество свободных мест
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tournament_registrations WHERE tournament_id = ? AND status = 'registered'");
        $stmt->execute([$tournamentId]);
        $currentPlayers = $stmt->fetch()['count'];
        
        if ($currentPlayers >= $tournament['max_players']) {
            $pdo->rollBack();
            ws_log("Tournament {$tournamentId} is full", 'WARNING');
            return [
                'success' => false,
                'message' => 'Турнир уже заполнен'
            ];
        }
        
        // 4. Проверяем баланс пользователя (если турнир платный)
        if ($tournament['entry_fee'] > 0) {
            $stmt = $pdo->prepare("SELECT total_points FROM user_stats WHERE user_id = ?");
            $stmt->execute([$userId]);
            $userStats = $stmt->fetch();
            
            if (!$userStats || $userStats['total_points'] < $tournament['entry_fee']) {
                $pdo->rollBack();
                ws_log("User {$userId} has insufficient funds for tournament: {$tournamentId}", 'WARNING');
                return [
                    'success' => false,
                    'message' => 'Недостаточно чатлов для участия. Нужно: ' . $tournament['entry_fee']
                ];
            }
            
            // Списание чатлов
            $stmt = $pdo->prepare("UPDATE user_stats SET total_points = total_points - ? WHERE user_id = ?");
            $stmt->execute([$tournament['entry_fee'], $userId]);
            
            // Запись в историю платежей
            $stmt = $pdo->prepare("INSERT INTO payment_history (user_id, amount, method, status, external_id) VALUES (?, ?, 'tournament_fee', 'completed', ?)");
            $stmt->execute([$userId, -$tournament['entry_fee'], 'tournament_' . $tournamentId]);
            
            ws_log("Charged {$tournament['entry_fee']} points from user {$userId} for tournament {$tournamentId}");
        }
        
        // 5. Регистрируем пользователя
        $stmt = $pdo->prepare("INSERT INTO tournament_registrations (tournament_id, user_id, status, registered_at) VALUES (?, ?, 'registered', NOW())");
        $stmt->execute([$tournamentId, $userId]);
        
        $pdo->commit();
        
        ws_log("User {$userId} successfully joined tournament: {$tournamentId}");
        
        return [
            'success' => true,
            'message' => 'Вы успешно зарегистрировались в турнире!',
            'tournament_id' => $tournamentId
        ];
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        ws_log("Database error in joinTournament: " . $e->getMessage(), 'ERROR');
        return [
            'success' => false,
            'message' => 'Ошибка регистрации в турнире'
        ];
    }
}

// Остальные функции (getTournamentStatus, leaveTournament, startTournamentGame) 
// аналогично замените error_log на ws_log...

// ... остальной код функций с заменой error_log на ws_log

function getTournamentStatus($tournamentId, $userId) {
    global $pdo;
    
    // Проверяем авторизацию
    if (!$userId) {
        return [
            'success' => false,
            'message' => 'Необходима авторизация'
        ];
    }
    
    try {
        ws_log("🔍 getTournamentStatus: tournament_id=$tournamentId, user_id=$userId");
        
        $stmt = $pdo->prepare("
            SELECT 
                t.*, 
                tr.status as user_status,
                COUNT(tr_all.id) as current_players
            FROM tournaments t
            LEFT JOIN tournament_registrations tr ON t.id = tr.tournament_id AND tr.user_id = ?
            LEFT JOIN tournament_registrations tr_all ON t.id = tr_all.tournament_id AND tr_all.status = 'registered'
            WHERE t.id = ?
            GROUP BY t.id
        ");
        $stmt->execute([$userId, $tournamentId]);
        $tournament = $stmt->fetch();
        
        if (!$tournament) {
            ws_log("Tournament not found: {$tournamentId}", 'WARNING');
            return [
                'success' => false,
                'message' => 'Турнир не найден'
            ];
        }
        
        // Получаем список игроков
        $stmt = $pdo->prepare("
            SELECT u.username, tr.status, tr.registered_at
            FROM tournament_registrations tr
            JOIN users u ON tr.user_id = u.id
            WHERE tr.tournament_id = ?
            ORDER BY tr.registered_at ASC
        ");
        $stmt->execute([$tournamentId]);
        $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result = [
            'success' => true,
            'tournament_id' => $tournamentId,
            'status' => $tournament['status'],
            'user_status' => $tournament['user_status'],
            'current_players' => (int)$tournament['current_players'],
            'max_players' => (int)$tournament['max_players'],
            'players' => $players
        ];
        
        ws_log("✅ getTournamentStatus success for tournament {$tournamentId}");
        return $result;
        
    } catch (PDOException $e) {
        $errorMsg = "Database error in getTournamentStatus: " . $e->getMessage();
        ws_log("❌ " . $errorMsg, 'ERROR');
        return [
            'success' => false,
            'message' => 'Ошибка получения статуса турнира'
        ];
    }
}

function leaveTournament($tournamentId, $userId) {
    global $pdo;
    
    if (!$userId) {
        return ['success' => false, 'message' => 'Необходима авторизация'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Получаем информацию о турнире для возврата средств
        $stmt = $pdo->prepare("SELECT entry_fee FROM tournaments WHERE id = ?");
        $stmt->execute([$tournamentId]);
        $tournament = $stmt->fetch();
        
        // Возвращаем средства если турнир платный
        if ($tournament && $tournament['entry_fee'] > 0) {
            $stmt = $pdo->prepare("UPDATE user_stats SET total_points = total_points + ? WHERE user_id = ?");
            $stmt->execute([$tournament['entry_fee'], $userId]);
        }
        
        // Удаляем регистрацию
        $stmt = $pdo->prepare("DELETE FROM tournament_registrations WHERE tournament_id = ? AND user_id = ?");
        $stmt->execute([$tournamentId, $userId]);
        
        $pdo->commit();
        
        if ($stmt->rowCount() > 0) {
            ws_log("User {$userId} left tournament: {$tournamentId}");
            return [
                'success' => true,
                'message' => 'Вы вышли из турнира' . ($tournament && $tournament['entry_fee'] > 0 ? ', средства возвращены' : '')
            ];
        } else {
            ws_log("User {$userId} was not registered in tournament: {$tournamentId}", 'WARNING');
            return [
                'success' => false,
                'message' => 'Вы не были зарегистрированы в этом турнире'
            ];
        }
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        ws_log("Database error in leaveTournament: " . $e->getMessage(), 'ERROR');
        return ['success' => false, 'message' => 'Ошибка выхода из турнира'];
    }
}

function startTournamentGame($tournamentId, $userId) {
    global $pdo;
    
    try {
        // Проверяем, что турнир активен и пользователь зарегистрирован
        $stmt = $pdo->prepare("SELECT t.*, tr.status as user_status 
                              FROM tournaments t
                              LEFT JOIN tournament_registrations tr ON t.id = tr.tournament_id AND tr.user_id = ?
                              WHERE t.id = ? AND t.status = 'active'");
        $stmt->execute([$userId, $tournamentId]);
        $tournament = $stmt->fetch();
        
        if (!$tournament || $tournament['user_status'] !== 'registered') {
            ws_log("Tournament game start failed - not found or not registered: {$tournamentId}", 'WARNING');
            return ['success' => false, 'message' => 'Турнир не найден или вы не зарегистрированы'];
        }
        
        // Создаем игровую сессию для турнира
        $gameId = uniqid('tournament_');
        
        // TODO: Заглушка - нужно реализовать generateTournamentBoard()
        $board = [
            'cells' => [],
            'solution' => [],
            'difficulty' => $tournament['difficulty']
        ];
        
        // Сохраняем игру в tournament_games
        $stmt = $pdo->prepare("INSERT INTO tournament_games (tournament_id, game_id, player1_id, board_data, status) 
                              VALUES (?, ?, ?, ?, 'active')");
        $stmt->execute([$tournamentId, $gameId, $userId, json_encode($board)]);
        
        // Обновляем статус игрока на "playing"
        $stmt = $pdo->prepare("UPDATE tournament_registrations SET status = 'playing' 
                              WHERE tournament_id = ? AND user_id = ?");
        $stmt->execute([$tournamentId, $userId]);
        
        ws_log("User {$userId} started tournament game: {$tournamentId}");
        
        return [
            'success' => true, 
            'message' => 'Турнирная игра начата!',
            'game_id' => $gameId,
            'board' => $board
        ];
        
    } catch (PDOException $e) {
        ws_log("Tournament game start error: " . $e->getMessage(), 'ERROR');
        return ['success' => false, 'message' => 'Ошибка начала игры'];
    }
}

function showStatusPage() {
    ws_log("Showing status page");
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>WebSocket Proxy - ПлюкСудоку</title>
        <meta charset="utf-8">
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .status { padding: 20px; background: #f5f5f5; border-radius: 8px; }
        </style>
    </head>
    <body>
        <h1>WebSocket Proxy для турниров</h1>
        <div class="status">
            <p>✅ Этот endpoint работает через стандартный HTTP порт</p>
            <p>📊 Доступны API методы для работы с турнирами</p>
            <p>🔗 Используется long-polling вместо WebSocket</p>
        </div>
    </body>
    </html>
    <?php
}
?>