<?php
header('Content-Type: application/json');
require_once 'config.php';

session_start();

// Проверка авторизации
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }
    return $_SESSION['user_id'];
}

// Получение игры
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['type'])) {
    $userId = checkAuth();
    $type = $_GET['type'];
    
    try {
        switch ($type) {
            case 'game':
                $gameData = get_user_game($userId);
                echo json_encode($gameData ?: ['error' => 'No game data']);
                break;
                
            case 'stats':
                $stats = get_user_stats($userId);
                echo json_encode($stats ?: ['error' => 'No stats data']);
                break;
                
            case 'achievements':
                $achievements = get_user_achievements($userId);
                echo json_encode($achievements ?: ['error' => 'No achievements data']);
                break;
                
            case 'game_exists':
                $exists = has_user_game($userId);
                echo json_encode(['exists' => $exists]);
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid type']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit();
}

// Сохранение данных
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['type'])) {
    $userId = checkAuth();
    $type = $_GET['type'];
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        switch ($type) {
            case 'game':
                save_user_game($userId, $data);
                echo json_encode(['success' => true]);
                break;
                
            case 'stats':
                save_user_stats($userId, $data);
                echo json_encode(['success' => true]);
                break;
                
            case 'achievements':
                save_user_achievements($userId, $data);
                echo json_encode(['success' => true]);
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid type']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit();
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
?>