<?php
// websocket_server.php - улучшенная версия для sweb.ru
if (php_sapi_name() !== 'cli') {
    die('Этот скрипт можно запускать только из командной строки');
}

// Настройки для sweb.ru
date_default_timezone_set('Europe/Moscow');
set_time_limit(0); // Убираем ограничение времени выполнения
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "[" . date('Y-m-d H:i:s') . "] 🚀 Запуск WebSocket сервера для ПлюкСудоку\n";
echo "[" . date('Y-m-d H:i:s') . "] 📍 Хост: localhost, Порт: 8081\n";

// Логируем в файл
function log_message($message) {
    $log_file = 'websocket.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message\n";
    echo $log_entry;
    
    // Сохраняем в лог (максимум 1000 строк)
    if (file_exists($log_file)) {
        $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_slice($lines, -999); // сохраняем последние 1000 строк
        $lines[] = $log_entry;
        file_put_contents($log_file, implode(PHP_EOL, $lines) . PHP_EOL);
    } else {
        file_put_contents($log_file, $log_entry, FILE_APPEND);
    }
}

// Подключаем конфиг
try {
    require_once 'config.php';
    log_message("✅ Конфиг загружен успешно");
} catch (Exception $e) {
    log_message("❌ Ошибка загрузки конфига: " . $e->getMessage());
    exit(1);
}

// Упрощенный WebSocket сервер для тестирования
class SimpleWebSocketServer {
    private $clients = [];
    private $server;

    public function __construct($host = 'localhost', $port = 8081) {
        $this->server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        
        if ($this->server === false) {
            throw new Exception("Не удалось создать сокет: " . socket_strerror(socket_last_error()));
        }
        
        socket_set_option($this->server, SOL_SOCKET, SO_REUSEADDR, 1);
        
        if (!socket_bind($this->server, $host, $port)) {
            throw new Exception("Не удалось привязать сокет: " . socket_strerror(socket_last_error()));
        }
        
        if (!socket_listen($this->server)) {
            throw new Exception("Не удалось слушать сокет: " . socket_strerror(socket_last_error()));
        }
        
        socket_set_nonblock($this->server);
        
        log_message("✅ Сервер запущен на $host:$port");
    }

    public function run() {
        log_message("🔄 Сервер начал работу");
        
        while (true) {
            $read = [$this->server];
            $write = $except = null;
            
            // Ждем активности (таймаут 1 секунда)
            $changed = socket_select($read, $write, $except, 1);
            
            if ($changed === false) {
                log_message("❌ Ошибка socket_select");
                continue;
            }
            
            if ($changed > 0) {
                foreach ($read as $socket) {
                    if ($socket == $this->server) {
                        $this->acceptClient();
                    } else {
                        $this->handleClient($socket);
                    }
                }
            }
            
            // Отправляем ping каждые 30 секунд чтобы проверить соединения
            static $last_ping = 0;
            if (time() - $last_ping > 30) {
                $this->sendPing();
                $last_ping = time();
            }
            
            usleep(100000); // 100ms пауза
        }
    }

    private function acceptClient() {
        $client = socket_accept($this->server);
        
        if ($client !== false) {
            socket_set_nonblock($client);
            
            // Простой handshake
            $headers = socket_read($client, 1024);
            
            if (strpos($headers, 'Sec-WebSocket-Key') !== false) {
                $this->performHandshake($client, $headers);
                
                $this->clients[] = $client;
                log_message("✅ Новый клиент подключен. Всего клиентов: " . count($this->clients));
                
                // Отправляем приветственное сообщение
                $this->sendToClient($client, [
                    'type' => 'welcome',
                    'message' => 'Добро пожаловать в турнирную систему ПлюкСудоку!',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                socket_close($client);
            }
        }
    }

    private function performHandshake($client, $headers) {
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $headers, $match)) {
            $key = base64_encode(sha1($match[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
            
            $response = "HTTP/1.1 101 Switching Protocols\r\n" .
                       "Upgrade: websocket\r\n" .
                       "Connection: Upgrade\r\n" .
                       "Sec-WebSocket-Accept: $key\r\n\r\n";
            
            socket_write($client, $response, strlen($response));
            return true;
        }
        return false;
    }

    private function handleClient($socket) {
        $data = socket_read($socket, 1024, PHP_NORMAL_READ);
        
        if ($data === false || $data === '') {
            // Клиент отключился
            $this->removeClient($socket);
            return;
        }
        
        // Обработка сообщений от клиента
        $message = $this->decodeMessage($data);
        if ($message) {
            $this->processMessage($socket, $message);
        }
    }

    private function decodeMessage($data) {
        // Упрощенный декодинг для тестирования
        $length = ord($data[1]) & 127;
        $mask = null;
        $payload = '';
        
        if ($length == 126) {
            $mask = substr($data, 4, 4);
            $payload = substr($data, 8);
        } elseif ($length == 127) {
            $mask = substr($data, 10, 4);
            $payload = substr($data, 14);
        } else {
            $mask = substr($data, 2, 4);
            $payload = substr($data, 6);
        }
        
        $decoded = '';
        for ($i = 0; $i < strlen($payload); $i++) {
            $decoded .= $payload[$i] ^ $mask[$i % 4];
        }
        
        return json_decode($decoded, true);
    }

    private function encodeMessage($text) {
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($text);
        
        if ($length <= 125) {
            $header = pack('CC', $b1, $length);
        } elseif ($length > 125 && $length < 65536) {
            $header = pack('CCn', $b1, 126, $length);
        } else {
            $header = pack('CCNN', $b1, 127, 0, $length);
        }
        
        return $header . $text;
    }

    private function processMessage($socket, $message) {
        if (!isset($message['type'])) return;
        
        log_message("📨 Получено сообщение: " . $message['type']);
        
        switch ($message['type']) {
            case 'auth':
                $this->sendToClient($socket, [
                    'type' => 'auth_success',
                    'user' => [
                        'id' => $message['user_id'] ?? 1,
                        'username' => $message['username'] ?? 'ТестовыйИгрок'
                    ],
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                break;
                
            case 'join_tournament':
                $this->sendToClient($socket, [
                    'type' => 'tournament_joined',
                    'tournament_id' => $message['tournament_id'],
                    'message' => 'Вы успешно присоединились к турниру!',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                break;
                
            case 'ping':
                $this->sendToClient($socket, [
                    'type' => 'pong',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                break;
        }
    }

    private function sendToClient($client, $message) {
        $encoded = $this->encodeMessage(json_encode($message));
        socket_write($client, $encoded, strlen($encoded));
    }

    private function sendPing() {
        $ping_message = $this->encodeMessage(json_encode(['type' => 'ping']));
        
        foreach ($this->clients as $client) {
            @socket_write($client, $ping_message, strlen($ping_message));
        }
        
        log_message("📤 Отправлен ping всем клиентам");
    }

    private function removeClient($socket) {
        $index = array_search($socket, $this->clients);
        if ($index !== false) {
            socket_close($socket);
            unset($this->clients[$index]);
            log_message("❌ Клиент отключен. Осталось клиентов: " . count($this->clients));
        }
    }

    public function __destruct() {
        if ($this->server) {
            socket_close($this->server);
            log_message("🔴 Сервер остановлен");
        }
    }
}

// Запуск сервера
try {
    $server = new SimpleWebSocketServer('localhost', 9001);
    $server->run();
} catch (Exception $e) {
    log_message("💥 Критическая ошибка: " . $e->getMessage());
    exit(1);
}
?>