<?php
require_once 'config.php';

// Устанавливаем заголовок для JSON-ответа
header('Content-Type: application/json');

// Проверяем метод запроса (должен быть POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Метод не разрешен']);
    exit;
}

// Проверяем авторизацию пользователя
if (!is_logged_in()) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Не авторизован']);
    exit;
}

// Получаем и декодируем JSON-данные
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Проверяем валидность JSON
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Неверный формат JSON']);
    exit;
}

// Проверяем обязательные поля
if (!isset($data['userId']) || !isset($data['board']) || !isset($data['solution'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Отсутствуют обязательные данные']);
    exit;
}

// Проверяем, что данные принадлежат текущему пользователю
if ($data['userId'] != $_SESSION['user_id']) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Доступ запрещен']);
    exit;
}

// Подготовка данных для сохранения
$gameData = [
    'board' => $data['board'],
    'solution' => $data['solution'],
    'fixedCells' => $data['fixedCells'] ?? [],
    'currentDifficulty' => $data['currentDifficulty'] ?? 'easy',
    'seconds' => $data['seconds'] ?? 0,
    'mistakes' => $data['mistakes'] ?? 0,
    'hintsUsed' => $data['hintsUsed'] ?? 0,
    'hintsLeft' => $data['hintsLeft'] ?? 3,
    'isGameOver' => $data['isGameOver'] ?? false,
    'savedAt' => date('Y-m-d H:i:s')
];

// Сохраняем игру в базу данных
try {
    // Начинаем транзакцию
    $pdo->beginTransaction();
    
    // Проверяем, есть ли уже сохраненная игра
    $stmt = $pdo->prepare("SELECT id FROM saved_games WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Обновляем существующую запись
        $stmt = $pdo->prepare("UPDATE saved_games SET game_data = ?, updated_at = NOW() WHERE user_id = ?");
        $stmt->execute([json_encode($gameData), $_SESSION['user_id']]);
    } else {
        // Создаем новую запись
        $stmt = $pdo->prepare("INSERT INTO saved_games (user_id, game_data) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], json_encode($gameData)]);
    }
    
    // Фиксируем транзакцию
    $pdo->commit();
    
    // Возвращаем успешный ответ
    echo json_encode([
        'success' => true,
        'message' => 'Игра успешно сохранена',
        'savedAt' => $gameData['savedAt']
    ]);
    
} catch (PDOException $e) {
    // Откатываем транзакцию в случае ошибки
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'Ошибка сохранения игры',
        'details' => DEBUG_MODE ? $e->getMessage() : null
    ]);
    
    // Логируем ошибку
    error_log('Ошибка сохранения игры: ' . $e->getMessage());
}
?>