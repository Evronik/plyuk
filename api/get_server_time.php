<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Устанавливаем правильный часовой пояс
date_default_timezone_set('Europe/Moscow');

// Возвращаем текущее время сервера в миллисекундах
$response = [
    'success' => true,
    'server_time' => round(microtime(true) * 1000), // Время в миллисекундах
    'server_time_iso' => date('c'),
    'server_timezone' => date_default_timezone_get()
];

echo json_encode($response);
?>