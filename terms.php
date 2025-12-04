<?php
require_once 'config.php';
session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$isGuest = !$isLoggedIn;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Условия использования - ПлюкСудоку</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/font-awesome/css/all.min.css">
    <link rel="stylesheet" href="/css/index.css">
    <link rel="stylesheet" href="/css/logo.css">
    <style>
        .terms-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            margin-bottom: 40px;
        }

        .terms-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #FFDD2D;
        }

        .terms-header h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .last-updated {
            color: #666;
            font-size: 0.9rem;
        }

        .terms-section {
            margin-bottom: 30px;
        }

        .terms-section h2 {
            color: #FFDD2D;
            font-size: 1.5rem;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
        }

        .terms-section h3 {
            color: #333;
            font-size: 1.2rem;
            margin: 20px 0 10px 0;
        }

        .terms-section p {
            line-height: 1.6;
            margin-bottom: 15px;
            color: #444;
        }

        .terms-section ul {
            margin: 15px 0;
            padding-left: 20px;
        }

        .terms-section li {
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .feature-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #FFDD2D;
        }

        .feature-card h4 {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }

        .subscription-info {
            background: #e8f5e8;
            border: 1px solid #c8e6c9;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: #FFDD2D;
            color: #333;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .back-button:hover {
            background: #ffd700;
            transform: translateY(-2px);
        }

        .contact-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }

        @media (max-width: 768px) {
            .terms-container {
                margin: 10px;
                padding: 15px;
            }

            .terms-header h1 {
                font-size: 2rem;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .terms-header h1 {
                font-size: 1.7rem;
            }

            .terms-section h2 {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body style="background-image: url('/img/fon6.jpg'); min-height: 100vh;">
    <div class="container">
        <header class="header">
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
            <div class="user-info-container">
                <img src="img/KU.png" width="155" height="100">
                <span style="font-size: 1.1rem;">Умная игра<br> для гениев</span>
            </div>
            <div>
                <?php if($isLoggedIn): ?>
                    <a href="game.php" class="btn btn-primary">
                        <i class="fas fa-gamepad"></i> К игре
                    </a>
                <?php else: ?>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> На главную
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <div class="terms-container">
            <div class="terms-header">
                <h1><i class="fas fa-file-contract"></i> Условия использования</h1>
                <p class="last-updated">Последнее обновление: <?= date('d.m.Y') ?></p>
            </div>

            <div class="terms-section">
                <h2>1. Принятие условий</h2>
                <p>Используя игру "ПлюкСудоку", Вы подтверждаете, что прочитали, поняли и соглашаетесь соблюдать настоящие Условия использования.</p>
            </div>

            <div class="terms-section">
                <h2>2. Описание услуг</h2>
                <p>"ПлюкСудоку" предоставляет:</p>
                
                <div class="feature-grid">
                    <div class="feature-card">
                        <h4><i class="fas fa-user"></i> Гостевой режим</h4>
                        <ul>
                            <li>Бесплатная игра в судоку</li>
                            <li>Локальное сохранение прогресса</li>
                            <li>Система достижений</li>
                            <li>Базовая статистика</li>
                        </ul>
                    </div>

                    <div class="feature-card">
                        <h4><i class="fas fa-user-check"></i> Регистрация</h4>
                        <ul>
                            <li>Синхронизация прогресса</li>
                            <li>Участие в рейтинге</li>
                            <li>Сохранение статистики</li>
                            <li>Доступ к истории игр</li>
                        </ul>
                    </div>

                    <div class="feature-card">
                        <h4><i class="fas fa-crown"></i> Платные подписки</h4>
                        <ul>
                            <li>Участие в платных турнирах</li>
                            <li>Эксклюзивные соревнования</li>
                            <li>Призовой фонд</li>
                            <li>Премиум-функции</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="terms-section">
                <h2>3. Регистрация и учетные записи</h2>
                <h3>Гостевые пользователи</h3>
                <p>Могут играть без регистрации с ограниченным функционалом. Данные сохраняются локально на устройстве.</p>

                <h3>Зарегистрированные пользователи</h3>
                <p>При регистрации Вы обязаны предоставить достоверную информацию. Вы несете ответственность за сохранность Ваших учетных данных.</p>

                <div class="warning-box">
                    <h4><i class="fas fa-exclamation-triangle"></i> Важно</h4>
                    <p>При потере доступа к учетной записи восстановление прогресса возможно только при наличии регистрации. Гостевые данные не подлежат восстановлению.</p>
                </div>
            </div>

            <div class="terms-section">
                <h2>4. Платные услуги и подписки</h2>
                
                <div class="subscription-info">
                    <h3><i class="fas fa-gem"></i> Турнирные подписки</h3>
                    <p>Предоставляют доступ к платным турнирам с призовым фондом:</p>
                    <ul>
                        <li>Подписка оформляется на определенный срок</li>
                        <li>Автоматическое продление (опционально)</li>
                        <li>Настраиваются в личном кабинете авторизованного игрока</li>
                        <li>Цены указаны в игровой валюте</li>
                    </ul>
                </div>

                <h3>Условия участия в платных турнирах</h3>
                <ul>
                    <li>Турниры доступны только пользователям с активной подпиской</li>
                    <li>Результаты определяются по системе начисления игровой валюты</li>
                    <li>Призовые места распределяются согласно рейтингу</li>
                    <li>Администрация оставляет за собой право отменять турниры без набора кворума</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>5. Игровая валюта (чатлы)</h2>
                <p>Чатлы - внутриигровая валюта, которая начисляется за:</p>
                <ul>
                    <li>Успешное решение головоломок</li>
                    <li>Достижение лучшего результата</li>
                    <li>Выполнение задач</li>
                    <li>Участие в турнирах</li>
                </ul>

                <div class="warning-box">
                    <h4><i class="fas fa-info-circle"></i> Обратите внимание</h4>
                    <p>Чатлы не являются реальными деньгами и не могут быть обналичены. Они используются только в рамках игровой экосистемы для определения рейтинга и участия в соревнованиях.</p>
                </div>
            </div>

            <div class="terms-section">
                <h2>6. Правила поведения</h2>
                <p>Запрещается:</p>
                <ul>
                    <li>Использование читов и сторонних программ</li>
                    <li>Попытки взлома игровой системы</li>
                    <li>Создание множественных аккаунтов</li>
                    <li>Мошенничество в турнирах</li>
                    <li>Любые действия, нарушающие честность игры</li>
                </ul>

                <p>Нарушение правил может привести к блокировке аккаунта и аннулированию результатов.</p>
            </div>

            <div class="terms-section">
                <h2>7. Интеллектуальная собственность</h2>
                <p>Все права на приложение, включая дизайн, код, графику и контент, принадлежат правообладателю. Вы получаете ограниченную лицензию на использование приложения в личных целях.</p>
            </div>

            <div class="terms-section">
                <h2>8. Ограничение ответственности</h2>
                <p>Приложение предоставляется "как есть". Мы не несем ответственности за:</p>
                <ul>
                    <li>Потерю гостевых данных</li>
                    <li>Временную недоступность сервиса</li>
                    <li>Действия третьих лиц</li>
                    <li>Последствия использования приложения</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>9. Изменения условий</h2>
                <p>Мы оставляем за собой право изменять настоящие Условия использования. Продолжая использовать приложение после внесения изменений, Вы соглашаетесь с новой редакцией.</p>
            </div>

            <div class="terms-section">
                <h2>10. Прекращение доступа</h2>
                <p>Мы можем приостановить или прекратить Ваш доступ к приложению в случае нарушения Условий использования.</p>
            </div>

            <div class="contact-info">
                <h3><i class="fas fa-question-circle"></i> Вопросы и поддержка</h3>
                <p>Если у вас есть вопросы относительно наших Условий использования, пожалуйста, свяжитесь с нами:</p>
                <p>Email: support@plyuk.site</p>
            </div>

            <a href="<?= $isLoggedIn ? 'game.php' : 'index.php' ?>" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Вернуться <?= $isLoggedIn ? 'к игре' : 'на главную' ?>
            </a>
        </div>
    </div>

    <script>
        // Плавная прокрутка для навигации
        document.addEventListener('DOMContentLoaded', function() {
            // Добавляем интерактивность для карточек
            const featureCards = document.querySelectorAll('.feature-card');
            featureCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                    this.style.transition = 'transform 0.3s ease';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>