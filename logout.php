<?php
require_once 'config.php';

// Сохраняем ID пользователя для очистки remember token
$user_id = $_SESSION['user_id'] ?? null;

// Сохраняем сообщение о выходе перед уничтожением сессии
$logout_message = 'Вы успешно вышли из системы';

// Очищаем все данные сессии
$_SESSION = array();

// Удаляем remember token из базы данных если есть
if (isset($_COOKIE['remember_token']) && $user_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = ? AND token = ?");
        $stmt->execute([$user_id, $_COOKIE['remember_token']]);
    } catch (PDOException $e) {
        error_log('Error deleting remember token: ' . $e->getMessage());
    }
}

// Удаляем remember me cookies
$cookie_params = [
    'expires' => time() - 3600,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
];

if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', $cookie_params);
}

if (isset($_COOKIE['user_id'])) {
    setcookie('user_id', '', $cookie_params);
}

// Уничтожаем сессию
session_destroy();

// Удаляем session cookie
setcookie(session_name(), '', time() - 42000, '/');

// Начинаем новую сессию с теми же параметрами, что и в config.php
session_start([
    'cookie_lifetime' => 86400,
    'gc_maxlifetime'  => 86400,
]);

// Генерируем новый CSRF токен
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$_SESSION['logout_message'] = $logout_message;

// Перенаправляем на страницу входа
header('Location: login.php');
exit();
?>