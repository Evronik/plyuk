<?php
// api/debug_tables.php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit;
}

try {
    // Проверяем существование таблицы
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'user_games'");
    $stmt->execute();
    $tableExists = $stmt->fetch();

    if (!$tableExists) {
        echo json_encode(['success' => true, 'tableExists' => false]);
        exit;
    }

    // Получаем структуру таблицы
    $stmt = $pdo->prepare("DESCRIBE user_games");
    $stmt->execute();
    $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Получаем количество записей
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_games");
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'tableExists' => true,
        'structure' => $structure,
        'recordCount' => $count['count']
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>