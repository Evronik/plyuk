<?php
require_once 'config.php';

// Устанавливаем заголовок для JSON-ответа
header('Content-Type: application/json');

// Проверяем метод запроса (должен быть GET)
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Метод запроса не поддерживается']);
    exit;
}

// Проверяем авторизацию пользователя
if (!is_logged_in()) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Необходима авторизация']);
    exit;
}

// Получаем и проверяем ID пользователя
$userId = filter_input(INPUT_GET, 'userId', FILTER_VALIDATE_INT, [
    'options' => [
        'min_range' => 1,
        'default' => 0
    ]
]);

// Проверяем, что запрашивает текущий пользователь
if ($userId !== $_SESSION['user_id']) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Доступ запрещен']);
    exit;
}

// Загружаем игру из базы данных
try {
    // Начинаем транзакцию
    $pdo->beginTransaction();
    
    // Получаем данные игры
    $stmt = $pdo->prepare("SELECT game_data, updated_at FROM saved_games WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $game = $stmt->fetch();
    
    if ($game) {
        // Декодируем JSON данные
        $gameData = json_decode($game['game_data'], true);
        
        // Проверяем валидность JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Неверный формат сохраненной игры');
        }
        
        // Добавляем метаданные
        $response = [
            'success' => true,
            'gameData' => $gameData,
            'lastSaved' => $game['updated_at'],
            'status' => 'loaded'
        ];
        
        echo json_encode($response);
    } else {
        echo json_encode([
            'success' => true,
            'gameData' => null,
            'status' => 'no_saved_game'
        ]);
    }
    
    // Фиксируем транзакцию
    $pdo->commit();
    
} catch (PDOException $e) {
    // Откатываем транзакцию в случае ошибки
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'Ошибка загрузки игры',
        'details' => DEBUG_MODE ? $e->getMessage() : null
    ]);
    
    // Логируем ошибку
    error_log('Ошибка загрузки игры: ' . $e->getMessage());
    
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'Ошибка обработки данных игры',
        'details' => DEBUG_MODE ? $e->getMessage() : null
    ]);
    
    // Логируем ошибку
    error_log('Ошибка обработки данных игры: ' . $e->getMessage());
}
?>