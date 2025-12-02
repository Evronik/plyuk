<?php
require_once 'config.php';

if (!isset($_SESSION['registration_pending'])) {
    redirect('register.php');
}

$email = $_SESSION['pending_email'] ?? '';
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

        .success-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 10px 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 20px auto;
            margin-bottom: 0px;
        }

        .success-icon {
            font-size: 30px;
            color: #4CAF50;
            margin-bottom: 1px;
            text-align: center;
        }

        .email-note {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #4CAF50;
        }
        
        h2 {
            color: #000;
            font-family: "Playfair Display", Vidaloka, serif;
            font-size: 2.1rem;
            line-height: 0.85;
            perspective: 200px;
            padding: 10px 15px;
            text-align: center;
        }
        
        .actions a {
            color: var(--pluk-blue);
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            transition: background 0.3s;
            text-align: center;
            display: flex;
            justify-content: space-around;
        }
        
        .actions a:hover {
            background: #45a049;
        }

        @media (max-width: 768px) {
            body {
                background-size: cover;
            }
            .success-container {
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

        <div class="success-container">
            <div class="success-icon">
                <i class="fas fa-envelope"></i>
            </div>
            
            <h2>Проверьте Вашу почту</h2>
            <br>
            <p>Мы отправили ссылку для подтверждения регистрации на email:</p>
            
            <div class="email-note">
                <strong><?= htmlspecialchars($email) ?></strong>
            </div>
            
            <p>Перейдите по ссылке в письме, чтобы активировать Ваш аккаунт.</p>
            
            <div class="tips">
                <p><small><strong>Не получили письмо?</strong></small></p>
                <ul>
                    <li><small>Проверьте папку "Спам" или "Рассылки"</small></li>
                    <li><small>Убедитесь, что email указан верно</small></li>
                    <li><small>Подождите несколько минут</small></li>
                    <li><small>Ссылка действительна в течение 24 часов</small></li>
                </ul>
            </div>
            <br>
            <div class="actions">
                <a href="login.php">Вход в игру</a>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// Очищаем сессию после показа
unset($_SESSION['registration_pending'], $_SESSION['pending_email']);
?>