<?php
require_once 'config.php';

// Если пользователь уже авторизован, перенаправляем на главную
if (is_logged_in()) {
    redirect('game.php');
}

$errors = [];
$email = '';
$logout_message = '';

// Проверяем сообщение о выходе из системы
if (isset($_SESSION['logout_message'])) {
    $logout_message = $_SESSION['logout_message'];
    unset($_SESSION['logout_message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка CSRF-токена
    verify_csrf_token();
    
    // Очистка и получение данных из формы
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember_me']);
    
    // Валидация
    if (empty($email)) {
        $errors['email'] = 'Введите email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Введите корректный email';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Введите пароль';
    }
    
    // Проверка учетных данных
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id, username, email, password_hash FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($password, $user['password_hash'])) {
                // Проверяем, есть ли пользователь в ожидающих регистрациях
                $stmt = $pdo->prepare("
                    SELECT id FROM pending_registrations 
                    WHERE email = ? AND expires_at > NOW()
                ");
                $stmt->execute([$email]);
                $pending_user = $stmt->fetch();
                
                if ($pending_user) {
                    $errors['auth'] = 'Аккаунт не подтвержден. Проверьте Вашу почту для подтверждения регистрации.';
                } else {
                    // Задержка для защиты от брутфорса
                    sleep(1);
                    $errors['auth'] = 'Неверный email или пароль';
                }
            } else {
                // Успешная авторизация
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];

                // ОЧИСТКА ФЛАГОВ ИГРЫ ПРИ АВТОРИЗАЦИИ
                unset($_SESSION['game_lost'], $_SESSION['was_solved'], $_SESSION['win_shown']);

                // Функция "Запомнить меня"
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expiry = time() + 60 * 60 * 24 * 30; // 30 дней
                    
                    // Правильные параметры cookies
                    setcookie(
                        'remember_token',
                        $token,
                        [
                            'expires' => $expiry,
                            'path' => '/',
                            'domain' => $_SERVER['HTTP_HOST'],
                            'secure' => isset($_SERVER['HTTPS']),
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ]
                    );
                    
                    setcookie(
                        'user_id',
                        $user['id'],
                        [
                            'expires' => $expiry,
                            'path' => '/',
                            'domain' => $_SERVER['HTTP_HOST'],
                            'secure' => isset($_SERVER['HTTPS']),
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ]
                    );
                    
                    // Сохраняем токен в базу данных
                    $stmt = $pdo->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                    $stmt->execute([$user['id'], $token, date('Y-m-d H:i:s', $expiry)]);
                }

                // Перенаправляем на главную
                redirect('game.php');
            }
        } catch (PDOException $e) {
            error_log('Ошибка при авторизации: ' . $e->getMessage());
            $errors['auth'] = 'Произошла ошибка при авторизации. Пожалуйста, попробуйте позже.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход | ПризСудоку</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/font-awesome/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/login.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/logo.css">
    <style>
    body {
        background-image: url("/img/fon.jpg");
        background-repeat: no-repeat;
        background-position: center center;
        background-attachment: fixed;
        background-size: cover;
        margin: 0;
        padding: 0;
        min-height: 100vh;
    }

    /* Дополнительно для мобильных устройств */
    @media (max-width: 768px) {
        body {
            background-size: cover;
            background-position: center center;
        }
    }

    /* Для очень маленьких экранов */
    @media (max-width: 480px) {
        body {
            background-size: cover;
            background-position: center center;
        }
    }
</style>
    
</head>
<body>
    <div class="container">
        <div class="logo">
                <a href="index.php" <?= $isGuest ? 'onclick="return handleGuestLogoClick(event)"' : '' ?>>
                    <svg width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 2.99976H29V17.5847C29 21.3236 27.0054 24.7786 23.7676 26.6483L14.5 31.9998L5.23246 26.6483C1.99458 24.7786 3.32377e-06 21.3236 3.32377e-06 17.5847L0 2.99976Z" fill="#FFDD2D"/>
                        <path xmlns="http://www.w3.org/2000/svg" d="M0 0 C5.28 0 10.56 0 16 0 C16 0.66 16 1.32 16 2 C15.34 2 14.68 2 14 2 C14 5.3 14 8.6 14 12 C14.66 12 15.32 12 16 12 C16 12.66 16 13.32 16 14 C13.69 14 11.38 14 9 14 C9 13.34 9 12.68 9 12 C9.66 12 10.32 12 11 12 C11 8.7 11 5.4 11 2 C9.02 2 7.04 2 5 2 C5 5.3 5 8.6 5 12 C5.66 12 6.32 12 7 12 C7 12.66 7 13.32 7 14 C4.69 14 2.38 14 0 14 C0 13.34 0 12.68 0 12 C0.66 12 1.32 12 2 12 C2 8.7 2 5.4 2 2 C1.34 2 0.68 2 0 2 C0 1.34 0 0.68 0 0 Z " fill="#333333" transform="translate(7,9)"/>
                    </svg>
                </a>
<a href="index.php" class="logo-text" <?= $isGuest ? 'onclick="return handleGuestLogoClick(event)"' : '' ?>>
<h1>
  <span class="word">Плюк<span class="superscript">s</span> </span>
  <span class="word sudoku-animated">Судоку</span>
</h1>
</a>
            </div>

        <h2>Вход в аккаунт</h2>
        
        <?php if (!empty($logout_message)): ?>
            <div class="success"><?= htmlspecialchars($logout_message) ?></div>
        <?php endif; ?>
        
        <?php if (isset($errors['auth'])): ?>
            <div class="error auth"><?= htmlspecialchars($errors['auth']) ?></div>
        <?php endif; ?>
        
        <form action="login.php" method="post" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" 
                       required autocomplete="email">
                <?php if (isset($errors['email'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['email']) ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required 
                       autocomplete="current-password">
                <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                <?php if (isset($errors['password'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['password']) ?></div>
                <?php endif; ?>
            </div>
            
            <div class="remember-me">
            <input type="checkbox" id="remember_me" name="remember_me">
            <label for="remember_me">Запомнить меня</label>
            </div>
            
            <button type="submit" id="submitBtn">Войти</button>
        </form>
        
        <div class="register-link">
            Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
        </div>
    </div>

    <script>
        // Переключение видимости пароля
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this;
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });

        // Фокус на поле email при загрузке страницы
        document.getElementById('email').focus();
    </script>
</body>
</html>