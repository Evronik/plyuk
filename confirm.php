<?php
require_once 'config.php';

$confirmation_code = $_GET['code'] ?? '';
$success = false;
$message = '';

if (empty($confirmation_code)) {
    $message = "Неверная ссылка подтверждения";
} else {
    $result = confirm_registration($confirmation_code);
    
    if ($result) {
        $success = true;
        $message = "Регистрация успешно подтверждена! Теперь Вы можете войти в систему.";
        
        // Автоматически авторизуем пользователя
        $_SESSION['user_id'] = $result['id'];
        $_SESSION['username'] = $result['username'];
        $_SESSION['email'] = $result['email'];
        $_SESSION['registration_confirmed'] = true;
    } else {
        $message = "Ссылка подтверждения недействительна или истекла. Пожалуйста, зарегистрируйтесь снова.";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение регистрации | ПлюкСудоку</title>
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

        .result-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            margin: 20px auto;
            margin-bottom: 0px;
        }

        .success-icon {
            font-size: 64px;
            color: #4CAF50;
            margin-bottom: 1px;
        }

        .error-icon {
            font-size: 64px;
            color: #f44336;
            margin-bottom: 1px;
        }
        
        h2 {
            color: #000;
            font-family: "Playfair Display", Vidaloka, serif;
            font-size: 2.5rem;
            line-height: 0.85;
            perspective: 200px;
            padding: 30px 15px;
            padding-top: 10px;
        }
        
        .actions a {
            color: var(--pluk-blue);
            text-decoration: none;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            body {
                background-size: cover;
            }
            .result-container {
                margin: 20px;
                padding: 30px 20px;
            }
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

        <div class="result-container">
            <?php if ($success): ?>
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>Ура!</h2>
            <?php else: ?>
                <div class="error-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h2>Ошибка</h2>
            <?php endif; ?>
            
            <p><?= htmlspecialchars($message) ?></p>
            
            <div class="actions">
                <?php if ($success): ?>
                    <a href="game.php" class="btn btn-primary">Начать играть!</a>
                <?php else: ?>
                    <a href="register.php" class="btn btn-primary">Регистрация</a>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</body>
</html>