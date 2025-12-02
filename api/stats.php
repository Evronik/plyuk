<?php
require_once '../config.php';

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Подключение к базе данных
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit();
}

// Обработка GET запроса (получение статистики)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM user_stats WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $stats = $stmt->fetch();
        
        if (!$stats) {
            // Если статистики нет, возвращаем пустые значения
            $dataToReturn = [
                'totalGames' => 0,
                'gamesWon' => 0,
                'bestTimes' => [
                    'easy' => null,
                    'medium' => null,
                    'hard' => null
                ]
            ];
        } else {
            $dataToReturn = [
                'totalGames' => (int)$stats['total_games'],
                'gamesWon' => (int)$stats['games_won'],
                'bestTimes' => [
                    'easy' => $stats['best_time_easy'],
                    'medium' => $stats['best_time_medium'],
                    'hard' => $stats['best_time_hard']
                ]
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode($dataToReturn);
        
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
}

// Обработка POST запроса (сохранение статистики)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Invalid JSON']);
        exit();
    }
    
    try {
        // Проверяем, есть ли уже статистика для пользователя
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_stats WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $exists = $stmt->fetchColumn();
        
        if ($exists) {
            // Обновляем существующую запись
            $stmt = $pdo->prepare("
                UPDATE user_stats SET 
                    total_games = ?,
                    games_won = ?,
                    best_time_easy = ?,
                    best_time_medium = ?,
                    best_time_hard = ?
                WHERE user_id = ?
            ");
            
            $stmt->execute([
                $input['totalGames'],
                $input['gamesWon'],
                $input['bestTimes']['easy'],
                $input['bestTimes']['medium'],
                $input['bestTimes']['hard'],
                $_SESSION['user_id']
            ]);
        } else {
            // Создаем новую запись
            $stmt = $pdo->prepare("
                INSERT INTO user_stats (
                    user_id, 
                    total_games, 
                    games_won, 
                    best_time_easy, 
                    best_time_medium, 
                    best_time_hard
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $input['totalGames'],
                $input['gamesWon'],
                $input['bestTimes']['easy'],
                $input['bestTimes']['medium'],
                $input['bestTimes']['hard']
            ]);
        }
        
        echo json_encode(['success' => true]);
        
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
}

// Если метод не поддерживается
header('HTTP/1.1 405 Method Not Allowed');
echo json_encode(['error' => 'Method not allowed']);
?>