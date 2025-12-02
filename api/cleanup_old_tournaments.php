<?php
require_once '../config.php';

// Удаляем турниры старше 30 дней
$pdo->exec("DELETE FROM tournaments WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");

// Очищаем просмотренные турниры старше 7 дней  
$pdo->exec("DELETE FROM tournament_seen WHERE seen_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");
?>