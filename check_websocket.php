<?php
// check_websocket.php - проверка и перезапуск сервера
$check = shell_exec('pgrep -f websocket_server.php');
if (empty(trim($check))) {
    // Сервер не запущен - запускаем
    shell_exec('cd /home/p/partners1p/plyuk/public_html && /usr/bin/php7.3 websocket_server.php > /dev/null 2>&1 &');
    file_put_contents('websocket.log', '[' . date('Y-m-d H:i:s') . '] Сервер перезапущен\n', FILE_APPEND);
}
?>