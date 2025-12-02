<?php
require_once 'config.php';
session_start();

// Ключ для кеша
$cache_key = 'leaderboard_cache';
$cache_lifetime = 300; // 5 минут

// Пытаемся получить данные из кеша (Memcached)
$memcached = new Memcached();
$memcached->addServer('localhost', 11211);
$leaderboard = $memcached->get($cache_key);

// Если в кеше нет данных, идем в базу
if ($leaderboard === false) {
    try {
        $stmt = $pdo->query("SELECT ... "); // Ваш запрос
        $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Сохраняем результат в кеш на 5 минут
        $memcached->set($cache_key, $leaderboard, $cache_lifetime);
    } catch (PDOException $e) {
        error_log("Leaderboard error: " . $e->getMessage());
        $leaderboard = [];
    }
}

header('Content-Type: application/json');
echo json_encode($leaderboard);
?>