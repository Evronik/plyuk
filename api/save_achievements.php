<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !is_array($input)) {
    echo json_encode(['success' => false, 'error' => 'Invalid input data']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Для каждого достижения обновляем или вставляем запись
    foreach ($input as $achievement) {
        if (!isset($achievement['id'])) {
            continue; // Пропускаем достижения без ID
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO user_achievements 
            (user_id, achievement_id, unlocked, progress, unlocked_at) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                unlocked = VALUES(unlocked),
                progress = VALUES(progress),
                unlocked_at = VALUES(unlocked_at),
                updated_at = NOW()
        ");
        
        $unlocked = isset($achievement['unlocked']) ? (int)$achievement['unlocked'] : 0;
        $progress = isset($achievement['progress']) ? (int)$achievement['progress'] : 0;
        
        // Устанавливаем время разблокировки если достижение разблокировано
        $unlockedAt = null;
        if ($unlocked && isset($achievement['unlockedAt'])) {
            $unlockedAt = $achievement['unlockedAt'];
        } elseif ($unlocked && !isset($achievement['unlockedAt'])) {
            $unlockedAt = date('Y-m-d H:i:s');
        }
        
        $stmt->execute([
            $user_id,
            $achievement['id'],
            $unlocked,
            $progress,
            $unlockedAt
        ]);
    }
    
    $pdo->commit();
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error saving achievements: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>