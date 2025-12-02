<?php
require_once '../config.php';

if (!is_admin()) {
    die('Access denied');
}

// Принудительно рассчитываем результаты для всех завершенных турниров
$stmt = $pdo->prepare("SELECT id FROM tournaments WHERE status = 'completed'");
$stmt->execute();
$tournaments = $stmt->fetchAll();

foreach ($tournaments as $tournament) {
    // Здесь должен быть вызов функции calculateTournamentWinners
    echo "Calculating results for tournament: " . $tournament['id'] . "<br>";
}

echo "Done!";
?>