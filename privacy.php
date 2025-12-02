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
    <title>Политика конфиденциальности - ПлюкСудоку</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/font-awesome/css/all.min.css">
    <link rel="stylesheet" href="/css/index.css">
    <link rel="stylesheet" href="/css/logo.css">
    <style>
        .policy-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            margin-bottom: 40px;
        }

        .policy-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #FFDD2D;
        }

        .policy-header h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .last-updated {
            color: #666;
            font-size: 0.9rem;
        }

        .policy-section {
            margin-bottom: 30px;
        }

        .policy-section h2 {
            color: #FFDD2D;
            font-size: 1.5rem;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
        }

        .policy-section h3 {
            color: #333;
            font-size: 1.2rem;
            margin: 20px 0 10px 0;
        }

        .policy-section p {
            line-height: 1.6;
            margin-bottom: 15px;
            color: #444;
        }

        .policy-section ul {
            margin: 15px 0;
            padding-left: 20px;
        }

        .policy-section li {
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .data-types {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .data-type-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #FFDD2D;
        }

        .data-type-card h4 {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.1rem;
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
            .policy-container {
                margin: 10px;
                padding: 15px;
            }

            .policy-header h1 {
                font-size: 2rem;
            }

            .data-types {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .policy-header h1 {
                font-size: 1.7rem;
            }

            .policy-section h2 {
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

        <div class="policy-container">
            <div class="policy-header">
                <h1><i class="fas fa-shield-alt"></i> Политика конфиденциальности</h1>
                <p class="last-updated">Последнее обновление: <?= date('d.m.Y') ?></p>
            </div>

            <div class="policy-section">
                <h2>1. Общие положения</h2>
                <p>Настоящая Политика конфиденциальности регулирует порядок сбора, хранения, использования и раскрытия информации, получаемой от пользователей игры "ПлюкСудоку".</p>
                <p>Используя наше приложение, Вы соглашаетесь с условиями данной Политики конфиденциальности.</p>
            </div>

            <div class="policy-section">
                <h2>2. Собираемая информация</h2>
                
                <div class="data-types">
                    <div class="data-type-card">
                        <h4><i class="fas fa-user"></i> Для гостевых пользователей</h4>
                        <ul>
                            <li>Статистика игр (количество завершенных игр, победы)</li>
                            <li>Лучшее время для каждого уровня сложности</li>
                            <li>Достижения и прогресс</li>
                            <li>Данные хранятся локально на устройстве</li>
                        </ul>
                    </div>

                    <div class="data-type-card">
                        <h4><i class="fas fa-user-check"></i> Для авторизованных пользователей</h4>
                        <ul>
                            <li>Учетные данные (email, хеш пароля)</li>
                            <li>Игровая статистика и прогресс</li>
                            <li>Информация о подписках и платежах</li>
                            <li>Данные для участия в турнирах</li>
                            <li>История начисления призов</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="policy-section">
                <h2>3. Использование информации</h2>
                <p>Собранная информация используется для:</p>
                <ul>
                    <li>Предоставления и улучшения игровых услуг</li>
                    <li>Организации платных турниров и соревнований</li>
                    <li>Персонализации игрового опыта</li>
                    <li>Обработки платежей за подписки</li>
                    <li>Формирования таблицы лидеров и рейтингов</li>
                    <li>Технической поддержки пользователей</li>
                </ul>
            </div>

            <div class="policy-section">
                <h2>4. Хранение и защита данных</h2>
                <h3>Гостевые пользователи</h3>
                <p>Все данные хранятся локально на Вашем устройстве в браузере. При переходе на другое устройство или очистке кеша данные будут утеряны.</p>

                <h3>Авторизованные пользователи</h3>
                <p>Данные хранятся на защищенных серверах и синхронизируются между устройствами. Мы применяем современные методы шифрования для защиты Вашей информации.</p>
            </div>

            <div class="policy-section">
                <h2>5. Платные услуги и подписки</h2>
                <p>Для участия в платных турнирах требуется приобретение подписки. При оформлении подписки собирается:</p>
                <ul>
                    <li>Информация об аккаунте</li>
                    <li>История платежей (обрабатывается через защищенные платежные системы)</li>
                    <li>Статус подписки и сроки действия</li>
                </ul>
                <p>Мы не храним данные Ваших банковских карт - обработка платежей осуществляется через защищенные платежные шлюзы.</p>
            </div>

            <div class="policy-section">
                <h2>6. Передача данных третьим лицам</h2>
                <p>Мы не передаем Ваши персональные данные третьим лицам, за исключением:</p>
                <ul>
                    <li>Платежных систем для обработки транзакций</li>
                    <li>Сервисов аналитики (в обезличенной форме)</li>
                    <li>По требованию законодательства</li>
                </ul>
            </div>

            <div class="policy-section">
                <h2>7. Ваши права</h2>
                <p>Вы имеете право:</p>
                <ul>
                    <li>На доступ к Вашим персональным данным</li>
                    <li>На исправление неточных данных</li>
                    <li>На удаление аккаунта и связанных данных</li>
                    <li>На отзыв согласия на обработку данных</li>
                    <li>На перенос данных в другой сервис</li>
                </ul>
            </div>

            <div class="policy-section">
                <h2>8. Файлы cookies</h2>
                <p>Приложение использует cookies и локальное хранилище для:</p>
                <ul>
                    <li>Сохранения игрового прогресса гостевых пользователей</li>
                    <li>Запоминания предпочтений интерфейса</li>
                    <li>Аутентификации авторизованных пользователей</li>
                    <li>Сбора анонимной статистики использования</li>
                </ul>
            </div>

            <div class="policy-section">
                <h2>9. Изменения в политике</h2>
                <p>Мы можем периодически обновлять настоящую Политику конфиденциальности. О всех существенных изменениях мы уведомим Вас через приложение или по электронной почте.</p>
            </div>

            <div class="contact-info">
                <h3><i class="fas fa-envelope"></i> Контакты</h3>
                <p>Если у вас есть вопросы относительно нашей Политики конфиденциальности, пожалуйста, свяжитесь с нами:</p>
                <p>Email: privacy@plyuk.site</p>
            </div>

            <a href="<?= $isLoggedIn ? 'game.php' : 'index.php' ?>" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Вернуться <?= $isLoggedIn ? 'к игре' : 'на главную' ?>
            </a>
        </div>
    </div>

    <script>
        // Добавляем плавную прокрутку для якорных ссылок
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('a[href^="#"]');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>