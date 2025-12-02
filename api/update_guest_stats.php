<?php
// api/update_guest_stats.php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $stats = json_decode($input, true);
    
    if ($stats) {
        // Сохраняем в сессии для текущего запроса
        $_SESSION['guest_stats'] = $stats;
        
        // Также устанавливаем куку
        setcookie('sudoku_stats', urlencode($input), time() + 31536000, '/');
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
    }
    exit;
}
?>