<?php
require_once 'config.php';

echo "<h2>Тест автоматической системы турниров</h2>";

// Вручную запускаем автоматическую систему
try {
    $result = update_tournament_statuses();
    
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px;'>";
    echo "<h3>✅ Обновление статусов:</h3>";
    echo "<p>Активировано турниров: " . $result['active'] . "</p>";
    echo "<p>Завершено турниров: " . $result['completed'] . "</p>";
    echo "</div>";
    
    // Проверяем турниры для расчета
    $stmt = $pdo->prepare("
        SELECT t.id, t.name, t.status, t.start_time
        FROM tournaments t
        LEFT JOIN tournament_results tr ON t.id = tr.tournament_id
        WHERE t.status = 'completed' 
        AND tr.id IS NULL
    ");
    $stmt->execute();
    $tournamentsToCalculate = $stmt->fetchAll();
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h3>🔔 Турниры для автоматического расчета:</h3>";
    
    if (empty($tournamentsToCalculate)) {
        echo "<p>Нет турниров для расчета</p>";
    } else {
        echo "<ul>";
        foreach ($tournamentsToCalculate as $tournament) {
            echo "<li><strong>{$tournament['name']}</strong> (ID: {$tournament['id']}) - {$tournament['start_time']}</li>";
        }
        echo "</ul>";
        
        echo "<p><a href='api/auto_calculate_tournaments.php' target='_blank' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Запустить расчет сейчас</a></p>";
    }
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ Ошибка:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>