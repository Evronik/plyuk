<?php
require_once '../config.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
    exit;
}

try {
    // Создаем таблицу если её нет
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tournament_seen (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT,
            tournament_id INT,
            seen_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
            UNIQUE KEY unique_seen (user_id, tournament_id)
    )");
    
    // Получаем ID просмотренных турниров
    $stmt = $pdo->prepare("
        SELECT tournament_id 
        FROM tournament_seen 
        WHERE user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $seenTournaments = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true,
        'seen_tournaments' => $seenTournaments
    ]);
    
} catch (PDOException $e) {
    error_log("Get seen tournaments error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Ошибка загрузки просмотренных турниров',
        'seen_tournaments' => []
    ]);
}
?>