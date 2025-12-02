<?php
// test_save.php - простой тест сохранения
file_put_contents('debug.txt', "=== TEST SAVE ===\n", FILE_APPEND);
file_put_contents('debug.txt', "Time: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

// Получаем сырые данные
$input = file_get_contents('php://input');
file_put_contents('debug.txt', "Raw input: " . $input . "\n", FILE_APPEND);

// Парсим JSON
$data = json_decode($input, true);
file_put_contents('debug.txt', "Parsed data: " . print_r($data, true) . "\n", FILE_APPEND);

// Проверяем наличие seconds
if (isset($data['seconds'])) {
    file_put_contents('debug.txt', "✓ Seconds found: " . $data['seconds'] . "\n", FILE_APPEND);
} else {
    file_put_contents('debug.txt', "✗ Seconds NOT FOUND in data!\n", FILE_APPEND);
    file_put_contents('debug.txt', "Available keys: " . implode(', ', array_keys($data)) . "\n", FILE_APPEND);
}

file_put_contents('debug.txt', "================\n\n", FILE_APPEND);

// Ответ
echo json_encode(['success' => true, 'received_seconds' => $data['seconds'] ?? 'NOT_FOUND']);
?>