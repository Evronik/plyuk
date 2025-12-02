<?php
require_once 'config.php';

// Если пользователь уже авторизован, перенаправляем на главную
if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка CSRF-токена
    try {
        verify_csrf_token();
    } catch (Exception $e) {
        $errors['general'] = $e->getMessage();
    }
    
    if (empty($errors)) {
        // Очистка и получение данных из формы
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        
        // Валидация имени пользователя
        if (empty($username)) {
            $errors['username'] = 'Введите имя пользователя';
        } elseif (strlen($username) < 3) {
            $errors['username'] = 'Имя пользователя должно быть не менее 3 латинских символов';
        } elseif (strlen($username) > 7) {
            $errors['username'] = 'Имя пользователя должно быть от 3 до 7 латинских символов';
        } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
            $errors['username'] = 'Имя пользователя может содержать только латинские буквы и цифры';
        }
        
        // Валидация email
        if (empty($email)) {
            $errors['email'] = 'Введите email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Введите корректный email';
        } elseif (strlen($email) > 100) {
            $errors['email'] = 'Email должен быть не более 100 символов';
        }
        
        // Валидация пароля
        if (empty($password)) {
            $errors['password'] = 'Введите пароль';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Пароль должен быть не менее 8 символов';
        } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors['password'] = 'Пароль должен содержать хотя бы одну заглавную букву и одну цифру';
        }
        
        // Проверка совпадения паролей
        if ($password !== $password_confirm) {
            $errors['password_confirm'] = 'Пароли не совпадают';
        }
        
        // Проверка уникальности username и email
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                $user = $stmt->fetch();
                
                if ($user) {
                    if (strtolower($user['username']) === strtolower($username)) {
                        $errors['username'] = 'Это имя пользователя уже занято';
                    } else {
                        $errors['email'] = 'Этот email уже занят';
                    }
                }
            } catch (PDOException $e) {
                error_log('Ошибка при проверке уникальности пользователя: ' . $e->getMessage());
                $errors['general'] = 'Произошла ошибка при регистрации. Пожалуйста, попробуйте позже.';
            }
        }
        
        // Регистрация пользователя
        if (empty($errors)) {
            try {
                // Проверяем, нет ли уже ожидающей регистрации
                if (has_pending_registration($username, $email)) {
                    $errors['general'] = 'Регистрация уже ожидает подтверждения. Проверьте вашу почту.';
                } else {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Создаем временную регистрацию
                    $confirmation_code = create_pending_registration($username, $email, $password_hash);
                    
                    if ($confirmation_code) {
                        // Отправляем email с подтверждением
                        $confirmation_link = BASE_URL . "confirm.php?code=" . $confirmation_code;
                        $email_sent = send_confirmation_email($email, $username, $confirmation_link);
                        
                        if ($email_sent) {
                            $_SESSION['registration_pending'] = true;
                            $_SESSION['pending_email'] = $email;
                            redirect('registration_pending.php');
                        } else {
                            $errors['general'] = 'Ошибка при отправке email подтверждения. Пожалуйста, попробуйте позже или свяжитесь с поддержкой.';
                            
                            // Логируем ошибку
                            error_log("Email sending failed for: {$email}");
                            
                            // Удаляем pending запись если email не отправился
                            $stmt = $pdo->prepare("DELETE FROM pending_registrations WHERE email = ?");
                            $stmt->execute([$email]);
                        }
                    } else {
                        $errors['general'] = 'Ошибка при создании временной регистрации. Пожалуйста, попробуйте позже.';
                    }
                }
            } catch (PDOException $e) {
                error_log('Ошибка при регистрации пользователя: ' . $e->getMessage());
                $errors['general'] = 'Произошла ошибка при регистрации. Пожалуйста, попробуйте позже.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация | ПлюкСудоку</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/font-awesome/css/all.min.css">
    <link rel="stylesheet" href="/css/register.css">
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
    
    .error.general {
        background: #ffebee;
        border: 1px solid #f44336;
        color: #c62828;
        padding: 12px;
        border-radius: 4px;
        margin-bottom: 15px;
    }
    
    .success {
        background: #e8f5e8;
        border: 1px solid #4caf50;
        color: #2e7d32;
        padding: 12px;
        border-radius: 4px;
        margin-bottom: 15px;
    }
</style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <a href="index.php">
                <svg width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 2.99976H29V17.5847C29 21.3236 27.0054 24.7786 23.7676 26.6483L14.5 31.9998L5.23246 26.6483C1.99458 24.7786 3.32377e-06 21.3236 3.32377e-06 17.5847L0 2.99976Z" fill="#FFDD2D"/>
                    <path xmlns="http://www.w3.org/2000/svg" d="M0 0 C5.28 0 10.56 0 16 0 C16 0.66 16 1.32 16 2 C15.34 2 14.68 2 14 2 C14 5.3 14 8.6 14 12 C14.66 12 15.32 12 16 12 C16 12.66 16 13.32 16 14 C13.69 14 11.38 14 9 14 C9 13.34 9 12.68 9 12 C9.66 12 10.32 12 11 12 C11 8.7 11 5.4 11 2 C9.02 2 7.04 2 5 2 C5 5.3 5 8.6 5 12 C5.66 12 6.32 12 7 12 C7 12.66 7 13.32 7 14 C4.69 14 2.38 14 0 14 C0 13.34 0 12.68 0 12 C0.66 12 1.32 12 2 12 C2 8.7 2 5.4 2 2 C1.34 2 0.68 2 0 2 C0 1.34 0 0.68 0 0 Z " fill="#333333" transform="translate(7,9)"/>
                </svg>
            </a>
            <a href="index.php" class="logo-text">
                <h1>
                    <span class="word">Плюк<span class="superscript">s</span> </span>
                    <span class="word sudoku-animated">Судоку</span>
                </h1>
            </a>
        </div>

        <h2>Регистрация</h2>
        
        <?php if (isset($errors['general'])): ?>
            <div class="error general"><?= htmlspecialchars($errors['general']) ?></div>
        <?php endif; ?>
        
        <form action="register.php" method="post" id="registerForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            
            <div class="form-group">
                <label for="username">Имя (Ник до 7 символов)</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" 
                       required autocomplete="username" minlength="3" maxlength="7">
                <?php if (isset($errors['username'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['username']) ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" 
                       required autocomplete="email" maxlength="100">
                <?php if (isset($errors['email'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['email']) ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required 
                       autocomplete="new-password" minlength="8">
                <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                <div class="password-strength">
                    <div class="strength-meter" id="strengthMeter"></div>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['password']) ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="password_confirm">Подтверждение пароля</label>
                <input type="password" id="password_confirm" name="password_confirm" required 
                       autocomplete="new-password" minlength="8">
                <i class="fas fa-eye password-toggle" id="togglePasswordConfirm"></i>
                <?php if (isset($errors['password_confirm'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['password_confirm']) ?></div>
                <?php endif; ?>
            </div>
            
            <button type="submit" id="submitBtn">Зарегистрироваться</button>
        </form>
        
        <div class="login-link">
            Уже есть аккаунт? <a href="login.php">Войти</a>
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

        document.getElementById('togglePasswordConfirm').addEventListener('click', function() {
            const passwordInput = document.getElementById('password_confirm');
            const icon = this;
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });

        // Проверка сложности пароля
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthMeter = document.getElementById('strengthMeter');
            let strength = 0;
            
            if (password.length >= 8) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            // Обновляем индикатор сложности
            switch(strength) {
                case 0:
                    strengthMeter.style.width = '0%';
                    strengthMeter.style.backgroundColor = 'var(--pluk-red)';
                    break;
                case 1:
                    strengthMeter.style.width = '25%';
                    strengthMeter.style.backgroundColor = 'var(--pluk-red)';
                    break;
                case 2:
                    strengthMeter.style.width = '50%';
                    strengthMeter.style.backgroundColor = 'var(--pluk-yellow)';
                    break;
                case 3:
                    strengthMeter.style.width = '75%';
                    strengthMeter.style.backgroundColor = 'var(--pluk-blue)';
                    break;
                case 4:
                    strengthMeter.style.width = '100%';
                    strengthMeter.style.backgroundColor = 'var(--pluk-green)';
                    break;
            }
        });

        // Проверка совпадения паролей в реальном времени
        document.getElementById('password_confirm').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword.length > 0 && password !== confirmPassword) {
                this.setCustomValidity('Пароли не совпадают');
            } else {
                this.setCustomValidity('');
            }
        });

        // Валидация формы перед отправкой
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirm').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Пароли не совпадают');
            }
        });
    </script>
</body>
</html>