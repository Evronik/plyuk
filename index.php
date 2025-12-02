<?php
require_once 'config.php';
session_start();

// Проверяем авторизацию
$isLoggedIn = isset($_SESSION['user_id']);
if ($isLoggedIn) {
    header('Location: game.php');
    exit();
}

// Инициализация гостевой статистики
$guestStats = [
    'totalGames' => 0,
    'gamesWon' => 0,
    'bestTimes' => [
        'easy' => null,
        'medium' => null,
        'hard' => null
    ]
];

// Инициализация гостевых достижений
$guestAchievements = [
    ['id' => 'no_mistakes', 'name' => 'Без ошибок', 'description' => 'Решите судоку без единой ошибки', 'unlocked' => false, 'icon' => 'fa-check-circle'],
    ['id' => 'no_hints', 'name' => 'Без подсказок', 'description' => 'Решите судоку без использования подсказок', 'unlocked' => false, 'icon' => 'fa-lightbulb'],
    ['id' => 'perfectionist', 'name' => 'Последний выдох', 'description' => 'Решите судоку без ошибок и подсказок', 'unlocked' => false, 'icon' => 'fa-cloud-meatball'],
    ['id' => 'speedster_easy', 'name' => 'Зелёные штаны', 'description' => 'Решите легкое судоку менее чем за 5 минут', 'unlocked' => false, 'icon' => 'fa-bolt'],
    ['id' => 'speedster_medium', 'name' => 'Сиреневые штаны', 'description' => 'Решите среднее судоку менее чем за 10 минут', 'unlocked' => false, 'icon' => 'fa-bolt'],
    ['id' => 'speedster_hard', 'name' => 'Жёлтые штаны', 'description' => 'Решите сложное судоку менее чем за 15 минут', 'unlocked' => false, 'icon' => 'fa-bolt'],
    ['id' => 'veteran', 'name' => 'Чатланин', 'description' => 'Решите 100 судоку', 'unlocked' => false, 'icon' => 'fa-medal'],
    ['id' => 'master', 'name' => 'Эцилопп', 'description' => 'Решите 500 судоку', 'unlocked' => false, 'icon' => 'fa-user-graduate'],
    ['id' => 'professional', 'name' => 'Господин ПЖ', 'description' => 'Решите 1000 судоку', 'unlocked' => false, 'icon' => 'fa-crown']
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ПлюкСудоку</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/font-awesome/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/index.css">
    <link rel="stylesheet" href="/css/logo.css">
</head>
<body class="loaded" style="background-image: url(&quot;/img/fon6.jpg&quot;);">
    <div class="container">
        <header class="header">
            <div class="logo">
                <a href="index.php">
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
<div class="user-info-container">
  <img src="img/KU.png" width="155" height="100">

<span style="font-size: 1.1rem;">Умная игра<br> для гениев</span>
</div>
            <div>
                <a href="login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Войти
                </a>
            </div>
        </header>
        
        <section class="welcome-section">
            <h1>Добро пожаловать на Плюк<span class="superscript">s</span>Судоку!</h1>
            <p>Играйте в судоку, зарабатывайте <i class="fa-solid fa-money-bill-1-wave"></i> чатлы, улучшайте свой статус.<br> Чем больше чатлов, тем больше шансов выиграть призы.<br> Зарегистрируйтесь/Войдите в систему, чтобы сохранить свой прогресс,<br> или попробуйте гостевую (Бесплатную) версию прямо сейчас.</p>
            
            <div class="action-buttons">
                <a href="game.php?guest=true" class="btn btn-primary" style="padding: 15px 25px;">
                    <i class="fas fa-play"></i> Играть как гость
                </a>
                <a href="register.php" class="btn btn-secondary">
                    <i class="fas fa-user-plus"></i> Регистрация
                </a>
            </div>
        </section>
        
        <section class="welcome-section">
            <p>Используйте гостевой режим для ознакомления с правилами игры, наработкой навыков и стратегии. Когда будете готовы сразиться с профессионалами присоединяйтесь к лидерам и учавствуйте в турнирах за розыгрыш призов!</p>
        </section>
        
        <section class="stats-section">
            <h2 class="section-title">
                <i class="fas fa-chart-bar"></i> Ваша гостевая статистика
            </h2>
            
            <div id="stats-content">
                <div class="stats-placeholder">
                    <i class="fas fa-spinner fa-spin"></i> Загрузка статистики...
                </div>
            </div>
        </section>
        
        <section class="achievements-section">
            <h2 class="section-title">
                <i class="fas fa-trophy"></i> Ваши гостевые достижения
            </h2>
            <div id="achievements-content">
                <div class="achievements-placeholder">
                    <i class="fas fa-spinner fa-spin"></i> Загрузка достижений...
                </div>
            </div>
        </section>

<footer class="site-footer">
    <div class="footer-content">
        <div class="footer-links">
            <a href="privacy.php" class="footer-link">
                <i class="fas fa-shield-alt"></i> Политика конфиденциальности
            </a>
            <a href="terms.php" class="footer-link">
                <i class="fas fa-file-contract"></i> Условия использования
            </a>
        </div>
        <div class="footer-bottom">
            <div class="footer-copyright">
                &copy; <?= date('Y') ?> ПлюкСудоку. Все права защищены.
            </div>
            <div class="age-rating">
                <span class="age-badge">+16</span>
            </div>
        </div>
    </div>
</footer>

</div>

    <script>
    // Функция для загрузки статистики
    async function loadStats() {
    try {
        const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
        
        if (isLoggedIn) {
            // Загрузка статистики из БД для авторизованных пользователей
            const response = await fetch('api/get_stats.php', {
                credentials: 'include'
            });
            
            if (response.ok) {
                const result = await response.json();
                if (result.success && result.stats) {
                    displayStats(result.stats);
                }
            }
        } else {
            // Загрузка статистики из localStorage для гостей с ПРАВИЛЬНЫМ ключом
            const guestStats = localStorage.getItem('pluk_sudoku_guest_stats'); // ИЗМЕНИТЬ КЛЮЧ
            if (guestStats) {
                try {
                    const parsedStats = JSON.parse(guestStats);
                    displayStats(parsedStats);
                } catch (e) {
                    console.error('Ошибка парсинга статистики:', e);
                    displayDefaultStats();
                }
            } else {
                displayDefaultStats();
            }
        }
    } catch (e) {
        console.error('Failed to load stats:', e);
        displayDefaultStats();
    }
}

    // Функция для отображения статистики по умолчанию
    function displayDefaultStats() {
        displayStats({
            totalGames: 0,
            gamesWon: 0,
            bestTimes: { easy: null, medium: null, hard: null }
        });
    }

    // Функция для загрузки достижений
    async function loadAchievements() {
    try {
        const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
        
        if (isLoggedIn) {
            // Загрузка достижений из БД для авторизованных пользователей
            const response = await fetch('api/get_achievements.php', {
                credentials: 'include'
            });
            
            if (response.ok) {
                const result = await response.json();
                if (result.success && result.achievements) {
                    displayAchievements(result.achievements);
                }
            }
        } else {
            // Загрузка достижений из localStorage для гостей с ПРАВИЛЬНЫМ ключом
            const guestAchievements = localStorage.getItem('pluk_sudoku_guest_achievements'); // ИЗМЕНИТЬ КЛЮЧ
            if (guestAchievements) {
                try {
                    const parsedAchievements = JSON.parse(guestAchievements);
                    const validatedAchievements = validateAchievementsOnLoad(parsedAchievements);
                    displayAchievements(validatedAchievements);
                } catch (e) {
                    console.error('Ошибка парсинга достижений:', e);
                    displayDefaultAchievements();
                }
            } else {
                displayDefaultAchievements();
            }
        }
    } catch (e) {
        console.error('Failed to load achievements:', e);
        displayDefaultAchievements();
    }
}

// Функция для проверки достижений при загрузке
function validateAchievementsOnLoad(achievements) {
    // Проверяем спринтерские достижения
    const speedsterAchievements = ['speedster_easy', 'speedster_medium', 'speedster_hard'];
    
    achievements.forEach(achievement => {
        if (speedsterAchievements.includes(achievement.id) && achievement.unlocked) {
            // Если достижение разблокировано, но прогресс = 0 - блокируем
            if (achievement.progress === 0) {
                achievement.unlocked = false;
            }
        }
    });
    
    return achievements;
}

    // Функция для отображения достижений по умолчанию
    function displayDefaultAchievements() {
        // Отображаем стандартный список достижений
        displayAchievements([
            {id: 'no_mistakes', name: 'Без ошибок<br> +2 <i class="fa-solid fa-money-bill-1-wave"></i>', description: 'Решите судоку без единой ошибки', unlocked: false, icon: 'fa-check-circle'},
            {id: 'no_hints', name: 'Без подсказок<br> +2 <i class="fa-solid fa-money-bill-1-wave"></i>', description: 'Решите судоку без использования подсказок', unlocked: false, icon: 'fa-lightbulb'},
            {id: 'perfectionist', name: 'Последний выдох<br> +5 <i class="fa-solid fa-money-bill-1-wave"></i>', description: 'Решите судоку без ошибок и подсказок', unlocked: false, icon: 'fa-cloud-meatball'},
            {id: 'speedster_easy', name: 'Зелёные штаны<br> +5 <i class="fa-solid fa-money-bill-1-wave"></i>', description: 'Решите легкое судоку менее чем за 5 минут', unlocked: false, icon: 'fa-universal-access'},
            {id: 'speedster_medium', name: 'Сиреневые штаны<br> +10 <i class="fa-solid fa-money-bill-1-wave"></i>', description: 'Решите среднее судоку менее чем за 10 минут', unlocked: false, icon: 'fa-universal-access'},
            {id: 'speedster_hard', name: 'Жёлтые штаны<br> +15 <i class="fa-solid fa-money-bill-1-wave"></i>', description: 'Решите сложное судоку менее чем за 15 минут', unlocked: false, icon: 'fa-universal-access'},
            {id: 'veteran', name: 'Чатланин<br> +100 <i class="fa-solid fa-money-bill-1-wave"></i>', description: 'Решите 100 судоку', unlocked: false, icon: 'fa-medal'},
            {id: 'master', name: 'Эцилопп<br> +500 <i class="fa-solid fa-money-bill-1-wave"></i>', description: 'Решите 500 судоку', unlocked: false, icon: 'fa-user-graduate'},
            {id: 'professional', name: 'Господин ПЖ<br> +1000 <i class="fa-solid fa-money-bill-1-wave"></i>', description: 'Решите 1000 судоку', unlocked: false, icon: 'fa-crown'}
        ]);
    }

    // Функция для отображения статистики
    function displayStats(stats) {
        const statsContent = document.getElementById('stats-content');
        
        // Форматируем время в минуты:секунды
        const formatTime = (seconds) => {
            if (!seconds || seconds === null || seconds === undefined) return '-';
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        };
        
        // Рассчитываем процент побед
        const winRate = stats.totalGames > 0 ? 
            Math.round((stats.gamesWon / stats.totalGames) * 100) : 0;
        
        let bestTimesHTML = '';
        if (stats.bestTimes && (stats.bestTimes.easy || stats.bestTimes.medium || stats.bestTimes.hard)) {
            bestTimesHTML = `
                <h3 style="margin-top: 30px; margin-bottom: 15px;">Лучшее время</h3>
                <div class="stats-grid">
                    ${stats.bestTimes.easy ? `
                        <div class="stat-card">
                            <div class="time-value">${formatTime(stats.bestTimes.easy)}</div>
                            <div class="stat-label">Легкий</div>
                        </div>
                    ` : ''}
                    ${stats.bestTimes.medium ? `
                        <div class="stat-card">
                            <div class="time-value">${formatTime(stats.bestTimes.medium)}</div>
                            <div class="stat-label">Средний</div>
                        </div>
                    ` : ''}
                    ${stats.bestTimes.hard ? `
                        <div class="stat-card">
                            <div class="time-value">${formatTime(stats.bestTimes.hard)}</div>
                            <div class="stat-label">Трудный</div>
                        </div>
                    ` : ''}
                </div>
            `;
        }
        
        statsContent.innerHTML = `
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">${stats.totalGames || 0}</div>
                    <div class="stat-label">Завершено игр</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${stats.gamesWon || 0}</div>
                    <div class="stat-label">Побед</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${winRate}%</div>
                    <div class="stat-label">Процент побед</div>
                </div>
            </div>
            ${bestTimesHTML}
        `;
    }

    // Функция для отображения достижений с гармошкой
    function displayAchievements(achievements) {
    const achievementsContent = document.getElementById('achievements-content');
    
    // Разделяем достижения на видимые и скрытые
    const visibleAchievements = achievements.filter(a => 
        ['no_mistakes', 'no_hints', 'perfectionist'].includes(a.id)
    );
    const hiddenAchievements = achievements.filter(a => 
        !['no_mistakes', 'no_hints', 'perfectionist'].includes(a.id)
    );
    
    let achievementsHTML = '<div class="achievements-accordion">';
    
    // Отображаем видимые достижения (первые три)
    visibleAchievements.forEach(achievement => {
        achievementsHTML += `
            <div class="achievement-card ${achievement.unlocked ? 'unlocked' : ''}">
                <div class="achievement-icon">
                    <i class="fas ${achievement.icon || 'fa-trophy'}"></i>
                </div>
                <div class="achievement-name">${achievement.name}</div>
                <div class="achievement-desc">${achievement.description}</div>
                <div class="achievement-status">
                    ${achievement.unlocked ? 'Получено' : 'Не получено'}
                </div>
            </div>
        `;
    });
    
    // Добавляем кнопку "Ещё" на месте "Зелёные штаны"
    achievementsHTML += `
        <div class="achievement-card more-button" onclick="toggleAchievementsAccordion(this)">
            <div class="achievement-icon">
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="achievement-name">Ещё</div>
            <div class="achievement-desc">Показать все достижения</div>
            <div class="achievement-status">Нажмите</div>
        </div>
    `;
    
    // Добавляем скрытые достижения (все остальные)
    hiddenAchievements.forEach(achievement => {
        achievementsHTML += `
            <div class="achievement-card hidden-achievement ${achievement.unlocked ? 'unlocked' : ''}">
                <div class="achievement-icon">
                    <i class="fas ${achievement.icon || 'fa-trophy'}"></i>
                </div>
                <div class="achievement-name">${achievement.name}</div>
                <div class="achievement-desc">${achievement.description}</div>
                <div class="achievement-status">
                    ${achievement.unlocked ? 'Получено' : 'Не получено'}
                </div>
            </div>
        `;
    });
    
    achievementsHTML += '</div>';
    achievementsContent.innerHTML = achievementsHTML;
}

    // Функция для переключения гармошки достижений
    function toggleAchievementsAccordion(button) {
    const accordion = button.closest('.achievements-accordion');
    const hiddenCards = accordion.querySelectorAll('.achievement-card.hidden-achievement');
    const isExpanded = accordion.classList.contains('expanded');
    
    if (isExpanded) {
        // Скрываем дополнительные достижения
        hiddenCards.forEach(card => {
            card.style.display = 'none';
            card.style.visibility = 'hidden';
            card.style.opacity = '0';
            card.style.height = '0';
            card.style.overflow = 'hidden';
        });
        accordion.classList.remove('expanded');
        
        // Обновляем кнопку
        button.querySelector('.achievement-icon i').className = 'fas fa-chevron-down';
        button.querySelector('.achievement-name').textContent = 'Ещё';
        button.querySelector('.achievement-desc').textContent = 'Показать дополнительные достижения';
    } else {
        // Показываем дополнительные достижения
        hiddenCards.forEach(card => {
            card.style.display = 'block';
            card.style.visibility = 'visible';
            card.style.opacity = '1';
            card.style.height = 'auto';
            card.style.overflow = 'visible';
        });
        accordion.classList.add('expanded');
        
        // Обновляем кнопку
        button.querySelector('.achievement-icon i').className = 'fas fa-chevron-up';
        button.querySelector('.achievement-name').textContent = 'Скрыть';
        button.querySelector('.achievement-desc').textContent = 'Скрыть дополнительные достижения';
    }
}

    // Загружаем статистику и достижения при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
    // Обработчик для кнопки "Играть как гость"
    const guestButton = document.querySelector('a[href*="game.php?guest=true"]');
    if (guestButton) {
        guestButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // ★★★ ОЧИСТКА ДАННЫХ АВТОРИЗОВАННЫХ ПОЛЬЗОВАТЕЛЕЙ ПЕРЕД ВХОДОМ В ГОСТЕВОЙ РЕЖИМ ★★★
            if (typeof localStorage !== 'undefined') {
                // Удаляем данные авторизованных пользователей
                localStorage.removeItem('pluk_sudoku_stats');
                localStorage.removeItem('pluk_sudoku_achievements');
                localStorage.removeItem('pluk_sudoku_game');
                
                // Инициализируем чистые гостевые данные
                const defaultStats = {
                    totalGames: 0,
                    gamesWon: 0,
                    totalPoints: 0,
                    rating: 0,
                    bestTimes: { easy: null, medium: null, hard: null }
                };
                
                const defaultAchievements = [
                    {id: 'no_mistakes', name: 'Без ошибок', description: 'Решите судоку без единой ошибки', unlocked: false, icon: 'fa-check-circle'},
                    {id: 'no_hints', name: 'Без подсказок', description: 'Решите судоку без использования подсказок', unlocked: false, icon: 'fa-lightbulb'},
                    {id: 'perfectionist', name: 'Последний выдох', description: 'Решите судоку без ошибок и подсказок', unlocked: false, icon: 'fa-cloud-meatball'},
                    {id: 'speedster_easy', name: 'Зелёные штаны', description: 'Решите легкое судоку менее чем за 5 минут', unlocked: false, icon: 'fa-bolt'},
                    {id: 'speedster_medium', name: 'Сиреневые штаны', description: 'Решите среднее судоку менее чем за 10 минут', unlocked: false, icon: 'fa-bolt'},
                    {id: 'speedster_hard', name: 'Жёлтые штаны', description: 'Решите сложное судоку менее чем за 15 минут', unlocked: false, icon: 'fa-bolt'},
                    {id: 'veteran', name: 'Чатланин', description: 'Решите 100 судоку', unlocked: false, icon: 'fa-medal'},
                    {id: 'master', name: 'Эцилопп', description: 'Решите 500 судоку', unlocked: false, icon: 'fa-user-graduate'},
                    {id: 'professional', name: 'Господин ПЖ', description: 'Решите 1000 судоку', unlocked: false, icon: 'fa-crown'}
                ];
                
                localStorage.setItem('pluk_sudoku_guest_stats', JSON.stringify(defaultStats));
                localStorage.setItem('pluk_sudoku_guest_achievements', JSON.stringify(defaultAchievements));
                console.log('✅ Гостевые данные инициализированы перед входом в игру');
            }
            
            // Переходим в игру
            window.location.href = this.href;
        });
    }
    
    loadStats();
    loadAchievements();
    
     // ★★★ ДОПОЛНИТЕЛЬНАЯ ГАРАНТИЯ СКРЫТИЯ БЛОКОВ ПРИ ЗАГРУЗКЕ ★★★
    function hideAchievements() {
        const hiddenCards = document.querySelectorAll('.achievement-card.hidden-achievement');
        hiddenCards.forEach(card => {
            card.style.display = 'none';
            card.style.visibility = 'hidden';
            card.style.opacity = '0';
            card.style.height = '0';
            card.style.overflow = 'hidden';
        });
    }
    
    // Скрываем сразу
    hideAchievements();
    
    // И после небольшой задержки на всякий случай
    setTimeout(hideAchievements, 100);
    setTimeout(hideAchievements, 500);
});
    </script>
</body>
</html>