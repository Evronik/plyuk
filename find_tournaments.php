<?php
require_once 'config.php';

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Управление турнирами</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        table { border-collapse: collapse; width: 100%; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        th, td { padding: 12px 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #667eea; color: white; font-weight: 600; }
        tr:hover { background: #f8f9fa; }
        .needs-calculation { background: #fff3cd !important; }
        .btn { background: #28a745; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: #218838; }
        .tournament-detail { border: 2px solid #667eea; padding: 15px; margin: 10px 0; border-radius: 8px; background: white; }
        .status-completed { color: #28a745; font-weight: 600; }
        .status-active { color: #007bff; font-weight: 600; }
        .status-registration { color: #6c757d; font-weight: 600; }
        .notification { background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>";

echo "<h1>🎯 Управление турнирами</h1>";

// Автоматический расчет завершенных турниров при загрузке
try {
    $stmt = $pdo->prepare("
        SELECT t.id, t.name
        FROM tournaments t
        LEFT JOIN tournament_results tr ON t.id = tr.tournament_id
        WHERE t.status = 'completed' 
        AND tr.id IS NULL
        LIMIT 1
    ");
    $stmt->execute();
    $tournamentToCalculate = $stmt->fetch();
    
    if ($tournamentToCalculate) {
        echo "<div class='notification'>";
        echo "🔔 Обнаружены завершенные турниры без результатов. ";
        echo "<a href='calculate_single.php?tournament_id={$tournamentToCalculate['id']}' style='color: #155724; text-decoration: underline;'>";
        echo "Рассчитать автоматически для турнира '{$tournamentToCalculate['name']}'";
        echo "</a>";
        echo "</div>";
    }
} catch (PDOException $e) {
    // Игнорируем ошибки в этом блоке
}

try {
    // Покажем все турниры с количеством участников
    $stmt = $pdo->query("
        SELECT 
            t.id,
            t.name,
            t.status,
            t.start_time,
            t.prize_pool,
            (SELECT COUNT(*) FROM tournament_registrations tr WHERE tr.tournament_id = t.id) as participants_count,
            (SELECT COUNT(*) FROM tournament_results tr_results WHERE tr_results.tournament_id = t.id) as results_count
        FROM tournaments t
        ORDER BY t.id DESC
    ");
    
    $tournaments = $stmt->fetchAll();
    
    echo "<h2>Все турниры</h2>";
    echo "<table>";
    echo "<tr>
            <th>ID</th>
            <th>Название</th>
            <th>Статус</th>
            <th>Участников</th>
            <th>Результатов</th>
            <th>Время начала</th>
            <th>Призовой фонд</th>
            <th>Действие</th>
          </tr>";
    
    foreach ($tournaments as $tournament) {
        $needs_calculation = $tournament['status'] == 'completed' && $tournament['results_count'] == 0;
        $row_class = $needs_calculation ? 'needs-calculation' : '';
        
        // Определяем цвет статуса
        $status_class = 'status-' . $tournament['status'];
        
        echo "<tr class='{$row_class}'>";
        echo "<td><strong>{$tournament['id']}</strong></td>";
        echo "<td>{$tournament['name']}</td>";
        echo "<td class='{$status_class}'>{$tournament['status']}</td>";
        echo "<td style='text-align: center;'>{$tournament['participants_count']}</td>";
        echo "<td style='text-align: center;'>{$tournament['results_count']}</td>";
        echo "<td>{$tournament['start_time']}</td>";
        echo "<td style='text-align: center;'>{$tournament['prize_pool']} чатлов</td>";
        
        if ($needs_calculation) {
            echo "<td><button onclick=\"calculateTournament({$tournament['id']})\" class='btn'>Рассчитать</button></td>";
        } else {
            echo "<td>-</td>";
        }
        
        echo "</tr>";
    }
    echo "</table>";
    
    // Покажем детали по последним 5 турнирам
    echo "<h2>Детали по последним 5 турнирам</h2>";
    
    // Сначала получим ID последних 5 турниров
    $stmt = $pdo->query("SELECT id FROM tournaments ORDER BY id DESC LIMIT 5");
    $lastTournamentIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($lastTournamentIds)) {
        $placeholders = str_repeat('?,', count($lastTournamentIds) - 1) . '?';
        
        $stmt = $pdo->prepare("
            SELECT t.id, t.name, t.status, t.start_time,
                   u.username, tr.status as reg_status
            FROM tournaments t
            LEFT JOIN tournament_registrations tr ON t.id = tr.tournament_id
            LEFT JOIN users u ON tr.user_id = u.id
            WHERE t.id IN ($placeholders)
            ORDER BY t.id DESC, tr.id ASC
        ");
        $stmt->execute($lastTournamentIds);
        
        $details = $stmt->fetchAll();
        
        $current_tournament = null;
        foreach ($details as $detail) {
            if ($current_tournament != $detail['id']) {
                if ($current_tournament !== null) echo "</div>";
                echo "<div class='tournament-detail'>";
                echo "<h3>🏆 {$detail['name']} (ID: {$detail['id']})</h3>";
                
                // Статус турнира с цветом
                $status_class = 'status-' . $detail['status'];
                echo "<p><strong>Статус:</strong> <span class='{$status_class}'>{$detail['status']}</span></p>";
                echo "<p><strong>Время начала:</strong> {$detail['start_time']}</p>";
                echo "<p><strong>Участники:</strong></p>";
                $current_tournament = $detail['id'];
            }
            
            if ($detail['username']) {
                $status_color = $detail['reg_status'] == 'completed' ? '#28a745' : '#ffc107';
                echo "<p>👤 {$detail['username']} (статус: <span style='color: {$status_color}; font-weight: 600;'>{$detail['reg_status']}</span>)</p>";
            }
        }
        if ($current_tournament !== null) echo "</div>";
    } else {
        echo "<p>Турниры не найдены</p>";
    }

} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ Ошибка базы данных</h3>";
    echo "<p><strong>Ошибка:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "
<script>
function calculateTournament(tournamentId) {
    if (confirm('Рассчитать результаты для турнира ' + tournamentId + '?')) {
        window.location.href = 'calculate_single.php?tournament_id=' + tournamentId;
    }
}
</script>
</body>
</html>";
?>