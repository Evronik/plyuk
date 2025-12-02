<?php
require_once 'config.php';

try {
    echo "Checking database tables...\n";
    
    // Таблица user_stats
    $sql = "CREATE TABLE IF NOT EXISTS user_stats (
        user_id INT NOT NULL PRIMARY KEY,
        stats_data LONGTEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    echo "Table user_stats: OK\n";
    
    // Проверить работу
    $testData = [
        'totalGames' => 1,
        'gamesWon' => 1,
        'bestTimes' => ['easy' => 100, 'medium' => 200, 'hard' => 300]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO user_stats (user_id, stats_data) VALUES (?, ?)");
    $result = $stmt->execute([1, json_encode($testData)]);
    
    if ($result) {
        echo "Test data inserted successfully\n";
        
        // Проверить чтение
        $stmt = $pdo->query("SELECT * FROM user_stats WHERE user_id = 1");
        $data = $stmt->fetch();
        
        if ($data) {
            echo "Data retrieved: " . print_r(json_decode($data['stats_data'], true), true) . "\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n";
}
?>