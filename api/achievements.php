<?php
error_reporting(0); // Отключаем вывод ошибок
header('Content-Type: application/json');
require_once __DIR__.'/../config.php';

// Включаем вывод ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$userId = $_SESSION['user_id'];

try {
    $pdo = new PDO(
        'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8',
        DB_USER,
        DB_PASSWORD
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->prepare("SELECT achievements_data FROM user_achievements WHERE user_id = ?");
        $stmt->execute([$userId]);
        $data = $stmt->fetchColumn();
        
        echo $data ?: '[]'; // Всегда возвращаем массив, даже пустой
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data format']);
            exit();
        }
        
        $jsonData = json_encode($data);
        $stmt = $pdo->prepare("REPLACE INTO user_achievements (user_id, achievements_data) VALUES (?, ?)");
        $stmt->execute([$userId, $jsonData]);
        
        echo json_encode(['success' => true]);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: '.$e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
echo json_encode($dataToReturn);
exit();
?>