// Звуковые эффекты
const soundEffects = {
    click: new Audio('/media/click.mp3'),
    shik: new Audio('/media/shik.mp3'), 
    correct: new Audio('/media/correct.mp3'),
    error: new Audio('/media/error.mp3'),
    win: new Audio('/media/preview.mp3')
};

// ==================== ГЛОБАЛЬНЫЕ КОНСТАНТЫ ====================
const BOARD_SIZE = 9;
const EMPTY_CELL = 0;
const MAX_HINTS = 3;
const MAX_MISTAKES = 3;

// НОВЫЕ КОНСТАНТЫ ВРЕМЕНИ (восходящий отсчет)
const TIME_LIMITS = {
    EASY: 10 * 60,    // 10 минут в секундах
    MEDIUM: 15 * 60,  // 15 минут в секундах  
    HARD: 20 * 60     // 20 минут в секундах
};

const POINTS = {
    EASY: 5,
    MEDIUM: 10,
    HARD: 20
};

// ==================== МИГРАЦИЯ ДАННЫХ ГОСТЕЙ ====================

// Функция для миграции гостевых данных на новые ключи
function migrateGuestData() {
    if (typeof isGuest !== 'undefined' && isGuest) {
        console.log('🔄 Проверка миграции гостевых данных...');
        
        // Миграция достижений
        const oldAchievements = localStorage.getItem('pluk_sudoku_achievements');
        if (oldAchievements) {
            localStorage.setItem('pluk_sudoku_guest_achievements', oldAchievements);
            localStorage.removeItem('pluk_sudoku_achievements');
            console.log('✅ Мигрированы достижения гостя на новый ключ');
        } else {
            // Если нет старых достижений, инициализируем чистые
            const defaultAchievements = getDefaultAchievements();
            localStorage.setItem('pluk_sudoku_guest_achievements', JSON.stringify(defaultAchievements));
            console.log('✅ Инициализированы чистые достижения для гостя');
        }
        
        // Миграция статистики
        const oldStats = localStorage.getItem('pluk_sudoku_stats');
        if (oldStats) {
            localStorage.setItem('pluk_sudoku_guest_stats', oldStats);
            localStorage.removeItem('pluk_sudoku_stats');
            console.log('✅ Мигрирована статистика гостя на новый ключ');
        } else {
            const defaultStats = getDefaultStats();
            localStorage.setItem('pluk_sudoku_guest_stats', JSON.stringify(defaultStats));
            console.log('✅ Инициализирована чистая статистика для гостя');
        }
        
        // Миграция игры
        const oldGame = localStorage.getItem('pluk_sudoku_game');
        if (oldGame) {
            localStorage.setItem('pluk_sudoku_guest_game', oldGame);
            localStorage.removeItem('pluk_sudoku_game');
            console.log('✅ Мигрирована игра гостя на новый ключ');
        } else {
            localStorage.removeItem('pluk_sudoku_guest_game');
            console.log('✅ Очищена сохраненная игра гостя');
        }
        
        console.log('✅ Миграция гостевых данных завершена');
    }
}

// ==================== КОНСТАНТЫ СТАТУСОВ И НАДБАВОК ====================
const STATUS_BONUSES = {
    'speedster_easy': { bonus: 1, icon: 'fa-universal-access', color: '#52ff30', name: 'Зелёные штаны' },
    'speedster_medium': { bonus: 2, icon: 'fa-universal-access', color: '#af52de', name: 'Сиреневые штаны' },
    'speedster_hard': { bonus: 3, icon: 'fa-universal-access', color: '#FFD700', name: 'Жёлтые штаны' },
    'veteran': { bonus: 10, icon: 'fa-user-tie', color: '#d5a582', name: 'Чатланин' },
    'master': { bonus: 50, icon: 'fa-user-ninja', color: '#af52de', name: 'Эцилопп' },
    'professional': { bonus: 100, icon: 'fa-crown', color: '#30dbff', name: 'Господин ПЖ' }
};

// ★★★ КОНСТАНТЫ ДЛЯ ЧАТЛОВ ЗА СКОРОСТЬ ★★★
const SPEED_BONUS = {
    EASY: { time: 300, points: 5 },    // меньше 5 минут = 300 секунд
    MEDIUM: { time: 600, points: 10 },  // меньше 10 минут = 600 секунд  
    HARD: { time: 900, points: 15 }     // меньше 15 минут = 900 секунд
};

const DIFFICULTY = {
    EASY: { name: 'easy', cellsToRemove: 40, label: 'Легкий' },
    MEDIUM: { name: 'medium', cellsToRemove: 50, label: 'Средний' },
    HARD: { name: 'hard', cellsToRemove: 60, label: 'Трудный' }
};

// ==================== ЕДИНЫЙ ОБЪЕКТ СОСТОЯНИЯ ИГРЫ ====================

// ★★★ Единый объект состояния игры ★★★
const gameState = {
    wasSolved: false,
    pageJustLoaded: true,
    gameLoadedFromStorage: false,
    gameStarted: false,
    gameCompleted: false,
    isGameOver: false,
    selectedCell: null,
    currentDifficulty: DIFFICULTY.EASY,
    timerInterval: null,
    seconds: 0,
    mistakes: 0,
    hintsUsed: 0,
    hintsLeft: MAX_HINTS,
    pendingDifficultyChange: null
};

// ==================== ФУНКЦИИ ВАЛИДАЦИИ ДАННЫХ ====================

// ★★★ Функция валидации статистики ★★★
function validateStats(stats) {
    if (!stats) {
        return getDefaultStats();
    }
    
    const validated = {
        totalGames: Math.max(0, parseInt(stats.totalGames) || 0),
        gamesWon: Math.max(0, parseInt(stats.gamesWon) || 0),
        totalPoints: Math.max(0, parseInt(stats.totalPoints) || 0),
        rating: Math.max(0, parseInt(stats.rating) || 0),
        bestTimes: {
            easy: stats.bestTimes?.easy ? parseInt(stats.bestTimes.easy) : null,
            medium: stats.bestTimes?.medium ? parseInt(stats.bestTimes.medium) : null,
            hard: stats.bestTimes?.hard ? parseInt(stats.bestTimes.hard) : null
        }
    };
    
    // Проверка целостности данных
    if (validated.gamesWon > validated.totalGames) {
        console.warn('⚠️ Data integrity issue: gamesWon > totalGames, fixing...');
        validated.totalGames = Math.max(validated.totalGames, validated.gamesWon);
    }
    
    // Рейтинг должен равняться общим чатлам
    validated.rating = validated.totalPoints;
    
    console.log('✅ Validated stats:', validated);
    return validated;
}

// ★★★ Функция для получения статистики по умолчанию ★★★
function getDefaultStats() {
    return {
        totalGames: 0,
        gamesWon: 0,
        totalPoints: 0,
        rating: 0,
        bestTimes: {
            easy: null,
            medium: null,
            hard: null
        }
    };
}

// Инициализация статистики с валидацией
let stats = validateStats(getDefaultStats());

// Форматирование времени
function formatTime(seconds) {
    if (seconds === null || seconds === undefined || isNaN(seconds)) {
        return '--:--';
    }
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
}


// Получение лимита времени для текущей сложности
function getTimeLimitForDifficulty() {
    switch(gameState.currentDifficulty.name) {
        case 'easy':
            return TIME_LIMITS.EASY;
        case 'medium':
            return TIME_LIMITS.MEDIUM;
        case 'hard':
            return TIME_LIMITS.HARD;
        default:
            return TIME_LIMITS.EASY;
    }
}

// ==================== НОВЫЕ ФУНКЦИИ БЕЗОПАСНОСТИ И ВАЛИДАЦИИ ====================

// ★★★ Функция экранирования HTML для безопасной вставки в DOM ★★★
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Функция расчеты чатлов с защитой от ошибок
function calculatePoints(difficulty, mistakes, hintsUsed, timeSeconds, newAchievements = []) {
    let basePoints = POINTS[difficulty.toUpperCase()] || POINTS.EASY;
    let bonus = 0;
    
    // Базовые чатлы за качество игры
    if (mistakes === 0) bonus += 2;
    if (hintsUsed === 0) bonus += 3;
    
    // ★★★ ЧАТЛЫ ЗА СКОРОСТЬ - ПОСТОЯННО ДОСТУПЕН ★★★
    const speedBonusConfig = SPEED_BONUS[difficulty.toUpperCase()];
    if (speedBonusConfig && timeSeconds <= speedBonusConfig.time) {
        bonus += speedBonusConfig.points;
        console.log(`🎯 чатлов за скорость: +${speedBonusConfig.points} чатлов`);
    }
    
    // ★★★ ЧАТЛЫ ЗА ДОСТИЖЕНИЯ ★★★
    let achievementsBonus = 0;
    if (newAchievements && newAchievements.length > 0) {
        newAchievements.forEach(achievement => {
            switch(achievement.id) {
                case 'first_win':
                    achievementsBonus += 5;
                    console.log('🎯 +5 чатлов за Привет, Плюк!');
                    break;
                case 'speedster_easy':
                    achievementsBonus += 5;
                    break;
                case 'speedster_medium':
                    achievementsBonus += 10;
                    break;
                case 'speedster_hard':
                    achievementsBonus += 15;
                    break;
                case 'perfectionist':
                    achievementsBonus += 5;
                    break;
                case 'veteran':
                    achievementsBonus += 100;
                    break;
                case 'master':
                    achievementsBonus += 500;
                    break;
                case 'professional':
                    achievementsBonus += 1000;
                    break;
            }
        });
    }
    
    // Защита от отрицательных значений
    bonus = Math.min(bonus, 999999);
    const totalPoints = Math.max(1, basePoints + bonus + achievementsBonus);
    
    console.log('🎯 Итоговый расчет чатлов:', {
        basePoints, 
        qualityBonus: bonus,
        achievementsBonus,
        totalPoints
    });
    
    return totalPoints;
}

// Функция расчета чатлов с детализацией
function calculatePointsWithBreakdown(difficulty, mistakes, hintsUsed, timeSeconds, newAchievements = []) {
    // ★★★ ВАЛИДАЦИЯ ВХОДНЫХ ДАННЫХ ★★★
    const validatedMistakes = Math.max(0, parseInt(mistakes) || 0);
    const validatedHintsUsed = Math.max(0, parseInt(hintsUsed) || 0);
    const validatedTimeSeconds = Math.max(0, parseInt(timeSeconds) || 0);
    
    console.log('Расчет чатлов с валидированными данными:', {
        difficulty,
        mistakes: validatedMistakes,
        hintsUsed: validatedHintsUsed,
        timeSeconds: validatedTimeSeconds
    });
    
    let basePoints = POINTS[difficulty.toUpperCase()] || POINTS.EASY;
    let bonus = 0;
    let breakdown = [];
    
    // Базовые чатлы за сложность
    breakdown.push({
        type: 'difficulty',
        label: `Сложность - ${DIFFICULTY[difficulty.toUpperCase()].label}`,
        points: basePoints
    });
    
    // ★★★ ЧАТЛЫ ЗА КАЧЕСТВО ИГРЫ - ТОЛЬКО ЕСЛИ ДЕЙСТВИТЕЛЬНО 0 ★★★
    if (validatedMistakes === 0) {
        breakdown.push({
            type: 'no_mistakes',
            label: 'Без ошибок',
            points: 2
        });
        bonus += 2;
    }
    
    if (validatedHintsUsed === 0) {
        breakdown.push({
            type: 'no_hints',
            label: 'Без подсказок', 
            points: 2
        });
        bonus += 2;
    }
    
    // ★★★ ЧАТЛЫ ЗА СКОРОСТЬ - ПОСТОЯННО ДОСТУПЕН ★★★
    const speedBonusConfig = SPEED_BONUS[difficulty.toUpperCase()];
    if (speedBonusConfig && validatedTimeSeconds <= speedBonusConfig.time) {
        breakdown.push({
            type: 'speed_bonus',
            label: `Чатлы за скорость (менее ${speedBonusConfig.time / 60} мин)`,
            points: speedBonusConfig.points
        });
        bonus += speedBonusConfig.points;
        console.log(`🎯 Чатлы за скорость: +${speedBonusConfig.points} чатлов за решение за ${validatedTimeSeconds} сек`);
    }
    
    // ★★★ ЧАТЛЫ ЗА ДОСТИЖЕНИЯ - показываем ТОЛЬКО полученные ★★★
    let achievementsBonus = 0;
    
    if (newAchievements && newAchievements.length > 0) {
        newAchievements.forEach(achievement => {
            let achievementPoints = 0;
            
            switch(achievement.id) {
                case 'first_win':
                    achievementPoints = 5;
                    break;
                case 'speedster_easy':
                    achievementPoints = 5;
                    break;
                case 'speedster_medium':
                    achievementPoints = 10;
                    break;
                case 'speedster_hard':
                    achievementPoints = 15;
                    break;
                case 'perfectionist':
                    achievementPoints = 5;
                    break;
                case 'veteran':
                    achievementPoints = 100;
                    break;
                case 'master':
                    achievementPoints = 500;
                    break;
                case 'professional':
                    achievementPoints = 1000;
                    break;
            }
            
            if (achievementPoints > 0) {
                achievementsBonus += achievementPoints;
                breakdown.push({
                    type: 'achievement',
                    label: achievement.name,
                    points: achievementPoints
                });
            }
        });
    }
    
    // ★★★ ЧАТЛЫ ЗА СТАТУС - ПОСТОЯННАЯ НАДБАВКА ★★★
const statusBonus = getStatusBonus();
if (statusBonus > 0) {
    const currentStatus = getCurrentStatus();
    const statusInfo = STATUS_BONUSES[currentStatus];
    breakdown.push({
        type: 'status_bonus',
        label: `Надбавка за статус - ${statusInfo.name}`,
        points: statusBonus
    });
    bonus += statusBonus;
    console.log(`🎯 Надбавка за статус - "${statusInfo.name}": +${statusBonus} чатлов`);
}
    
    // Защита от отрицательных значений
    bonus = Math.min(bonus, 50); // Увеличиваем лимит чатлов
    const totalPoints = Math.max(1, basePoints + bonus + achievementsBonus);
    
    console.log('🎯 Детальный расчет чатлов:', {
        basePoints, 
        qualityBonus: bonus,
        achievementsBonus,
        totalPoints,
        breakdown
    });
    
    return {
        total: totalPoints,
        base: basePoints,
        bonus: bonus,
        achievements: achievementsBonus,
        breakdown: breakdown
    };
}

// ★★★ Функция для получения чатлов за достижение ★★★
function getAchievementBonus(achievementId) {
    switch(achievementId) {
        case 'first_win': return 5;
        case 'speedster_easy': return 5;
        case 'speedster_medium': return 10;
        case 'speedster_hard': return 15;
        case 'perfectionist': return 5;
        case 'veteran': return 100;
        case 'master': return 500;
        case 'professional': return 1000;
        default: return 0;
    }
}

// ★★★ Функция для определения текущего статуса игрока ★★★
function getCurrentStatus() {
    const unlockedAchievements = achievements.filter(a => a.unlocked);
    
    // Проверяем статусы в порядке приоритета (от высшего к низшему)
    if (unlockedAchievements.some(a => a.id === 'professional')) {
        return 'professional';
    }
    if (unlockedAchievements.some(a => a.id === 'master')) {
        return 'master';
    }
    if (unlockedAchievements.some(a => a.id === 'veteran')) {
        return 'veteran';
    }
    if (unlockedAchievements.some(a => a.id === 'speedster_hard')) {
        return 'speedster_hard';
    }
    if (unlockedAchievements.some(a => a.id === 'speedster_medium')) {
        return 'speedster_medium';
    }
    if (unlockedAchievements.some(a => a.id === 'speedster_easy')) {
        return 'speedster_easy';
    }
    
    return null; // Нет статуса
}

// ★★★ Функция для получения бонуса за статус ★★★
function getStatusBonus() {
    const currentStatus = getCurrentStatus();
    return currentStatus ? STATUS_BONUSES[currentStatus].bonus : 0;
}

// ★★★ Функция для обновления отображения статуса игрока ★★★
function updateStatusDisplay() {
    const userInfoContainer = document.querySelector('.user-info-container');
    if (!userInfoContainer) return;
    
    // Удаляем старый элемент статуса если есть
    const oldStatusElement = document.getElementById('user-status');
    if (oldStatusElement) {
        oldStatusElement.remove();
    }
    
    const currentStatus = getCurrentStatus();
    if (currentStatus) {
        const statusInfo = STATUS_BONUSES[currentStatus];
        
        // Получаем URL изображения и иконку для текущего статуса
        const statusImage = getStatusImage(currentStatus);
        const statusIcon = getStatusIcon(currentStatus);
        
        // Создаем элемент статуса с иконкой и текстом
        const statusElement = document.createElement('div');
        statusElement.id = 'user-status';
        statusElement.className = 'user-status';
        statusElement.innerHTML = `
            <div class="status-content">
                ${statusIcon}
                <span style="background-image: url('${statusImage}')">${statusInfo.name}</span>
            </div>
        `;
        
        // Вставляем перед контейнером с ником
        const badgeElement = userInfoContainer.querySelector('.bad');
        if (badgeElement) {
            userInfoContainer.insertBefore(statusElement, badgeElement);
        } else {
            userInfoContainer.prepend(statusElement);
        }
        
        // Добавляем стили
        addStatusStyles();
        
        console.log(`✅ Статус обновлен: ${statusInfo.name}`);
    }
}

// Функция для получения иконки статуса
function getStatusIcon(status) {
    switch(status) {
        case 'speedster_easy': // Зелёные штаны
            return '<i class="fa-solid fa-universal-access" style="color: #52ff30;"></i>';
        case 'speedster_medium': // Сиреневые штаны
            return '<i class="fa-solid fa-universal-access" style="color: #af52de;"></i>';
        case 'speedster_hard': // Жёлтые штаны
            return '<i class="fa-solid fa-universal-access" style="color: #FFD700;"></i>';
        case 'veteran': // Чатланин
            return '<i class="fa-solid fa-user-tie" style="color: #d5a582;"></i>';
        case 'master': // Эцилопп
            return '<i class="fa-solid fa-user-ninja" style="color: #af52de;"></i>';
        case 'professional': // Господин ПЖ
            return '<i class="fa-solid fa-crown" style="color: #30dbff;"></i>';
        default:
            return '<i class="fa-solid fa-user" style="color: #d5a582;"></i>';
    }
}

// Функция для получения изображения статуса
function getStatusImage(status) {
    switch(status) {
        case 'speedster_easy':
            return 'img/status-1.png'; // Зелёные штаны
        case 'speedster_medium':
            return 'img/status-2.png'; // Сиреневые штаны
        case 'speedster_hard':
            return 'img/status-3.png'; // Жёлтые штаны
        case 'veteran':
            return 'img/status-4.png'; // Чатланин
        case 'master':
            return 'img/status-5.png'; // Эцилопп
        case 'professional':
            return 'img/status-6.png'; // Господин ПЖ
        default:
            return 'img/status-1.png';
    }
}

// Функция для добавления стилей
function addStatusStyles() {
    // Проверяем, не добавлены ли стили уже
    if (document.getElementById('status-styles')) return;
    
    const styleElement = document.createElement('style');
    styleElement.id = 'status-styles';
    styleElement.textContent = `
        .user-status {
            text-align: center;
        }

        .user-status span {
            margin: 0 auto;
            text-shadow: 0 0 80px rgba(255,255,255,.5);
            background-repeat: repeat-y;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: aitf 80s linear infinite;
            -webkit-transform: translate3d(0,0,0);
            -webkit-backface-visibility: hidden;
            display: inline-block;
        }

        @-webkit-keyframes aitf {
            0% { background-position: 0% 50%; }
            100% { background-position: 100% 50%; }
        }

        @keyframes aitf {
            0% { background-position: 0% 50%; }
            100% { background-position: 100% 50%; }
        }

        /* Адаптивность для мобильных устройств */
        @media (max-width: 768px) {
            .user-status span {
                font-size: 1.1em;
                padding: 4px 8px;
            }
        }

        @media (max-width: 480px) {
            .user-status span {
                font-size: 1em;
                padding: 3px 3px;
            }
        }
    `;
    
    document.head.appendChild(styleElement);
}

// Функция для определения класса цвета статуса
function getStatusColorClass(status) {
    switch(status) {
        case 'speedster_easy': return 'green-pants';
        case 'speedster_medium': return 'purple-pants';
        case 'speedster_hard': return 'yellow-pants';
        case 'veteran': return 'veteran';
        case 'master': return 'master';
        case 'professional': return 'professional';
        default: return 'green-pants';
    }
}

function getDefaultAchievements() {
    return [
        {
            id: 'first_win',
            name: 'Привет, Плюк!', 
            description: 'Решите Ваше первое судоку',
            unlocked: false,
            icon: 'fa-meteor',
            color: 'linear-gradient(135deg, #FFB800, #254BCC)',
            progress: 0,
            progressMax: 1,
            points: 5 // ★ Добавлено
        },
        { 
            id: 'no_mistakes', 
            name: 'Без ошибок', 
            description: 'Решите судоку без единой ошибки', 
            unlocked: false, 
            icon: 'fa-check-circle', 
            color: 'linear-gradient(135deg, #fd4d00, #b3832a)',
            progress: 0,
            progressMax: 1,
            points: 2 // ★ Добавлено
        },
        { 
            id: 'no_hints', 
            name: 'Без подсказок', 
            description: 'Решите судоку без использования подсказок', 
            unlocked: false, 
            icon: 'fa-lightbulb', 
            color: 'linear-gradient(135deg, #c9a5df, #254BCC)',
            progress: 0,
            progressMax: 1,
            points: 2 // ★ Добавлено
        },
        { 
            id: 'perfectionist', 
            name: 'Последний выдох', 
            description: 'Решите судоку без ошибок и подсказок', 
            unlocked: false, 
            icon: 'fa-cloud-meatball', 
            color: 'linear-gradient(135deg, #375d5d, #43d2fd)',
            progress: 0,
            progressMax: 1,
            points: 5 // ★ Добавлено
        },
        { 
            id: 'speedster_easy', 
            name: 'Зелёные штаны', 
            description: 'Решите легкое судоку менее чем за 5 минут', 
            unlocked: false, 
            icon: 'fa-universal-access', 
            color: 'linear-gradient(135deg, #52ff30, #00ff51)',
            progress: 0,
            progressMax: 300,
            points: 5 // ★ Добавлено
        },
        { 
            id: 'speedster_medium', 
            name: 'Сиреневые штаны', 
            description: 'Решите среднее судоку менее чем за 10 минут', 
            unlocked: false, 
            icon: 'fa-universal-access', 
            color: 'linear-gradient(135deg, #af52de, #8E3DBD)',
            progress: 0,
            progressMax: 600,
            points: 10 // ★ Добавлено
        },
        { 
            id: 'speedster_hard', 
            name: 'Жёлтые штаны', 
            description: 'Решите сложное судоку менее чем за 15 минут', 
            unlocked: false, 
            icon: 'fa-universal-access', 
            color: 'linear-gradient(135deg, #FFD700, #FFB800)',
            progress: 0,
            progressMax: 900,
            points: 15 // ★ Добавлено
        },
        { 
            id: 'veteran', 
            name: 'Чатланин', 
            description: 'Решите 100 судоку за любое время', 
            unlocked: false, 
            icon: 'fa-user-tie', 
            color: 'linear-gradient(135deg, #835003, #d5a582)',
            progress: 0,
            progressMax: 100,
            points: 100 // ★ Добавлено
        },
        { 
            id: 'master', 
            name: 'Эцилопп', 
            description: 'Решите 500 судоку за любое время', 
            unlocked: false, 
            icon: 'fa-user-ninja', 
            color: 'linear-gradient(135deg, #af52de, #CC7700)',
            progress: 0,
            progressMax: 500,
            points: 500 // ★ Добавлено
        },
        { 
            id: 'professional', 
            name: 'Господин ПЖ', 
            description: 'Решите 1000 судоку за любое время', 
            unlocked: false, 
            icon: 'fa-crown', 
            color: 'linear-gradient(135deg, #30dbff, #CC2444)',
            progress: 0,
            progressMax: 1000,
            points: 1000 // ★ Добавлено
        }
    ];
}

// Функция для воспроизведения звука клика
function playClickSound() {
    if (soundEffects && soundEffects.click) {
        soundEffects.click.play().catch(() => {
            // Игнорируем ошибки воспроизведения звука
        });
    }
}

// Функция для воспроизведения звука очистки
function playShikSound() {
    if (soundEffects && soundEffects.shik) {
        soundEffects.shik.play().catch(() => {
            // Игнорируем ошибки воспроизведения звука
        });
    }
}

// Функция для воспроизведения звука ошибки
function playCorrectSound() {
    if (soundEffects && soundEffects.correct) {
        soundEffects.correct.play().catch(() => {
            // Игнорируем ошибки воспроизведения звука
        });
    }
}

// Достижения
let achievements = getDefaultAchievements();

// ==================== ТАБЛИЦА ЛИДЕРОВ И ПОИСК ====================
let originalLeaderboardData = [];

    // Функция для экранирования HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

// Вспомогательная функция для экранирования regex
function escapeRegex(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

    // Функция для обновления таблицы
    function updateLeaderboardTable(leaderboardData, searchTerm = '') {
    const leaderboardBody = document.getElementById('leaderboard-body');
    const leaderboardContent = document.getElementById('leaderboard-content');
    
    if (!leaderboardBody) return;
    
    // Удаляем старую информацию о результатах поиска
    const oldResultsInfo = document.getElementById('search-results-info');
    if (oldResultsInfo) oldResultsInfo.remove();
    
    if (leaderboardData.length === 0) {
        if (searchTerm) {
            leaderboardBody.innerHTML = '';
            const noResultsRow = document.createElement('tr');
            noResultsRow.innerHTML = `
                <td colspan="6" class="no-results">
                    <i class="fas fa-search"></i>
                    <h4>Игроки не найдены</h4>
                    <p>Попробуйте изменить поисковый запрос</p>
                </td>
            `;
            leaderboardBody.appendChild(noResultsRow);
        } else {
            leaderboardContent.innerHTML = `
                <div class="leaderboard-placeholder">
                    <i class="fas fa-users"></i>
                    <p>Пока нет данных для таблицы лидеров</p>
                    <p>Станьте первым, кто сыграет и победит!</p>
                </div>
            `;
        }
        return;
    }
    
    // Создаем новую таблицу с рейтингом
    let newTableContent = '';
    
    leaderboardData.forEach((player, index) => {
        const bestTimes = [];
        
        if (player.best_time_easy) {
            const mins = Math.floor(player.best_time_easy / 60);
            const secs = player.best_time_easy % 60;
            bestTimes.push(`Л: ${mins}:${secs.toString().padStart(2, '0')}`);
        }
        
        if (player.best_time_medium) {
            const mins = Math.floor(player.best_time_medium / 60);
            const secs = player.best_time_medium % 60;
            bestTimes.push(`С: ${mins}:${secs.toString().padStart(2, '0')}`);
        }
        
        if (player.best_time_hard) {
            const mins = Math.floor(player.best_time_hard / 60);
            const secs = player.best_time_hard % 60;
            bestTimes.push(`Т: ${mins}:${secs.toString().padStart(2, '0')}`);
        }
        
        const bestTimeHTML = bestTimes.length > 0 
            ? `<div class="leaderboard-time">${bestTimes.join('<br>')}</div>`
            : '<div class="leaderboard-time">-</div>';
        
        let medalIcon = '';
        if (index === 0) medalIcon = '<i class="fas fa-trophy medal-gold"></i>';
        else if (index === 1) medalIcon = '<i class="fas fa-trophy medal-silver"></i>';
        else if (index === 2) medalIcon = '<i class="fas fa-trophy medal-bronze"></i>';
        
        // Подсветка совпадений в имени пользователя
        let usernameDisplay = escapeHtml(player.username);
        if (searchTerm) {
            const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
            usernameDisplay = usernameDisplay.replace(regex, '<mark>$1</mark>');
        }
        
        const isCurrentUser = window.user && player.username === window.user.username;
        
        newTableContent += `
            <tr>
                <td class="leaderboard-rank">${medalIcon || (index + 1)}</td>
                <td>
                    <div class="leaderboard-user">
                        <div class="leaderboard-avatar">
                            ${player.username.charAt(0).toUpperCase()}
                        </div>
                        ${usernameDisplay}
                        ${isCurrentUser ? '<span class="you-badge">Вы</span>' : ''}
                    </div>
                </td>
                <td class="leaderboard-stats">
                    <span class="leaderboard-rating">${player.total_points || 0}</span>
                </td>
                <td class="leaderboard-stats">
                    <span class="leaderboard-wins">${player.games_won || 0}</span>
                </td>
                <td class="leaderboard-stats">
                    <span class="leaderboard-rate">${Math.round(player.win_rate || 0)}%</span>
                </td>
                <td class="leaderboard-stats">
                    ${bestTimeHTML}
                </td>
            </tr>
        `;
    });
    
    leaderboardBody.innerHTML = newTableContent;
    
    // Добавляем информацию о результатах поиска
    if (searchTerm) {
        const resultsInfo = document.createElement('div');
        resultsInfo.id = 'search-results-info';
        resultsInfo.className = 'search-results-info';
        resultsInfo.textContent = `Найдено игроков: ${leaderboardData.length}`;
        
        const table = leaderboardBody.closest('.table-responsive');
        if (table) {
            table.parentNode.insertBefore(resultsInfo, table);
        }
    }
}

function updateLeaderboardWithGroups(leaderboardData, searchTerm = '') {
    const leaderboardBody = document.getElementById('leaderboard-body');
    const leaderboardContent = document.getElementById('leaderboard-content');
    
    if (!leaderboardBody) return;
    
    // Удаляем старую информацию о результатах поиска
    const oldResultsInfo = document.getElementById('search-results-info');
    if (oldResultsInfo) oldResultsInfo.remove();
    
    if (leaderboardData.length === 0) {
        if (searchTerm) {
            leaderboardBody.innerHTML = '';
            const noResultsRow = document.createElement('tr');
            noResultsRow.innerHTML = `
                <td colspan="6" class="no-results">
                    <i class="fas fa-search"></i>
                    <h4>Игроки не найдены</h4>
                    <p>Попробуйте изменить поисковый запрос</p>
                </td>
            `;
            leaderboardBody.appendChild(noResultsRow);
        } else {
            leaderboardContent.innerHTML = `
                <div class="leaderboard-placeholder">
                    <i class="fas fa-users"></i>
                    <p>Пока нет данных для таблицы лидеров</p>
                    <p>Станьте первым, кто сыграет и победит!</p>
                </div>
            `;
        }
        return;
    }
    
    // Создаем группы
    const groups = {
        gold: leaderboardData.slice(0, 10),
        silver: leaderboardData.slice(10, 20),
        bronze: leaderboardData.slice(20, 30),
        other: leaderboardData.slice(30)
    };
    
    let newTableContent = '';
    
    // Функция для рендеринга строки игрока
    function renderPlayerRow(player, globalIndex, groupClass) {
        const isCurrentUser = window.user && player.username === window.user.username;
        
        // Локальная функция форматирования времени
        function formatTimeForLeaderboard(time) {
            if (!time || time == 0) return '-';
            const mins = Math.floor(time / 60);
            const secs = time % 60;
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        }
        
        // Подсветка совпадений в имени пользователя
        let usernameDisplay = escapeHtml(player.username);
        if (searchTerm) {
            const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
            usernameDisplay = usernameDisplay.replace(regex, '<mark>$1</mark>');
        }
        
        // ★★★ ОБНОВЛЕНО: Кубки + номера для всех игроков ★★★
        let rankDisplay = '';
        if (groupClass === 'gold') {
            rankDisplay = `
                <div class="rank-with-medal">
                    <i class="fas fa-trophy medal-gold"></i>
                    <span class="rank-number">${globalIndex + 1}</span>
                </div>
            `;
        } else if (groupClass === 'silver') {
            rankDisplay = `
                <div class="rank-with-medal">
                    <i class="fas fa-trophy medal-silver"></i>
                    <span class="rank-number">${globalIndex + 1}</span>
                </div>
            `;
        } else if (groupClass === 'bronze') {
            rankDisplay = `
                <div class="rank-with-medal">
                    <i class="fas fa-trophy medal-bronze"></i>
                    <span class="rank-number">${globalIndex + 1}</span>
                </div>
            `;
        } else {
            rankDisplay = `<span class="rank-number">${globalIndex + 1}</span>`;
        }
        
        // Форматирование времени
        const bestTimes = [];
        if (player.best_time_easy && player.best_time_easy > 0) {
            bestTimes.push(`Л: ${formatTimeForLeaderboard(player.best_time_easy)}`);
        }
        if (player.best_time_medium && player.best_time_medium > 0) {
            bestTimes.push(`С: ${formatTimeForLeaderboard(player.best_time_medium)}`);
        }
        if (player.best_time_hard && player.best_time_hard > 0) {
            bestTimes.push(`Т: ${formatTimeForLeaderboard(player.best_time_hard)}`);
        }
        
        const bestTimeHTML = bestTimes.length > 0 
            ? `<div class="leaderboard-time">${bestTimes.join('<br>')}</div>`
            : '<div class="leaderboard-time">-</div>';
        
        return `
            <tr class="${groupClass}-row ${isCurrentUser ? 'current-user' : ''}">
                <td class="leaderboard-rank">
                    ${rankDisplay}
                </td>
                <td>
                    <div class="leaderboard-user">
                        <div class="leaderboard-avatar">
                            ${player.username.charAt(0).toUpperCase()}
                        </div>
                        ${usernameDisplay}
                        ${isCurrentUser ? '<span class="you-badge">Вы</span>' : ''}
                    </div>
                </td>
                <td class="leaderboard-stats">
                    <span class="leaderboard-rating">${player.total_points || 0}</span>
                </td>
                <td class="leaderboard-stats">
                    <span class="leaderboard-wins">${player.games_won || 0}</span>
                </td>
                <td class="leaderboard-stats">
                    <span class="leaderboard-rate">${Math.round(player.win_rate || 0)}%</span>
                </td>
                <td class="leaderboard-stats">
                    ${bestTimeHTML}
                </td>
            </tr>
    `;
}
    
    // Рендерим группы
    const groupConfigs = [
        { key: 'gold', title: 'Золотые призёры (Топ-10)', icon: 'medal-gold', startIndex: 0 },
        { key: 'silver', title: 'Серебряные призёры (11-20)', icon: 'medal-silver', startIndex: 10 },
        { key: 'bronze', title: 'Бронзовые призёры (21-30)', icon: 'medal-bronze', startIndex: 20 },
        { key: 'other', title: 'Остальные игроки', icon: 'users', startIndex: 30 }
    ];
    
    groupConfigs.forEach(config => {
        const groupPlayers = groups[config.key];
        if (groupPlayers.length > 0) {
            // Заголовок группы
            newTableContent += `
                <tr class="group-header ${config.key}-group">
                    <td colspan="6">
                        <div class="group-title">
                            <i class="fas fa-${config.icon === 'users' ? 'users' : 'trophy'} ${config.icon}"></i>
                            ${config.title}
                        </div>
                    </td>
                </tr>
            `;
            
            // Игроки группы
            groupPlayers.forEach((player, index) => {
                const globalIndex = config.startIndex + index;
                newTableContent += renderPlayerRow(player, globalIndex, config.key);
            });
        }
    });
    
    leaderboardBody.innerHTML = newTableContent;
    
    // Добавляем информацию о результатах поиска
    if (searchTerm) {
        const resultsInfo = document.createElement('div');
        resultsInfo.id = 'search-results-info';
        resultsInfo.className = 'search-results-info';
        resultsInfo.textContent = `Найдено игроков: ${leaderboardData.length}`;
        
        const table = leaderboardBody.closest('.table-responsive');
        if (table) {
            table.parentNode.insertBefore(resultsInfo, table);
        }
    }
}

// Функция обновления секции текущего пользователя
function updateCurrentUserSection(currentUserData, leaderboardData, searchTerm = '') {
    // ★★★ ЕСЛИ ПОЛЬЗОВАТЕЛЬ ГОСТЬ - НЕ ПОКАЗЫВАТЬ В ТАБЛИЦЕ ЛИДЕРОВ ★★★
    if (typeof isGuest !== 'undefined' && isGuest) {
        const currentUserSection = document.querySelector('.current-user-section');
        if (currentUserSection) {
            currentUserSection.remove();
        }
        return;
    }
    
    // Если идет поиск, скрываем секцию текущего пользователя
    if (searchTerm) {
        const currentUserSection = document.querySelector('.current-user-section');
        if (currentUserSection) {
            currentUserSection.style.display = 'none';
        }
        return;
    }
    
    if (!currentUserData) {
        const currentUserSection = document.querySelector('.current-user-section');
        if (currentUserSection) {
            currentUserSection.remove();
        }
        return;
    }
    
    // Проверяем наличие leaderboardData
    if (!leaderboardData || leaderboardData.length === 0) {
        renderUserPosition(currentUserData, 0, 0);
        return;
    }
    
    // Находим полные данные пользователя из leaderboardData
    const userInLeaderboard = leaderboardData.find(player => 
        player && player.username === currentUserData.username
    );
    
    // Находим позицию пользователя
    const userPosition = leaderboardData.findIndex(player => 
        player && player.username === currentUserData.username
    ) + 1;
    
    // ★★★ ИСПРАВЛЕНИЕ: Всегда показываем фактическую позицию ★★★
    renderUserPosition(userInLeaderboard || currentUserData, userPosition, leaderboardData.length);
}

// Функция для отображения пользователей вне топ-30
function renderUserPosition(userData, userPosition, totalPlayers) {
    const currentUserSection = document.querySelector('.current-user-section');
    
    // Локальная функция форматирования времени
    function formatTimeForCell(time) {
        if (!time || time == 0) return '-';
        const mins = Math.floor(time / 60);
        const secs = time % 60;
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }
    
    const bestTimes = [];
    
    if (userData.best_time_easy && userData.best_time_easy > 0) {
        bestTimes.push(`Л: ${formatTimeForCell(userData.best_time_easy)}`);
    }
    if (userData.best_time_medium && userData.best_time_medium > 0) {
        bestTimes.push(`С: ${formatTimeForCell(userData.best_time_medium)}`);
    }
    if (userData.best_time_hard && userData.best_time_hard > 0) {
        bestTimes.push(`Т: ${formatTimeForCell(userData.best_time_hard)}`);
    }
    
    const bestTimeHTML = bestTimes.length > 0 
        ? `<div class="leaderboard-time">${bestTimes.join('<br>')}</div>`
        : '<div class="leaderboard-time">-</div>';
    
    // ★★★ ДОБАВЛЕНО: кнопка прокрутки и обработчик ★★★
    const userHTML = `
        <div class="current-user-section">
            <table class="leaderboard-table">
                <thead>
                    <tr class="current-user-header">
                        <td colspan="6">
                            <div class="group-title" style="display: flex; align-items: center; justify-content: space-between;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i class="fas fa-user"></i>
                                    Ваша позиция
                                </div>
                                <button class="scroll-to-user-btn" id="scroll-to-user-btn">
                                    <i class="fa-solid fa-arrow-up"></i> Показать в таблице
                                </button>
                            </div>
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr class="current-user" style="cursor: pointer;">
                        <td class="leaderboard-rank">
                            ${renderCurrentUserRank(userPosition)}
                        </td>
                        <td>
                            <div class="leaderboard-user">
                                <div class="leaderboard-avatar">
                                    ${userData.username.charAt(0).toUpperCase()}
                                </div>
                                ${escapeHtml(userData.username)}
                                <span class="you-badge">Вы</span>
                            </div>
                        </td>
                        <td class="leaderboard-stats">
                            <span class="leaderboard-rating">${userData.total_points || userData.rating || 0}</span>
                        </td>
                        <td class="leaderboard-stats">
                            <span class="leaderboard-wins">${userData.games_won || 0}</span>
                        </td>
                        <td class="leaderboard-stats">
                            <span class="leaderboard-rate">${Math.round(userData.win_rate || 0)}%</span>
                        </td>
                        <td class="leaderboard-stats">
                            ${bestTimeHTML}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    `;
    
    if (!currentUserSection) {
        const leaderboardContainer = document.querySelector('.leaderboard-container');
        if (leaderboardContainer) {
            leaderboardContainer.insertAdjacentHTML('afterend', userHTML);
        } else {
            const modalBody = document.querySelector('.modal-body');
            if (modalBody) {
                modalBody.insertAdjacentHTML('beforeend', userHTML);
            }
        }
    } else {
        currentUserSection.outerHTML = userHTML;
    }
    
    // ★★★ ДОБАВЛЕНО: обработчик для кнопки прокрутки ★★★
    const scrollButton = document.getElementById('scroll-to-user-btn');
    if (scrollButton) {
        scrollButton.addEventListener('click', scrollToUserInLeaderboard);
    }
    
    // ★★★ ИСПРАВЛЕНИЕ: Обновляем информацию о фактической позиции ★★★
    updateUserPositionInfo(userPosition, totalPlayers);
}

// ★★★ Функция для плавной прокрутки до пользователя в таблице лидеров ★★★
function scrollToUserInLeaderboard() {
    console.log('Прокрутка до пользователя в таблице лидеров...');
    
    // Находим строку текущего пользователя в основной таблице
    const userRows = document.querySelectorAll('.leaderboard-table tr.current-user');
    
    if (userRows.length > 0) {
        // Берем первую найденную строку (должна быть только одна)
        const userRow = userRows[0];
        
        // Добавляем класс подсветки
        userRow.classList.add('current-user-highlight');
        
        // Прокручиваем до строки пользователя
        userRow.scrollIntoView({ 
            behavior: 'smooth',
            block: 'center'
        });
        
        // Убираем подсветку через 3 секунды
        setTimeout(() => {
            userRow.classList.remove('current-user-highlight');
        }, 3000);
        
        console.log('✅ Прокрутка выполнена успешно');
    } else {
        // Если пользователь не в топ-30, показываем сообщение
        showNotification('Вы не входите в топ-30 игроков', 'info');
        console.log('❌ Пользователь не найден в основной таблице');
    }
}

// Функция для отображения ранга пользователя
function renderCurrentUserRank(position) {
    if (position <= 3) {
        // Для топ-3 позиций показываем кубки
        if (position === 1) {
            return `
                <div class="rank-with-medal">
                    <i class="fas fa-trophy medal-gold"></i>
                    <span class="rank-number">${position}</span>
                </div>
            `;
        } else if (position === 2) {
            return `
                <div class="rank-with-medal">
                    <i class="fas fa-trophy medal-silver"></i>
                    <span class="rank-number">${position}</span>
                </div>
            `;
        } else if (position === 3) {
            return `
                <div class="rank-with-medal">
                    <i class="fas fa-trophy medal-bronze"></i>
                    <span class="rank-number">${position}</span>
                </div>
            `;
        }
    } else {
        // Для остальных позиций - только номер
        return `<span class="rank-number">${position}</span>`;
    }
}

// Функция рендеринга строки текущего пользователя
function renderCurrentUserRow(player, position) {
    // Локальная функция форматирования времени
    function formatTimeForCell(time) {
        if (!time || time == 0) return '-';
        const mins = Math.floor(time / 60);
        const secs = time % 60;
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }
    
    const bestTimes = [];
    
    if (player.best_time_easy && player.best_time_easy > 0) {
        bestTimes.push(`Л: ${formatTimeForCell(player.best_time_easy)}`);
    }
    if (player.best_time_medium && player.best_time_medium > 0) {
        bestTimes.push(`С: ${formatTimeForCell(player.best_time_medium)}`);
    }
    if (player.best_time_hard && player.best_time_hard > 0) {
        bestTimes.push(`Т: ${formatTimeForCell(player.best_time_hard)}`);
    }
    
    const bestTimeHTML = bestTimes.length > 0 
        ? `<div class="leaderboard-time">${bestTimes.join('<br>')}</div>`
        : '<div class="leaderboard-time">-</div>';
    
    // ★★★ ИСПРАВЛЕНИЕ: Используем единую функцию для отображения ранга ★★★
    let rankDisplay = renderCurrentUserRank(position);
    
    return `
        <tr class="current-user">
            <td class="leaderboard-rank">
                ${rankDisplay}
            </td>
            <td>
                <div class="leaderboard-user">
                    <div class="leaderboard-avatar">
                        ${player.username.charAt(0).toUpperCase()}
                    </div>
                    ${escapeHtml(player.username)}
                    <span class="you-badge">Вы</span>
                </div>
            </td>
            <td class="leaderboard-stats">
                <span class="leaderboard-rating">${player.total_points || player.rating || 0}</span>
            </td>
            <td class="leaderboard-stats">
                <span class="leaderboard-wins">${player.games_won || 0}</span>
            </td>
            <td class="leaderboard-stats">
                <span class="leaderboard-rate">${Math.round(player.win_rate || 0)}%</span>
            </td>
            <td class="leaderboard-stats">
                ${bestTimeHTML}
            </td>
        </tr>
    `;
}

// Функция обновления информации о позиции пользователя
function updateUserPositionInfo(position, totalPlayers) {
    let userPositionInfo = document.querySelector('.user-position');
    
    if (!userPositionInfo) {
        // Создаем элемент если его нет
        const leaderboardInfo = document.querySelector('.leaderboard-info');
        if (leaderboardInfo) {
            userPositionInfo = document.createElement('span');
            userPositionInfo.className = 'user-position';
            userPositionInfo.style.marginLeft = '15px';
            userPositionInfo.style.fontWeight = '600';
            userPositionInfo.style.color = '#667eea';
            leaderboardInfo.appendChild(userPositionInfo);
        }
    }
    
    if (userPositionInfo) {
        userPositionInfo.textContent = `Ваша позиция: ${position} из ${totalPlayers}`;
    }
}

// ==================== Модальное окно для входа ====================
const loginWarningModal = document.getElementById('login-warning-modal');
const cancelLoginBtn = document.getElementById('cancel-login');
const confirmLoginBtn = document.getElementById('confirm-login');
const closeLoginModal = document.getElementById('close-login-modal');

let originalHref = 'login.php';

    // Функция для обновления таблицы лидеров
    async function refreshLeaderboard() {
    const refreshBtn = document.getElementById('refresh-leaderboard');
    const leaderboardContent = document.getElementById('leaderboard-content');
    const leaderboardBody = document.getElementById('leaderboard-body');
    const searchInput = document.getElementById('leaderboard-search');
    
    if (!refreshBtn || !leaderboardContent) return;
    
    // Сохраняем текущий поисковый запрос
    const currentSearch = searchInput ? searchInput.value.trim() : '';
    
    // Показываем анимацию загрузки
    refreshBtn.classList.add('loading');
    
    try {
        const response = await fetch('api/get_leaderboard.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        });
        
        if (response.ok) {
            const data = await response.json();
            
            if (data.success && data.leaderboard && data.leaderboard.length > 0) {
                // Сохраняем оригинальные данные
                originalLeaderboardData = [...data.leaderboard];
                window.leaderboardData = [...data.leaderboard]; // ★★★ ДОБАВЛЕНО: сохраняем в глобальную переменную
                
                // Обновляем таблицу с группировкой
                updateLeaderboardWithGroups(data.leaderboard, currentSearch);
                
                // ★★★ ДОБАВЛЕНО: Немедленно обновляем секцию пользователя ★★★
                if (window.user) {
                    updateCurrentUserSection(window.user, data.leaderboard, currentSearch);
                }
                
                showNotification('Таблица лидеров обновлена!', 'success');
            } else {
                showNotification('Нет данных для отображения', 'info');
                originalLeaderboardData = [];
                window.leaderboardData = [];
                updateLeaderboardWithGroups([]);
                
                // ★★★ ДОБАВЛЕНО: Обновляем секцию пользователя даже при пустых данных ★★★
                if (window.user) {
                    updateCurrentUserSection(window.user, [], currentSearch);
                }
            }
        } else {
            throw new Error('Ошибка загрузки данных');
        }
    } catch (error) {
        console.error('Ошибка обновления таблицы лидеров:', error);
        showNotification('Ошибка загрузки данных', 'error');
        
        // ★★★ ДОБАВЛЕНО: Обновляем секцию пользователя даже при ошибке ★★★
        if (window.user && window.leaderboardData) {
            updateCurrentUserSection(window.user, window.leaderboardData, currentSearch);
        }
    } finally {
        // Скрываем анимацию загрузки
        refreshBtn.classList.remove('loading');
    }
}

function blockLogoForAuthUsers() {
    // Добавляем проверку на существование isGuest
    if (typeof isGuest === 'undefined') {
        console.log('isGuest не определен, пропускаем блокировку логотипа');
        return;
    }
    
    if (isGuest) {
        // Для гостей ничего не делаем - разрешаем стандартное поведение
        console.log('Гостевой режим: переход по логотипу разрешен');
        return;
    }
    
    const logoContainer = document.querySelector('.logo');
    if (!logoContainer) return;
    
    // 1. Удаляем все обработчики событий
    const newLogo = logoContainer.cloneNode(true);
    logoContainer.parentNode.replaceChild(newLogo, logoContainer);
    
    // 2. Делаем логотип полностью некликабельным
    newLogo.style.pointerEvents = 'none';
    newLogo.style.cursor = 'default';
    
    // 3. Удаляем все ссылки внутри логотипа
    const links = newLogo.querySelectorAll('a');
    links.forEach(link => {
        link.removeAttribute('href');
        link.style.pointerEvents = 'none';
        link.style.cursor = 'default';
        
        // Добавляем обработчик для блокировки кликов
        link.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }, true);
    });
    
    // 4. Добавляем основной обработчик блокировки
    newLogo.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        return false;
    }, true);
    
    console.log('Логотип заблокирован для авторизованного пользователя');
}

// Функция для инициализации поиска
function initLeaderboardSearch() {
    const searchInput = document.getElementById('leaderboard-search');
    const clearSearchBtn = document.getElementById('clear-search');
    const leaderboardBody = document.getElementById('leaderboard-body');
    
    if (!searchInput || !leaderboardBody) return;
    
    // Сохраняем оригинальные данные при первой загрузке
    if (originalLeaderboardData.length === 0 && window.leaderboardData && window.leaderboardData.length > 0) {
        originalLeaderboardData = [...window.leaderboardData];
    }
    
    // Обработчик ввода поиска
    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.trim().toLowerCase();
        
        if (searchTerm.length > 0) {
            if (clearSearchBtn) clearSearchBtn.style.display = 'block';
            
            // Скрываем секцию текущего пользователя при поиске
            const currentUserSection = document.querySelector('.current-user-section');
            if (currentUserSection) {
                currentUserSection.style.display = 'none';
            }
            
            // Скрываем информацию о позиции пользователя при поиске
            const userPositionInfo = document.querySelector('.user-position');
            if (userPositionInfo) {
                userPositionInfo.style.display = 'none';
            }
            
            filterLeaderboard(searchTerm);
        } else {
            if (clearSearchBtn) clearSearchBtn.style.display = 'none';
            
            // Показываем секцию текущего пользователя при очистке поиска
            const currentUserSection = document.querySelector('.current-user-section');
            if (currentUserSection) {
                currentUserSection.style.display = 'block';
            }
            
            // Показываем информацию о позиции пользователя при очистке поиска
            const userPositionInfo = document.querySelector('.user-position');
            if (userPositionInfo) {
                userPositionInfo.style.display = 'inline';
            }
            
            restoreOriginalLeaderboard();
        }
    });
    
    // Обработчик очистки поиска
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            clearSearchBtn.style.display = 'none';
            
            // Показываем секцию текущего пользователя при очистке
            const currentUserSection = document.querySelector('.current-user-section');
            if (currentUserSection) {
                currentUserSection.style.display = 'block';
            }
            
            // Показываем информацию о позиции пользователя при очистке
            const userPositionInfo = document.querySelector('.user-position');
            if (userPositionInfo) {
                userPositionInfo.style.display = 'inline';
            }
            
            restoreOriginalLeaderboard();
            searchInput.focus();
        });
    }
    
    // Обработчик клавиши Escape
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            searchInput.value = '';
            if (clearSearchBtn) clearSearchBtn.style.display = 'none';
            
            // Показываем секцию текущего пользователя при Escape
            const currentUserSection = document.querySelector('.current-user-section');
            if (currentUserSection) {
                currentUserSection.style.display = 'block';
            }
            
            // Показываем информацию о позиции пользователя при Escape
            const userPositionInfo = document.querySelector('.user-position');
            if (userPositionInfo) {
                userPositionInfo.style.display = 'inline';
            }
            
            restoreOriginalLeaderboard();
            searchInput.blur();
        }
    });

    // Обработчик потери фокуса (для мобильных устройств)
    searchInput.addEventListener('blur', function() {
        // Не восстанавливаем автоматически при потере фокуса,
        // чтобы пользователь мог видеть результаты поиска
    });

    // Обработчик получения фокуса
    searchInput.addEventListener('focus', function() {
        // При фокусе показываем текущие результаты поиска (если есть)
        const currentSearch = searchInput.value.trim();
        if (currentSearch) {
            filterLeaderboard(currentSearch);
        }
    });

    // Автоматический фокус на поле поиска при открытии модального окна
    const leaderboardModal = document.getElementById('leaderboard-modal');
    if (leaderboardModal) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    const displayStyle = leaderboardModal.style.display;
                    if (displayStyle === 'flex' || displayStyle === 'block') {
                        // Небольшая задержка для плавности
                        setTimeout(() => {
                            if (searchInput) {
                                searchInput.focus();
                            }
                        }, 300);
                    }
                }
            });
        });
        
        observer.observe(leaderboardModal, { 
            attributes: true, 
            attributeFilter: ['style'] 
        });
    }
}

// Функция для фильтрации таблицы лидеров
function filterLeaderboard(searchTerm) {
    if (!originalLeaderboardData || originalLeaderboardData.length === 0) {
        console.warn('Нет данных для фильтрации');
        return;
    }
    
    const filteredData = originalLeaderboardData.filter(player => {
        const username = player.username.toLowerCase();
        return username.includes(searchTerm);
    });
    
    // Безопасный вызов
    if (typeof updateLeaderboardTable === 'function') {
        updateLeaderboardTable(filteredData, searchTerm);
    } else {
        console.warn('Функция updateLeaderboardTable не определена');
        // Временное решение - обновляем вручную
        updateLeaderboardManually(filteredData, searchTerm);
    }
}

// Функция для восстановления оригинальной таблицы
function restoreOriginalLeaderboard() {
    if (typeof updateLeaderboardWithGroups === 'function') {
        // Восстанавливаем оригинальные данные с обновленной секцией пользователя
        updateLeaderboardWithGroups(originalLeaderboardData);
    } else {
        console.warn('Функция updateLeaderboardWithGroups не определена');
        updateLeaderboardManually(originalLeaderboardData);
    }
    
    // Убеждаемся, что секция пользователя видна
    const currentUserSection = document.querySelector('.current-user-section');
    if (currentUserSection) {
        currentUserSection.style.display = 'block';
    }
    
    // Убеждаемся, что информация о позиции видна
    const userPositionInfo = document.querySelector('.user-position');
    if (userPositionInfo) {
        userPositionInfo.style.display = 'inline';
    }
}

// Функция для показа модального окна входа
function showLoginWarningModal() {
    if (loginWarningModal) {
        loginWarningModal.style.display = 'flex';
    }
}

// Функция для скрытия модального окна входа
function hideLoginWarningModal() {
    if (loginWarningModal) {
        loginWarningModal.style.display = 'none';
    }
}

// Функция для сохранения новых достижений
function saveNewAchievements(achievements) {
    if (achievements.length === 0) return;
    
    // Сохраняем timestamp показа достижений
    const achievementsWithTimestamp = achievements.map(achievement => ({
        ...achievement,
        shownAt: new Date().toISOString()
    }));
    
    localStorage.setItem('sudoku_new_achievements', JSON.stringify(achievementsWithTimestamp));
    // Также устанавливаем cookie для PHP доступа
    document.cookie = 'sudoku_new_achievements=' + encodeURIComponent(JSON.stringify(achievementsWithTimestamp)) + '; path=/; max-age=3600';
}

// Функция для очистки новых достижений после показа
function clearNewAchievements() {
    localStorage.removeItem('sudoku_new_achievements');
    // Также очищаем cookie
    document.cookie = 'sudoku_new_achievements=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT';
}

// Функция для загрузки новых достижений
function loadNewAchievements() {
    const saved = localStorage.getItem('sudoku_new_achievements');
    if (saved) {
        try {
            const achievements = JSON.parse(saved);
            
            // Фильтруем достижения, показанные более 24 часов назад
            const twentyFourHoursAgo = new Date();
            twentyFourHoursAgo.setHours(twentyFourHoursAgo.getHours() - 24);
            
            const recentAchievements = achievements.filter(achievement => {
                const shownAt = new Date(achievement.shownAt);
                return shownAt > twentyFourHoursAgo;
            });
            
            // Обновляем хранилище только с актуальными достижениями
            if (recentAchievements.length !== achievements.length) {
                saveNewAchievements(recentAchievements);
            }
            
            return recentAchievements;
        } catch (e) {
            console.error('Failed to parse new achievements:', e);
        }
    }
    
    // Проверяем cookie для PHP
    const cookieValue = document.cookie.split('; ')
        .find(row => row.startsWith('sudoku_new_achievements='))
        ?.split('=')[1];
    
    if (cookieValue) {
        try {
            return JSON.parse(decodeURIComponent(cookieValue));
        } catch (e) {
            console.error('Failed to parse cookie achievements:', e);
        }
    }
    
    return [];
}

// Функция для очистки устаревших достижений
function cleanupOldAchievements() {
    const achievements = loadNewAchievements();
    const oneWeekAgo = new Date();
    oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
    
    const recentAchievements = achievements.filter(achievement => {
        const shownAt = new Date(achievement.shownAt);
        return shownAt > oneWeekAgo;
    });
    
    if (recentAchievements.length !== achievements.length) {
        saveNewAchievements(recentAchievements);
    }
}

// Функция для очистки некорректных достижений из localStorage
function cleanupInvalidAchievements() {
    // Добавляем проверку на существование isGuest
    if (typeof isGuest === 'undefined') {
        console.log('isGuest не определен, пропускаем очистку достижений');
        return;
    }
    
    if (isGuest) {
        const achievementsData = localStorage.getItem('pluk_sudoku_achievements');
        if (achievementsData) {
            try {
                const achievements = JSON.parse(achievementsData);
                let needsUpdate = false;
                
                // Проверяем спринтерские достижения
                const speedsterAchievements = achievements.filter(a => 
                    a.id.includes('speedster') && a.unlocked && a.progress === 0
                );
                
                if (speedsterAchievements.length > 0) {
                    speedsterAchievements.forEach(achievement => {
                        achievement.unlocked = false;
                    });
                    needsUpdate = true;
                }
                
                if (needsUpdate) {
                    localStorage.setItem('pluk_sudoku_achievements', JSON.stringify(achievements));
                }
            } catch (e) {
                console.error('Error cleaning up achievements:', e);
            }
        }
    }
}

// Вызывайте эту функцию при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    let pageJustLoaded = true; // ← ДОБАВИТЬ
    // Очищаем некорректные достижения
    cleanupInvalidAchievements();
    cleanupOldAchievements();
});

// Функция для показа модального окна победы
function showWinModal(seconds, mistakes, hintsUsed, newAchievements = [], pointsEarned = 0) {
    const winSeconds = parseInt(localStorage.getItem('win_seconds') || '0');
    const winMistakes = parseInt(localStorage.getItem('win_mistakes') || '0');
    const winHintsUsed = parseInt(localStorage.getItem('win_hints_used') || '0');
    
    const winTime = document.getElementById('win-time');
    const winMistakesElement = document.getElementById('win-mistakes');
    const winHints = document.getElementById('win-hints');
    
    if (winTime) winTime.textContent = formatTime(winSeconds);
    if (winMistakesElement) winMistakesElement.textContent = `${winMistakes}/${MAX_MISTAKES}`; // ← использует глобальную константу
    if (winHints) winHints.textContent = `${winHintsUsed}/${MAX_HINTS}`; // ← использует глобальную константу
    
    // Добавляем отображение чатлов
    const winStatsGrid = document.querySelector('.win-stats-grid');
    if (winStatsGrid && pointsEarned > 0) {
        const pointsElement = document.createElement('div');
        pointsElement.className = 'win-stat';
        pointsElement.innerHTML = `
            <div class="win-stat-icon">
                <i class="fa-solid fa-money-bill-1-wave" style="color: #FFD700;"></i>
            </div>
            <div class="win-stat-value" style="color: #FFD700;">+${pointsEarned}</div>
            <div class="win-stat-label">Чатлы</div>
        `;
        winStatsGrid.appendChild(pointsElement);
    }
    
    // Получаем элементы DOM для контейнера достижений
    const newAchievementsContainer = document.getElementById('new-achievements-container');
    const newAchievementsList = document.getElementById('new-achievements-list');
    
    // ИСПРАВЛЕНИЕ: Используем ТОЛЬКО переданные новые достижения
    // Не загружаем сохраненные из предыдущих игр
    const achievementsToShow = newAchievements || [];
    
    // Показываем новые достижения, если есть
    if (achievementsToShow.length > 0 && newAchievementsContainer && newAchievementsList) {
        newAchievementsList.innerHTML = achievementsToShow.map(achievement => `
            <div class="achievement-card unlocked" style="margin: 10px 0; padding: 15px; border-left: 4px solid ${achievement.color};">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div class="achievement-icon" style="background: ${achievement.color}; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas ${achievement.icon}" style="color: white; font-size: 18px;"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; color: ${achievement.color};">${achievement.name}</div>
                        <div style="font-size: 14px; opacity: 0.8;">${achievement.description}</div>
                    </div>
                </div>
            </div>
        `).join('');
        newAchievementsContainer.style.display = 'flex';
    } else if (newAchievementsContainer) {
        newAchievementsContainer.style.display = 'none';
    }
    
    // Очищаем сохраненные достижения после показа
    clearNewAchievements();
    
    // Показываем модальное окно
    const winModal = document.getElementById('win-modal');
    if (winModal) {
        winModal.style.display = 'flex';
    }
    
    // Воспроизводим звук победы
    if (soundEffects && soundEffects.win) {
        soundEffects.win.play().catch(() => {
            // Игнорируем ошибки воспроизведения звука
        });
    }
}

// Функция для очистки чатлов в модальном окне победы
function clearWinModalPoints() {
    const winStatsGrid = document.querySelector('.win-stats-grid');
    if (winStatsGrid) {
        // Удаляем все элементы чатлов
        const pointsElements = winStatsGrid.querySelectorAll('.points-breakdown, .win-stat-points');
        pointsElements.forEach(element => element.remove());
        
        // Удаляем статический элемент чатлов если он был добавлен
        const staticPoints = winStatsGrid.querySelector('.win-stat:has(.fa-star)');
        if (staticPoints) {
            staticPoints.remove();
        }
        
        console.log('Очищены предыдущие элементы чатлов в модальном окне победы');
    }
}

// Функция для показа модального окна победы с детализацией чатлов
function showWinModalWithBreakdown(seconds, mistakes, hintsUsed, newAchievements = [], pointsBreakdown) {
    // Очищаем предыдущие данные
    clearWinModalPoints();
    
    const winSeconds = parseInt(localStorage.getItem('win_seconds') || '0');
    const winMistakes = parseInt(localStorage.getItem('win_mistakes') || '0');
    const winHintsUsed = parseInt(localStorage.getItem('win_hints_used') || '0');
    
    const winTime = document.getElementById('win-time');
    const winMistakesElement = document.getElementById('win-mistakes');
    const winHints = document.getElementById('win-hints');
    
    if (winTime) winTime.textContent = formatTime(winSeconds);
    if (winMistakesElement) winMistakesElement.textContent = `${winMistakes}/${MAX_MISTAKES}`;
    if (winHints) winHints.textContent = `${winHintsUsed}/${MAX_HINTS}`;
    
    // ОБНОВЛЯЕМ ОТОБРАЖЕНИЕ ЧАТЛОВ С ДЕТАЛИЗАЦИЕЙ
    const winStatsGrid = document.querySelector('.win-stats-grid');
    if (winStatsGrid && pointsBreakdown.total > 0) {
        // Удаляем ВСЕ предыдущие элементы детализации чатлов
        const oldPointsElements = winStatsGrid.querySelectorAll('.points-breakdown, .win-stat-points');
        oldPointsElements.forEach(element => element.remove());
        
        // ★★★ СОЗДАЕМ ЕДИНУЮ ТАБЛИЦУ ДЕТАЛИЗАЦИИ ★★★
        const pointsDetailsContainer = document.createElement('div');
        pointsDetailsContainer.className = 'points-breakdown';
        pointsDetailsContainer.style.gridColumn = '1 / -1';
        pointsDetailsContainer.style.marginTop = '15px';
        pointsDetailsContainer.style.padding = '15px';
        pointsDetailsContainer.style.background = 'rgba(255, 215, 0, 0.1)';
        pointsDetailsContainer.style.borderRadius = '10px';
        pointsDetailsContainer.style.border = '1px solid rgba(255, 215, 0, 0.3)';
        
        // Проверяем, есть ли детализация (может быть только базовая сложность)
        if (pointsBreakdown.breakdown && pointsBreakdown.breakdown.length > 0) {
            pointsDetailsContainer.innerHTML = `
                <div style="font-weight: 600; color: #FFD700; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-money-bill-1-wave"></i>
                    Всего чатлов: +${pointsBreakdown.total}
                </div>
                <div style="font-size: 16px; margin-bottom: 10px;">
                    <i class="fas fa-list-ul"></i> 
                    Детализация начисления чатлов:
                </div>
                <div class="breakdown-items" style="display: flex; flex-direction: column; gap: 8px;">
                    ${pointsBreakdown.breakdown.map(item => `
                        <div class="breakdown-item" style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px;">${item.label}:</span>
                            <span style="font-weight: 600; color: #FFD700; display: flex; align-items: center; gap: 4px; padding-right: 15px;">
                                <i class="fa-solid fa-money-bill-1-wave" style="font-size: 12px;"></i>
                                +${item.points}
                            </span>
                        </div>
                    `).join('')}
                    <div class="breakdown-total" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; margin-top: 5px; border-top: 1px solid rgba(255, 215, 0, 0.3);">
                        <span style="font-weight: 600;">Итого:</span>
                        <span style="font-weight: 700; color: #FFD700; display: flex; align-items: center; gap: 4px;">
                            <i class="fa-solid fa-money-bill-1-wave"></i>
                            +${pointsBreakdown.total}
                        </span>
                    </div>
                </div>
            `;
        } else {
            // Если только базовая сложность
            pointsDetailsContainer.innerHTML = `
                <div style="font-weight: 600; color: #FFD700; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-money-bill-1-wave"></i>
                    Получено чатлов: +${pointsBreakdown.total}
                </div>
            `;
        }
        
        winStatsGrid.appendChild(pointsDetailsContainer);
    }
    
    // Получаем элементы DOM для контейнера достижений
    const newAchievementsContainer = document.getElementById('new-achievements-container');
    const newAchievementsList = document.getElementById('new-achievements-list');
    
    // ИСПРАВЛЕНИЕ: Используем ТОЛЬКО переданные новые достижения
    const achievementsToShow = newAchievements || [];
    
    // Показываем новые достижения, если есть
    if (achievementsToShow.length > 0 && newAchievementsContainer && newAchievementsList) {
        newAchievementsList.innerHTML = achievementsToShow.map(achievement => `
            <div class="achievement-card unlocked" style="margin: 10px 0; padding: 15px; border-left: 4px solid ${achievement.color};">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div class="achievement-icon" style="background: ${achievement.color}; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas ${achievement.icon}" style="color: white; font-size: 18px;"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; color: ${achievement.color};">${achievement.name}</div>
                        <div style="font-size: 14px; opacity: 0.8;">${achievement.description}</div>
                        <div style="font-size: 12px; color: #FFD700; margin-top: 5px; display: flex; align-items: center; gap: 4px;">
                            <i class="fa-solid fa-money-bill-1-wave"></i>
                            +${achievement.points || 0} чатлов
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
        newAchievementsContainer.style.display = 'block';
    } else if (newAchievementsContainer) {
        newAchievementsContainer.style.display = 'none';
    }
    
    // Очищаем сохраненные достижения после показа
    clearNewAchievements();
    
    // Показываем модальное окно
    const winModal = document.getElementById('win-modal');
    if (winModal) {
        winModal.style.display = 'flex';
    }
    
    // Воспроизводим звук победы
    if (soundEffects && soundEffects.win) {
        soundEffects.win.play().catch(() => {
            // Игнорируем ошибки воспроизведения звука
        });
    }
}

// Показ уведомлений
function showNotification(message, type = 'info') {
    // Определяем длительность в зависимости от типа
    const durations = {
        'info': 1500, // Информация 1.5 секунды
        'success': 2000, // Сообщение 2 секунды
        'warning': 2500, // Предупреждение 2.5 секунды
        'error': 3000 // Ошибка 3 секунды
    };
    
    const duration = durations[type] || 1500;
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, duration);
    
    return notification;
}

// Функция для проверки и сброса некорректно разблокированных достижений
function validateAchievements() {
    if (!achievements || !stats) {
        console.log('Проверка достижений: achievements или stats не загружены');
        return false;
    }
    
    let needsSave = false;
    console.log('=== ПРОВЕРКА ДОСТИЖЕНИЙ ===');
    console.log('Текущая статистика:', { 
        gamesWon: stats.gamesWon, 
        totalGames: stats.totalGames,
        bestTimes: stats.bestTimes 
    });
    
    achievements.forEach(achievement => {
        if (achievement.unlocked) {
            let shouldBeLocked = false;
            let reason = '';
            
            switch(achievement.id) {
                case 'speedster_easy':
                    // Проверяем наличие лучшего времени и что оно меньше 5 минут (300 секунд)
                    if (!stats.bestTimes || !stats.bestTimes.easy || stats.bestTimes.easy > 300) {
                        shouldBeLocked = true;
                        reason = `нет лучшего времени для легкого уровня или время > 5 минут (текущее: ${stats.bestTimes?.easy})`;
                    }
                    break;
                    
                case 'speedster_medium':
                    if (!stats.bestTimes || !stats.bestTimes.medium || stats.bestTimes.medium > 600) {
                        shouldBeLocked = true;
                        reason = `нет лучшего времени для среднего уровня или время > 10 минут (текущее: ${stats.bestTimes?.medium})`;
                    }
                    break;
                    
                case 'speedster_hard':
                    if (!stats.bestTimes || !stats.bestTimes.hard || stats.bestTimes.hard > 900) {
                        shouldBeLocked = true;
                        reason = `нет лучшего времени для сложного уровня или время > 15 минут (текущее: ${stats.bestTimes?.hard})`;
                    }
                    break;
                    
                case 'first_win':
                    // Достижение "Привет, Плюк!" должно быть разблокировано если есть хотя бы 1 победа
                    if (stats.gamesWon < 1) {
                        shouldBeLocked = true;
                        reason = `нет побед (текущее: ${stats.gamesWon})`;
                    }
                    break;
                    
                case 'no_mistakes':
                case 'no_hints':
                case 'perfectionist':
                    // Не можем проверить ретроспективно, оставляем как есть
                    console.log(`Достижение "${achievement.name}" не проверяется ретроспективно`);
                    break;
                    
                case 'veteran':
                    if (stats.gamesWon < 100) {
                        shouldBeLocked = true;
                        reason = `меньше 100 побед (текущее: ${stats.gamesWon})`;
                    }
                    break;
                    
                case 'master':
                    if (stats.gamesWon < 500) {
                        shouldBeLocked = true;
                        reason = `меньше 500 побед (текущее: ${stats.gamesWon})`;
                    }
                    break;
                    
                case 'professional':
                    if (stats.gamesWon < 1000) {
                        shouldBeLocked = true;
                        reason = `меньше 1000 побед (текущее: ${stats.gamesWon})`;
                    }
                    break;
                    
                default:
                    console.log(`Неизвестное достижение: ${achievement.id}`);
            }
            
            if (shouldBeLocked) {
                console.log(`❌ Сброс достижения: ${achievement.name} - ${reason}`);
                achievement.unlocked = false;
                achievement.progress = 0;
                delete achievement.unlockedAt;
                needsSave = true;
            } else {
                console.log(`✅ Достижение "${achievement.name}" валидно: ${stats.gamesWon} побед`);
            }
        } else {
            console.log(`🔒 Достижение "${achievement.name}" заблокировано`);
        }
    });
    
    if (needsSave) {
        console.log('💾 Требуется сохранение достижений после проверки');
    } else {
        console.log('👍 Все достижения валидны, сохранение не требуется');
    }
    
    console.log('=== КОНЕЦ ПРОВЕРКИ ДОСТИЖЕНИЙ ===');
    return needsSave;
}

// Очистка некорректных достижений при загрузке
function cleanupAchievementsOnLoad() {
    // Добавляем проверку на существование isGuest
    if (typeof isGuest === 'undefined') {
        console.log('isGuest не определен, пропускаем очистку достижений при загрузке');
        return;
    }
    
    if (isGuest) {
        const achievementsData = localStorage.getItem('pluk_sudoku_achievements');
        if (achievementsData) {
            try {
                const achievements = JSON.parse(achievementsData);
                let needsUpdate = false;
                
                // Проверяем спринтерские достижения с нулевым прогрессом
                const invalidAchievements = achievements.filter(a => 
                    a.id.includes('speedster') && a.unlocked && a.progress === 0
                );
                
                if (invalidAchievements.length > 0) {
                    invalidAchievements.forEach(achievement => {
                        achievement.unlocked = false;
                        console.log('Заблокировано некорректное достижение:', achievement.id);
                    });
                    needsUpdate = true;
                }
                
                // Проверяем достижения, которые требуют побед, но побед нет
                const winDependentAchievements = ['first_win', 'veteran', 'master'];
                const winDependentInvalid = achievements.filter(a => 
                    winDependentAchievements.includes(a.id) && 
                    a.unlocked && 
                    (!stats || stats.gamesWon === 0)
                );
                
                if (winDependentInvalid.length > 0) {
                    winDependentInvalid.forEach(achievement => {
                        achievement.unlocked = false;
                        achievement.progress = 0;
                        console.log('Заблокировано достижение, требующее побед:', achievement.id);
                    });
                    needsUpdate = true;
                }
                
                if (needsUpdate) {
                    localStorage.setItem('pluk_sudoku_achievements', JSON.stringify(achievements));
                    console.log('Достижения очищены от некорректных записей');
                }
            } catch (e) {
                console.error('Error cleaning up achievements on load:', e);
            }
        }
    }
}

function handleGuestLogoClick(event) {
    // Добавляем проверку на существование isGuest
    if (typeof isGuest === 'undefined') {
        console.log('isGuest не определен, разрешаем переход');
        return true;
    }
    
    if (isGuest) {
        // Для гостей разрешаем переход без предупреждения и без изменения статистики
        return true;
    }
    
    // Для авторизованных пользователей - стандартная обработка
    if (gameState.gameStarted && !gameState.gameCompleted && !gameState.isGameOver) {
        event.preventDefault();
        event.stopPropagation();
        showHomepageWarningModal();
        return false;
    }
    
    return true;
}

// Временная функция для обновления таблицы
function updateLeaderboardManually(leaderboardData, searchTerm = '') {
    const leaderboardBody = document.getElementById('leaderboard-body');
    if (!leaderboardBody) return;
    
    console.log('Ручное обновление таблицы:', leaderboardData.length, 'игроков');
    
    // Простая реализация для тестирования
    let html = '';
    leaderboardData.forEach((player, index) => {
        html += `<tr>
            <td>${index + 1}</td>
            <td>${player.username}</td>
            <td>${player.games_won || 0}</td>
            <td>${Math.round(player.win_rate || 0)}%</td>
        </tr>`;
    });
    
    leaderboardBody.innerHTML = html;
}

// ==================== ОПТИМИЗАЦИЯ ЗАГРУЗКИ СТРАНИЦЫ ====================

// Управление загрузкой страницы и предотвращение дергания
function initializePageLoad() {
    // Помечаем тело как загружающееся
    document.body.classList.add('loading');
    
    // Ждем полной загрузки DOM и стилей
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', handleDOMReady);
    } else {
        handleDOMReady();
    }
}

function handleDOMReady() {
    // Даем время браузеру на применение стилей
    requestAnimationFrame(() => {
        // Показываем контент после небольшой задержки
        setTimeout(() => {
            document.body.classList.remove('loading');
            document.body.classList.add('loaded');
            
            // Запускаем инициализацию через существующий обработчик
            // Вместо прямого вызова initGame, запускаем стандартную инициализацию
            console.log('🔄 Запуск стандартной инициализации игры...');
        }, 50);
    });
}

// ==================== Основной игровой скрипт ====================

document.addEventListener('DOMContentLoaded', function() {
    
    // ВЫЗОВ МИГРАЦИИ ПЕРВЫМ ДЕЛОМ
    migrateGuestData();
    
    // Восстанавливаем достижения при загрузке
    achievements = restoreAchievementsOnLoad();
    
    // Добавляем проверку на существование isGuest перед вызовами функций
    if (typeof isGuest !== 'undefined') {
        cleanupInvalidAchievements();
        cleanupOldAchievements();
    } else {
        console.log('isGuest не определен, пропускаем очистку достижений');
    }
    
    // Переменные игры
    let board = Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(EMPTY_CELL));
    let solution = Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(EMPTY_CELL));
    let fixedCells = Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(false));
    
    achievements = getDefaultAchievements();

    // Новая функция для принудительной проверки состояния
    async function forceStateCheck() {
    console.log('=== ПРИНУДИТЕЛЬНАЯ ПРОВЕРКА СОСТОЯНИЯ ===');
    
    // Проверяем все возможные состояния
    const savedGameLost = localStorage.getItem('pluk_sudoku_game_lost');
    const savedWasSolved = localStorage.getItem('pluk_sudoku_was_solved');
    const savedWinShown = localStorage.getItem('pluk_sudoku_win_shown');
    const savedGame = localStorage.getItem('pluk_sudoku_game');
    
    console.log('Состояния из localStorage:', {
        savedGameLost,
        savedWasSolved, 
        savedWinShown,
        hasSavedGame: !!savedGame
    });
    
    // ЕСЛИ ИГРА БЫЛА ПРОИГРАНА - УСТАНАВЛИВАЕМ КОРРЕКТНЫЕ ФЛАГИ И ЗАПОЛНЯЕМ РЕШЕНИЕМ
    if (savedGameLost === 'true') {
        console.log('Обнаружено состояние проигрыша - устанавливаем флаги и заполняем решением');
        gameState.wasSolved = false;
        gameState.gameCompleted = true;
        gameState.isGameOver = true;
        gameState.gameStarted = true;
        
        // Заполняем доску решением из сохраненной игры
        if (savedGame) {
            try {
                const gameData = JSON.parse(savedGame);
                if (gameData.solution) {
                    for (let row = 0; row < BOARD_SIZE; row++) {
                        for (let col = 0; col < BOARD_SIZE; col++) {
                            board[row][col] = gameData.solution[row][col];
                            fixedCells[row][col] = true;
                        }
                    }
                    console.log('Доска заполнена решением из сохраненной игры');
                }
            } catch (e) {
                console.error('Ошибка при восстановлении решения:', e);
            }
        }
        
        // ОСТАНАВЛИВАЕМ ТАЙМЕР ПРИ ПРОИГРЫШЕ
        stopTimer();
        console.log('Таймер остановлен (проигрыш)');
    }
    // ЕСЛИ ИГРА БЫЛА РЕШЕНА - УСТАНАВЛИВАЕМ КОРРЕКТНЫЕ ФЛАГИ
    else if (savedWasSolved === 'true' || savedWinShown === 'true') {
        console.log('Обнаружено состояние решенной игры - устанавливаем флаги');
        gameState.wasSolved = true;
        gameState.gameCompleted = true;
        gameState.isGameOver = true;
        gameState.gameStarted = true;
        
        // Заполняем доску решением
        if (savedGame) {
            try {
                const gameData = JSON.parse(savedGame);
                if (gameData.solution) {
                    for (let row = 0; row < BOARD_SIZE; row++) {
                        for (let col = 0; col < BOARD_SIZE; col++) {
                            board[row][col] = gameData.solution[row][col];
                            fixedCells[row][col] = true;
                        }
                    }
                }
            } catch (e) {
                console.error('Ошибка при восстановлении решения:', e);
            }
        }
        
        // ОСТАНАВЛИВАЕМ ТАЙМЕР ПРИ ПОБЕДЕ
        stopTimer();
        console.log('Таймер остановлен (победа)');
    }
    // ДОПОЛНИТЕЛЬНАЯ ПРОВЕРКА: ЕСЛИ ПОЛЕ УЖЕ ЗАПОЛНЕНО - СЧИТАЕМ ИГРУ ЗАВЕРШЕННОЙ
    else if (savedGame) {
        try {
            const gameData = JSON.parse(savedGame);
            const isFilled = isBoardFilled();
            
            if (isFilled) {
                console.log('Обнаружено заполненное поле - считаем игру завершенной');
                gameState.gameCompleted = true;
                gameState.isGameOver = true;
                gameState.gameStarted = true;
                stopTimer();
                console.log('Таймер остановлен (заполненное поле)');
            } else {
                const hasProgress = gameData.seconds > 0 || gameData.mistakes > 0 || gameData.hintsUsed > 0;
                
                if (hasProgress) {
                    console.log('Обнаружена активная игра с прогрессом');
                    gameState.gameStarted = true;
                    gameState.gameCompleted = false;
                    gameState.isGameOver = false;
                    gameState.wasSolved = false;
                } else {
                    console.log('Сохраненная игра без прогресса - считаем не начатой');
                    gameState.gameStarted = false;
                    gameState.gameCompleted = false;
                    gameState.isGameOver = false;
                    gameState.wasSolved = false;
                }
            }
        } catch (e) {
            console.error('Ошибка при анализе сохраненной игры:', e);
        }
    }
    // НЕТ СОХРАНЕННОЙ ИГРЫ
    else {
        console.log('Нет сохраненной игры - начинаем новую');
        gameState.gameStarted = false;
        gameState.gameCompleted = false;
        gameState.isGameOver = false;
        gameState.wasSolved = false;
    }
    
    console.log('Финальное состояние после проверка:', {
        gameStarted: gameState.gameStarted,
        gameCompleted: gameState.gameCompleted,
        isGameOver: gameState.isGameOver, 
        wasSolved: gameState.wasSolved,
        isBoardFilled: isBoardFilled()
    });
    console.log('=== КОНЕЦ ПРОВЕРКИ СОСТОЯНИЯ ===');
}

// Функция для проверки необходимости показа ЛЮБОГО предупреждения
function shouldShowAnyWarning() {
    console.log('Проверка предупреждений:', {
        gameStarted: gameState.gameStarted,
        gameCompleted: gameState.gameCompleted, 
        isGameOver: gameState.isGameOver,
        wasSolved: gameState.wasSolved,
        isBoardFilled: isBoardFilled()
    });
    
    // ЕСЛИ ИГРА ЗАВЕРШЕНА (ПОБЕДА ИЛИ ПРОИГРЫШ) ИЛИ ПОЛЕ ЗАПОЛНЕНО - НЕ ПОКАЗЫВАТЬ ПРЕДУПРЕЖДЕНИЯ
    if (gameState.gameCompleted || gameState.isGameOver || gameState.wasSolved || isBoardFilled()) {
        console.log('❌ Игра завершена или поле заполнено - предупреждения НЕ показываются');
        return false;
    }
    
    // ЕСЛИ ИГРА АКТИВНА И НЕ ЗАВЕРШЕНА - ПОКАЗЫВАТЬ ПРЕДУПРЕЖДЕНИЯ
    if (gameState.gameStarted && !gameState.gameCompleted && !gameState.isGameOver) {
        console.log('✅ Игра активна - показываем предупреждения');
        return true;
    }
    
    // ВСЕ ОСТАЛЬНЫЕ СЛУЧАИ - НЕ ПОКАЗЫВАТЬ ПРЕДУПРЕЖДЕНИЯ
    console.log('❌ Игра не активна - предупреждения НЕ показываются');
    return false;
}

// Функция для проверки, заполнено ли игровое поле
function isBoardFilled() {
    for (let row = 0; row < BOARD_SIZE; row++) {
        for (let col = 0; col < BOARD_SIZE; col++) {
            if (board[row][col] === EMPTY_CELL) {
                return false;
            }
        }
    }
    return true;
}
    
        // Функция для проверки, решена ли игра
    function isGameSolved() {
        return gameState.wasSolved || 
               localStorage.getItem('pluk_sudoku_was_solved') === 'true' || 
               localStorage.getItem('pluk_sudoku_win_shown') === 'true' ||
               gameState.gameCompleted ||
               gameState.isGameOver;
}

    // ==================== Вспомогательные функции ====================
    
    // Функция для проверки существования DOM элементов
function checkDomElements() {
    const elementsToCheck = [
        'total-games', 'games-won', 'win-rate',
        'best-time-easy', 'best-time-medium', 'best-time-hard',
        'stats-modal'
    ];
    
    elementsToCheck.forEach(id => {
        const element = document.getElementById(id);
        console.log(`Элемент #${id}:`, element ? 'найден' : 'не найден');
    });
}

// Вызовите эту функцию в initGame() после создания доски
console.log('Проверка DOM элементов...');
checkDomElements();
    
    // Функция для сброса wasSolved на сервере
async function resetWasSolvedOnServer() {
    try {
        const response = await fetch('api/reset_was_solved.php', {
            method: 'POST',
            credentials: 'same-origin'
        });
        
        if (response.ok) {
            console.log('wasSolved сброшен на сервере');
        }
    } catch (error) {
        console.error('Ошибка сброса wasSolved:', error);
    }
}

async function saveStatsLocally(statsData) {
    try {
        console.log('💾 Saving stats:', statsData);
        
        const validatedStats = validateStats(statsData);
        
        if (typeof isGuest === 'undefined') {
            console.log('isGuest не определен, сохраняем в localStorage');
            localStorage.setItem('pluk_sudoku_guest_stats', JSON.stringify(validatedStats)); // ИЗМЕНИТЬ КЛЮЧ
            document.cookie = `sudoku_guest_stats=${encodeURIComponent(JSON.stringify(validatedStats))}; path=/`; // ИЗМЕНИТЬ КУКИ
            return true;
        }
        
        if (isGuest) {
            localStorage.setItem('pluk_sudoku_guest_stats', JSON.stringify(validatedStats)); // ИЗМЕНИТЬ КЛЮЧ
            document.cookie = `sudoku_guest_stats=${encodeURIComponent(JSON.stringify(validatedStats))}; path=/`; // ИЗМЕНИТЬ КУКИ
            console.log('✅ Stats saved to localStorage for guest');
            return true;
        } else {
            // Для авторизованных пользователей сохраняем на сервер
            try {
                console.log('🔄 Saving stats to server...');
                const response = await fetch('api/save_stats.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(validatedStats),
                    credentials: 'same-origin'
                });
                
                console.log('💾 Save stats response status:', response.status);
                
                if (response.ok) {
                    const result = await response.json();
                    console.log('💾 Save stats result:', result);
                    
                    if (result.success) {
                        console.log('✅ Stats saved to server successfully');
                        return true;
                    } else {
                        console.error('❌ Save stats server error:', result.error);
                        // При ошибке сервера сохраняем в localStorage как fallback
                        localStorage.setItem('pluk_sudoku_stats', JSON.stringify(validatedStats));
                        return true;
                    }
                } else {
                    console.error('❌ Save stats HTTP error:', response.status);
                    // При HTTP ошибке сохраняем в localStorage как fallback
                    localStorage.setItem('pluk_sudoku_stats', JSON.stringify(validatedStats));
                    return true;
                }
            } catch (fetchError) {
                console.error('❌ Failed to save stats to server:', fetchError);
                // При ошибке сети сохраняем в localStorage
                localStorage.setItem('pluk_sudoku_stats', JSON.stringify(validatedStats));
                return true;
            }
        }
    } catch (e) {
        console.error('❌ Failed to save stats:', e);
        return false;
    }
}
        
     // ★★★ Функция для принудительного сохранения статистики ★★★
async function forceSaveStats() {
    try {
        console.log('💾 Принудительное сохранение статистики...');
        
        // ★★★ ВАЛИДАЦИЯ ДАННЫХ ПЕРЕД СОХРАНЕНИЕМ ★★★
        const validatedStats = validateStats(stats);
        
        // Добавляем проверку на существование isGuest
        if (typeof isGuest === 'undefined') {
            console.log('isGuest не определен, сохраняем в localStorage');
            localStorage.setItem('pluk_sudoku_stats', JSON.stringify(validatedStats));
            document.cookie = `sudoku_stats=${encodeURIComponent(JSON.stringify(validatedStats))}; path=/`;
            
            // ★★★ ОБНОВЛЯЕМ ОТОБРАЖЕНИЕ БАЛАНСА ПОСЛЕ СОХРАНЕНИЯ ★★★
            updateBalanceDisplay();
            
            return true;
        }
        
        if (isGuest) {
            localStorage.setItem('pluk_sudoku_stats', JSON.stringify(validatedStats));
            document.cookie = `sudoku_stats=${encodeURIComponent(JSON.stringify(validatedStats))}; path=/`;
            console.log('✅ Stats saved to localStorage for guest');
            
            // ★★★ ОБНОВЛЯЕМ ОТОБРАЖЕНИЕ БАЛАНСА ПОСЛЕ СОХРАНЕНИЯ ★★★
            updateBalanceDisplay();
            
            return true;
        } else {
            // Для авторизованных пользователей сохраняем на сервер
            try {
                console.log('🔄 Saving stats to server...');
                const response = await fetch('api/save_stats.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(validatedStats),
                    credentials: 'same-origin'
                });
                
                console.log('💾 Save stats response status:', response.status);
                
                if (response.ok) {
                    const result = await response.json();
                    console.log('💾 Save stats result:', result);
                    
                    if (result.success) {
                        console.log('✅ Stats saved to server successfully');
                        return true;
                    } else {
                        console.error('❌ Save stats server error:', result.error);
                        // При ошибке сервера сохраняем в localStorage как fallback
                        localStorage.setItem('pluk_sudoku_stats', JSON.stringify(validatedStats));
                        return true;
                    }
                } else {
                    console.error('❌ Save stats HTTP error:', response.status);
                    // При HTTP ошибке сохраняем в localStorage как fallback
                    localStorage.setItem('pluk_sudoku_stats', JSON.stringify(validatedStats));
                    return true;
                }
            } catch (fetchError) {
                console.error('❌ Failed to save stats to server:', fetchError);
                // При ошибке сети сохраняем в localStorage
                localStorage.setItem('pluk_sudoku_stats', JSON.stringify(validatedStats));
                return true;
            }
        }
    } catch (e) {
        console.error('❌ Failed to save stats:', e);
        return false;
    } finally {
        // ★★★ ОБНОВЛЯЕМ БАЛАНС В ЛЮБОМ СЛУЧАЕ ★★★
        updateBalanceDisplay();
    }
}

    async function saveAchievementsLocally(achievementsData) {
    try {
        console.log('💾 Сохранение достижений:', achievementsData);
        
        if (typeof isGuest === 'undefined') {
            localStorage.setItem('pluk_sudoku_achievements', JSON.stringify(achievementsData));
            console.log('✅ Достижения сохранены в localStorage');
            return true;
        }
        
        if (isGuest) {
            // ДЛЯ ГОСТЕЙ - ИСПОЛЬЗУЕМ ПРАВИЛЬНЫЙ КЛЮЧ
            localStorage.setItem('pluk_sudoku_guest_achievements', JSON.stringify(achievementsData));
            console.log('✅ Достижения гостя сохранены в localStorage');
            return true;
        } else {
            // Для авторизованных пользователей
            try {
                // Подготавливаем данные для отправки на сервер
                const dataToSend = achievementsData.map(achievement => ({
                    id: achievement.id,
                    unlocked: achievement.unlocked || false,
                    progress: achievement.progress || 0,
                    unlockedAt: achievement.unlockedAt || null
                }));
                
                const response = await fetch('api/save_achievements.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(dataToSend),
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const result = await response.json();
                    console.log('✅ Достижения сохранены на сервере:', result);
                    
                    if (result.success) {
                        // Также сохраняем локально как резервную копию
                        localStorage.setItem('pluk_sudoku_achievements', JSON.stringify(achievementsData));
                        return true;
                    } else {
                        console.error('❌ Ошибка сохранения на сервере:', result.error);
                        // Сохраняем локально при ошибке сервера
                        localStorage.setItem('pluk_sudoku_achievements', JSON.stringify(achievementsData));
                        return true;
                    }
                } else {
                    console.error('❌ Ошибка HTTP при сохранении достижений:', response.status);
                    // При ошибке HTTP сохраняем локально
                    localStorage.setItem('pluk_sudoku_achievements', JSON.stringify(achievementsData));
                    return true;
                }
            } catch (fetchError) {
                console.error('❌ Ошибка сети при сохранении достижений:', fetchError);
                // При ошибке сети сохраняем локально
                localStorage.setItem('pluk_sudoku_achievements', JSON.stringify(achievementsData));
                return true;
            }
        }
    } catch (e) {
        console.error('❌ Ошибка сохранения достижений:', e);
        return false;
    }
}
    
    async function loadAchievements() {
    try {
        console.log('🔄 Загрузка достижений...');
        let loadedAchievements = null;
        
        if (typeof isGuest === 'undefined') {
            console.log('isGuest не определен, загружаем достижения из localStorage');
            const achievementsData = localStorage.getItem('pluk_sudoku_achievements');
            if (achievementsData) {
                try {
                    loadedAchievements = JSON.parse(achievementsData);
                    console.log('✅ Достижения загружены из localStorage:', loadedAchievements);
                } catch (e) {
                    console.error('❌ Ошибка парсинга достижений:', e);
                    loadedAchievements = getDefaultAchievements();
                }
            } else {
                loadedAchievements = getDefaultAchievements();
            }
        } else if (isGuest) {
            // ДЛЯ ГОСТЕЙ - ИСПОЛЬЗУЕМ ПРАВИЛЬНЫЙ КЛЮЧ
            const achievementsData = localStorage.getItem('pluk_sudoku_guest_achievements');
            if (achievementsData) {
                try {
                    loadedAchievements = JSON.parse(achievementsData);
                    console.log('✅ Достижения гостя загружены:', loadedAchievements);
                } catch (e) {
                    console.error('❌ Ошибка парсинга гостевых достижений:', e);
                    loadedAchievements = getDefaultAchievements();
                }
            } else {
                loadedAchievements = getDefaultAchievements();
                console.log('✅ Используются достижения по умолчанию для гостя');
            }
        } else {
            // Для авторизованных пользователей загружаем с сервера
            try {
                console.log('🔄 Загрузка достижений с сервера...');
                const response = await fetch('api/get_achievements.php?_=' + Date.now(), {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                    }
                });
                
                if (response.ok) {
                    const result = await response.json();
                    console.log('📦 Ответ сервера достижений:', result);
                    
                    if (result.success && Array.isArray(result.achievements)) {
                        loadedAchievements = result.achievements;
                        console.log('✅ Достижения загружены с сервера:', loadedAchievements.length, 'шт.');
                    } else if (Array.isArray(result)) {
                        // Резервный вариант - если сервер вернул массив напрямую
                        loadedAchievements = result;
                        console.log('✅ Достижения загружены (прямой массив):', loadedAchievements.length, 'шт.');
                    } else {
                        console.warn('⚠️ Неожиданный формат достижений, используем по умолчанию');
                        loadedAchievements = getDefaultAchievements();
                    }
                } else {
                    console.error('❌ Ошибка сервера достижений:', response.status);
                    // При ошибке сервера используем локальные данные
                    const localData = localStorage.getItem('pluk_sudoku_achievements');
                    if (localData) {
                        try {
                            loadedAchievements = JSON.parse(localData);
                            console.log('✅ Используем локальные достижения из-за ошибки сервера');
                        } catch (e) {
                            loadedAchievements = getDefaultAchievements();
                        }
                    } else {
                        loadedAchievements = getDefaultAchievements();
                    }
                }
            } catch (fetchError) {
                console.error('❌ Ошибка загрузки достижений с сервера:', fetchError);
                // При ошибке сети используем локальные данные
                const localData = localStorage.getItem('pluk_sudoku_achievements');
                if (localData) {
                    try {
                        loadedAchievements = JSON.parse(localData);
                        console.log('✅ Используем локальные достижения из-за ошибки сети');
                    } catch (e) {
                        loadedAchievements = getDefaultAchievements();
                    }
                } else {
                    loadedAchievements = getDefaultAchievements();
                }
            }
        }
        
        // ★★★ ВАЛИДАЦИЯ И ОБНОВЛЕНИЕ ДОСТИЖЕНИЙ ★★★
        const defaultAchievements = getDefaultAchievements();
        
        if (loadedAchievements && Array.isArray(loadedAchievements)) {
            // Сопоставляем сохраненные достижения с достижениями по умолчанию
            defaultAchievements.forEach(defaultAchievement => {
                const saved = loadedAchievements.find(a => a && a.id === defaultAchievement.id);
                if (saved) {
                    // Сохраняем состояние разблокировки и прогресс
                    defaultAchievement.unlocked = saved.unlocked || false;
                    defaultAchievement.progress = saved.progress || 0;
                    if (saved.unlockedAt) defaultAchievement.unlockedAt = saved.unlockedAt;
                    
                    // Валидация данных
                    if (defaultAchievement.unlocked && defaultAchievement.progress === 0) {
                        // Если достижение разблокировано, но прогресс 0 - устанавливаем максимальный прогресс
                        defaultAchievement.progress = defaultAchievement.progressMax || 1;
                    }
                }
            });
        }
        
        achievements = defaultAchievements;
        console.log('✅ Финальные достижения:', achievements);
        
        // Сохраняем в localStorage как резервную копию (только для авторизованных)
        if (!isGuest) {
            localStorage.setItem('pluk_sudoku_achievements', JSON.stringify(achievements));
        }
        
        // Рендерим достижения если статистика уже загружена
        if (stats) {
            renderAchievements(stats);
        }
        
        // Обновляем статус после загрузки достижений
        updateStatusDisplay();
        
        return true;
        
    } catch (e) {
        console.error('❌ Критическая ошибка загрузки достижений:', e);
        achievements = getDefaultAchievements();
        if (stats) {
            renderAchievements(stats);
        }
        return false;
    }
}
    
    // ==================== Основные игровые функции ====================

    // Инициализация игры
async function initGame() {
    console.log('Инициализация игры...');
    
    // ПРИНУДИТЕЛЬНАЯ ПРОВЕРКА СОСТОЯНИЯ ПРИ ЗАГРУЗКЕ
    await forceStateCheck();
    
    // ПРИНУДИТЕЛЬНЫЙ СБРОС СОСТОЯНИЯ ПРИ ЗАГРРУЗКЕ СТРАНИЦЫ
    // Если игра была проиграна, устанавливаем корректные флаги
    const savedGameLost = localStorage.getItem('pluk_sudoku_game_lost');
    if (savedGameLost === 'true') {
        console.log('Обнаружено состояние проигрыша при загрузке');
        gameState.wasSolved = false;
        gameState.gameCompleted = true;
        gameState.isGameOver = true;
        gameState.gameStarted = true;
    }

    // Вызываем функцию блокировки при загрузке
    // Добавляем проверку на существование isGuest
    if (typeof isGuest !== 'undefined') {
        blockLogoForAuthUsers();
    }
    
    // ПРОВЕРЯЕМ СОСТОЯНИЕ ИГРЫ ПЕРЕД ЗАГРУЗКОЙ
    const savedWasSolved = localStorage.getItem('pluk_sudoku_was_solved');
    const winShown = localStorage.getItem('pluk_sudoku_win_shown');
    const solveBtnDisabled = localStorage.getItem('solveBtnDisabled');
    
    // Устанавливаем флаги ДО загрузки игры
    gameState.wasSolved = savedWasSolved === 'true';
    gameState.gameCompleted = gameState.wasSolved || winShown === 'true';
    gameState.isGameOver = gameState.gameCompleted;
    
    // Проверяем и исправляем достижения
    const needsSave = validateAchievements();
    if (needsSave) {
        await saveAchievementsLocally(achievements);
        console.log('Достижения проверены и исправлены');
    }
    
    // Проверяем состояние кнопки "Решить" из localStorage
    if (solveBtnDisabled === 'true' && solveBtn) {
        solveBtn.disabled = true;
        solveBtn.classList.add('disabled');
    }
    
    // Сначала пытаемся загрузить сохраненную игру
    const gameLoaded = await loadGame();
    
    await loadAchievements();
    renderAchievements();

    // Инициализируем отображение статуса
    updateStatusDisplay();
    
    // ОБРАБОТКА ОШИБОК ЗАГРУЗКИ
    if (!gameLoaded) {
        if (typeof isGuest !== 'undefined' && !isGuest) {
            // Для авторизованных пользователей - нормально, если нет сохраненной игры
            console.log('No saved game found - starting new game');
        } else if (typeof isGuest !== 'undefined' && isGuest) {
            // Для гостей - тоже нормально
            console.log('Guest mode - no saved game to load');
        }
    }
    
    // Если игра не загружена, создаем доску и начинаем новую игру
    if (!gameLoaded) {
        // Загружаем сохраненный уровень сложности
        const savedDifficulty = localStorage.getItem('currentDifficulty');
        if (savedDifficulty && DIFFICULTY[savedDifficulty.toUpperCase()]) {
            gameState.currentDifficulty = DIFFICULTY[savedDifficulty.toUpperCase()];
        }
        
        // СОЗДАЕМ ДОСКУ ДЛЯ НОВОЙ ИГРЫ
        createBoard();
        
        // Начинаем новую игру
        console.log('Новая игра...');
        startNewGame();
    } else {
        console.log('Игра загружена из сохранения');
        
        // ЕСЛИ ИГРА БЫЛА РЕШЕНА, УСТАНАВЛИВАЕМ СОСТОЯНИЕ
        if (gameState.wasSolved) {
            gameState.gameStarted = true;
            gameState.gameCompleted = true;
            gameState.isGameOver = true;
            
            // Отключаем взаимодействие с доской
            disableBoardInteraction();
            
            // Останавливаем таймер
            stopTimer();
            
            console.log('Игра была решена, блокируем взаимодействие');
        } else {
            gameState.gameStarted = true;
        }
    }
    
    // ОБНОВЛЯЕМ КНОПКИ СЛОЖНОСТИ СРАЗУ ПОСЛЕ ЗАГРУЗКИ
    updateDifficultyButtons();
    
    try {
        // Загружаем статистику и достижения
        console.log('Загрузка статистики...');
        await loadStats();
        console.log('Статистика загружена:', stats);
        
        // ОБНОВЛЯЕМ ОТОБРАЖЕНИЕ СТАТИСТИКИ ПОСЛЕ ЗАГРУЗКИ
        updateStatsDisplay();
        
        console.log('Загрузка достижений...');
        await loadAchievements();
        
        updateNumberButtons();
        updateMistakesDisplay(); // ← ДОБАВЛЕНО: гарантируем обновление отображения ошибок
        updateHintsDisplay();
        setupEventListeners();
        setupWarningModals();
        
        // ДОБАВЛЯЕМ ОБРАБОТЧИК ДЛЯ КНОПКИ "НОВАЯ ИГРА" В МОДАЛЬНОМ ОКНЕ ПОБЕДЫ
        const newGameWinBtn = document.getElementById('new-game-win-btn');
if (newGameWinBtn) {
    newGameWinBtn.addEventListener('click', function() {
        // Очищаем состояние победы, но НЕ очищаем достижения
        localStorage.removeItem('pluk_sudoku_win_shown');
        
        closeModal(winModal);
        
        // РАЗБЛОКИРУЕМ КНОПКИ ПЕРЕД НАЧАЛОМ НОВОЙ ИГРЫ
        enableControlButtons();
        
        startNewGameWithoutStatsUpdate();
    });
}
        
        // Сбрасываем флаг загрузки страницы после небольшой задержки
        setTimeout(() => {
            gameState.pageJustLoaded = false;
        }, 1000);
        
        console.log('Игра инициализирована успешно. Состояние:', {
            wasSolved: gameState.wasSolved,
            gameCompleted: gameState.gameCompleted,
            isGameOver: gameState.isGameOver,
            gameStarted: gameState.gameStarted
        });
    } catch (error) {
        console.error('Error initializing game:', error);
        startNewGame();
    }
}

function restoreAchievementsOnLoad() {
    if (typeof isGuest !== 'undefined' && isGuest) {
        const achievementsData = localStorage.getItem('pluk_sudoku_guest_achievements');
        if (achievementsData) {
            try {
                const parsed = JSON.parse(achievementsData);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    console.log('✅ Достижения восстановлены из localStorage');
                    return parsed;
                }
            } catch (e) {
                console.error('❌ Ошибка восстановления достижений:', e);
            }
        }
    }
    return getDefaultAchievements();
}

// Полный сброс игры (без сохранения прогресса)
function resetGameCompletely() {
    // Очищаем сохраненные значения победы и проигрыша
    localStorage.removeItem('win_seconds');
    localStorage.removeItem('win_mistakes');
    localStorage.removeItem('win_hints_used');
    localStorage.removeItem('pluk_sudoku_game_lost');
    
    // Очищаем сохраненную игру
    if (typeof isGuest !== 'undefined' && isGuest) {
    localStorage.removeItem('pluk_sudoku_guest_game'); // ИЗМЕНИТЬ КЛЮЧ
    localStorage.removeItem('pluk_sudoku_was_solved');
    localStorage.removeItem('pluk_sudoku_game_lost');
} else {
    // Для авторизованных пользователей отправляем запрос на очистку
    clearSavedGame();
}
    
    // Сбрасываем флаги решенной игры и проигрыша
    gameState.wasSolved = false;
    localStorage.removeItem('pluk_sudoku_was_solved');
    localStorage.removeItem('pluk_sudoku_game_lost');
    
    // Очищаем состояние победы
    localStorage.removeItem('pluk_sudoku_win_shown');
    
    // Очищаем состояние кнопки "Решить"
    localStorage.removeItem('solveBtnDisabled');
    
    // Сбрасываем состояние
    resetGameState();
    
    // Генерируем новую головоломку
    generatePuzzle();
    
    // Запускаем таймер
    startTimer();
    
    // Обновляем отображение
    updateBoardView();
    updateNumberButtons();
    updateHintsDisplay();
    
    // РАЗБЛОКИРУЕМ ВСЕ КНОПКИ УПРАВЛЕНИЯ
    enableControlButtons();
    
    gameState.gameStarted = true;
    gameState.gameCompleted = false;
    gameState.isGameOver = false;
    
    // Обновляем статус
    updateStatusDisplay();
    
    showNotification('Новая игра начата!', 'info');
}

// Функция для проверки необходимости показа предупреждения о новой игре
function shouldShowNewGameWarning() {
    console.log('Проверка предупреждения о новой игре:', {
        gameStarted: gameState.gameStarted,
        gameCompleted: gameState.gameCompleted, 
        isGameOver: gameState.isGameOver,
        wasSolved: gameState.wasSolved
    });
    
    // ЕСЛИ ИГРА ЗАВЕРШЕНА (ПОБЕДА ИЛИ ПРОИГРЫШ) - НЕ ПОКАЗЫВАТЬ ПРЕДУПРЕЖДЕНИЕ
    if (gameState.gameCompleted || gameState.isGameOver || gameState.wasSolved) {
        console.log('❌ Игра завершена - предупреждение НЕ показывается');
        return false;
    }
    
    // ЕСЛИ ИГРА АКТИВНА И НЕ ЗАВЕРШЕНА - ПОКАЗЫВАТЬ ПРЕДУПРЕЖДЕНИЕ
    if (gameState.gameStarted && !gameState.gameCompleted && !gameState.isGameOver) {
        console.log('✅ Игра активна - показываем предупреждение');
        return true;
    }
    
    // ВСЕ ОСТАЛЬНЫЕ СЛУЧАИ - НЕ ПОКАЗЫВАТЬ ПРЕДУПРЕЖДЕНИЕ
    console.log('❌ Игра не активна - предупреждение НЕ показывается');
    return false;
}

// Настройка обработчиков событий
function setupEventListeners() {
    // Обработчики для кнопки "Назад" в модальном окне победы
const backWinBtn = document.getElementById('cancel-solve');
if (backWinBtn) {
    backWinBtn.addEventListener('click', closeWinModal);
}

// Обработчики для крестика закрытия модального окна победы
const closeWinModalBtn = document.getElementById('close-win-modal');
if (closeWinModalBtn) {
    closeWinModalBtn.addEventListener('click', function() {
        closeWinModal();
    });
}

// Закрытие по клику вне области модального окна победы
const winModal = document.getElementById('win-modal');
if (winModal) {
    winModal.addEventListener('click', function(e) {
        if (e.target === winModal) {
            closeWinModal();
            enableNewGameButton();
        }
    });
}

    // Обработчики для кнопок с цифрами
    numberBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const number = parseInt(btn.dataset.number);
            handleNumberInput(number);
        });
    });

    // Обработчики для кнопок управления
    if (newGameBtn) {
        newGameBtn.addEventListener('click', function() {
            playClickSound(); // Звук кнопки
        console.log('Нажата кнопка Новая игра. Текущее состояние:', {
            wasSolved: gameState.wasSolved, 
            gameCompleted: gameState.gameCompleted, 
            isGameOver: gameState.isGameOver, 
            gameStarted: gameState.gameStarted, 
            isBoardFilled: isBoardFilled()
        });
        
        // ЕСЛИ ИГРА РЕШЕНА, ПРОИГРАНА ИЛИ ПОЛЕ ЗАПОЛНЕНО - НАЧИНАЕМ НОВУЮ БЕЗ ПРЕДУПРЕЖДЕНИЯ
        if (gameState.wasSolved || gameState.gameCompleted || gameState.isGameOver || isBoardFilled()) {
            console.log('Игра завершена или поле заполнено - начинаем новую без предупреждения');
            resetGameCompletely();
            return;
        }
        
        // Используем функцию проверки вместо прямых условий
        if (shouldShowAnyWarning()) {
            // Для активной игры - показываем подтверждение
            console.log('Показываем модальное окно подтверждения');
            showNewGameConfirmModal();
        } else {
            // Для других случаев - полный сброс БЕЗ засчета проигрыша
            console.log('Начинаем новую игру без предупреждения');
            resetGameCompletely();
        }
    });
}

    if (hintBtn) {
        hintBtn.addEventListener('click', function() {
            playClickSound(); // Звук кнопки
            giveHint();
        });
    }

    if (checkBtn) {
        checkBtn.addEventListener('click', function() {
            playClickSound(); // Звук кнопки
            checkSolution();
        });
    }

 // Обработчики для модального окна перехода на главную
const cancelHomepageBtn = document.getElementById('cancel-homepage');
const confirmHomepageBtn = document.getElementById('confirm-homepage');
const closeHomepageModal = document.getElementById('close-homepage-modal');
const homepageModal = document.getElementById('homepage-warning-modal');

if (cancelHomepageBtn) {
    cancelHomepageBtn.addEventListener('click', hideHomepageWarningModal);
}

if (confirmHomepageBtn) {
    confirmHomepageBtn.addEventListener('click', confirmHomepageRedirect);
}

if (closeHomepageModal) {
    closeHomepageModal.addEventListener('click', hideHomepageWarningModal);
}

if (homepageModal) {
    homepageModal.addEventListener('click', function(e) {
        if (e.target === homepageModal) {
            hideHomepageWarningModal();
        }
    });
}

    // Обработчик для логотипа
const logoContainer = document.querySelector('.logo');
    if (logoContainer) {
        // Добавляем проверку на существование isGuest
        if (typeof isGuest === 'undefined') {
            console.log('isGuest не определен, разрешаем стандартное поведение логотипа');
            // Разрешаем стандартное поведение
        } else if (!isGuest) {
            // Для авторизованных пользователей - полная блокировка
            logoContainer.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                // Ничего не делаем, не показываем предупреждений
                return false;
            });
            
            // Делаем логотип некликабельным
            logoContainer.style.pointerEvents = 'none';
            logoContainer.style.cursor = 'default';
            
            // Блокируем все ссылки внутри логотипа
            const logoLinks = logoContainer.querySelectorAll('a');
            logoLinks.forEach(link => {
                link.removeAttribute('href');
                link.style.pointerEvents = 'none';
            });
        } else {
            // Для гостей - разрешаем переход без предупреждения и без изменения статистики
            logoContainer.addEventListener('click', function(e) {
                // Ничего не делаем - разрешаем стандартное поведение (переход на главную)
                console.log('Гость переходит на главную страницу');
                return true;
            });
            
            // Восстанавливаем кликабельность для гостей
            logoContainer.style.pointerEvents = 'auto';
            logoContainer.style.cursor = 'pointer';
            
            // Восстанавливаем ссылки внутри логотипа для гостей
            const logoLinks = logoContainer.querySelectorAll('a');
            logoLinks.forEach(link => {
                link.setAttribute('href', 'index.php');
                link.style.pointerEvents = 'auto';
            });
        }
    }

    // Обработчики для модального окна решения
    const confirmSolveBtn = document.getElementById('confirm-solve');
    const closeSolveModal = document.getElementById('close-solve-modal');
    const cancelSolveBtn = document.getElementById('cancel-sol');

    if (cancelSolveBtn) {
    cancelSolveBtn.addEventListener('click', function() {
        playClickSound(); // Звук кнопки
        hideSolveWarningModal();
    });
}

    if (confirmSolveBtn) {
    confirmSolveBtn.addEventListener('click', function() {
        hideSolveWarningModal();
        
        // Только если игра не была завершена, засчитываем проигрыш
        if (!gameState.gameCompleted && !gameState.isGameOver) {
            handleGameLoss('solve_button');
        }
        
        solvePuzzle(); // Решаем головоломку
    });
}

    if (closeSolveModal) {
        closeSolveModal.addEventListener('click', hideSolveWarningModal);
    }

    // Обработчик для кнопки "Решить"
    if (solveBtn) {
    solveBtn.addEventListener('click', function() {
        playClickSound(); // Звук кнопки
        
        // ЕСЛИ ИГРА УЖЕ РЕШЕНА, ПРОИГРАНА ИЛИ ПОЛЕ ЗАПОЛНЕНО - НИЧЕГО НЕ ДЕЛАЕМ
        if (gameState.wasSolved || gameState.gameCompleted || gameState.isGameOver || isBoardFilled()) {
            return;
        }
        
        if (shouldShowAnyWarning()) {
            // Показываем предупреждение о том, что это засчитается как проигрыш
            // ★★★ ОБНОВЛЕНО ТЕКСТ ПРЕДУПРЕЖДЕНИЯ ★★★
            showSolveWarningModal();
        } else if (!gameState.gameCompleted) {
            // Если игра не завершена, но и не активна - просто решаем
            solvePuzzle();
        }
        // Если игра уже завершена, ничего не делаем
    });
}

    // Закрытие по клику вне области модального окна решения
    const solveWarningModal = document.getElementById('solve-warning-modal');
    if (solveWarningModal) {
        solveWarningModal.addEventListener('click', function(e) {
            if (e.target === solveWarningModal) {
                hideSolveWarningModal();
            }
        });
    }

    // Обработчики для кнопок навигации
    const statsBtn = document.getElementById('stats-btn');
    if (statsBtn) {
        statsBtn.addEventListener('click', function() {
            playClickSound(); // Звук кнопки
            showStatsModal();
        });
    }

    const achievementsBtn = document.getElementById('achievements-btn');
    if (achievementsBtn) {
        achievementsBtn.addEventListener('click', function() {
            playClickSound(); // Звук кнопки
            showAchievementsModal();
        });
    }
    
     // Обработчики для фильтрации достижений
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Убираем активный класс со всех кнопок
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Добавляем активный класс текущей кнопке
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            const achievementCards = document.querySelectorAll('.achievement-card');
            
            achievementCards.forEach(card => {
                switch (filter) {
                    case 'all':
                        card.style.display = 'flex';
                        break;
                    case 'unlocked':
                        card.style.display = card.classList.contains('unlocked') ? 'flex' : 'none';
                        break;
                    case 'locked':
                        card.style.display = card.classList.contains('locked') ? 'flex' : 'none';
                        break;
                }
            });
        });
    });
    
        // ==================== ОБРАБОТЧИКИ ДЛЯ МОДАЛЬНОГО ОКНА ВХОДА ====================

    if (cancelLoginBtn) {
        cancelLoginBtn.addEventListener('click', hideLoginWarningModal);
    }

    if (confirmLoginBtn) {
    confirmLoginBtn.addEventListener('click', async function() {
        hideLoginWarningModal();
        
        // Только для гостей засчитываем проигрыш при активной игре
        if (typeof isGuest !== 'undefined' && isGuest && gameState.gameStarted && !gameState.gameCompleted && !gameState.isGameOver) {
            await handleGameLoss('login_redirect');
        }
        
        // Перенаправляем на страницу авторизации
        window.location.href = originalHref || 'login.php';
    });
}

    if (closeLoginModal) {
        closeLoginModal.addEventListener('click', hideLoginWarningModal);
    }

    // Закрытие по клику вне области модального окна входа
    if (loginWarningModal) {
        loginWarningModal.addEventListener('click', function(e) {
            if (e.target === loginWarningModal) {
                hideLoginWarningModal();
            }
        });
    }

    // Модифицируем обработчик для кнопки "Войти" в гостевом режиме
    const loginBtn = document.querySelector('a[href="login.php"]');
if (loginBtn) {
    // ИСПРАВЛЕНИЕ: используем глобальную переменную, а не создаем новую
    originalHref = loginBtn.getAttribute('href') || originalHref;
    
    loginBtn.addEventListener('click', function(e) {
        playClickSound(); // Звук кнопки
        e.preventDefault();

        // Если игра завершена или поле заполнено - переходим без предупреждения
        if (gameState.wasSolved || gameState.gameCompleted || gameState.isGameOver || isBoardFilled()) {
            window.location.href = originalHref;
            return;
        }
        
        // Если игра активна и не завершена, показываем предупреждение
        if (shouldShowAnyWarning()) {
            showLoginWarningModal();
        } else {
            // Если игра не активна, просто переходим
            window.location.href = originalHref;
        }
    });
}

    // ==================== КОНЕЦ ОБРАБОТЧИКОВ ДЛЯ МОДАЛЬНОГО ОКНА ВХОДА ====================
    
    // Заменяем стандартный обработчик выхода
    const logoutBtn = document.querySelector('.btn-danger[href*="logout"]');
if (logoutBtn) {
    logoutBtn.addEventListener('click', function(e) {
        playClickSound(); // Звук кнопки
        // ЕСЛИ ИГРА РЕШЕНА, ПРОИГРАНА ИЛИ ПОЛЕ ЗАПОЛНЕНО - РАЗРЕШАЕМ ВЫХОД БЕЗ ПРЕДУПРЕЖДЕНИЯ
        if (gameState.wasSolved || gameState.gameCompleted || gameState.isGameOver || isBoardFilled()) {
            return; // Разрешаем стандартное поведение
        }
        
        e.preventDefault();
        
        // Если игра была решена, разрешаем выход без предупреждения
        if (gameState.wasSolved) {
            window.location.href = '/logout.php';
            return;
        }
        
        // Если игра начата и не завершена, показываем предупреждение
        if (shouldShowAnyWarning()) {
            showLogoutWarningModal();
        } else {
            // Если игра не активна, просто перенаправляем на PHP скрипт выхода
            window.location.href = '/logout.php';
        }
    });
}

// ==================== ОБРАБОТЧИКИ ДЛЯ ТАБЛИЦЫ ЛИДЕРОВ ====================

// Обработчик для клика по всей секции "Ваша позиция"
document.addEventListener('click', function(e) {
    const currentUserSection = document.querySelector('.current-user-section');
    if (currentUserSection && currentUserSection.contains(e.target)) {
        // Исключаем клики по кнопке прокрутки (у нее уже есть свой обработчик)
        if (!e.target.closest('.scroll-to-user-btn')) {
            scrollToUserInLeaderboard();
        }
    }
});

    // Обработчики для кнопки обновления таблицы лидеров
    const refreshLeaderboardBtn = document.getElementById('refresh-leaderboard');
    if (refreshLeaderboardBtn) {
        refreshLeaderboardBtn.addEventListener('click', refreshLeaderboard);
    }
    
    // Добавляем обработчик для кнопки таблицы лидеров
    const leaderboardBtn = document.getElementById('leaderboard-btn');
    if (leaderboardBtn) {
        leaderboardBtn.addEventListener('click', function() {
            playClickSound(); // Звук кнопки
            showLeaderboardModal();
        });
    }
    
    // Закрытие таблицы лидеров
    const closeLeaderboardBtn = document.getElementById('close-leaderboard-btn');
    if (closeLeaderboardBtn) {
        closeLeaderboardBtn.addEventListener('click', function() {
            playClickSound(); // Звук кнопки
            closeModal(document.getElementById('leaderboard-modal'));
        });
    }
    
    const closeLeaderboardModal = document.getElementById('close-leaderboard-modal');
    if (closeLeaderboardModal) {
        closeLeaderboardModal.addEventListener('click', function() {
            playClickSound(); // Звук кнопки
            closeModal(document.getElementById('leaderboard-modal'));
        });
    }

    // Обработчики для закрытия модальных окон
    document.querySelectorAll('.modal-close').forEach(closeBtn => {
        closeBtn.addEventListener('click', (e) => {
            const modal = e.target.closest('.modal');
            if (modal) {
                closeModal(modal);
            }
        });
    });
    
     // Звук Отмена (в различных модальных окнах)
    const cancelButtons = [
        'cancel-login', 'cancel-logout', 'cancel-solve', 
        'cancel-new-game', 'cancel-difficulty-change'
    ];
    
    cancelButtons.forEach(btnId => {
        const button = document.getElementById(btnId);
        if (button) {
            button.addEventListener('click', function() {
                playClickSound(); // Звук кнопки
            });
        }
    });

    // Звук Перейти/Подтвердить (в различных модальных окнах)
    const confirmButtons = [
        'confirm-login', 'confirm-logout', 'confirm-solve',
        'confirm-new-game', 'confirm-difficulty-change'
    ];
    
    confirmButtons.forEach(btnId => {
        const button = document.getElementById(btnId);
        if (button) {
            button.addEventListener('click', function() {
                playClickSound(); // Звук кнопки
            });
        }
    });
    
    // Звук акрыть (крестики в модальных окнах)
    document.querySelectorAll('.modal-close, .close-btn').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            playClickSound(); // Звук кнопки
            const modal = this.closest('.modal');
            if (modal) {
                closeModal(modal);
            }
        });
    });
    
        // Закрытие статистики
    const closeStatsBtn = document.getElementById('close-stats-btn');
    if (closeStatsBtn) {
        closeStatsBtn.addEventListener('click', function() {
            playClickSound(); // Звук кнопки
            closeModal(statsModal);
        });
    }

    // Закрытие достижений
    const closeAchievementsBtn = document.getElementById('close-achievements-btn');
    if (closeAchievementsBtn) {
        closeAchievementsBtn.addEventListener('click', function() {
            playClickSound(); // Звук кнопки
            closeModal(achievementsModal);
        });
    }

    // const closeWinModal = document.getElementById('close-win-modal');
    // if (closeWinModal) {
        // closeWinModal.addEventListener('click', () => closeModal(winModal));
    // }

    const newGameWinBtn = document.getElementById('new-game-win-btn');
    if (newGameWinBtn) {
        newGameWinBtn.addEventListener('click', () => {
            closeModal(winModal);
            startNewGame();
        });
    }

    // Обработчики для модального окна новой игры
    const closeNewGameConfirm = document.getElementById('close-new-game-confirm-modal');
    if (closeNewGameConfirm) {
        closeNewGameConfirm.addEventListener('click', hideNewGameConfirmModal);
    }

    const cancelNewGame = document.getElementById('cancel-new-game');
    if (cancelNewGame) {
        cancelNewGame.addEventListener('click', hideNewGameConfirmModal);
    }

    const confirmNewGame = document.getElementById('confirm-new-game');
if (confirmNewGame) {
    confirmNewGame.addEventListener('click', async () => {
        hideNewGameConfirmModal();
        
        // Только если игра не была завершена, засчитываем проигрыш
        if (!gameState.gameCompleted && !gameState.isGameOver) {
            // Обновляем статистику (засчитываем проигрыш)
            stats.totalGames++;
            await saveStatsLocally(stats);
            updateStatsDisplay();
        }
        
        // Начинаем новую игру с полным сбросом
        resetGameCompletely();
    });
}

    // Закрытие по клику вне области модальных окон (кроме окна победы)
document.querySelectorAll('.modal').forEach(modal => {
    if (modal && modal.id !== 'win-modal') {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                playClickSound(); // Звук вне области
                closeModal(modal);
            }
        });
    }
});

    // Обработчики для модального окна проигрыша при 3 ошибках
    const closeLoseGameModal = document.getElementById('close-lose-game-modal');
    const fillBoardBtn = document.getElementById('fill-board-btn'); // ← ИЗМЕНИТЬ НАЗВАНИЕ
    const newGameAfterLoseBtn = document.getElementById('new-game-after-lose-btn');

    if (closeLoseGameModal) {
        closeLoseGameModal.addEventListener('click', function() {
            hideLoseGameModal();
            enableNewGameButton();
        });
    }

    if (fillBoardBtn) { // ← ИЗМЕНИТЬ НАЗВАНИЕ
        fillBoardBtn.addEventListener('click', function() {
        playClickSound(); // Звук кнопки "Посмотреть решение"
            // Доска уже заполнена, просто закрываем модальное окно
            hideLoseGameModal();
            enableNewGameButton();
        });
    }

    if (newGameAfterLoseBtn) {
        newGameAfterLoseBtn.addEventListener('click', function() {
        playClickSound(); // Звук кнопки "Новая игра"
            hideLoseGameModal();
            startNewGame();
        });
    }

    // Закрытие по клику вне области модального окна проигрыша
    const loseGameModal = document.getElementById('lose-game-modal');
    if (loseGameModal) {
        loseGameModal.addEventListener('click', function(e) {
            if (e.target === loseGameModal) {
                hideLoseGameModal();
                enableNewGameButton();
            }
        });
    }

    // Обработчики для клавиатуры
    document.addEventListener('keydown', handleKeyboardInput);
    
    // Настройка обработчиков предупреждающих модальных окон
    setupWarningModals();
    setupDifficultyModal();
    
     // ★★★ ОБРАБОТЧИКИ ДЛЯ МОДАЛЬНОГО ОКНА ИНСТРУКЦИИ ★★★
    const instructionsBtn = document.getElementById('instructions-btn');
    const closeInstructionsBtn = document.getElementById('close-instructions-btn');
    const closeInstructionsModal = document.getElementById('close-instructions-modal');
    
    if (instructionsBtn) {
        instructionsBtn.addEventListener('click', showInstructionsModal);
    }
    
    if (closeInstructionsBtn) {
        closeInstructionsBtn.addEventListener('click', hideInstructionsModal);
    }
    
    if (closeInstructionsModal) {
        closeInstructionsModal.addEventListener('click', hideInstructionsModal);
    }
    
    // Закрытие по клику вне области модального окна инструкции
    const instructionsModal = document.getElementById('instructions-modal');
    if (instructionsModal) {
        instructionsModal.addEventListener('click', function(e) {
            if (e.target === instructionsModal) {
                hideInstructionsModal();
            }
        });
    }
}

// Обработчики для новых модальных окон
function setupWarningModals() {
    // Для кнопок сложности
    if (easyBtn) {
        easyBtn.addEventListener('click', function() {
            playClickSound(); // Звук кнопки
            console.log('Нажата кнопка Легкий. Состояние:', {
                wasSolved: gameState.wasSolved, 
                gameCompleted: gameState.gameCompleted, 
                gameStarted: gameState.gameStarted, 
                isGameOver: gameState.isGameOver, 
                isBoardFilled: isBoardFilled()
            });
            
            // ЕСЛИ ИГРА РЕШЕНА, ПРОИГРАНА ИЛИ ПОЛЕ ЗАПОЛНЕНО - МЕНЯЕМ СЛОЖНОСТЬ БЕЗ ПРЕДУПРЕЖДЕНИЯ
            if (gameState.wasSolved || gameState.gameCompleted || gameState.isGameOver || isBoardFilled()) {
                console.log('Игра завершена или поле заполнено - меняем сложность без предупреждения');
                changeDifficulty(DIFFICULTY.EASY);
                return;
            }
            
            if (shouldShowAnyWarning() && gameState.currentDifficulty !== DIFFICULTY.EASY) {
                console.log('Игра активна - показываем предупреждение');
                showDifficultyWarningModal(DIFFICULTY.EASY);
            } else if (gameState.currentDifficulty !== DIFFICULTY.EASY) {
                console.log('Меняем сложность без предупреждения');
                changeDifficulty(DIFFICULTY.EASY);
            }
        });
    }

    if (mediumBtn) {
        mediumBtn.addEventListener('click', function() {
            playClickSound(); // Звук кнопки
            console.log('Нажата кнопка Средний. Состояние:', {
                wasSolved: gameState.wasSolved, 
                gameCompleted: gameState.gameCompleted, 
                gameStarted: gameState.gameStarted, 
                isGameOver: gameState.isGameOver, 
                isBoardFilled: isBoardFilled()
            });
            
            // ЕСЛИ ИГРА РЕШЕНА, ПРОИГРАНА ИЛИ ПОЛЕ ЗАПОЛНЕНО - МЕНЯЕМ СЛОЖНОСТЬ БЕЗ ПРЕДУПРЕЖДЕНИЯ
            if (gameState.wasSolved || gameState.gameCompleted || gameState.isGameOver || isBoardFilled()) {
                console.log('Игра завершена или поле заполнено - меняем сложность без предупреждения');
                changeDifficulty(DIFFICULTY.MEDIUM);
                return;
            }
            
            if (shouldShowAnyWarning() && gameState.currentDifficulty !== DIFFICULTY.MEDIUM) {
                console.log('Игра активна - показываем предупреждение');
                showDifficultyWarningModal(DIFFICULTY.MEDIUM);
            } else if (gameState.currentDifficulty !== DIFFICULTY.MEDIUM) {
                console.log('Меняем сложность без предупреждения');
                changeDifficulty(DIFFICULTY.MEDIUM);
            }
        });
    }

    if (hardBtn) {
        hardBtn.addEventListener('click', function() {
            playClickSound(); // Звук кнопки
            console.log('Нажата кнопка Трудный. Состояние:', {
                wasSolved: gameState.wasSolved, 
                gameCompleted: gameState.gameCompleted, 
                gameStarted: gameState.gameStarted, 
                isGameOver: gameState.isGameOver, 
                isBoardFilled: isBoardFilled()
            });
            
            // ЕСЛИ ИГРА РЕШЕНА, ПРОИГРАНА ИЛИ ПОЛЕ ЗАПОЛНЕНО - МЕНЯЕМ СЛОЖНОСТЬ БЕЗ ПРЕДУПРЕЖДЕНИЯ
            if (gameState.wasSolved || gameState.gameCompleted || gameState.isGameOver || isBoardFilled()) {
                console.log('Игра завершена или поле заполнено - меняем сложность без предупреждения');
                changeDifficulty(DIFFICULTY.HARD);
                return;
            }
            
            if (shouldShowAnyWarning() && gameState.currentDifficulty !== DIFFICULTY.HARD) {
                console.log('Игра активна - показываем предупреждение');
                showDifficultyWarningModal(DIFFICULTY.HARD);
            } else if (gameState.currentDifficulty !== DIFFICULTY.HARD) {
                console.log('Меняем сложность без предупреждения');
                changeDifficulty(DIFFICULTY.HARD);
            }
        });
    }
}

// ==================== Модальное окно для выхода из системы ====================

const logoutWarningModal = document.getElementById('logout-warning-modal');
const cancelLogoutBtn = document.getElementById('cancel-logout');
const confirmLogoutBtn = document.getElementById('confirm-logout');
const closeLogoutModal = document.getElementById('close-logout-modal');

// Функция для показа модального окна выхода
function showLogoutWarningModal() {
    if (logoutWarningModal) {
        logoutWarningModal.style.display = 'flex';
    }
}

// Функция для скрытия модального окна выхода
function hideLogoutWarningModal() {
    if (logoutWarningModal) {
        logoutWarningModal.style.display = 'none';
    }
}

// Обработчики для модального окна выхода
if (cancelLogoutBtn) {
    cancelLogoutBtn.addEventListener('click', hideLogoutWarningModal);
}

if (confirmLogoutBtn) {
    confirmLogoutBtn.addEventListener('click', async function() {
        hideLogoutWarningModal();
        await handleGameLoss('logout');
        
        // Перенаправляем на PHP скрипт выхода, который обработает сессию и CSRF
        window.location.href = '/logout.php';
    });
}

if (closeLogoutModal) {
    closeLogoutModal.addEventListener('click', hideLogoutWarningModal);
}

// Закрытие по клику вне области модального окна выхода
if (logoutWarningModal) {
    logoutWarningModal.addEventListener('click', function(e) {
        if (e.target === logoutWarningModal) {
            hideLogoutWarningModal();
        }
    });
}

// Функция для настройки модального окна сложности
function setupDifficultyModal() {
    const modal = document.getElementById('difficulty-warning-modal');
    if (!modal) return;

    // Обработчик подтверждения смены сложности
    const confirmBtn = document.getElementById('confirm-difficulty-change');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            playClickSound(); // Звук кнопки
            if (gameState.pendingDifficultyChange) {
                handleGameLoss('change_difficulty');
                changeDifficulty(gameState.pendingDifficultyChange);
                hideDifficultyWarningModal();
            }
        });
    }

    // Обработчик отмены
    const cancelBtn = document.getElementById('cancel-difficulty-change');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            playClickSound(); // Звук кнопки
            hideDifficultyWarningModal();
        });
    }

    // Обработчик закрытия через крестик
    const closeBtn = document.getElementById('close-difficulty-warning-modal');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            playClickSound(); // Звук кнопки
            hideDifficultyWarningModal();
        });
    }

    // Закрытие по клику вне области
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            playClickSound(); // Звук кнопки
            hideDifficultyWarningModal();
        }
    });
}

function showDifficultyWarningModal(difficulty) {
    // ЕСЛИ ИГРА РЕШЕНА, МЕНЯЕМ СЛОЖНОСТЬ БЕЗ ПРЕДУПРЕЖДЕНИЯ
    if (gameState.wasSolved || gameState.gameCompleted) {
        changeDifficulty(difficulty);
        return;
    }
    
    const modal = document.getElementById('difficulty-warning-modal');
    if (modal) {
        gameState.pendingDifficultyChange = difficulty;
        modal.style.display = 'flex';
    }
}

function hideDifficultyWarningModal() {
    const modal = document.getElementById('difficulty-warning-modal');
    if (modal) {
        modal.style.display = 'none';
        gameState.pendingDifficultyChange = null;
    }
}

function showNewGameConfirmModal() {
    // ЕСЛИ ИГРА РЕШЕНА ИЛИ ПРОИГРАНА, НЕ ПОКАЗЫВАЕМ ПРЕДУПРЕЖДЕНИЕ
    if (gameState.wasSolved || gameState.gameCompleted || gameState.isGameOver) {
        hideNewGameConfirmModal();
        resetGameCompletely();
        return;
    }
    
    if (newGameConfirmModal) {
        newGameConfirmModal.style.display = 'flex';
    }
}

function hideNewGameConfirmModal() {
    if (newGameConfirmModal) {
        newGameConfirmModal.style.display = 'none';
    }
}

    // Очистка сохраненной игры на сервере
    async function clearSavedGame() {
    if (typeof isGuest !== 'undefined' && !isGuest) {
        try {
            const response = await fetch('api/clear_game.php', {
                method: 'POST',
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                console.log('Сохраненная игра очищена на сервере');
            }
        } catch (error) {
            console.error('Ошибка очистки игры на сервере:', error);
        }
    } else if (typeof isGuest !== 'undefined' && isGuest) {
        // Для гостей очищаем из localStorage с НОВЫМ ключом
        localStorage.removeItem('pluk_sudoku_guest_game'); // ИЗМЕНИТЬ КЛЮЧ
        localStorage.removeItem('pluk_sudoku_was_solved');
        localStorage.removeItem('pluk_sudoku_game_lost');
        console.log('Сохраненная игра гостя очищена из localStorage');
    }
}

    // Обработка ввода с клавиатуры
    function handleKeyboardInput(e) {
    if (gameState.isGameOver) return;
    
    // ★★★ ЗАКРЫТИЕ МОДАЛЬНОГО ОКНА ИНСТРУКЦИИ ПРИ НАЖАТИИ ESCAPE ★★★
    if (e.key === 'Escape') {
        const instructionsModal = document.getElementById('instructions-modal');
        if (instructionsModal && instructionsModal.style.display === 'flex') {
            hideInstructionsModal();
            return;
        }
    }
    
    if (gameState.selectedCell) {
        const key = e.key;
        
        if (key >= '1' && key <= '9') {
            handleNumberInput(parseInt(key));
        } else if (key === '0' || key === 'Backspace' || key === 'Delete') {
            handleNumberInput(0);
        } else if (key === 'h' || key === 'H') {
            giveHint();
        } else if (key === 'c' || key === 'C') {
            checkSolution();
        } else if (key === 'ArrowUp') {
            e.preventDefault();
            const newRow = Math.max(0, gameState.selectedCell.row - 1);
            selectCell(newRow, gameState.selectedCell.col);
        } else if (key === 'ArrowDown') {
            e.preventDefault();
            const newRow = Math.min(BOARD_SIZE - 1, gameState.selectedCell.row + 1);
            selectCell(newRow, gameState.selectedCell.col);
        } else if (key === 'ArrowLeft') {
            e.preventDefault();
            const newCol = Math.max(0, gameState.selectedCell.col - 1);
            selectCell(gameState.selectedCell.row, newCol);
        } else if (key === 'ArrowRight') {
            e.preventDefault();
            const newCol = Math.min(BOARD_SIZE - 1, gameState.selectedCell.col + 1);
            selectCell(gameState.selectedCell.row, newCol);
        }
    }
}

    // Создание игрового поля
    function createBoard() {
        if (!boardElement) return;
        
        boardElement.innerHTML = '';
        
        for (let row = 0; row < BOARD_SIZE; row++) {
            for (let col = 0; col < BOARD_SIZE; col++) {
                const cell = document.createElement('div');
                cell.className = 'cell';
                cell.dataset.row = row;
                cell.dataset.col = col;
                
                cell.addEventListener('click', () => selectCell(row, col));
                
                boardElement.appendChild(cell);
            }
        }
    }

    // Выбор ячейки
    function selectCell(row, col) {
        // Снимаем выделение со всех ячеек
        document.querySelectorAll('.cell').forEach(cell => {
            cell.classList.remove('selected', 'related', 'highlighted', 'error');
        });

        // Выделяем выбранную ячейку
        const cell = getCellElement(row, col);
        if (cell) {
            cell.classList.add('selected');
            gameState.selectedCell = { row, col };

            // Подсвечиваем связанные ячейки
            highlightRelatedCells(row, col);
        }
    }

    // Подсветка связанных ячеек
    function highlightRelatedCells(row, col) {
    const value = board[row][col];
    
    // Подсвечиваем ячейки с таким же значением
    if (value !== EMPTY_CELL) {
        for (let r = 0; r < BOARD_SIZE; r++) {
            for (let c = 0; c < BOARD_SIZE; c++) {
                if (board[r][c] === value && !(r === row && c === col)) {
                    const cell = getCellElement(r, c);
                    if (cell) cell.classList.add('highlighted');
                }
            }
        }
    }

    // Подсвечиваем связанные ячейки (строка, столбец, блок)
    // Строка
    for (let c = 0; c < BOARD_SIZE; c++) {
        if (c !== col) {
            const cell = getCellElement(row, c);
            if (cell) cell.classList.add('related');
        }
    }
    
    // Столбец
    for (let r = 0; r < BOARD_SIZE; r++) {
        if (r !== row) {
            const cell = getCellElement(r, col);
            if (cell) cell.classList.add('related');
        }
    }
    
    // Блок 3x3:
    const blockRowStart = Math.floor(row / 3) * 3;
    const blockColStart = Math.floor(col / 3) * 3;
    
    for (let r = blockRowStart; r < blockRowStart + 3; r++) {
        for (let c = blockColStart; c < blockColStart + 3; c++) {
            if ((r !== row || c !== col)) {
                const cell = getCellElement(r, c);
                if (cell) cell.classList.add('related');
            }
        }
    }
}

    // Получение элемента ячейки по координатам
    function getCellElement(row, col) {
        return document.querySelector(`.cell[data-row="${row}"][data-col="${col}"]`);
    }

    // Обновление отображения доски
    function updateBoardView() {
    for (let row = 0; row < BOARD_SIZE; row++) {
        for (let col = 0; col < BOARD_SIZE; col++) {
            const cell = getCellElement(row, col);
            if (cell) {
                // Очищаем ячейку и ВОССТАНАВЛИВАЕМ СТИЛИ
                cell.textContent = '';
                cell.className = 'cell';
                cell.style.pointerEvents = '';
                cell.style.opacity = '';
                cell.style.backgroundColor = '';
                
                // Устанавливаем значение, если оно есть
                if (board[row][col] !== EMPTY_CELL) {
                    cell.textContent = board[row][col];
                    
                    if (fixedCells[row][col]) {
                        cell.classList.add('fixed');
                    } else {
                        cell.classList.add('user-input');
                        
                        // Проверяем на ошибки
                        if (board[row][col] !== solution[row][col]) {
                            cell.classList.add('error');
                        }
                    }
                }
            }
        }
    }
    
    // Восстанавливаем выделение, если есть выбранная ячейка
    if (gameState.selectedCell) {
        const { row, col } = gameState.selectedCell;
        const cell = getCellElement(row, col);
        if (cell) {
            cell.classList.add('selected');
            highlightRelatedCells(row, col);
        }
    }
    
    // Обновляем состояние кнопок с цифрами
    updateNumberButtons();
}

    // Функция для проверки, все ли ячейки с определенным числом заполнены
    function isNumberCompleted(number) {
        if (number === 0) return false; // Для кнопки очистки
        
        let count = 0;
        for (let row = 0; row < BOARD_SIZE; row++) {
            for (let col = 0; col < BOARD_SIZE; col++) {
                if (board[row][col] === number) {
                    count++;
                }
            }
        }
        
        // В судоку каждое число должно встречаться ровно 9 раз
        return count >= 9;
    }

    // Функция для обновления состояния кнопок с цифрами
    function updateNumberButtons() {
        numberBtns.forEach(btn => {
            const number = parseInt(btn.dataset.number);
            if (isNumberCompleted(number)) {
                btn.classList.add('completed');
                btn.disabled = true;
            } else {
                btn.classList.remove('completed');
                btn.disabled = false;
            }
        });
    }

    // Новая игра
function startNewGame() {
    // Очищаем сохраненные значения победы
    localStorage.removeItem('win_seconds');
    localStorage.removeItem('win_mistakes');
    localStorage.removeItem('win_hints_used');
    gameState.isGameOver = false;
    gameState.gameCompleted = false;
    gameState.gameLoadedFromStorage = false;
    resetGameState(); // Эта функция теперь сбрасывает все стили
    
    // РАЗБЛОКИРУЕМ ВСЕ КНОПКИ ПЕРЕД НАЧАЛОМ НОВОЙ ИГРЫ
    enableControlButtons();
    
    generatePuzzle();
    startTimer();
    saveGame();
    updateBoardView();
    updateNumberButtons();
    updateMistakesDisplay();
    updateHintsDisplay();
    
    // РАЗБЛОКИРУЕМ КНОПКУ "РЕШИТЬ"
    if (solveBtn) {
        solveBtn.disabled = false;
        solveBtn.classList.remove('disabled');
    }
    
    // Сбрасываем флаг решенной игры
    gameState.wasSolved = false;
    localStorage.removeItem('pluk_sudoku_was_solved');
    
    // Удаляем состояние кнопки из localStorage
    localStorage.removeItem('solveBtnDisabled');
    
    gameState.gameStarted = true;
    
    // Сохраняем текущий уровень сложности
    localStorage.setItem('currentDifficulty', gameState.currentDifficulty.name);
    
    updateStatsDisplay();
    
    // Проверяем и обновляют достижения
    checkAndUpdateAchievements();
    
    // Обновляем статус
    updateStatusDisplay();
    
    showNotification('Новая игра начата!', 'info');
}

// Новая игра без обновления статистики (после победы)
function startNewGameWithoutStatsUpdate() {
    // Очищаем сохраненные значения победы
    localStorage.removeItem('win_seconds');
    localStorage.removeItem('win_mistakes');
    localStorage.removeItem('win_hints_used');
    gameState.isGameOver = false;
    gameState.gameCompleted = false;
    gameState.gameLoadedFromStorage = false;
    resetGameState(); // Эта функция теперь сбрасывает все стили
    generatePuzzle();
    startTimer(); // Запускаем таймер заново
    saveGame();
    updateBoardView();
    updateNumberButtons();
    updateHintsDisplay();
    
    // РАЗБЛОКИРУЕМ КНОПКУ "РЕШИТЬ"
    if (solveBtn) {
        solveBtn.disabled = false;
        solveBtn.classList.remove('disabled');
    }
    
    // Сбрасываем флаг решенной игры
    gameState.wasSolved = false;
    localStorage.removeItem('pluk_sudoku_was_solved');
    
    // Удаляем состояние кнопки из localStorage
    localStorage.removeItem('solveBtnDisabled');
    
    gameState.gameStarted = true;
    
    // Обновляем статус
    updateStatusDisplay();
    
    showNotification('Новая игра начата!', 'info');
}

    // Сброс состояния игры
function resetGameState() {
    board = Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(EMPTY_CELL));
    solution = Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(EMPTY_CELL));
    fixedCells = Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(false));
    gameState.selectedCell = null;
    
    // Устанавливаем начальное время в 0 для восходящего отсчета
    gameState.seconds = 0;
    
    gameState.mistakes = 0;
    gameState.hintsUsed = 0;
    gameState.hintsLeft = MAX_HINTS;
    gameState.isGameOver = false;
    
    // Сбрасываем флаги проигрыша и победы
    localStorage.removeItem('pluk_sudoku_game_lost');
    localStorage.removeItem('pluk_sudoku_was_solved');
    localStorage.removeItem('pluk_sudoku_win_shown');
    localStorage.removeItem('pluk_sudoku_time_expired');
    
    updateTimerDisplay();
    updateMistakesDisplay();
    updateHintsDisplay();
    
    // Снимаем выделение со всех ячеек и ВОССТАНАВЛИВАЕМ СТИЛИ
    document.querySelectorAll('.cell').forEach(cell => {
        cell.classList.remove('selected', 'related', 'highlighted', 'error', 'solved');
        cell.style.pointerEvents = '';
        cell.style.opacity = '';
        cell.style.backgroundColor = '';
    });
    
    // Удаляем эффекты победы/проигрыша
    if (boardElement) boardElement.classList.remove('win-pulse');
    document.querySelectorAll('.win-effect').forEach(el => el.remove());
    
    // Сбрасываем состояние кнопок с цифрами
    numberBtns.forEach(btn => {
        btn.classList.remove('completed');
        btn.disabled = false;
        btn.style.opacity = '';
    });
    
    // ВАЖНО: Восстанавливаем взаимодействие с доской
    enableBoardInteraction();
    
    // ВАЖНО: Разблокируем все кнопки управления
    enableControlButtons();
}

    // Генерация головоломки
    function generatePuzzle() {
    // Сначала сбрасываем доску
    board = Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(EMPTY_CELL));
    solution = Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(EMPTY_CELL));
    fixedCells = Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(false));
    
    // Генерируем полное решение
    generateSolution();
    
    // Создаем копию решения для головоломки
    for (let row = 0; row < BOARD_SIZE; row++) {
        for (let col = 0; col < BOARD_SIZE; col++) {
            board[row][col] = solution[row][col];
        }
    }
    
    // Удаляем часть чисел в зависимости от сложности
    const cellsToRemove = gameState.currentDifficulty.cellsToRemove;
    let removedCells = 0;
    let attempts = 0;
    const maxAttempts = 1000; // Защита от бесконечного цикла
    
    while (removedCells < cellsToRemove && attempts < maxAttempts) {
        const row = Math.floor(Math.random() * BOARD_SIZE);
        const col = Math.floor(Math.random() * BOARD_SIZE);
        
        if (board[row][col] !== EMPTY_CELL) {
            // Сохраняем значение на случай, если нужно будет откатить
            const backup = board[row][col];
            board[row][col] = EMPTY_CELL;
            
            // Проверяем, остается ли головоломка решаемой
            // (здесь можно добавить проверку уникальности решения)
            
            board[row][col] = EMPTY_CELL;
            fixedCells[row][col] = false;
            removedCells++;
        }
        attempts++;
    }
    
    // Помечаем оставшиеся числа как фиксированные
    for (let row = 0; row < BOARD_SIZE; row++) {
        for (let col = 0; col < BOARD_SIZE; col++) {
            if (board[row][col] !== EMPTY_CELL) {
                fixedCells[row][col] = true;
            }
        }
    }
}

    // Генерация решения судоку (алгоритм с возвратом)
    function generateSolution() {
        // Заполняем диагональные блоки 3x3
        fillDiagonalBoxes();
        
        // Заполняем остальные клетки
        solveSudoku(0, 0);
        
        // Копируем решение
        for (let row = 0; row < BOARD_SIZE; row++) {
            for (let col = 0; col < BOARD_SIZE; col++) {
                solution[row][col] = board[row][col];
            }
        }
    }

    // Заполнение диагональных блоков 3x3
    function fillDiagonalBoxes() {
        for (let box = 0; box < BOARD_SIZE; box += 3) {
            fillBox(box, box);
        }
    }

    // Заполнение блока 3x3 случайными числами
    function fillBox(row, col) {
        const nums = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        shuffleArray(nums);
        
        let index = 0;
        for (let r = 0; r < 3; r++) {
            for (let c = 0; c < 3; c++) {
                board[row + r][col + c] = nums[index++];
            }
        }
    }

    // Перемешивание массива
    function shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
        return array;
    }

    // Решение судоку (алгоритм с возвратом)
    function solveSudoku(row, col) {
        if (row === BOARD_SIZE - 1 && col === BOARD_SIZE) {
            return true;
        }
        
        if (col === BOARD_SIZE) {
            row++;
            col = 0;
        }
        
        if (board[row][col] !== EMPTY_CELL) {
            return solveSudoku(row, col + 1);
        }
        
        const nums = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        shuffleArray(nums);
        
        for (let num of nums) {
            if (isValidPlacement(row, col, num)) {
                board[row][col] = num;
                
                if (solveSudoku(row, col + 1)) {
                    return true;
                }
                
                board[row][col] = EMPTY_CELL;
            }
        }
        
        return false;
    }

    // Проверка валидности размещения числа
    function isValidPlacement(row, col, num) {
        // Проверка строки
        for (let c = 0; c < BOARD_SIZE; c++) {
            if (board[row][c] === num) {
                return false;
            }
        }
        
        // Проверка столбца
        for (let r = 0; r < BOARD_SIZE; r++) {
            if (board[r][col] === num) {
                return false;
            }
        }
        
        // Проверка блока 3x3
        const blockRowStart = Math.floor(row / 3) * 3;
        const blockColStart = Math.floor(col / 3) * 3;
        
        for (let r = blockRowStart; r < blockRowStart + 3; r++) {
            for (let c = blockColStart; c < blockColStart + 3; c++) {
                if (board[r][c] === num) {
                    return false;
                }
            }
        }
        
        return true;
    }

    // Таймер
    function startTimer() {
    if (gameState.timerInterval) {
        clearInterval(gameState.timerInterval);
    }
    
    // Если игра была загружена из хранилища, не сбрасываем время
    if (!gameState.gameLoadedFromStorage) {
        // Устанавливаем начальное время в 0 для восходящего отсчета
        gameState.seconds = 0;
    }
    
    updateTimerDisplay();
    
    gameState.timerInterval = setInterval(() => {
        gameState.seconds++;
        updateTimerDisplay();
        
        // Проверяем, не превышен ли лимит времени
        const timeLimit = getTimeLimitForDifficulty();
        if (gameState.seconds >= timeLimit) {
            handleTimeExpired();
            return;
        }
        
        saveGame();
    }, 1000);
}

    function updateTimerDisplay() {
    if (gameState.seconds < 0) gameState.seconds = 0;
    
    const mins = Math.floor(gameState.seconds / 60).toString().padStart(2, '0');
    const secs = (gameState.seconds % 60).toString().padStart(2, '0');
    
    if (timerElement) {
        timerElement.textContent = `${mins}:${secs}`;
        
        // Убираем предыдущие стили
        timerElement.classList.remove('timer-warning', 'timer-critical');
        
        // Добавляем предупреждающие стили при приближении к лимиту
        const timeLimit = getTimeLimitForDifficulty();
        const timeLeft = timeLimit - gameState.seconds;
        
        if (timeLeft <= 30) {
            // Меньше 30 секунд до конца - критическое время
            timerElement.classList.add('timer-critical');
        } else if (timeLeft <= 60) {
            // Меньше 1 минуты до конца - предупреждение
            timerElement.classList.add('timer-warning');
        }
    }
}

    function stopTimer() {
    if (gameState.timerInterval) {
        clearInterval(gameState.timerInterval);
        gameState.timerInterval = null;
    }
}

// Обработка истечения времени
async function handleTimeExpired() {
    if (gameState.isGameOver || gameState.gameCompleted) return;
    
    stopTimer();
    gameState.isGameOver = true;
    gameState.gameCompleted = true;
    
    // Сохраняем состояние проигрыша по времени
    localStorage.setItem('pluk_sudoku_game_lost', 'true');
    localStorage.setItem('pluk_sudoku_time_expired', 'true');
    
    // ★★★ ИСПРАВЛЕНИЕ: Сохраняем статистику ПЕРЕД заполнением доски ★★★
    if (!gameState.pageJustLoaded) {
        stats.totalGames++;
        
        // ★★★ ВАЖНО: Сохраняем статистику СРАЗУ ★★★
        await saveStatsLocally(stats);
        updateStatsDisplay();
        
        // ★★★ ПРИНУДИТЕЛЬНОЕ СОХРАНЕНИЕ ЧАТЛОВ ★★★
        if (stats.totalPoints > 0) {
            await forceSaveStats();
        }
        
        console.log('📈 Статистика обновлена при истечении времени');
    }
    
    // Заполняем доску решением
    fillBoardWithSolution();
    
    // Сохраняем игру
    await saveGame();
    
    // Показываем модальное окно проигрыша по времени
    showTimeExpiredModal();
}
    
    function stopTimerAfterWin() {
    if (gameState.timerInterval) {
        clearInterval(gameState.timerInterval);
        gameState.timerInterval = null;
    }
    // Сохраняем финальное время победы
    localStorage.setItem('win_seconds', gameState.seconds);
    // Сохраняем состояние остановленного таймера
    saveGame();
}

    // Ввод чисел
    function handleNumberInput(number) {
    if (!gameState.selectedCell || gameState.isGameOver) return;
    
    const { row, col } = gameState.selectedCell;
    
    // Проверяем, можно ли изменять эту ячейку
    if (fixedCells[row][col]) {
        showNotification('Эта ячейка фиксирована и не может быть изменена', 'warning');
        return;
    }
    
    // Очищаем предыдущие ошибки
    clearHighlights();
    
    if (number === 0) {
        playShikSound(); // Звук очистки
        // Очистка ячейки
        board[row][col] = EMPTY_CELL;
    } else {
        playClickSound(); // Звук ввода цифры
        // Ввод числа
        board[row][col] = number;
        
        // Проверка на ошибку
        if (board[row][col] !== solution[row][col]) {
            playCorrectSound(); // Звук ошибки
            gameState.mistakes++;
            updateMistakesDisplay(); // ← ОБНОВЛЕНО: используем новую функцию
            showNotification('Ошибка!', 'error');
            highlightConflicts(row, col, number);
            
            // Проверяем на проигрыш (3 ошибки)
            if (gameState.mistakes >= MAX_MISTAKES) {
                setTimeout(() => {
                    handleGameLoss('three_mistakes');
                }, 500);
                return;
            }
        } else {
            // Анимация правильного ввода
            const cell = getCellElement(row, col);
            if (cell) {
                cell.classList.add('pulse');
                setTimeout(() => cell.classList.remove('pulse'), 500);
            }
        }
    }
    
    updateBoardView();
    saveGame();
    checkGameCompletion();
}

    // Подсветка конфликтов
    function highlightConflicts(row, col, number) {
        // Подсвечиваем строку
        for (let c = 0; c < BOARD_SIZE; c++) {
            if (c !== col && board[row][c] === number) {
                const cell = getCellElement(row, c);
                if (cell) cell.classList.add('error');
            }
        }
        
        // Подсвечиваем столбец
        for (let r = 0; r < BOARD_SIZE; r++) {
            if (r !== row && board[r][col] === number) {
                const cell = getCellElement(r, col);
                if (cell) cell.classList.add('error');
            }
        }
        
        // Подсвечиваем блок 3x3
        const blockRowStart = Math.floor(row / 3) * 3;
        const blockColStart = Math.floor(col / 3) * 3;
        
        for (let r = blockRowStart; r < blockRowStart + 3; r++) {
            for (let c = blockColStart; c < blockColStart + 3; c++) {
                if ((r !== row || c !== col) && board[r][c] === number) {
                    const cell = getCellElement(r, c);
                    if (cell) cell.classList.add('error');
                }
            }
        }
    }

    // Очистка подсветка
    function clearHighlights() {
        document.querySelectorAll('.cell').forEach(cell => {
            cell.classList.remove('error');
        });
    }

    // Проверка завершения игры
    function checkGameCompletion() {
        for (let row = 0; row < BOARD_SIZE; row++) {
            for (let col = 0; col < BOARD_SIZE; col++) {
                if (board[row][col] === EMPTY_CELL || board[row][col] !== solution[row][col]) {
                    return false;
                }
            }
        }
        
        // Игра завершена
        gameWon();
        return true;
    }

    // Победа в игре
    async function gameWon() {
    gameState.isGameOver = true;
    gameState.gameCompleted = true;
    
    // Сохраняем финальные значения
    localStorage.setItem('win_seconds', gameState.seconds);
    localStorage.setItem('win_mistakes', gameState.mistakes);
    localStorage.setItem('win_hints_used', gameState.hintsUsed);
    
    // ★★★ ПРОВЕРКА КОРРЕКТНОСТИ ДАННЫХ ★★★
    console.log('Проверка данных перед расчетом чатлов:', {
        difficulty: gameState.currentDifficulty.name,
        mistakes: gameState.mistakes,
        hintsUsed: gameState.hintsUsed,
        timeSeconds: gameState.seconds
    });
    
    // Убеждаемся, что значения корректны
    const validatedMistakes = Math.max(0, gameState.mistakes);
    const validatedHintsUsed = Math.max(0, gameState.hintsUsed);
    
    stopTimerAfterWin();
    
    // ПРОВЕРЯЕМ ДОСТИЖЕНИЯ ДО обновления статистики
    const winAchievements = checkAchievementsOnWin();
    
    // ★★★ РАСЧЕТ ЧАТЛОВ С ДЕТАЛИЗАЦИЕЙ
    const pointsBreakdown = calculatePointsWithBreakdown(
        gameState.currentDifficulty.name, 
        validatedMistakes, 
        validatedHintsUsed, 
        gameState.seconds,
        winAchievements
    );
    
    console.log('🎯 Points breakdown:', pointsBreakdown);
    
        // ★★★ СОХРАНЕНИЕ ТУРНИРНОЙ СТАТИСТИКИ ★★★
    if (typeof currentTournamentId !== 'undefined' && currentTournamentId && currentTournamentId > 0) {
        try {
            console.log('💾 Сохранение турнирной статистики для tournament_id:', currentTournamentId);
            
            const tournamentStats = {
                tournament_id: currentTournamentId,
                game_id: 'tournament_game_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                chatls_earned: pointsBreakdown.total, // Используем реально заработанные чатлы
                time_seconds: gameState.seconds,
                mistakes: gameState.mistakes,
                hints_used: gameState.hintsUsed,
                won_game: 1 // Победа в игре
            };
            
            console.log('📊 Данные для турнира:', tournamentStats);
            
            const response = await fetch('api/save_tournament_game.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(tournamentStats),
                credentials: 'same-origin'
            });
            
            const result = await response.json();
            console.log('📊 Турнирная статистика сохранена:', result);
            
        } catch (error) {
            console.error('❌ Ошибка сохранения турнирной статистики:', error);
        }
    } else {
        console.log('ℹ️ Не турнирная игра, пропускаем сохранение турнирной статистики');
    }
    
    // Показываем анимацию получения чатлов
    showPointsAnimation(pointsBreakdown.total);
    
    // ★★★ ОБНОВЛЯЕМ СТАТИСТИКУ С ВАЛИДАЦИЕЙ ★★★
    stats.totalGames++;
    stats.gamesWon++;
    stats.totalPoints += pointsBreakdown.total;
    stats.rating = stats.totalPoints; // Рейтинг = общим чатлам
    
    // ★★★ ВАЛИДИРУЕМ СТАТИСТИКУ ПЕРЕД СОХРАНЕНИЕМ ★★★
    stats = validateStats(stats);
    
    console.log('📈 Updated and validated stats:', stats);
    
    // Проверяем лучшее время (меньше время = лучше)
    const currentTime = gameState.seconds;
    const bestTime = stats.bestTimes[gameState.currentDifficulty.name];
    if (!bestTime || currentTime < bestTime) {
        stats.bestTimes[gameState.currentDifficulty.name] = currentTime;
    }
    
    // Проверяем прогрессивные достижения
    const updatedAchievements = await checkAndUpdateAchievements();
    
    // Объединяем все новые достижения
    const allNewAchievements = [...winAchievements, ...updatedAchievements];
    
    // Сохраняем статистику и достижения
    const saveResult = await saveStatsLocally(stats);
    console.log('💾 Save stats result:', saveResult);
    
    await saveAchievementsLocally(achievements);
    
    // Сохраняем новые достижения для отображения
    if (allNewAchievements.length > 0) {
        saveNewAchievements(allNewAchievements);
    }
    
    // Сохраняем состояние победы
    localStorage.setItem('pluk_sudoku_win_shown', 'true');
    localStorage.removeItem('pluk_sudoku_was_solved');
    
    // Обновляем отображение
    updateStatsDisplay();
    renderAchievements(stats);
    
    // Применяем функционал кнопки "Решить"
    applySolveFunctionality();
    
    // Создаем эффект конфетти
    createConfettiEffect();
    
    // Добавляем пульсацию к доске
    if (boardElement) boardElement.classList.add('win-pulse');
    
    // Показываем окно победы с ДЕТАЛИЗАЦИЕЙ ЧАТЛОВ
    setTimeout(() => {
        showWinModalWithBreakdown(
            gameState.seconds, 
            gameState.mistakes, 
            gameState.hintsUsed, 
            allNewAchievements, 
            pointsBreakdown
        );
    }, 1500);

// Обновляем отображение статуса
    updateStatusDisplay();        
        
    }

// ПРИМЕНЕНИЕ ФУНКЦИОНАЛА КНОПКИ "РЕШИТЬ"
function applySolveFunctionality() {
    console.log('Применение функционала кнопки "Решить" после победы');
    
    // Заполняем все ячейки решением (если еще не заполнены)
    fillBoardWithSolution();
    
    // Отключаем взаимодействие с доской
    disableBoardInteraction();
    
    // Блокируем кнопки управления, но НЕ блокируем кнопку "Новая игра"
    const controlButtonsToDisable = ['hint-btn', 'check-btn', 'solve-btn'];
    
    controlButtonsToDisable.forEach(btnId => {
        const button = document.getElementById(btnId);
        if (button) {
            button.disabled = true;
            button.classList.add('disabled');
        }
    });
    
    // Кнопка "Новая игра" должна оставаться активной
    const newGameBtn = document.getElementById('new-game-btn');
    if (newGameBtn) {
        newGameBtn.disabled = false;
        newGameBtn.classList.remove('disabled');
    }
    
    // Устанавливаем флаги решенной игры
    gameState.wasSolved = true;
    gameState.gameCompleted = true;
    gameState.isGameOver = true;
    
    // Сохраняем состояние
    saveGame();
    
    console.log('Кнопки управления заблокированы после победы (кроме Новой игры)');
}

// ОТКЛЮЧЕНИЕ КНОПОК УПРАВЛЕНИЯ
function disableControlButtons() {
    const controlButtons = [
        'hint-btn', 'check-btn', 'solve-btn'
    ];
    
    controlButtons.forEach(btnId => {
        const button = document.getElementById(btnId);
        if (button) {
            button.disabled = true;
            button.classList.add('disabled');
        }
    });
    
    console.log('Кнопки управления заблокированы после победы (кроме Новой игры)');
}

// РАЗБЛОКИРОВКА КНОПОК УПРАВЛЕНИЯ
function enableControlButtons() {
    const controlButtons = [
        'new-game-btn', 'hint-btn', 'check-btn', 'solve-btn'
    ];
    
    controlButtons.forEach(btnId => {
        const button = document.getElementById(btnId);
        if (button) {
            button.disabled = false;
            button.classList.remove('disabled');
            console.log(`Кнопка ${btnId} разблокирована`);
        }
    });
    
    // Также включаем кнопки цифр
    const numberButtons = document.querySelectorAll('.number-btn');
    numberButtons.forEach(btn => {
        btn.disabled = false;
        btn.style.opacity = '';
    });
    
    // Восстанавливаем взаимодействие с доской
    enableBoardInteraction();
    
    console.log('Все кнопки управления разблокированы');
}

// Функция для разблокировки кнопки "Новая игра" после закрытия модальных окон
function enableNewGameButton() {
    const newGameBtn = document.getElementById('new-game-btn');
    if (newGameBtn) {
        newGameBtn.disabled = false;
        newGameBtn.classList.remove('disabled');
        console.log('Кнопка "Новая игра" разблокирована');
    }
}

// Функция для закрытия модального окна победы
function closeWinModal() {
    const winModal = document.getElementById('win-modal');
    if (winModal) {
        winModal.style.display = 'none';
        clearNewAchievements(); // Очищаем достижения
        // РАЗБЛОКИРУЕМ кнопку "Новая игра" при закрытии окна победы
        enableNewGameButton();
    }
}

// Функция для обновления статистики на главной странице
async function updateMainPageStats() {
    try {
        const response = await fetch('api/update_main_stats.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                stats: stats,
                achievements: achievements.filter(a => a.unlocked)
            }),
            credentials: 'same-origin'
        });
        
        if (response.ok) {
            const result = await response.json();
            console.log('Данные обновлены:', result);
        }
    } catch (error) {
        console.error('Ошибка обновления данных:', error);
    }
}

    // Создание эффекта конфетти
    function createConfettiEffect() {
    const effectContainer = document.createElement('div');
    effectContainer.className = 'win-effect';
    document.body.appendChild(effectContainer);
    
    // Создаем 100 конфетти
    for (let i = 0; i < 100; i++) {
        setTimeout(() => {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            
            // Случайные параметры
            const size = Math.random() * 10 + 5;
            const color = `hsl(${Math.random() * 60 + 30}, 100%, 50%)`; // Желто-оранжевые оттенки
            const left = Math.random() * 100;
            const animationDuration = Math.random() * 2 + 2;
            
            confetti.style.width = `${size}px`;
            confetti.style.height = `${size}px`;
            confetti.style.backgroundColor = color;
            confetti.style.left = `${left}%`;
            confetti.style.animationDuration = `${animationDuration}s`;
            confetti.style.borderRadius = '50%';
            
            effectContainer.appendChild(confetti);
            
            // Удаляем конфетти после анимации
            setTimeout(() => {
                confetti.remove();
                if (effectContainer.children.length === 0) {
                    effectContainer.remove();
                }
            }, animationDuration * 1000);
        }, Math.random() * 1000);
    }
}

function showPointsAnimation(points) {
    const pointsAnimation = document.createElement('div');
    pointsAnimation.className = 'points-animation';
    pointsAnimation.innerHTML = `
        <div class="points-popup">
            <i class="fa-solid fa-money-bill-1-wave"></i>
            +${points} чатлов!
        </div>
    `;
    
    document.body.appendChild(pointsAnimation);
    
    // Анимация появления и исчезновения
    setTimeout(() => {
        pointsAnimation.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        pointsAnimation.classList.remove('show');
        setTimeout(() => {
            pointsAnimation.remove();
        }, 500);
    }, 3000);
}

    // Проверка достижений
    function checkAchievements() {
        const newAchievements = [];
        const timeInMinutes = gameState.seconds / 60;
        
        // Привет, Плюк!
        if (stats.gamesWon === 1 && !achievements.find(a => a.id === 'first_win').unlocked) {
            const achievement = achievements.find(a => a.id === 'first_win');
            achievement.unlocked = true;
            newAchievements.push(achievement);
        }
        
        // Без ошибок
        if (gameState.mistakes === 0 && !achievements.find(a => a.id === 'no_mistakes').unlocked) {
            const achievement = achievements.find(a => a.id === 'no_mistakes');
            achievement.unlocked = true;
            newAchievements.push(achievement);
        }
        
        // Без подсказок
        if (gameState.hintsUsed === 0 && !achievements.find(a => a.id === 'no_hints').unlocked) {
            const achievement = achievements.find(a => a.id === 'no_hints');
            achievement.unlocked = true;
            newAchievements.push(achievement);
        }
        
        // Спринтер (в зависимости от сложности)
        if (gameState.currentDifficulty === DIFFICULTY.EASY && timeInMinutes < 5 && !achievements.find(a => a.id === 'speedster_easy').unlocked) {
            const achievement = achievements.find(a => a.id === 'speedster_easy');
            achievement.unlocked = true;
            newAchievements.push(achievement);
        }
        
        if (gameState.currentDifficulty === DIFFICULTY.MEDIUM && timeInMinutes < 10 && !achievements.find(a => a.id === 'speedster_medium').unlocked) {
            const achievement = achievements.find(a => a.id === 'speedster_medium');
            achievement.unlocked = true;
            newAchievements.push(achievement);
        }
        
        if (gameState.currentDifficulty === DIFFICULTY.HARD && timeInMinutes < 15 && !achievements.find(a => a.id === 'speedster_hard').unlocked) {
            const achievement = achievements.find(a => a.id === 'speedster_hard');
            achievement.unlocked = true;
            newAchievements.push(achievement);
        }
        
        // Последний выдох
        if (gameState.mistakes === 0 && gameState.hintsUsed === 0 && !achievements.find(a => a.id === 'perfectionist').unlocked) {
            const achievement = achievements.find(a => a.id === 'perfectionist');
            achievement.unlocked = true;
            newAchievements.push(achievement);
        }
        
        // Чатланин
        if (stats.gamesWon >= 10 && !achievements.find(a => a.id === 'veteran').unlocked) {
            const achievement = achievements.find(a => a.id === 'veteran');
            achievement.unlocked = true;
            newAchievements.push(achievement);
        }
        
        // Эцилопп
        if (stats.gamesWon >= 50 && !achievements.find(a => a.id === 'master').unlocked) {
            const achievement = achievements.find(a => a.id === 'master');
            achievement.unlocked = true;
            newAchievements.push(achievement);
        }
        
        return newAchievements;
    }

    // Подсказка
    function giveHint() {
        if (gameState.hintsLeft <= 0 || gameState.isGameOver) {
            showNotification('Подсказки закончились!', 'warning');
            return;
        }
        
        if (!gameState.selectedCell) {
            showNotification('Выберите ячейку для подсказки', 'warning');
            return;
        }
        
        const { row, col } = gameState.selectedCell;
        
        // Проверяем, можно ли дать подсказку для этой ячейки
        if (board[row][col] !== EMPTY_CELL) {
            showNotification('Эта ячейка уже заполнена', 'warning');
            return;
        }
        
        // Даем подсказку
        board[row][col] = solution[row][col];
        fixedCells[row][col] = true;
        
        gameState.hintsUsed++;
        gameState.hintsLeft--;
        updateHintsDisplay();
        
        // Анимация подсказки
        const cell = getCellElement(row, col);
        if (cell) {
            cell.classList.add('hint-pulse');
            setTimeout(() => cell.classList.remove('hint-pulse'), 1000);
        }
        
        updateBoardView();
        saveGame();
        checkGameCompletion();
        
        showNotification('Подсказка использована!', 'info');
    }

    // Проверка решения
    function checkSolution() {
        let hasErrors = false;
        
        for (let row = 0; row < BOARD_SIZE; row++) {
            for (let col = 0; col < BOARD_SIZE; col++) {
                if (board[row][col] !== EMPTY_CELL && board[row][col] !== solution[row][col]) {
                    const cell = getCellElement(row, col);
                    if (cell) cell.classList.add('error');
                    hasErrors = true;
                }
            }
        }
        
        if (hasErrors) {
            showNotification('Найдены ошибки!', 'error');
        } else {
            showNotification('Ошибок не найдено!', 'success');
        }
        
        setTimeout(() => {
            clearHighlights();
        }, 2000);
    }
    
    function solvePuzzle() {
    // Заполняем все ячейки решением
    for (let row = 0; row < BOARD_SIZE; row++) {
        for (let col = 0; col < BOARD_SIZE; col++) {
            board[row][col] = solution[row][col];
            fixedCells[row][col] = true;
        }
    }
    
    updateBoardView();
    stopTimer();
    gameState.isGameOver = true;
    gameState.gameCompleted = true;
    
    // ★★★ ВОССТАНОВЛЕНО: Только устанавливаем флаги, без начисления чатлов ★★★
    gameState.wasSolved = true;
    localStorage.setItem('pluk_sudoku_was_solved', 'true');
    
    // ★★★ УБРАНО: начисление чатлов при авторешении ★★★
    
    // СБРАСЫВАЕМ ФЛАГ ПОБЕДЫ, ЧТОБЫ НЕ ПОКАЗЫВАЛОСЬ ОКНО ПОБЕДЫ
    localStorage.removeItem('pluk_sudoku_win_shown');
    
    // ПРИМЕНЯЕМ ТОТ ЖЕ ФУНКЦИОНАЛ, ЧТО И ПОСЛЕ ПОБЕДЫ
    applySolveFunctionality();
    
    showNotification('Головоломка решена!', 'info');
    saveGame(); // Сохраняем состояние с флагом wasSolved
}

function changeDifficulty(difficulty) {
    gameState.currentDifficulty = difficulty;
    updateDifficultyButtons();
    
    // Сохраняем выбранную сложность в localStorage
    localStorage.setItem('currentDifficulty', gameState.currentDifficulty.name);
    
    // РАЗБЛОКИРУЕМ КНОПКИ ПРИ СМЕНЕ СЛОЖНОСТИ
    enableControlButtons();
    
    // Устанавливаем время в зависимости от сложности
    gameState.seconds = getTimeLimitForDifficulty();
    
    resetGameCompletely();
    showNotification(`Выбрана сложность: ${difficulty.label}`, 'info');
}

        // Обработка выхода из игры
        function handleExit() {
            // Если игра начата и не завершена, показываем предупреждение
            if (gameState.gameStarted && !gameState.gameCompleted && !gameState.isGameOver) {
                showLoseModal();
                return false;
            }
            return true;
        }

// Обработка проигрыша (уменьшение процента побед)
async function handleGameLoss(reason) {
    // Если игра была решена автоматически, не засчитываем проигрыш
    if (gameState.wasSolved) {
        console.log('Игра была решена, проигрыш не засчитывается');
        return;
    }
    
    // Если игра уже завершена, не обрабатываем повторно
    if (gameState.gameCompleted || gameState.isGameOver) {
        return;
    }
    
    // Останавливаем таймер
    stopTimer();
    gameState.isGameOver = true;
    gameState.gameCompleted = true;
    
    // Сохраняем состояние проигрыша
    localStorage.setItem('pluk_sudoku_game_lost', 'true');
    
    // ★★★ ИСПРАВЛЕНИЕ: Для авторешения сохраняем чатлы ★★★
    if (reason === 'solve_button') {
    // ★★★ УБРАНО: начисление чатлов при авторешении ★★★
    // Только увеличиваем общее количество игр (как было раньше)
    stats.totalGames++;
    
    console.log('📈 Авторешение через кнопку: игра засчитана, чатлы не начислены');
} else {
    // Для других причин проигрыша - стандартная логика
    stats.totalGames++;
}
    
    // Сохраняем статистику
    await saveStatsLocally(stats);
    updateStatsDisplay();
    
    // ЗАПОЛНЯЕМ ДОСКУ РЕШЕНИЕМ ПРИ ПРОИГРЫШЕ
    fillBoardWithSolution();
    
    // Сохраняем игру с заполненным решением
    await saveGame();
    
    // Для проигрыша при 3 ошибках показываем специальное модальное окно С РЕШЕНИЕМ
    if (reason === 'three_mistakes') {
        showLoseGameModal();
    }
    
    // Логируем причину проигрыша
    console.log(`Game lost due to: ${reason}`);
}

// Функция для обновления статистики на сервере
    async function updateServerStats() {
        if (typeof isGuest !== 'undefined' && !isGuest) {
            try {
                const response = await fetch('api/update_stats.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        totalGames: stats.totalGames,
                        gamesWon: stats.gamesWon,
                        bestTimes: stats.bestTimes
                    }),
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    console.log('Статистика обновлена на сервере');
                }
            } catch (error) {
                console.error('Ошибка обновления статистики на сервере:', error);
            }
        }
    }
    
// Показ модального окна предупреждения для кнопки "Решить"
function showSolveWarningModal() {
    const modal = document.getElementById('solve-warning-modal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

// Скрытие модального окна предупреждения для кнопки "Решить"
function hideSolveWarningModal() {
    const modal = document.getElementById('solve-warning-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Функция для показа модального окна перехода на главную
function showHomepageWarningModal() {
    const modal = document.getElementById('homepage-warning-modal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

// Функция для скрытия модального окна перехода на главную
function hideHomepageWarningModal() {
    const modal = document.getElementById('homepage-warning-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Обработчик для подтверждения перехода на главную
function confirmHomepageRedirect() {
    hideHomepageWarningModal();
    
    // Для гостей не засчитываем проигрыш при переходе на главную
    if (typeof isGuest !== 'undefined' && isGuest) {
        console.log('Гость переходит на главную - статистика не изменяется');
        window.location.href = 'index.php';
        return;
    }
    
    // Для авторизованных пользователей засчитываем проигрыш
    handleGameLoss('homepage_redirect');
    window.location.href = 'index.php';
}

// Функция для показа модального окна проигрыша при 3 ошибках //
function showLoseGameModal() {
    const loseModal = document.getElementById('lose-game-modal');
    if (!loseModal) return;
    
    // Заполняем статистику
    const loseTime = document.getElementById('lose-time');
    const loseMistakes = document.getElementById('lose-mistakes');
    const loseHints = document.getElementById('lose-hints');
    
    if (loseTime) loseTime.textContent = formatTime(gameState.seconds);
    if (loseMistakes) loseMistakes.textContent = `${gameState.mistakes}/${MAX_MISTAKES}`; // ← ОБНОВЛЕНО: формат X/3
    if (loseHints) loseHints.textContent = `${gameState.hintsUsed}/${MAX_HINTS}`; // ← ОБНОВЛЕНО: формат X/3
    
    // Показываем решенную головоломку СРАЗУ при открытии модального окна
    displaySolvedBoard();
    
    // Показываем модальное окно
    loseModal.style.display = 'flex';
    
    // Воспроизводим звук проигрыша (если есть)
    if (soundEffects && soundEffects.error) {
        soundEffects.error.play().catch(() => {
            // Игнорируем ошибки воспроизведения звука
        });
    }
    
    // Также заполняем основную доску решением (но делаем ее неактивной)
    fillBoardWithSolution();
}

// Показ модального окна проигрыша по времени
function showTimeExpiredModal() {
    const loseModal = document.getElementById('lose-game-modal');
    if (!loseModal) return;
    
    // Обновляем заголовок и сообщение для случая истечения времени
    const modalTitle = loseModal.querySelector('.modal-title');
    const loseMessage = loseModal.querySelector('.lose-message h3');
    
    if (modalTitle) {
        modalTitle.innerHTML = '<i class="fas fa-clock"></i> Время вышло!';
    }
    
    if (loseMessage) {
        loseMessage.textContent = 'Время истекло!';
    }
    
    // Заполняем статистику
    const loseTime = document.getElementById('lose-time');
    const loseMistakes = document.getElementById('lose-mistakes');
    const loseHints = document.getElementById('lose-hints');
    
    if (loseTime) loseTime.textContent = formatTime(gameState.seconds);
    if (loseMistakes) loseMistakes.textContent = `${gameState.mistakes}/${MAX_MISTAKES}`;
    if (loseHints) loseHints.textContent = `${gameState.hintsUsed}/${MAX_HINTS}`;
    
    // Показываем решенную головоломку
    displaySolvedBoard();
    
    // ★★★ СОХРАНЯЕМ СТАТИСТИКУ ПЕРЕД ПОКАЗОМ МОДАЛЬНОГО ОКНА ★★★
    if (stats && stats.totalPoints > 0) {
        forceSaveStats().then(success => {
            if (success) {
                console.log('✅ Чатлы сохранены при истечении времени');
            }
        });
    }
    
    // Показываем модальное окно
    loseModal.style.display = 'flex';
    
    // Воспроизводим звук проигрыша
    if (soundEffects && soundEffects.error) {
        soundEffects.error.play().catch(() => {
            // Игнорируем ошибки воспроизведения звука
        });
    }
    
    // Также заполняем основную доску решением
    fillBoardWithSolution();
}

// Функция для отображения решенной головоломки //
function displaySolvedBoard() {
    const solvedBoard = document.getElementById('solved-board');
    if (!solvedBoard) return;
    
    solvedBoard.innerHTML = '';
    
    // Создаем миниатюрную версию доски для модального окна
    for (let row = 0; row < BOARD_SIZE; row++) {
        for (let col = 0; col < BOARD_SIZE; col++) {
            const cell = document.createElement('div');
            cell.className = 'cell';
            cell.textContent = solution[row][col]; // ← ВЫВОДИМ ЧИСЛО НЕПОСРЕДСТВЕННО
            
            if (fixedCells[row][col]) {
                cell.classList.add('fixed');
            }
            
            // Добавляем стили для миниатюрной доски
            cell.style.width = '20px';
            cell.style.height = '20px';
            cell.style.fontSize = '12px';
            cell.style.display = 'flex';
            cell.style.alignItems = 'center';
            cell.style.justifyContent = 'center';
            cell.style.border = '1px solid #ccc';
            
            solvedBoard.appendChild(cell);
        }
    }
}

// Функция для скрытия модального окна проигрыша при 3 ошибках //
function hideLoseGameModal() {
    const loseModal = document.getElementById('lose-game-modal');
    if (loseModal) {
        loseModal.style.display = 'none';
    }
    
    // НЕ сбрасываем игру при закрытии модального окна
    // Игра остается в состоянии "проиграна" до нажатия "Новая игра"
    console.log('Модальное окно проигрыша закрыто, игра остается в завершенном состоянии');
}

    // Сохранение игры
    async function saveGame() {
    try {
        const gameData = {
            board: board,
            solution: solution,
            fixedCells: fixedCells,
            difficulty: gameState.currentDifficulty.name,
            seconds: gameState.seconds,
            mistakes: gameState.mistakes,
            hintsUsed: gameState.hintsUsed,
            hintsLeft: gameState.hintsLeft,
            wasSolved: gameState.wasSolved,
            gameLost: gameState.isGameOver && (gameState.mistakes >= MAX_MISTAKES || gameState.seconds <= 0) // ← ОБНОВЛЕНО
        };
        
        // Добавляем проверку на существование isGuest
        if (typeof isGuest === 'undefined') {
            localStorage.setItem('pluk_sudoku_guest_game', JSON.stringify(gameData)); // ИЗМЕНИТЬ КЛЮЧ
            localStorage.setItem('pluk_sudoku_was_solved', gameState.wasSolved.toString());
            localStorage.setItem('pluk_sudoku_game_lost', (gameState.isGameOver && gameState.mistakes >= MAX_MISTAKES).toString());
            return true;
        } else if (isGuest) {
            localStorage.setItem('pluk_sudoku_guest_game', JSON.stringify(gameData)); // ИЗМЕНИТЬ КЛЮЧ
            localStorage.setItem('pluk_sudoku_was_solved', gameState.wasSolved.toString());
            localStorage.setItem('pluk_sudoku_game_lost', (gameState.isGameOver && gameState.mistakes >= MAX_MISTAKES).toString());
            return true;
        } else {
            // Для авторизованных пользователей
            const response = await fetch('api/save_game.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(gameData),
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                const result = await response.json();
                return result.success;
            }
            return false;
        }
    } catch (e) {
        console.error('Failed to save game:', e);
        return false;
    }
}

    // Загрузка игры
    async function loadGame() {
    try {
        let gameData = null;
        
        // Добавляем проверку на существование isGuest
        if (typeof isGuest === 'undefined') {
            // Если isGuest не определен, загружаем из localStorage с НОВЫМ ключом
            const savedGame = localStorage.getItem('pluk_sudoku_guest_game'); // ИЗМЕНИТЬ КЛЮЧ
            
            if (savedGame) {
                try {
                    gameData = JSON.parse(savedGame);
                    console.log('Игра загружена из localStorage (isGuest не определен)');
                    
                    // Восстанавливаем данные игры
                    board = gameData.board || Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(EMPTY_CELL));
                    solution = gameData.solution || Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(EMPTY_CELL));
                    fixedCells = gameData.fixedCells || Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(false));
                    
                    // Обновляем сложность
                    if (gameData.difficulty && DIFFICULTY[gameData.difficulty.toUpperCase()]) {
                        gameState.currentDifficulty = DIFFICULTY[gameData.difficulty.toUpperCase()];
                        localStorage.setItem('currentDifficulty', gameState.currentDifficulty.name);
                        console.log('Difficulty loaded:', gameState.currentDifficulty.name);
                    }
                    
                    gameState.seconds = gameData.seconds || getTimeLimitForDifficulty();
                    
                    // Если время больше лимита, устанавливаем лимит
                    const timeLimit = getTimeLimitForDifficulty();
                    if (gameState.seconds > timeLimit) {
                        gameState.seconds = timeLimit;
                    }
                    
                    gameState.mistakes = gameData.mistakes || 0;
                    gameState.hintsUsed = gameData.hintsUsed || 0;
                    gameState.hintsLeft = gameData.hintsLeft || MAX_HINTS;
                    
                    // СОЗДАЕМ ДОСКУ ПЕРВОЙ!
                    createBoard();
                    
                    // ОБНОВЛЯЕМ ОТОБРАЖЕНИЕ ПОСЛЕ СОЗДАНИЯ ДОСКИ
                    updateBoardView();
                    updateTimerDisplay();
                    updateMistakesDisplay();
                    updateHintsDisplay();
                    
                    // Обновляем активную кнопку сложности
                    updateDifficultyButtons();
                    
                    // Запускаем таймер с сохраненным временем
                    startTimerWithSavedTime(gameState.seconds);
                    
                    return true;
                    
                } catch (e) {
                    console.error('Failed to parse localStorage game:', e);
                    return false;
                }
            }
            return false;
        } else if (isGuest) {
            // Для гостей загружаем из localStorage с НОВЫМ ключом
            const savedGame = localStorage.getItem('pluk_sudoku_guest_game'); // ИЗМЕНИТЬ КЛЮЧ
            
            if (savedGame) {
                try {
                    gameData = JSON.parse(savedGame);
                    console.log('Игра загружена из localStorage для гостя');
                    
                    // Восстанавливаем данные игры
                    board = gameData.board || Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(EMPTY_CELL));
                    solution = gameData.solution || Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(EMPTY_CELL));
                    fixedCells = gameData.fixedCells || Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(false));
                    
                    // Обновляем сложность
                    if (gameData.difficulty && DIFFICULTY[gameData.difficulty.toUpperCase()]) {
                        gameState.currentDifficulty = DIFFICULTY[gameData.difficulty.toUpperCase()];
                        localStorage.setItem('currentDifficulty', gameState.currentDifficulty.name);
                        console.log('Difficulty loaded:', gameState.currentDifficulty.name);
                    }
                    
                    gameState.seconds = gameData.seconds || getTimeLimitForDifficulty();
                    
                    // Если время больше лимита, устанавливаем лимит
                    const timeLimit = getTimeLimitForDifficulty();
                    if (gameState.seconds > timeLimit) {
                        gameState.seconds = timeLimit;
                    }
                    
                    gameState.mistakes = gameData.mistakes || 0;
                    gameState.hintsUsed = gameData.hintsUsed || 0;
                    gameState.hintsLeft = gameData.hintsLeft || MAX_HINTS;
                    
                    // СОЗДАЕМ ДОСКУ ПЕРВОЙ!
                    createBoard();
                    
                    // ОБНОВЛЯЕМ ОТОБРАЖЕНИЕ ПОСЛЕ СОЗДАНИЯ ДОСКИ
                    updateBoardView();
                    updateTimerDisplay();
                    updateMistakesDisplay();
                    updateHintsDisplay();
                    
                    // Обновляем активную кнопку сложности
                    updateDifficultyButtons();
                    
                    // Запускаем таймер с сохраненным временем
                    startTimerWithSavedTime(gameState.seconds);
                    
                    return true;
                    
                } catch (e) {
                    console.error('Failed to parse localStorage game:', e);
                    return false;
                }
            }
            return false;
        } else {
            // Для авторизованных пользователей загружаем с сервера
            try {
                const response = await fetch('api/get_game.php', {
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const result = await response.json();
                    console.log('📦 Get game result:', result);
                    
                    // ★★★ ОБНОВЛЕННАЯ ПРОВЕРКА ★★★
                    if (result.success && result.gameExists) {
                        // Есть сохраненная игра - загружаем данные
                        gameData = result;
                        gameState.wasSolved = result.wasSolved || false;
                        console.log('✅ Saved game loaded from server');
                    } else if (result.success && !result.gameExists) {
                        // Нет сохраненной игры - это нормально для новых пользователей
                        console.log('🆕 No saved game found - this is normal for new users');
                        return false;
                    } else {
                        // Ошибка загрузки
                        console.warn('⚠️ Server returned unexpected game format:', result);
                        return false;
                    }
                    
                    if (result.success) {
                        gameData = result;
                        
                        gameState.wasSolved = result.wasSolved || false;
                        
                        // Восстанавливаем данные игры
                        board = gameData.board || Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(EMPTY_CELL));
                        solution = gameData.solution || Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(EMPTY_CELL));
                        fixedCells = gameData.fixedCells || Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(false));
                        
                        // Обновляем сложность
                        if (gameData.difficulty && DIFFICULTY[gameData.difficulty.toUpperCase()]) {
                            gameState.currentDifficulty = DIFFICULTY[gameData.difficulty.toUpperCase()];
                            localStorage.setItem('currentDifficulty', gameState.currentDifficulty.name);
                            console.log('Difficulty loaded:', gameState.currentDifficulty.name);
                        }
                        
                        gameState.seconds = gameData.seconds || getTimeLimitForDifficulty();
                        
                        // Если время больше лимита, устанавливаем лимит
                        const timeLimit = getTimeLimitForDifficulty();
                        if (gameState.seconds > timeLimit) {
                            gameState.seconds = timeLimit;
                        }
                        
                        gameState.mistakes = gameData.mistakes || 0;
                        gameState.hintsUsed = gameData.hintsUsed || 0;
                        gameState.hintsLeft = gameData.hintsLeft || MAX_HINTS;
                        
                        // УЛУЧШЕННАЯ ЛОГИКА ОПРЕДЕЛЕНИЯ СОСТОЯНИЯ ДЛЯ АВТОРИЗОВАННЫХ ПОЛЬЗОВАТЕЛЕЙ
                        const gameLost = result.gameLost || false;
                        
                        // ЕСЛИ ИГРА БЫЛА ПРОИГРАНА ИЛИ РЕШЕНА - УСТАНАВЛИВАЕМ КОРРЕКТНЫЕ ФЛАГИ
                        if (gameLost || gameState.wasSolved) {
                            gameState.gameCompleted = true;
                            gameState.isGameOver = true;
                            gameState.gameStarted = true;
                            
                            // ЗАПОЛНЯЕМ ДОСКУ РЕШЕНИЕМ ЕСЛИ ОНА ЕЩЕ НЕ ЗАПОЛНЕНА
                            if (!isBoardFilled() && solution) {
                                console.log('Заполняем доску решением при загрузке завершенной игры');
                                for (let row = 0; row < BOARD_SIZE; row++) {
                                    for (let col = 0; col < BOARD_SIZE; col++) {
                                        board[row][col] = solution[row][col];
                                        fixedCells[row][col] = true;
                                    }
                                }
                            }
                            
                            // ОСТАНАВЛИВАЕМ ТАЙМЕР ДЛЯ ЗАВЕРШЕННОЙ ИГРЫ
                            stopTimer();
                            console.log('Таймер остановлен - игра завершена (проигрыш или решение)');
                        } 
                        // ЕСЛИ ИГРА АКТИВНА
                        else if (result.seconds > 0 || result.mistakes > 0 || result.hintsUsed > 0) {
                            gameState.gameStarted = true;
                            gameState.gameCompleted = false;
                            gameState.isGameOver = false;
                        }
                        // НОВАЯ ИГРА
                        else {
                            gameState.gameStarted = false;
                            gameState.gameCompleted = false;
                            gameState.isGameOver = false;
                        }
                        
                        // СОЗДАЕМ ДОСКУ ПЕРВОЙ!
                        createBoard();
                        
                        // ОБНОВЛЯЕМ ОТОБРАЖЕНИЕ ПОСЛЕ СОЗДАНИЯ ДОСКИ
                        updateBoardView();
                        updateTimerDisplay();
                        updateMistakesDisplay();
                        updateHintsDisplay();
                        
                        // Обновляем активную кнопку сложности
                        updateDifficultyButtons();
                        
                        // ЗАПУСКАЕМ ТАЙМЕР ТОЛЬКО ЕСЛИ ИГРА НЕ ЗАВЕРШЕНА
                        if (!gameState.gameCompleted && !gameState.isGameOver) {
                            startTimerWithSavedTime(gameState.seconds);
                        } else {
                            console.log('Таймер не запускается - игра завершена');
                        }
                        
                        // Если игра была решена или проиграна, блокируем кнопку "Решить"
                        if (gameState.wasSolved || gameState.isGameOver) {
                            if (solveBtn) {
                                solveBtn.disabled = true;
                                solveBtn.classList.add('disabled');
                            }
                        }
                        
                        console.log('Игра загружена с сервера для авторизованного пользователя:', {
                            gameStarted: gameState.gameStarted,
                            gameCompleted: gameState.gameCompleted, 
                            isGameOver: gameState.isGameOver,
                            wasSolved: gameState.wasSolved,
                            gameLost,
                            isBoardFilled: isBoardFilled()
                        });
                        return true;
                    }
                }
            } catch (fetchError) {
                console.error('Failed to fetch game from server:', fetchError);
            }
        }
        
        return false;
    } catch (e) {
        console.error('Failed to load game:', e);
        return false;
    }
}

// Функция для запуска таймера с сохраненным временем
function startTimerWithSavedTime(savedSeconds) {
    // ПРОВЕРЯЕМ, ЧТО ИГРА НЕ ЗАВЕРШЕНА ПЕРЕД ЗАПУСКОМ ТАЙМЕРА
    if (gameState.gameCompleted || gameState.isGameOver || gameState.wasSolved || isBoardFilled()) {
        console.log('Таймер не запускается - игра завершена или поле заполнено');
        return;
    }
    
    if (gameState.timerInterval) {
        clearInterval(gameState.timerInterval);
    }
    
    // Используем переданное сохраненное время (восходящий отсчет)
    gameState.seconds = savedSeconds || 0;
    
    // Обновляем отображение
    updateTimerDisplay();
    
    // Запускаем таймер, который продолжит с сохраненного времени
    gameState.timerInterval = setInterval(() => {
        gameState.seconds++;
        updateTimerDisplay();
        
        // Проверяем, не превышен ли лимит времени
        const timeLimit = getTimeLimitForDifficulty();
        if (gameState.seconds >= timeLimit) {
            handleTimeExpired();
            return;
        }
        
        saveGame();
    }, 1000);
}

    // Обновление кнопок сложности
    function updateDifficultyButtons() {
    const difficultyButtons = {
        'easy': easyBtn,
        'medium': mediumBtn,
        'hard': hardBtn
    };
    
    // Сбрасываем все кнопки
    Object.values(difficultyButtons).forEach(btn => {
        if (btn) btn.classList.remove('active');
    });
    
    // Активируем текущую сложность
    if (difficultyButtons[gameState.currentDifficulty.name]) {
        difficultyButtons[gameState.currentDifficulty.name].classList.add('active');
    }
}

    // ==================== Модальные окна ====================

// Функция для проверки и обновления достижений
async function checkAndUpdateAchievements() {
    // НЕ проверяем достижения, если игра не была завершена победой
    if (!gameState.gameCompleted || !gameState.isGameOver) {
        console.log('Достижения не проверяются - игра не завершена');
        return [];
    }
    
    const newAchievements = [];
    console.log('Проверка прогрессивных достижений...');
    
    // Проверяем каждое достижение
    achievements.forEach(achievement => {
        if (!achievement.unlocked) {
            let shouldUnlock = false;
            let newProgress = achievement.progress;
            
            switch(achievement.id) {
                case 'first_win':
                    // Привет, Плюк! уже обработана в checkAchievementsOnWin()
                    break;
                    
                case 'veteran':
                    newProgress = Math.min(stats.gamesWon, achievement.progressMax);
                    if (stats.gamesWon >= achievement.progressMax) {
                        shouldUnlock = true;
                        console.log('Получено достижение: Чатланин');
                    }
                    break;
                    
                case 'master':
                    newProgress = Math.min(stats.gamesWon, achievement.progressMax);
                    if (stats.gamesWon >= achievement.progressMax) {
                        shouldUnlock = true;
                        console.log('Получено достижение: Эцилопп');
                    }
                    break;
                    
                case 'professional':
                    newProgress = Math.min(stats.gamesWon, achievement.progressMax);
                    if (stats.gamesWon >= achievement.progressMax) {
                        shouldUnlock = true;
                        console.log('Получено достижение: Господин ПЖ');
                    }
                    break;
            }
            
            // Обновляем прогресс
            if (newProgress !== achievement.progress) {
                achievement.progress = newProgress;
            }
            
            if (shouldUnlock) {
                achievement.unlocked = true;
                achievement.unlockedAt = new Date().toISOString();
                newAchievements.push({...achievement});
            }
        }
    });
    
    // Сохраняем обновленные достижения
    if (newAchievements.length > 0) {
        await saveAchievementsLocally(achievements);
        renderAchievements(stats);
    }
    
    console.log('Найдено прогрессивных достижений:', newAchievements.length);
    
    // Обновляем статус если разблокированы новые достижения
    if (newAchievements.length > 0) {
        updateStatusDisplay();
    }
    
    return newAchievements;
}

// Функция для проверки достижений при победе
function checkAchievementsOnWin() {
    const newAchievements = [];
    console.log('Проверка достижений при победе...');
    
    // Без ошибок
    if (gameState.mistakes === 0) {
        const achievement = achievements.find(a => a.id === 'no_mistakes');
        if (achievement && !achievement.unlocked) {
            achievement.unlocked = true;
            achievement.progress = 1;
            achievement.unlockedAt = new Date().toISOString();
            newAchievements.push({...achievement});
            console.log('Получено достижение: Без ошибок');
        }
    }
    
    // Без подсказок
    if (gameState.hintsUsed === 0) {
        const achievement = achievements.find(a => a.id === 'no_hints');
        if (achievement && !achievement.unlocked) {
            achievement.unlocked = true;
            achievement.progress = 1;
            achievement.unlockedAt = new Date().toISOString();
            newAchievements.push({...achievement});
            console.log('Получено достижение: Без подсказок');
        }
    }
    
    // Привет, Плюк! (ВАЖНО: проверяем ДО обновления статистики)
    if (stats.gamesWon === 0) { // Проверяем ДО увеличения счетчика
        const achievement = achievements.find(a => a.id === 'first_win');
        if (achievement && !achievement.unlocked) {
            achievement.unlocked = true;
            achievement.progress = 1;
            achievement.unlockedAt = new Date().toISOString();
            newAchievements.push({...achievement});
            console.log('Получено достижение: Привет, Плюк!');
        }
    }
    
    // Последний выдох (без ошибок и без подсказок)
    if (gameState.mistakes === 0 && gameState.hintsUsed === 0) {
        const achievement = achievements.find(a => a.id === 'perfectionist');
        if (achievement && !achievement.unlocked) {
            achievement.unlocked = true;
            achievement.progress = 1;
            achievement.unlockedAt = new Date().toISOString();
            newAchievements.push({...achievement});
            console.log('Получено достижение: Последний выдох');
        }
    }
    
    // Также проверяем достижения, зависящие от времени
    const timeAchievements = ['speedster_easy', 'speedster_medium', 'speedster_hard'];
    timeAchievements.forEach(achievementId => {
        const achievement = achievements.find(a => a.id === achievementId);
        if (achievement && !achievement.unlocked) {
            // Проверяем, что текущая сложности соответствует достижению
            const difficultyPart = achievementId.split('_')[1];
            if (gameState.currentDifficulty.name === difficultyPart && gameState.seconds <= achievement.progressMax) {
                achievement.unlocked = true;
                achievement.progress = achievement.progressMax;
                achievement.unlockedAt = new Date().toISOString();
                newAchievements.push({...achievement});
                console.log('Получено достижение:', achievement.name);
            }
        }
    });
    
    console.log('Найдено новых достижений при победе:', newAchievements.length);
    return newAchievements;
}

// Обработчик для кнопки "Отмена" в модальном окне победы
function fillBoardWithSolution() {
    console.log('Заполняем доску решением...');
    for (let row = 0; row < BOARD_SIZE; row++) {
        for (let col = 0; col < BOARD_SIZE; col++) {
            board[row][col] = solution[row][col];
            fixedCells[row][col] = true; // Помечаем все ячейки как фиксированные
        }
    }
    
    // Обновляем отображение доски
    updateBoardView();
    
    // Отключаем взаимодействие с доской
    disableBoardInteraction();
    
    // БЛОКИРУЕМ КНОПКИ УПРАВЛЕНИЯ
    disableControlButtons();
    
    console.log('Доска заполнена решением, кнопки заблокированы');
}

function handleCancelWinModal() {
    // Закрываем модальное окно победы
    closeModal(winModal);
    
    // Заполняем все поля решенной головоломкой (как в функции "Решить")
    fillBoardWithSolution();
    
    // Отключаем взаимодействие с доской
    disableBoardInteraction();
    
    // ДЕАКТИВИРУЕМ КНОПКУ "РЕШИТЬ"
    if (solveBtn) {
        solveBtn.disabled = true;
        solveBtn.classList.add('disabled');
        localStorage.setItem('solveBtnDisabled', 'true');
    }
    
    // УСТАНАВЛИВАЕМ ФЛАГИ РЕШЕННОЙ ИГРЫ
    gameState.wasSolved = true;
    gameState.gameCompleted = true;
    gameState.isGameOver = true;
    
    // Сохраняем флаги в localStorage
    localStorage.setItem('pluk_sudoku_was_solved', 'true');
    localStorage.setItem('pluk_sudoku_win_shown', 'false'); // Сбрасываем флаг показа победы
    
    // Сохраняем состояние игры
    saveGame();
    
    console.log('Модальное окно победы закрыто, игра помечена как решенная');
}

// Функция для отключения взаимодействия с решенной доской
function disableBoardInteraction() {
    const cells = document.querySelectorAll('.cell');
    cells.forEach(cell => {
        cell.style.pointerEvents = 'none';
        cell.style.opacity = '0.7';
        cell.classList.add('solved');
    });
    
    // Отключаем кнопки цифр
    const numberButtons = document.querySelectorAll('.number-btn');
    numberButtons.forEach(btn => {
        btn.disabled = true;
        btn.style.opacity = '0.5';
    });
    
    // Отключаем кнопку "Решить"
    if (solveBtn) {
        solveBtn.disabled = true;
        solveBtn.classList.add('disabled');
    }
}

// ВКЛЮЧЕНИЕ ВЗАИМОДЕЙСТВИЯ С ДОСКОЙ
function enableBoardInteraction() {
    const cells = document.querySelectorAll('.cell');
    cells.forEach(cell => {
        cell.style.pointerEvents = '';
        cell.style.opacity = '';
        cell.classList.remove('solved');
    });
    
    // Включаем кнопки цифр
    const numberButtons = document.querySelectorAll('.number-btn');
    numberButtons.forEach(btn => {
        btn.disabled = false;
        btn.style.opacity = '';
    });
    
    console.log('Взаимодействие с доской восстановлено');
}

// Обработка подтверждения смены сложности
function confirmDifficultyChange() {
    if (gameState.pendingDifficultyChange) {
        handleGameLoss('change_difficulty');
        gameState.currentDifficulty = gameState.pendingDifficultyChange;
        updateDifficultyButtons();
        startNewGame();
        gameState.pendingDifficultyChange = null;
    }
    hideDifficultyWarningModal();
}

    // Закрытие модальных окон
    function closeModal(modal) {
        if (modal) modal.style.display = 'none';
    }

    // Инициализируем игру
    initGame();
});

// Вспомогательная функция для применения фильтра
function applyAchievementsFilter() {
    const activeFilter = document.querySelector('.filter-btn.active')?.dataset.filter || 'all';
    const achievementCards = document.querySelectorAll('.achievement-card');
    
    achievementCards.forEach(card => {
        switch (activeFilter) {
            case 'all':
                card.style.display = 'flex';
                break;
            case 'unlocked':
                card.style.display = card.classList.contains('unlocked') ? 'flex' : 'none';
                break;
            case 'locked':
                card.style.display = card.classList.contains('locked') ? 'flex' : 'none';
                break;
        }
    });
}

// Функция для отрисовки достижений
function renderAchievements(stats) {
    const achievementsContainer = document.getElementById('achievements-container');
    if (!achievementsContainer) return;
    
    // Сначала проверяем валидность достижений
    validateAchievements();
    
    // Для гостей без игр показываем сообщение
    if (typeof isGuest !== 'undefined' && isGuest && stats && stats.totalGames === 0) {
        achievementsContainer.innerHTML = `
            <div class="empty-achievements">
                <i class="fas fa-gamepad"></i>
                <h3>Достижения появятся здесь</h3>
                <p>Сыграйте свою первую игру, чтобы открыть достижения!</p>
            </div>
        `;
        return;
    }
    
    // Подсчитываем статистику
    const unlockedCount = achievements.filter(a => a.unlocked).length;
    const totalCount = achievements.length;
    const rareCount = achievements.filter(a => a.unlocked && a.rare).length;
    
    // Обновляем счетчики
    const achievementsCount = document.getElementById('achievements-count');
    const totalAchievements = document.getElementById('total-achievements');
    const rareAchievements = document.getElementById('rare-achievements');
    
    if (achievementsCount) achievementsCount.textContent = `${unlockedCount}/${totalCount}`;
    if (totalAchievements) totalAchievements.textContent = unlockedCount;
    if (rareAchievements) rareAchievements.textContent = rareCount;
    
    // Обновляем прогресс бар
    const progressPercent = Math.round((unlockedCount / totalCount) * 100);
    const progressFill = document.getElementById('achievements-progress');
    const progressPercentElem = document.getElementById('progress-percent');
    
    if (progressFill) progressFill.style.width = `${progressPercent}%`;
    if (progressPercentElem) progressPercentElem.textContent = `${progressPercent}%`;
    
    // Очищаем контейнер
    achievementsContainer.innerHTML = '';
    
    // Если нет достижений или они все заблокированы для новых игроков
    if (unlockedCount === 0 && stats && stats.totalGames === 0) {
        achievementsContainer.innerHTML = `
            <div class="empty-achievements">
                <i class="fas fa-gamepad"></i>
                <h3>Достижения появятся здесь</h3>
                <p>Сыграйте свою первую игру, чтобы открыть достижения!</p>
            </div>
        `;
        return;
    }
    
    // Рендерим достижения
    achievements.forEach(achievement => {
        const achievementCard = document.createElement('div');
        achievementCard.className = `achievement-card ${achievement.unlocked ? 'unlocked' : 'locked'}`;
        
        achievementCard.innerHTML = `
            <div class="achievement-icon" style="background: ${achievement.color};">
                <i class="fas ${achievement.icon}"></i>
            </div>
            <div class="achievement-info">
                <div class="achievement-name">${achievement.name}</div> 
                <div class="achievement-points">+${achievement.points} <i class="fa-solid fa-money-bill-1-wave"></i></div>
                <div class="achievement-desc">${achievement.description}</div>
                ${achievement.progress > 0 ? `
                <div class="achievement-progress">
                    <div class="progress-bar">
                        <div class="progress" style="width: ${(achievement.progress / achievement.progressMax) * 100}%"></div>
                    </div>
                    <span>${achievement.progress}/${achievement.progressMax}</span>
                </div>
                ` : ''}
            </div>
        `;
        
        achievementsContainer.appendChild(achievementCard);
    });
    
    // Применяем текущий фильтр
    applyAchievementsFilter();
}

// Обработчики для фильтрации достижений
function setupAchievementsFilter() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Убираем активный класс со всех кнопок
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Добавляем активный класс текущей кнопке
            button.classList.add('active');
            
            // Применяем фильтр
            applyAchievementsFilter();
        });
    });
}

// ==================== ФУНКЦИИ ДЛЯ ОБНОВЛЕНИЯ БАЛАНСА ====================

// ★★★ Функция для обновления баланса чатлов во всех элементах интерфейса ★★★
function updateBalanceDisplay() {
    console.log('🔄 Обновление отображения баланса. Текущий баланс:', stats.totalPoints);
    
    // Обновляем баланс в хедере под ником
    const userRatingElement = document.getElementById('user-rating');
    if (userRatingElement) {
        userRatingElement.textContent = stats.totalPoints || 0;
        console.log('✅ Баланс в хедере обновлен:', stats.totalPoints);
    }
    
    // Обновляем баланс в личном кабинете
    const balanceAmountElement = document.querySelector('.balance-amount');
    if (balanceAmountElement) {
        balanceAmountElement.innerHTML = `${stats.totalPoints || 0} <i class="fa-solid fa-money-bill-1-wave fa-beat"></i>`;
        console.log('✅ Баланс в личном кабинете обновлен:', stats.totalPoints);
    }
    
    // Обновляем элемент total-points в модальном окне статистики
    const totalPointsElement = document.getElementById('total-points');
    if (totalPointsElement) {
        totalPointsElement.textContent = stats.totalPoints || 0;
        console.log('✅ Баланс в статистике обновлен:', stats.totalPoints);
    }
}

// ★★★ Функция для принудительного обновления статистики с сервера ★★★
async function refreshStats() {
    try {
        console.log('🔄 Принудительное обновление статистики...');
        
        if (typeof isGuest !== 'undefined' && isGuest) {
            // Для гостей обновляем из localStorage
            const statsData = localStorage.getItem('pluk_sudoku_stats');
            if (statsData) {
                try {
                    stats = validateStats(JSON.parse(statsData));
                    updateBalanceDisplay();
                    console.log('✅ Статистика гостя обновлена:', stats.totalPoints);
                } catch (e) {
                    console.error('❌ Ошибка обновления статистики гостя:', e);
                }
            }
            return;
        }
        
        // Для авторизованных пользователей загружаем с сервера
        const response = await fetch('api/get_stats.php?_=' + Date.now(), { // Добавляем timestamp для избежания кэширования
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            const result = await response.json();
            
            if (result.success && result.stats) {
                stats = validateStats(result.stats);
                updateBalanceDisplay();
                console.log('✅ Статистика обновлена с сервера:', stats.totalPoints);
            } else {
                console.warn('⚠️ Сервер вернул неожиданный формат:', result);
            }
        } else {
            console.error('❌ Ошибка HTTP при обновлении статистики:', response.status);
        }
    } catch (error) {
        console.error('❌ Ошибка обновления статистики:', error);
        
        // Fallback: пробуем загрузить из localStorage
        try {
            const statsData = localStorage.getItem('pluk_sudoku_stats');
            if (statsData) {
                stats = validateStats(JSON.parse(statsData));
                updateBalanceDisplay();
                console.log('✅ Статистика обновлена из localStorage (fallback):', stats.totalPoints);
            }
        } catch (fallbackError) {
            console.error('❌ Fallback также не сработал:', fallbackError);
        }
    }
}

// ★★★ Функция для загрузки статистики ★★★
async function loadStats() {
    try {
        console.log('🔄 Starting loadStats...');
        let loadedStats = null;
        
        if (typeof isGuest === 'undefined') {
            console.log('isGuest не определен, загружаем из localStorage');
            const statsData = localStorage.getItem('pluk_sudoku_guest_stats');
            if (statsData) {
                try {
                    loadedStats = JSON.parse(statsData);
                    console.log('📊 Stats loaded from localStorage (isGuest undefined):', loadedStats);
                } catch (e) {
                    console.error('❌ Failed to parse localStorage stats:', e);
                    loadedStats = getDefaultStats();
                }
            } else {
                loadedStats = getDefaultStats();
                console.log('📊 Using default stats (no localStorage data)');
            }
        } else if (isGuest) {
            // Для гостей загружаем из localStorage с другим ключом
            const statsData = localStorage.getItem('pluk_sudoku_guest_stats');
            
            if (statsData) {
                try {
                    loadedStats = JSON.parse(statsData);
                    console.log('📊 Stats loaded from localStorage for guest:', loadedStats);
                } catch (e) {
                    console.error('❌ Failed to parse localStorage stats:', e);
                    loadedStats = getDefaultStats();
                }
            } else {
                loadedStats = getDefaultStats();
                console.log('📊 Using default stats for guest (no localStorage data)');
            }
        } else {
            // Для авторизованных пользователей загружаем с сервера
            try {
                console.log('🔄 Загрузка статистики с сервера...');
                const response = await fetch('api/get_stats.php?_=' + Date.now(), {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                
                console.log('📊 Get stats response status:', response.status);
                
                if (response.ok) {
                    const result = await response.json();
                    console.log('📊 Parsed result:', result);
                    
                    if (result.success && result.stats) {
                        loadedStats = result.stats;
                        console.log('✅ Статистика загружена с сервера:', loadedStats);
                        
                        // Сохраняем в localStorage для кэширования
                        localStorage.setItem('pluk_sudoku_stats', JSON.stringify(loadedStats));
                    } else if (result.stats) {
                        // Если stats есть, но success=false, все равно используем данные
                        loadedStats = result.stats;
                        console.log('⚠️ Using stats despite success=false:', loadedStats);
                        localStorage.setItem('pluk_sudoku_stats', JSON.stringify(loadedStats));
                    } else {
                        console.warn('⚠️ Сервер вернул неожиданный формат статистики:', result);
                        throw new Error('Unexpected server response format');
                    }
                } else {
                    console.error('❌ Ошибка HTTP при загрузке статистики:', response.status);
                    throw new Error('HTTP error loading stats');
                }
            } catch (fetchError) {
                console.error('❌ Failed to fetch stats from server:', fetchError);
                throw new Error('Network error loading stats');
            }
        }
        
        // ★★★ ВАЛИДИРУЕМ ДАННЫЕ ПЕРЕД ИСПОЛЬЗОВАНИЕМ ★★★
        stats = validateStats(loadedStats);
        console.log('📊 Final validated stats:', stats);
        
        // ★★★ ОБНОВЛЯЕМ БАЛАНС ПОСЛЕ ЗАГРУЗКИ СТАТИСТИКИ ★★★
        updateBalanceDisplay();
        
        console.log('✅ loadStats completed successfully');
        return true;
        
    } catch (e) {
        console.error('❌ Critical error in loadStats:', e);
        stats = validateStats(getDefaultStats());
        
        // ★★★ ОБНОВЛЯЕМ БАЛАНС ДАЖЕ ПРИ ОШИБКЕ ★★★
        updateBalanceDisplay();
        
        return false;
    }
}

// ==================== ГЛОБАЛЬНЫЕ DOM ЭЛЕМЕНТЫ ====================
// Игровые элементы
const boardElement = document.getElementById('board');
const timerElement = document.getElementById('timer');
const mistakesElement = document.getElementById('mistakes');
const hintsCounterElement = document.getElementById('hints-counter');
const hintBadgeElement = document.getElementById('hint-badge');

// Кнопки управления
const newGameBtn = document.getElementById('new-game-btn');
const hintBtn = document.getElementById('hint-btn');
const checkBtn = document.getElementById('check-btn');
const solveBtn = document.getElementById('solve-btn');
const easyBtn = document.getElementById('easy-btn');
const mediumBtn = document.getElementById('medium-btn');
const hardBtn = document.getElementById('hard-btn');
const numberBtns = document.querySelectorAll('.number-btn');

// Модальные окна
const statsModal = document.getElementById('stats-modal');
const achievementsModal = document.getElementById('achievements-modal');
const winModal = document.getElementById('win-modal');
const newGameConfirmModal = document.getElementById('new-game-confirm-modal');
const leaderboardModal = document.getElementById('leaderboard-modal');

// ==================== ФУНКЦИИ ОБНОВЛЕНИЯ ИНТЕРФЕЙСА ====================

// Функция для обновления отображения подсказок
function updateHintsDisplay() {
    const hintsText = `${gameState.hintsUsed}/${MAX_HINTS}`;
    if (hintsCounterElement) hintsCounterElement.textContent = hintsText;
    if (hintBadgeElement) hintBadgeElement.textContent = hintsText;
}

// Функция для обновления отображения ошибок
function updateMistakesDisplay() {
    const mistakesText = `${gameState.mistakes}/${MAX_MISTAKES}`;
    if (mistakesElement) {
        mistakesElement.textContent = mistakesText;
        console.log('Обновлено отображение ошибок:', mistakesText); // Для отладки
    }
}

// Функция для обновления только элементов баланса (без рекурсии)
function updateBalanceElementsOnly() {
    // Обновляем баланс в хедере под ником
    const userRatingElement = document.getElementById('user-rating');
    if (userRatingElement) {
        userRatingElement.textContent = stats.totalPoints || 0;
    }
    
    // Обновляем баланс в личном кабинете
    const balanceAmountElement = document.querySelector('.balance-amount');
    if (balanceAmountElement) {
        balanceAmountElement.innerHTML = `${stats.totalPoints || 0} <i class="fa-solid fa-money-bill-1-wave fa-beat"></i>`;
    }
    
    // Обновляем элемент total-points в модальном окне статистики
    const totalPointsElement = document.getElementById('total-points');
    if (totalPointsElement) {
        totalPointsElement.textContent = stats.totalPoints || 0;
    }
}

// ★★★ Функция для обновления отображения статистики ★★★
function updateStatsDisplay() {
    console.log('🔄 Updating stats display:', stats);
    
    // Обновляем основную панель статистики в игре
    if (mistakesElement) {
        mistakesElement.textContent = gameState.mistakes;
        console.log('Обновлено отображение ошибок:', gameState.mistakes);
    }
    
    updateHintsDisplay();
    
    // ★★★ ОБНОВЛЯЕМ БАЛАНС ВО ВСЕХ МЕСТАХ (без рекурсии) ★★★
    updateBalanceElementsOnly();
    
    // Обновляем модальное окно статистики
    const totalGames = document.getElementById('total-games');
    const gamesWon = document.getElementById('games-won');
    const winRate = document.getElementById('win-rate');
    const totalPoints = document.getElementById('total-points');
    const bestTimeEasy = document.getElementById('best-time-easy');
    const bestTimeMedium = document.getElementById('best-time-medium');
    const bestTimeHard = document.getElementById('best-time-hard');
    
    if (totalGames) totalGames.textContent = stats.totalGames || 0;
    if (gamesWon) gamesWon.textContent = stats.gamesWon || 0;
    if (totalPoints) totalPoints.textContent = stats.totalPoints || 0;
    
    // Расчет процента побед
    const winRateValue = stats.totalGames > 0 ? Math.round((stats.gamesWon / stats.totalGames) * 100) : 0;
    if (winRate) winRate.textContent = `${winRateValue}%`;
    
    // Форматирование времени для лучших результатов
    if (bestTimeEasy) bestTimeEasy.textContent = formatTime(stats.bestTimes?.easy);
    if (bestTimeMedium) bestTimeMedium.textContent = formatTime(stats.bestTimes?.medium);
    if (bestTimeHard) bestTimeHard.textContent = formatTime(stats.bestTimes?.hard);
    
    console.log('✅ Stats display updated');
}

// ==================== ФУНКЦИИ ДЛЯ МОДАЛЬНЫХ ОКОН ====================

// Показ модального окна статистики
async function showStatsModal() {
    try {
        console.log('🔄 Opening stats modal...');
        
        // Загружаем свежие данные статистики
        const success = await loadStats();
        
        if (!success) {
            console.warn('⚠️ loadStats returned false, but continuing with available data');
        }
        
        updateStatsDisplay();
        
        const statsModal = document.getElementById('stats-modal');
        if (statsModal) {
            statsModal.style.display = 'flex';
            
            // Добавляем информацию о режиме (гость/авторизованный)
            const modalTitle = statsModal.querySelector('.modal-title');
            const modalBody = statsModal.querySelector('.modal-body');
            
            // Удаляем старую информацию о гостевом режиме если есть
            const oldGuestInfo = modalBody.querySelector('.guest-info, .auth-info');
            if (oldGuestInfo) oldGuestInfo.remove();
            
            if (typeof isGuest !== 'undefined') {
                if (isGuest) {
                    modalTitle.innerHTML = '<i class="fas fa-chart-bar"></i> Статистика (Гостевой режим)';
                    
                    // Добавляем информацию о гостевом режиме
                    const guestInfoDiv = document.createElement('div');
                    guestInfoDiv.className = 'guest-info';
                    guestInfoDiv.style.marginTop = '20px';
                    guestInfoDiv.style.padding = '15px';
                    guestInfoDiv.style.borderRadius = 'var(--radius-md)';
                    guestInfoDiv.innerHTML = `
                        <i class="fas fa-info-circle"></i>
                        <strong>Гостевой режим</strong>
                        <p style="margin: 10px 0 0; font-size: 14px;">
                            Статистика и достижения не сохраняются. 
                            <strong>Для сохранения прогресса Войдите или Зарегистрируйтесь.</strong>
                        </p>
                    `;
                    modalBody.appendChild(guestInfoDiv);
                } else {
                    modalTitle.innerHTML = '<i class="fas fa-chart-bar"></i> Статистика';
                    
                    // ПРОВЕРЯЕМ, ЕСТЬ ЛИ РЕАЛЬНЫЕ ПРОБЛЕМЫ С СЕРВЕРОМ
                    const statsData = localStorage.getItem('pluk_sudoku_stats');
                    const serverStats = stats; // текущие данные из сервера
                    
                    // Показываем предупреждение ТОЛЬКО если:
                    // 1. Есть локальные данные И
                    // 2. Серверные данные пустые или не соответствуют (признак ошибки)
                    if (statsData && (!serverStats || serverStats.totalGames === 0)) {
                        try {
                            const localStats = JSON.parse(statsData);
                            // ДОПОЛНИТЕЛЬНАЯ ПРОВЕРКА: показываем только если локальные данные существенно отличаются
                            // или если сервер вернул данные по умолчанию (признак ошибки)
                            if (localStats.totalGames > 0 && (!serverStats || serverStats.totalGames === 0)) {
                                const authInfoDiv = document.createElement('div');
                                authInfoDiv.className = 'auth-info';
                                authInfoDiv.style.marginTop = '20px';
                                authInfoDiv.style.padding = '15px';
                                authInfoDiv.style.backgroundColor = '#e8f4fd';
                                authInfoDiv.style.borderRadius = 'var(--radius-md)';
                                authInfoDiv.style.border = '1px solid #b6d7e8';
                                authInfoDiv.innerHTML = `
                                    <i class="fas fa-sync-alt"></i>
                                    <strong>Используются локальные данные</strong>
                                    <p style="margin: 10px 0 0; font-size: 14px;">
                                        Не удалось подключиться к серверу. Показаны данные из кэша браузера.
                                        <br><small>Обновите страницу или проверьте подключение к интернету.</small>
                                    </p>
                                `;
                                modalBody.appendChild(authInfoDiv);
                            }
                        } catch (e) {
                            console.error('Error checking local stats:', e);
                        }
                    }
                }
            }
        }
    } catch (error) {
        console.error('❌ Error showing stats modal:', error);
        // Даже при ошибке показываем модальное окно с доступными данными
        updateStatsDisplay();
        const statsModal = document.getElementById('stats-modal');
        if (statsModal) {
            statsModal.style.display = 'flex';
        }
    }
}

// Показ модального окна достижений
function showAchievementsModal() {
    // Проверяем, есть ли завершенные игры
    const hasCompletedGames = stats && stats.totalGames > 0;
    
    if (!hasCompletedGames && typeof isGuest !== 'undefined' && isGuest) {
        showNotification('Сначала сыграйте хотя бы одну игру!', 'info');
        return;
    }
    
    renderAchievements(stats); // Передаем stats
    const modal = document.getElementById('achievements-modal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.classList.add('modal-open');
    }
}

// Функция для скрытия модального окна достижений
function hideAchievementsModal() {
    const modal = document.getElementById('achievements-modal');
    if (modal) {
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
    }
}

// Показ модального окна инструкции
function showInstructionsModal() {
    const instructionsModal = document.getElementById('instructions-modal');
    if (instructionsModal) {
        instructionsModal.style.display = 'flex';
        playClickSound();
    }
}

// Функция для скрытия модального окна инструкции
function hideInstructionsModal() {
    const instructionsModal = document.getElementById('instructions-modal');
    if (instructionsModal) {
        instructionsModal.style.display = 'none';
        playClickSound();
    }
}

// Показ таблицы лидеров
function showLeaderboardModal() {
    const leaderboardModal = document.getElementById('leaderboard-modal');
    if (leaderboardModal) {
        leaderboardModal.style.display = 'flex';
        
        setTimeout(() => {
            initLeaderboardSearch();
            refreshLeaderboard();
            
            if (window.user && window.leaderboardData) {
                updateCurrentUserSection(window.user, window.leaderboardData);
                
                // ★★★ ДОБАВЛЕНО: Автоматическая прокрутка к пользователю при открытии ★★★
                setTimeout(() => {
                    const searchInput = document.getElementById('leaderboard-search');
                    if (searchInput && !searchInput.value.trim()) {
                        const userRows = document.querySelectorAll('.leaderboard-table tr.current-user');
                        if (userRows.length > 0) {
                            userRows[0].scrollIntoView({ 
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }
                    }
                }, 800);
            }
        }, 100);
    }
}

// ==================== КОНЕЦ ФУНКЦИЙ ДЛЯ МОДАЛЬНЫХ ОКОН ====================

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {

    // Инициализируем originalHref из кнопки входа если существует
    const loginBtn = document.querySelector('a[href="login.php"]');
    if (loginBtn) {
        originalHref = loginBtn.getAttribute('href') || originalHref;
    }
    
    // Вызываем функцию блокировки логотипа с проверкой
    if (typeof isGuest !== 'undefined') {
        blockLogoForAuthUsers();
    }
    
    // Добавляем обработчики для модального окна
    const achievementsBtn = document.getElementById('achievements-btn');
    const closeAchievementsBtn = document.getElementById('close-achievements-modal');
    
    if (achievementsBtn) {
        achievementsBtn.addEventListener('click', showAchievementsModal);
    }
    
    if (closeAchievementsBtn) {
        closeAchievementsBtn.addEventListener('click', hideAchievementsModal);
    }
    
    // Закрытие по клику вне области
    const achievementsModal = document.getElementById('achievements-modal');
    if (achievementsModal) {
        achievementsModal.addEventListener('click', function(e) {
            if (e.target === achievementsModal) {
                hideAchievementsModal();
            }
        });
    }
    
    // Настройка фильтрации
    setupAchievementsFilter();
    
    // Добавляем дополнительные свойства к достижениям
    achievements = achievements.map(achievement => {
        // Определяем цвета для разных типов достижений
        let color, secondaryColor, rare = false;
        
        switch(achievement.id) {
            case 'first_win':
                color = '#FFB800'; secondaryColor = '#254BCC'; break;
            case 'no_mistakes':
                color = '#fd4d00'; secondaryColor = '#b3832a'; break;
            case 'no_hints':
                color = '#c9a5df'; secondaryColor = '#254BCC'; break;
            case 'speedster_easy':
                color = '#52ff30'; secondaryColor = '#00ff51'; break;
            case 'speedster_medium':
                color = '#af52de'; secondaryColor = '#8E3DBD'; break;
            case 'speedster_hard':
                color = '#FFD700'; secondaryColor = '#FFB800'; break;
            case 'perfectionist':
                color = '#b2eaf5'; secondaryColor = '#CC2444'; break;
            case 'veteran':
                color = '#52ff30'; secondaryColor = '#CC7700'; rare = true; break;
            case 'master':
                color = '#af52de'; secondaryColor = '#CC7700'; rare = true; break;
            case 'professional':
                color = '#30dbff'; secondaryColor = '#CC7700'; rare = true; break;
            default:
                color = '#8E8E93'; secondaryColor = '#6C6C70';
        }
        
        return {
            ...achievement,
            color,
            secondaryColor,
            rare,
            progress: achievement.progress || 0,
            progressMax: achievement.progressMax || 0,
            new: false
        };
    });
    
    // Проверяем наличие новых достижений при загрузке страницы
    const newAchievements = loadNewAchievements();
    if (newAchievements && newAchievements.length > 0) {
        // Показываем модальное окно победы с сохраненными достижениями
        setTimeout(() => {
            showWinModal(0, 0, 0, newAchievements);
        }, 1000);
    }
    
    // Инициализация поиска в таблице лидеров
    setTimeout(initLeaderboardSearch, 1000);
});

// ==================== ОПТИМИЗАЦИЯ ЗАГРУЗКИ СТРАНИЦЫ ====================

// Оптимизированная функция инициализации игры
async function optimizedInitGame() {
    try {
        console.log('Оптимизированная инициализация игры...');
        
        // Быстрая инициализация критически важных элементов
        await quickInitialize();
        
        // Отложенная инициализация тяжелых компонентов
        setTimeout(() => {
            initializeHeavyComponents();
        }, 500);
        
    } catch (error) {
        console.error('Ошибка оптимизированной инициализации:', error);
        // Fallback к стандартной инициализации
        initGame();
    }
}

// Быстрая инициализация критических элементов
async function quickInitialize() {
    // 1. Сначала инициализируем header
    initializeHeader();
    
    // 2. Быстрая инициализация игрового поля
    createBoard();
    
    // 3. Загружаем только необходимые данные для отображения
    await loadEssentialData();
    
    // 4. Обновляем отображение
    updateBoardView();
    updateStatsDisplay();
}

function initializeHeader() {
    // Гарантируем, что header полностью инициализирован
    const header = document.querySelector('.header');
    if (header) {
        header.style.visibility = 'visible';
        header.style.opacity = '1';
    }
}

async function loadEssentialData() {
    // Загружаем только самое необходимое для первичного отображения
    const promises = [];
    
    if (typeof isGuest !== 'undefined') {
        if (isGuest) {
            // Для гостей - быстрая загрузка из localStorage
            const statsData = localStorage.getItem('pluk_sudoku_stats');
            if (statsData) {
                try {
                    stats = validateStats(JSON.parse(statsData));
                } catch (e) {
                    stats = getDefaultStats();
                }
            }
        } else {
            // Для авторизованных пользователей - минимальный запрос
            promises.push(loadStats());
        }
    }
    
    // Загружаем игру
    promises.push(loadGame());
    
    await Promise.allSettled(promises);
}

function initializeHeavyComponents() {
    // Инициализация тяжелых компонентов после первичного показа
    setupEventListeners();
    loadAchievements();
    initializeBackgroundManager();
    
    // Инициализация таблицы лидеров (может быть отложена)
    setTimeout(() => {
        if (typeof initLeaderboardSearch === 'function') {
            initLeaderboardSearch();
        }
    }, 1000);
}

// ==================== ПРОСТОЕ РЕШЕНИЕ ДЛЯ ПРЕДОТВРАЩЕНИЯ ДЕРГАНИЯ ====================

// Сразу скрываем контент до загрузки
document.addEventListener('DOMContentLoaded', function() {
    // Даем время на применение CSS
    setTimeout(() => {
        document.body.classList.remove('loading');
        document.body.classList.add('loaded');
    }, 100);
});

// ==================== КОНЕЦ РЕШЕНИЯ ====================

// ★★★ СОХРАНЕНИЕ ПРИ ПЕРЕЗАГРУЗКЕ СТРАНИЦЫ ★★★
window.addEventListener('beforeunload', function(e) {
    console.log('🔄 Страница обновляется/закрывается - сохраняем данные...');
    
    // ★★★ ПРОВЕРКА СУЩЕСТВОВАНИЯ ФУНКЦИЙ ★★★
    if (typeof saveGame === 'function' && gameState.gameStarted) {
        saveGame();
        console.log('💾 Игра сохранена перед обновлением');
    } else {
        // Убираем warning, так как это нормально при быстром обновлении
        console.log('ℹ️ Игра не сохранена (функция еще не инициализирована)');
    }
    
    if (stats && stats.totalPoints > 0) {
        // Сохраняем в localStorage как fallback
        localStorage.setItem('pluk_sudoku_stats', JSON.stringify(validateStats(stats)));
        console.log('✅ Статистика сохранена в localStorage');
    }
});

// ★★★ СОХРАНЕНИЕ ПРИ ВИДИМОСТИ СТРАНИЦЫ ★★★
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'hidden') {
        console.log('👋 Пользователь уходит со страницы - сохраняем данные...');
        
        if (typeof saveGame === 'function' && gameState.gameStarted) {
            saveGame();
            console.log('💾 Игра сохранена при смене вкладки');
        }
        
        if (stats && stats.totalPoints > 0 && typeof forceSaveStats === 'function') {
            forceSaveStats().then(success => {
                if (success) {
                    console.log('✅ Статистика сохранена при смене вкладки');
                }
            });
        }
    }
});

// ==================== ТУРНИРНАЯ СИСТЕМА ====================

class TournamentManager {
    constructor() {
        this.isConnected = true;
        this.currentTournament = null;
        this.pollingInterval = null;
        this.heartbeatInterval = null;
        this.lastUpdateCheck = null;
        this.lastHeartbeat = null;
        this.retryCount = 0;
        this.maxRetries = 5;
        this.baseDelay = 1000;
        this.maxDelay = 300000;
        
        // Статистика heartbeat
        this.heartbeatStats = {
            totalChecks: 0,
            successfulChecks: 0,
            failedChecks: 0,
            lastCheckTime: null,
            averageResponseTime: 0
        };
        
        this.registeredTournaments = new Set(
            JSON.parse(localStorage.getItem('registeredTournaments') || '[]')
        );
    }

    // Сохраняем зарегистрированные турниры в localStorage
    saveRegisteredTournaments() {
        localStorage.setItem('registeredTournaments', 
            JSON.stringify(Array.from(this.registeredTournaments))
        );
    }

    // Имитация WebSocket через HTTP polling
    connect() {
        console.log('🔄 Турнирная система инициализирована (HTTP режим)');
        this.isConnected = true;
        
        this.startPolling();
        this.startHeartbeat();
    }

    // Запуск heartbeat для проверки соединения
    startHeartbeat() {
        console.log('💓 Запуск heartbeat проверки соединения...');
        
        this.performHeartbeatCheck();
        
        this.heartbeatInterval = setInterval(() => {
            this.performHeartbeatCheck();
        }, 60000); // 1 минута
        
        console.log('✅ Heartbeat запущен с интервалом 1 минута');
    }

    // Остановка heartbeat
    stopHeartbeat() {
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
            this.heartbeatInterval = null;
            console.log('✅ Heartbeat остановлен');
        }
    }

    // Выполнение проверки heartbeat
    async performHeartbeatCheck() {
        const checkStartTime = Date.now();
        
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 5000);
            
            const response = await fetch('websocket_proxy.php?action=get_tournaments&heartbeat=1', {
                method: 'GET',
                signal: controller.signal,
                credentials: 'include'
            });
            
            clearTimeout(timeoutId);
            
            const responseTime = Date.now() - checkStartTime;
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.handleHeartbeatSuccess(responseTime);
            } else {
                throw new Error(`API Error: ${data.message}`);
            }
            
        } catch (error) {
            this.handleHeartbeatFailure(error, Date.now() - checkStartTime);
        }
    }

    // Обработка успешного heartbeat
    handleHeartbeatSuccess(responseTime) {
        this.isConnected = true;
        this.lastHeartbeat = Date.now();
        this.retryCount = 0;
        
        this.heartbeatStats.totalChecks++;
        this.heartbeatStats.successfulChecks++;
        this.heartbeatStats.lastCheckTime = new Date().toISOString();
        
        this.heartbeatStats.averageResponseTime = 
            (this.heartbeatStats.averageResponseTime * (this.heartbeatStats.successfulChecks - 1) + responseTime) / 
            this.heartbeatStats.successfulChecks;
        
        if (this.heartbeatStats.failedChecks > 0) {
            console.log('✅ Соединение восстановлено!');
            showNotification('Соединение с сервером восстановлено', 'success');
        }
        
        if (this.heartbeatStats.successfulChecks % 10 === 0) {
            console.log(`💓 Heartbeat успешен (${this.heartbeatStats.successfulChecks}): ${responseTime}мс`);
        }
    }

    // Обработка неудачного heartbeat
    handleHeartbeatFailure(error, responseTime) {
        this.isConnected = false;
        this.heartbeatStats.totalChecks++;
        this.heartbeatStats.failedChecks++;
        this.heartbeatStats.lastCheckTime = new Date().toISOString();
        
        const errorType = error.name === 'AbortError' ? 'Таймаут' : 'Ошибка сети';
        
        console.error(`💔 Heartbeat неудачен (${errorType}):`, error.message);
        
        if (this.heartbeatStats.failedChecks === 1) {
            showNotification('Потеряно соединение с сервером турниров', 'warning');
        }
        
        if (this.heartbeatStats.failedChecks >= 3) {
            this.handleConnectionProblems();
        }
    }

    // Обработка проблем с соединением
    handleConnectionProblems() {
        console.warn(`⚠️ Обнаружены проблемы с соединением: ${this.heartbeatStats.failedChecks} сбоев подряд`);
        
        this.stopPolling();
        
        if (this.heartbeatStats.failedChecks === 3) {
            showNotification('Проблемы с подключением к турнирам. Пытаемся восстановить...', 'error');
        }
        
        this.attemptConnectionRecovery();
    }

    // Попытка восстановления соединения
    async attemptConnectionRecovery() {
        console.log('🔄 Попытка восстановления соединения...');
        
        try {
            const response = await fetch('api/get_tournaments.php?reconnect=1', {
                signal: AbortSignal.timeout(10000)
            });
            
            if (response.ok) {
                console.log('✅ Соединение восстановлено через основной API');
                this.isConnected = true;
                this.retryCount = 0;
                this.heartbeatStats.failedChecks = 0;
                
                this.startPolling();
                
                showNotification('Соединение восстановлено!', 'success');
                return true;
            }
        } catch (error) {
            console.error('❌ Не удалось восстановить соединение:', error);
        }
        
        return false;
    }

    // Метод для расчета экспоненциальной задержки
    calculateExponentialBackoff(retryCount) {
        const delay = Math.min(
            this.baseDelay * Math.pow(2, retryCount),
            this.maxDelay
        );
        
        const jitter = delay * 0.1 * Math.random();
        const finalDelay = delay + jitter;
        
        console.log(`📊 Экспоненциальная задержка: попытка ${retryCount + 1}, задержка: ${Math.round(finalDelay / 1000)} сек`);
        
        return finalDelay;
    }

    async startPolling() {
        console.log('🔄 Запуск HTTP polling для турниров...');
        
        if (!this.isConnected) {
            console.warn('⚠️ Соединение недоступно, откладываем запуск polling');
            setTimeout(() => this.startPolling(), 30000);
            return;
        }
        
        await this.checkTournamentUpdates();
        
        this.pollingInterval = setInterval(async () => {
            if (!this.isConnected) {
                console.warn('⚠️ Пропускаем polling - соединение недоступно');
                return;
            }
            
            try {
                await this.checkTournamentUpdates();
                this.retryCount = 0;
                console.log('✅ Polling успешно выполнен, счетчик попыток сброшен');
            } catch (error) {
                console.error('❌ Ошибка в polling:', error);
                this.retryCount++;
                
                if (this.retryCount >= this.maxRetries) {
                    console.warn(`⚠️ Превышено максимальное количество попыток (${this.maxRetries}), остановка polling`);
                    this.stopPolling();
                    
                    const finalDelay = this.calculateExponentialBackoff(this.maxRetries);
                    console.log(`⏰ Финальная попытка переподключения через ${Math.round(finalDelay / 1000)} сек`);
                    
                    setTimeout(() => {
                        console.log('🔄 Финальная попытка переподключения...');
                        this.retryCount = 0;
                        this.startPolling();
                    }, finalDelay);
                } else {
                    const backoffDelay = this.calculateExponentialBackoff(this.retryCount);
                    
                    console.log(`⏰ Повторная попытка ${this.retryCount}/${this.maxRetries} через ${Math.round(backoffDelay / 1000)} сек`);
                    
                    this.stopPolling();
                    
                    setTimeout(() => {
                        console.log(`🔄 Попытка переподключения ${this.retryCount}/${this.maxRetries}...`);
                        this.startPolling();
                    }, backoffDelay);
                }
            }
        }, 300000); // 5 минут
        
        console.log('✅ HTTP polling запущен с интервалом 5 минут');
    }

    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
            console.log('✅ HTTP polling остановлен');
        }
    }

    async checkTournamentUpdates() {
        if (!this.isConnected) {
            console.warn('⚠️ Пропускаем проверку турниров - соединение недоступно');
            throw new Error('Соединение недоступно');
        }
        
        const now = Date.now();
        if (this.lastUpdateCheck && (now - this.lastUpdateCheck) < 120000) {
            console.log('⏰ Слишком частая проверка обновлений турниров, пропускаем...');
            return;
        }
        this.lastUpdateCheck = now;
        
        const tournamentsToCheck = [this.currentTournament, ...this.registeredTournaments]
            .filter(id => id !== null)
            .filter((value, index, self) => self.indexOf(value) === index);
        
        if (tournamentsToCheck.length === 0) {
            console.log('ℹ️ Нет турниров для проверки');
            return;
        }
        
        console.log('🔍 Проверка обновлений для турниров:', tournamentsToCheck.length);
        
        let successfulChecks = 0;
        let failedChecks = 0;
        
        for (const tournamentId of tournamentsToCheck) {
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 10000);
                
                const response = await fetch(`websocket_proxy.php?action=get_tournament_status&tournament_id=${tournamentId}&_=${Date.now()}`, {
                    credentials: 'include',
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    this.onTournamentUpdate(data);
                    successfulChecks++;
                } else {
                    throw new Error(`API error: ${data.message}`);
                }
            } catch (error) {
                if (error.name === 'AbortError') {
                    console.error(`❌ Таймаут проверки турнира ${tournamentId}`);
                } else {
                    console.error(`❌ Ошибка проверки турнира ${tournamentId}:`, error.message);
                }
                failedChecks++;
                continue;
            }
        }
        
        if (failedChecks > 0 && successfulChecks === 0) {
            throw new Error(`Все проверки турниров завершились ошибкой: ${failedChecks} неудачных, 0 успешных`);
        }
        
        console.log(`✅ Проверка турниров завершена: ${successfulChecks} успешных, ${failedChecks} неудачных`);
    }

    // Метод для отдельных запросов с экспоненциальной задержкой
    async fetchWithRetry(url, options = {}, maxRetries = 3) {
        let lastError;
        
        for (let attempt = 0; attempt < maxRetries; attempt++) {
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 10000);
                
                const response = await fetch(url, {
                    ...options,
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    console.log(`✅ Запрос успешен с попытки ${attempt + 1}`);
                    return data;
                } else {
                    throw new Error(`API Error: ${data.message}`);
                }
                
            } catch (error) {
                lastError = error;
                console.warn(`⚠️ Попытка ${attempt + 1}/${maxRetries} неудачна:`, error.message);
                
                if (attempt < maxRetries - 1) {
                    const backoffDelay = this.calculateExponentialBackoff(attempt);
                    console.log(`⏰ Повторная попытка через ${Math.round(backoffDelay / 1000)} сек`);
                    await new Promise(resolve => setTimeout(resolve, backoffDelay));
                }
            }
        }
        
        throw lastError;
    }

    async joinTournament(tournamentId) {
        if (!window.isLoggedIn) {
            showNotification('Для участия в турнирах необходимо войти в систему', 'warning');
            return false;
        }

        try {
            const data = await this.fetchWithRetry(
                `websocket_proxy.php?action=join_tournament&tournament_id=${tournamentId}`,
                {},
                3
            );
            
            this.currentTournament = tournamentId;
            this.registeredTournaments.add(tournamentId.toString());
            this.saveRegisteredTournaments();
            
            showNotification(data.message, 'success');
            console.log('✅ Турнир добавлен в зарегистрированные:', tournamentId);
            
            return true;
            
        } catch (error) {
            console.error('❌ Ошибка регистрации в турнире после всех попыток:', error);
            showNotification('Ошибка соединения после нескольких попыток', 'error');
            return false;
        }
    }

    onTournamentUpdate(data) {
        console.log('Обновление турнира:', data);
        
        if (data.status === 'completed' || data.status === 'cancelled') {
            this.registeredTournaments.delete(data.tournament_id);
            this.saveRegisteredTournaments();
            
            if (this.currentTournament === data.tournament_id) {
                this.currentTournament = null;
            }
            
            if (typeof safeLoadTournaments === 'function') {
                safeLoadTournaments();
            }
        }
    }

    leaveTournament(tournamentId = null) {
        const idToLeave = tournamentId || this.currentTournament;
        
        if (idToLeave) {
            this.registeredTournaments.delete(idToLeave);
            this.saveRegisteredTournaments();
            if (this.currentTournament === idToLeave) {
                this.currentTournament = null;
            }
        }
        
        if (this.registeredTournaments.size === 0 && !this.currentTournament) {
            if (this.pollingInterval) {
                clearInterval(this.pollingInterval);
                this.pollingInterval = null;
            }
        }
    }

    // Получить список зарегистрированных турниров
    getRegisteredTournaments() {
        return Array.from(this.registeredTournaments);
    }

    // Проверить, зарегистрирован ли пользователь в турнире
    isRegistered(tournamentId) {
        const tournamentIdStr = tournamentId.toString();
        const isRegistered = this.registeredTournaments.has(tournamentIdStr);
        console.log('🔍 Проверка регистрации в турнире:', tournamentIdStr, isRegistered);
        return isRegistered;
    }

    // Метод для отслеживания обработанных турниров
    isTournamentProcessed(tournamentId) {
        try {
            const processed = JSON.parse(localStorage.getItem('processedTournaments') || '[]');
            return processed.includes(tournamentId.toString());
        } catch (e) {
            return false;
        }
    }

    markTournamentAsProcessed(tournamentId) {
        try {
            const processed = JSON.parse(localStorage.getItem('processedTournaments') || '[]');
            if (!processed.includes(tournamentId.toString())) {
                processed.push(tournamentId.toString());
                localStorage.setItem('processedTournaments', JSON.stringify(processed));
            }
        } catch (e) {
            console.error('Ошибка сохранения обработанного турнира:', e);
        }
    }

    // Заглушки для совместимости
    send(message) {
        console.log('HTTP режим - отправка сообщения:', message);
    }

    handleMessage(message) {
        console.log('HTTP режим - получение сообщения:', message);
    }

    // Получить статус соединения
    getConnectionStatus() {
        return {
            isConnected: this.isConnected,
            lastHeartbeat: this.lastHeartbeat,
            heartbeatStats: { ...this.heartbeatStats },
            retryCount: this.retryCount,
            registeredTournaments: this.getRegisteredTournaments().length
        };
    }

    // Деструктор для очистки
    destroy() {
        this.stopPolling();
        this.stopHeartbeat();
        console.log('✅ TournamentManager уничтожен');
    }
}

class TournamentResultsManager {
    constructor() {
        this.results = [];
        this.stats = {
            participations: 0,
            wins: 0,
            top3: 0,
            totalPoints: 0,
            totalPrize: 0
        };
    }

    // Загрузка результатов турниров пользователя
    async loadTournamentResults() {
    try {
        console.log('🔄 Загрузка результатов турниров...');
        
        // Проверка авторизации
        if (typeof isGuest !== 'undefined' && isGuest) {
            console.log('👤 Гостевой режим: загрузка результатов недоступна');
            this.showEmptyState();
            return false;
        }
        
        // ★★★ ИСПОЛЬЗУЕМ ОСНОВНОЙ API ★★★
        const response = await fetch('api/get_tournament_results.php?_=' + Date.now(), {
            method: 'GET',
            credentials: 'include',
            headers: {
                'Cache-Control': 'no-cache',
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('📊 Ответ результатов турниров:', data);

        if (data.success && Array.isArray(data.results)) {
            // Обрабатываем результаты
            this.results = data.results.map(result => ({
                tournament_id: result.tournament_id || 0,
                tournament_name: result.tournament_name || `Турнир #${result.tournament_id}`,
                position: parseInt(result.position) || 999,
                prize: parseInt(result.prize) || 0,
                entry_fee: parseInt(result.entry_fee) || 0,
                total_points: parseInt(result.total_points) || 0,
                score: parseInt(result.score) || 0,
                games_won: parseInt(result.games_won) || 0,
                win_rate: parseFloat(result.win_rate) || 0,
                best_time: parseInt(result.best_time) || 0,
                completed_at: result.tournament_completed_at || result.completed_at,
                date_formatted: result.date_formatted || 'Дата неизвестна',
                best_time_formatted: result.best_time_formatted || '-',
                medal_color: result.medal_color || '#667eea'
            }));
            
            console.log('✅ Загружено результатов:', this.results.length);
            this.calculateStats();
            this.renderResults();
            return true;
        } else {
            console.log('ℹ️ Нет результатов:', data.message);
            this.showEmptyState();
            return false;
        }
    } catch (error) {
        console.error('❌ Ошибка загрузки результатов турниров:', error);
        this.showEmptyState();
        return false;
    }
}

    // Форматирование времени для отображения
    formatTimeForDisplay(seconds) {
        if (!seconds || seconds === 0) return '-';
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }

    // Расчет статистики
    calculateStats() {
        // ИСПРАВЛЕНИЕ: Считаем только реальные участия (где есть чатлы или приз)
        const validResults = this.results.filter(r => 
            (r.total_points && r.total_points > 0) || 
            (r.prize && r.prize > 0)
        );
        
        this.stats = {
            participations: validResults.length,
            wins: validResults.filter(r => parseInt(r.position) === 1).length,
            top3: validResults.filter(r => {
                const pos = parseInt(r.position);
                return pos >= 1 && pos <= 3;
            }).length,
            totalPoints: validResults.reduce((sum, r) => sum + (parseInt(r.total_points) || 0), 0),
            totalPrize: validResults.reduce((sum, r) => sum + (parseInt(r.prize) || 0), 0)
        };

        console.log('📈 Статистика турниров:', this.stats);
    }

    // Обновление отображения статистики
    updateStatsDisplay() {
        const statsOverview = document.getElementById('tournament-stats-overview');
        if (!statsOverview) return;

        statsOverview.innerHTML = `
            <div class="stats-grid-compact">
                <div class="stat-item-compact">
                    <div class="stat-label">Участий в турнирах:</div>
                    <div class="stat-number">${this.stats.participations || 0}</div>
                </div>
                <div class="stat-item-compact">
                    <div class="stat-label">Побед в турнирах:</div>
                    <div class="stat-number">${this.stats.wins || 0}</div>
                </div>
                <div class="stat-item-compact">
                    <div class="stat-label">Топ-3 в турнирах:</div>
                    <div class="stat-number">${this.stats.top3 || 0}</div>
                </div>
                <div class="stat-item-compact">
                    <div class="stat-label">Заработано в турнирах:</div>
                    <div class="stat-number">${this.stats.totalPoints + this.stats.totalPrize}</div>
                </div>
            </div>
        `;
    }

    // Рендеринг списка результатов (ОБНОВЛЕННЫЙ)
    renderResults() {
        const resultsList = document.getElementById('tournament-results-list');
        if (!resultsList) {
            console.error('Элемент tournament-results-list не найден');
            return;
        }

        resultsList.innerHTML = '';
        this.updateStatsDisplay();

        if (this.results.length === 0) {
            this.showEmptyState();
            return;
        }

        let resultsHTML = '';
        
        this.results.forEach(result => {
            // Определяем стиль в зависимости от места
            const resultClass = this.getResultClass(result);
            const medalIcon = this.getMedalIcon(result.position);
            const dateFormatted = this.formatDate(result.completed_at);
            const positionText = this.getPositionText(result.position);
            
            const tournamentDisplayName = `#${result.tournament_id} "${result.tournament_name}"`;
            
            // ★★★ ВАЖНО: Показываем лучший время только если оно есть
            const timeDisplay = result.best_time > 0 ? 
                `<div class="stat-item">
                    <i class="fas fa-clock"></i>
                    <span>Лучшее время: <strong>${result.best_time_formatted}</strong></span>
                </div>` : '';
            
            // ★★★ ВАЖНО: Показываем процент побед только если есть игры
            const winRateDisplay = result.win_rate > 0 ? 
                `<div class="stat-item">
                    <i class="fas fa-percentage"></i>
                    <span>Процент побед: <strong>${result.win_rate}%</strong></span>
                </div>` : '';
            
            resultsHTML += `
                <div class="tournament-result-card ${resultClass}" 
                     data-tournament-id="${result.tournament_id}" 
                     data-prize-amount="${result.prize}">
                    <div class="result-header">
                        <div class="tournament-name">${tournamentDisplayName}</div>
                        <div class="result-date">${dateFormatted}</div>
                    </div>
                    
                    <div class="result-details">
                        <div class="result-position">
                            ${medalIcon}
                            <span class="position-text">${positionText}</span>
                        </div>
                        
                        <div class="result-stats">
                            <div class="stat-item">
                                <i class="fa-solid fa-money-bill-1-wave"></i>
                                <span>Набрано чатлов: <strong>${result.total_points}</strong></span>
                            </div>
                            
                            ${result.prize > 0 ? `
                            <div class="stat-item">
                                <i class="fas fa-trophy"></i>
                                <span>Выигрыш: <strong class="prize-amount" style="color: #FFD700;">${result.prize}</strong> чатлов</span>
                            </div>
                            ` : ''}
                            
                            ${result.entry_fee > 0 ? `
                            <div class="stat-item">
                                <i class="fas fa-coins"></i>
                                <span>Взнос: <strong>${result.entry_fee}</strong> чатлов</span>
                            </div>
                            ` : ''}
                            
                            ${timeDisplay}
                            ${winRateDisplay}
                            
                            ${result.games_won > 0 ? `
                            <div class="stat-item">
                                <i class="fas fa-flag-checkered"></i>
                                <span>Побед в турнире: <strong>${result.games_won}</strong></span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        });

        resultsList.innerHTML = resultsHTML;
        
        // Настройка фильтров
        setTimeout(() => {
            this.setupFilters();
            const activeFilter = document.querySelector('.filter-btn-compact.active')?.dataset.filter || 'all';
            this.applyFilter(activeFilter);
        }, 100);
    }

    // Показать состояние при отсутствии результатов
    showEmptyState() {
    const resultsList = document.getElementById('tournament-results-list');
    if (!resultsList) return;

    // ★★★ РАЗНЫЕ СООБЩЕНИЯ ДЛЯ ГОСТЕЙ И АВТОРИЗОВАННЫХ ★★★
    if (typeof isGuest !== 'undefined' && isGuest) {
        resultsList.innerHTML = `
            <div class="empty-results">
                <i class="fas fa-user-lock"></i>
                <h4>Доступ только для авторизованных пользователей</h4>
                <p>Войдите/Зарегистрируйтесь, чтобы увидеть свои результаты турниров!</p>
            </div>
        `;
    } else {
        resultsList.innerHTML = `
            <div class="empty-results">
                <i class="fas fa-trophy"></i>
                <h4>Пока нет результатов турниров</h4>
                <p>Участвуйте в турнирах, чтобы увидеть здесь свои достижения!</p>
            </div>
        `;
    }

    // Обнуляем статистику
    const statsOverview = document.getElementById('tournament-stats-overview');
    if (statsOverview) {
        statsOverview.querySelectorAll('.stat-number').forEach(el => {
            el.textContent = '0';
        });
    }
}

    // Получить CSS класс для результата
    getResultClass(result) {
        if (result.position === 1) return 'result-gold';
        if (result.position === 2) return 'result-silver';
        if (result.position === 3) return 'result-bronze';
        if (result.position <= 10) return 'result-top10';
        return 'result-other';
    }

    // Получить иконку медали
    getMedalIcon(position) {
        if (position === 1) return '<i class="fas fa-trophy medal-gold"></i>';
        if (position === 2) return '<i class="fas fa-trophy medal-silver"></i>';
        if (position === 3) return '<i class="fas fa-trophy medal-bronze"></i>';
        return '<i class="fas fa-award"></i>';
    }

    // Получить текстовое описание позиции
    getPositionText(position) {
        if (position === 1) return 'ПЕРВОЕ МЕСТО';
        if (position === 2) return 'ВТОРОЕ МЕСТО';
        if (position === 3) return 'ТРЕТЬЕ МЕСТО';
        return `${position} МЕСТО`;
    }

    // Форматирование даты
    formatDate(dateString) {
        if (!dateString) return 'Дата неизвестна';
        
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('ru-RU', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
        } catch (e) {
            return 'Дата неизвестна';
        }
    }

    // Настройка фильтров
    setupFilters() {
    const filterBtns = document.querySelectorAll('.filter-btn-compact');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            console.log('Фильтр нажат:', e.target.dataset.filter);
            
            // Убрать активный класс со всех кнопок
            filterBtns.forEach(b => b.classList.remove('active'));
            // Добавить активный класс текущей кнопке
            btn.classList.add('active');
            
            const filter = btn.dataset.filter;
            this.applyFilter(filter);
        });
    });
}

    // Применить фильтр
    applyFilter(filter) {
    console.log('Применение фильтра:', filter);
    const results = document.querySelectorAll('.tournament-result-card');
    let visibleCount = 0;
    
    results.forEach(card => {
        let shouldShow = true;
        
        switch (filter) {
            case 'all':
                shouldShow = true;
                break;
            case 'prize':
                // ИСПРАВЛЕНИЕ: Используем data-атрибут для надежной фильтрации
                const prizeAmount = parseInt(card.getAttribute('data-prize-amount')) || 0;
                shouldShow = prizeAmount > 0;
                console.log('Фильтр "С выигрышем":', prizeAmount, shouldShow);
                break;
            case 'no-prize':
                // ИСПРАВЛЕНИЕ: Используем data-атрибут для надежной фильтрации
                const noPrizeAmount = parseInt(card.getAttribute('data-prize-amount')) || 0;
                shouldShow = noPrizeAmount === 0;
                console.log('Фильтр "Без выигрыша":', noPrizeAmount, shouldShow);
                break;
            default:
                shouldShow = true;
        }
        
        card.style.display = shouldShow ? 'flex' : 'none';
        if (shouldShow) visibleCount++;
    });
    
    console.log(`Показано результатов: ${visibleCount} из ${results.length} (фильтр: ${filter})`);
    
    // Обновляем информацию о количестве
    this.updateFilterStats(visibleCount, results.length, filter);
}

// Обновление статистики фильтра (ОПЦИОНАЛЬНО - можно не добавлять)
updateFilterStats(visible, total, filter) {
    const filterStats = document.getElementById('filter-stats');
    if (!filterStats) {
        // Создаем элемент для статистики, если его нет
        const filtersContainer = document.querySelector('.results-filters-compact');
        if (filtersContainer) {
            const statsElement = document.createElement('div');
            statsElement.id = 'filter-stats';
            statsElement.className = 'filter-stats';
            statsElement.style.marginTop = '10px';
            statsElement.style.fontSize = '14px';
            statsElement.style.color = '#666';
            filtersContainer.parentNode.insertBefore(statsElement, filtersContainer.nextSibling);
        }
    }
    
    const statsElement = document.getElementById('filter-stats');
    if (statsElement) {
        let filterText = '';
        switch (filter) {
            case 'all': filterText = 'все турниры'; break;
            case 'prize': filterText = 'турниры с выигрышем'; break;
            case 'no-prize': filterText = 'турниры без выигрыша'; break;
            default: filterText = 'турниры';
        }
        statsElement.textContent = `${visible} из ${total} (${filterText})`;
    }
}

    // Обновить результаты
    async refreshResults() {
        console.log('🔄 Обновление результатов турниров...');
        await this.loadTournamentResults();
    }
}

// Инициализация менеджера результатов
function initializeTournamentResults() {
    if (typeof window.tournamentResultsManager === 'undefined') {
        window.tournamentResultsManager = new TournamentResultsManager();
        console.log('✅ Менеджер результатов турниров инициализирован');
    }
    return window.tournamentResultsManager;
}

// Функция для загрузки результатов при переключении на вкладку
async function loadTournamentResultsTab() {
    // ★★★ ПРОВЕРКА АВТОРИЗАЦИИ ПЕРЕД ИНИЦИАЛИЗАЦИЕЙ ★★★
    if (typeof isGuest !== 'undefined' && isGuest) {
        console.log('👤 Гостевой режим: раздел результатов турниров недоступен');
        
        // Показываем сообщение для гостей
        const resultsList = document.getElementById('tournament-results-list');
        if (resultsList) {
            resultsList.innerHTML = `
                <div class="empty-results">
                    <i class="fas fa-user-lock"></i>
                    <h4>Доступ только для авторизованных пользователей</h4>
                    <p>Войдите/Зарегистрируйтесь, чтобы увидеть свои результаты турниров!</p>
                </div>
            `;
        }
        return;
    }
    
    const manager = initializeTournamentResults();
    await manager.loadTournamentResults();
}

// Обновите функцию loadTournaments
async function loadTournaments() {
    try {
        console.log('🔄 Загрузка турниров из базы...');
        const response = await fetch('api/get_tournaments.php');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('📊 Ответ от сервера:', data);
        
        if (data.success && data.tournaments) {
            console.log('✅ Загружено реальных турниров:', data.tournaments.length);
            
            // После загрузки турниров проверяем реальный статус регистрации
            await checkActualRegistrationStatus(data.tournaments);
            
            // Отображаем турниры
            displayTournaments(data.tournaments);
            
            if (data.tournaments.length === 0) {
                console.log('ℹ️ В базе нет активных турниров');
            }
        } else {
            console.error('❌ Ошибка сервера:', data.message);
            displayTournaments([]);
        }
    } catch (error) {
        console.error('❌ Ошибка загрузки турниров:', error);
        displayTournaments([]);
    }
}

// Проверка реального статуса регистрации на сервере
async function checkActualRegistrationStatus(tournaments) {
    if (!window.tournamentManager || !window.isLoggedIn) return;
    
    console.log('🔍 Проверка статуса регистрации для', tournaments.length, 'турниров');
    
    try {
        for (const tournament of tournaments) {
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 8000);
                
                const response = await fetch(`websocket_proxy.php?action=get_tournament_status&tournament_id=${tournament.id}&_=${Date.now()}`, {
                    credentials: 'include',
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                if (!response.ok) {
                    console.warn(`❌ HTTP error for tournament ${tournament.id}:`, response.status);
                    continue;
                }
                
                const data = await response.json();
                
                if (data.success && data.user_status === 'registered') {
                    // Пользователь зарегистрирован - добавляем в множество
                    window.tournamentManager.registeredTournaments.add(tournament.id.toString());
                    console.log(`✅ User registered in tournament ${tournament.id}`);
                    
                    // НЕМЕДЛЕННО ОБНОВЛЯЕМ ИНТЕРФЕЙС
                    updateTournamentButtonStatus(tournament.id, true);
                } else if (data.success && !data.user_status) {
                    // Пользователь не зарегистрирован - удаляем из множества
                    window.tournamentManager.registeredTournaments.delete(tournament.id.toString());
                    console.log(`❌ User NOT registered in tournament ${tournament.id}`);
                    
                    // НЕМЕДЛЕННО ОБНОВЛЯЕМ ИНТЕРФЕЙС
                    updateTournamentButtonStatus(tournament.id, false);
                }
                
            } catch (tournamentError) {
                console.error(`❌ Error checking tournament ${tournament.id}:`, tournamentError);
                continue;
            }
        }
        
        // Сохраняем обновленный статус
        window.tournamentManager.saveRegisteredTournaments();
        console.log('💾 Saved registration status to localStorage');
        
    } catch (error) {
        console.error('❌ Общая ошибка проверки статуса регистрации:', error);
    }
}

function displayTournaments(tournaments) {
    console.log('🎯 Displaying tournaments:', tournaments);
    
    const activeList = document.getElementById('active-tournaments-list');
    const upcomingList = document.getElementById('upcoming-tournaments-list');
    const completedList = document.getElementById('completed-tournaments-list');
    
    if (!activeList || !upcomingList || !completedList) {
        console.error('❌ Tournament list elements not found!');
        return;
    }
    
    // Очищаем списки
    activeList.innerHTML = '';
    upcomingList.innerHTML = '';
    completedList.innerHTML = '';
    
    console.log('📋 Total tournaments to display:', tournaments.length);
    
    if (!tournaments || tournaments.length === 0) {
        activeList.innerHTML = '<div class="no-tournaments">🎯 Нет активных турниров</div>';
        upcomingList.innerHTML = '<div class="no-tournaments">📅 Нет предстоящих турниров</div>';
        completedList.innerHTML = '<div class="no-tournaments">🏆 Нет завершенных турниров</div>';
        return;
    }
    
    let activeCount = 0, upcomingCount = 0, completedCount = 0;
    
    // ★★★ ИСПРАВЛЕНИЕ: Сортируем только завершенные турниры ★★★
    const completedTournaments = tournaments
        .filter(t => t.status === 'completed')
        .sort((a, b) => {
            // Сначала по дате завершения (новые сверху)
            if (a.completed_at && b.completed_at) {
                const dateA = new Date(a.completed_at);
                const dateB = new Date(b.completed_at);
                const dateDiff = dateB - dateA;
                if (dateDiff !== 0) return dateDiff;
            }
            // Если даты одинаковые или отсутствуют - по убыванию ID
            return b.id - a.id;
        });
    
    const activeTournaments = tournaments.filter(t => t.status === 'active');
    const upcomingTournaments = tournaments.filter(t => t.status === 'registration');
    
    // Отображаем активные турниры
    activeTournaments.forEach(tournament => {
        activeList.appendChild(createTournamentElement(tournament));
        activeCount++;
    });
    
    // Отображаем предстоящие турниры
    upcomingTournaments.forEach(tournament => {
        upcomingList.appendChild(createTournamentElement(tournament));
        upcomingCount++;
    });
    
    // Отображаем завершенные турниры (уже отсортированные)
    completedTournaments.forEach(tournament => {
        completedList.appendChild(createTournamentElement(tournament));
        completedCount++;
    });
    
    console.log(`📊 Displayed: ${activeCount} active, ${upcomingCount} upcoming, ${completedCount} completed`);
    
    // Обновляем заголовки с количеством
    updateTournamentSectionHeaders(activeCount, upcomingCount, completedCount);
    
    // ★★★ ОБНОВЛЯЕМ ВСЕ КНОПКИ ПОСЛЕ ОТОБРАЖЕНИЯ ТУРНИРОВ ★★★
    setTimeout(() => {
        updateAllTournamentButtons();
    }, 100);
    
    // Если какой-то список пуст, показываем сообщение
    if (activeCount === 0) {
        activeList.innerHTML = '<div class="no-tournaments">🎯 Пока нет активных турниров. Следите за обновлениями!</div>';
    }
    if (upcomingCount === 0) {
        upcomingList.innerHTML = '<div class="no-tournaments">📅 Новые турниры появятся здесь скоро</div>';
    }
    if (completedCount === 0) {
        completedList.innerHTML = '<div class="no-tournaments">🏆 Здесь будут отображаться завершенные турниры</div>';
    }
}

function updateTournamentSectionHeaders(activeCount, upcomingCount, completedCount) {
    const activeHeader = document.querySelector('#tournaments-tab h3:nth-of-type(1)');
    const upcomingHeader = document.querySelector('#tournaments-tab h3:nth-of-type(2)');
    const completedHeader = document.querySelector('#tournaments-tab h3:nth-of-type(3)');
    
    if (activeHeader) activeHeader.textContent = `Активные турниры по 2 часа каждый: (${activeCount})`;
    if (upcomingHeader) upcomingHeader.textContent = `Предстоящие турниры по 2 часа каждый: (${upcomingCount})`;
    if (completedHeader) completedHeader.textContent = `Завершенные турниры: (${completedCount})`;
}

function createTournamentElement(tournament) {
    const div = document.createElement('div');
    div.className = 'tournament-card';
    
    const entryFee = tournament.entry_fee > 0 ? 
        `${tournament.entry_fee} чатлов` : 'Бесплатно';
    
    const playersInfo = `${tournament.current_players || 0}/${tournament.max_players} игроков`;
    
    // Форматируем дату
    let startTime = 'Скоро';
    if (tournament.start_time) {
        try {
            const date = new Date(tournament.start_time);
            startTime = date.toLocaleDateString('ru-RU', {
                day: 'numeric',
                month: 'long',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            console.warn('Invalid date format:', tournament.start_time);
        }
    }
    
    // Проверяем, зарегистрирован ли пользователь в турнире
    const tournamentIdStr = tournament.id.toString();
    const isRegistered = window.tournamentManager && 
                        window.tournamentManager.isRegistered(tournamentIdStr);
    
    console.log('🎯 Tournament:', tournamentIdStr, 'Status:', tournament.status, 'User registered:', isRegistered);
    
    // ★★★ ОПРЕДЕЛЯЕМ ТЕКСТ КНОПКИ С УЧЕТОМ СТАТУСА ТУРНИРА И РЕГИСТРАЦИИ ★★★
    let buttonClass = '';
    let buttonText = '';
    let buttonIcon = '';
    let isDisabled = false;
    
    // Если турнир завершен - всегда показываем "Завершен"
    if (tournament.status === 'completed') {
        buttonClass = 'btn-secondary';
        buttonText = 'Завершен';
        buttonIcon = 'fa-flag-checkered';
        isDisabled = true;
    }
    // Если турнир активен и регистрация закрыта
    else if (tournament.status === 'active') {
        buttonClass = 'btn-secondary';
        buttonText = 'Регистрация закрыта';
        buttonIcon = 'fa-lock';
        isDisabled = true;
    }
    // Если пользователь зарегистрирован в турнире с открытой регистрацией
    else if (isRegistered && tournament.status === 'registration') {
        buttonClass = 'btn-success';
        buttonText = 'Вы в деле! ✓';
        buttonIcon = 'fa-check-circle';
        isDisabled = true;
    }
    // Если регистрация открыта и пользователь не зарегистрирован
    else if (tournament.status === 'registration') {
        buttonClass = 'btn-primary';
        buttonText = 'Зарегистрироваться';
        buttonIcon = 'fa-sign-in-alt';
        isDisabled = false;
    }
    // Все остальные случаи
    else {
        buttonClass = 'btn-secondary';
        buttonText = 'Недоступно';
        buttonIcon = 'fa-times';
        isDisabled = true;
    }
    
    // Добавляем бейдж "Зарегистрирован" если пользователь уже зарегистрирован И турнир не завершен
    const registeredBadge = (isRegistered && tournament.status !== 'completed') ? 
        '<span class="registered-badge">✓ Зарегистрирован</span>' : 
        '';
    
    // ★★★ ИСПРАВЛЕНИЕ: Добавляем ID турнира перед названием ★★★
    const tournamentDisplayName = `#${tournament.id} "${tournament.name || 'Турнир'}"`;
    
    div.innerHTML = `
        <div class="tournament-header">
            <h4>${tournamentDisplayName}${registeredBadge}</h4>
            <span class="tournament-prize">🏆 Приз: ${tournament.prize_pool || 0} чатлов</span>
        </div>
        <div class="tournament-info">
            <p>${tournament.description || 'Соревнование по решению судоку'}</p>
            <div class="tournament-details">
                <span><i class="fas fa-coins"></i> Взнос: ${entryFee}</span>
                <span><i class="fas fa-users"></i> ${playersInfo}</span>
                <span><i class="fas fa-calendar"></i> Начало: ${startTime}</span>
                <span><i class="fas fa-brain"></i> Уровень: ${getDifficultyLabel(tournament.difficulty)}</span>
            </div>
        </div>
        <div class="tournament-actions">
            <button class="btn ${buttonClass} join-tournament-btn" 
                    data-id="${tournamentIdStr}" 
                    ${isDisabled ? 'disabled' : ''}>
                <i class="fas ${buttonIcon}"></i> ${buttonText}
            </button>
        </div>
    `;
    
    return div;
}

// Функция для отладки статуса авторизации
function debugAuthStatus() {
    console.log('🔐 Debug Auth Status:');
    console.log('- window.isLoggedIn:', window.isLoggedIn);
    console.log('- window.userId:', window.userId);
    console.log('- window.username:', window.username);
    console.log('- window.isGuest:', typeof isGuest !== 'undefined' ? isGuest : 'undefined');
    console.log('- User elements:', document.querySelectorAll('.user-info-container, .user-nick-btn').length);
    console.log('- Tournament manager:', typeof tournamentManager !== 'undefined' ? 'exists' : 'undefined');
    
    // Проверяем куки
    console.log('- Cookies:', document.cookie);
    
    // Проверяем сессию через API
    fetch('api/check_auth.php')
        .then(response => response.json())
        .then(data => console.log('- API auth check:', data))
        .catch(err => console.log('- API auth check failed:', err));
}

function getDifficultyLabel(difficulty) {
    const labels = {
        'easy': '🥉 Легкий',
        'medium': '🥈 Средний', 
        'hard': '🥇 Трудный',
        'tournament': '🏆 Турнирный'
    };
    return labels[difficulty] || difficulty;
}

function createDemoTournaments() {
    console.log('🚫 Демо-турниры отключены');
    // Ничего не делаем - показываем только реальные турниры
    displayTournaments([]);
}

function setupCabinetHandlers() {
    // Обработчики для вкладок
    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const tabName = btn.dataset.tab;
            switchTab(tabName);
            
            // Загружаем данные при переключении на вкладку результатов
            if (tabName === 'tournament-results') {
                loadTournamentResultsTab();
            }
        });
    });
    
    // Обработчики для кнопок регистрации в турниры
    document.addEventListener('click', async (e) => {
        if (e.target.classList.contains('join-tournament-btn') || 
            e.target.closest('.join-tournament-btn')) {
            
            const btn = e.target.classList.contains('join-tournament-btn') ? 
                       e.target : e.target.closest('.join-tournament-btn');
            
            if (!btn.disabled) {
                const tournamentId = btn.dataset.id;
                console.log('🎯 Tournament button clicked:', tournamentId);
                
                // Добавляем индикатор загрузки
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Загрузка...';
                btn.disabled = true;
                
                try {
                    // Определяем какое действие выполнить
                    if (btn.textContent.includes('Присоединиться') || btn.textContent.includes('Продолжить')) {
                        await startTournamentGame(tournamentId);
                    } else {
                        await joinTournament(tournamentId);
                    }
                } finally {
                    // Восстанавливаем кнопку
                    setTimeout(() => {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                        safeLoadTournaments();
                    }, 1000);
                }
            }
        }
    });
}

function switchTab(tabName) {
    // Скрыть все вкладки
    document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.classList.remove('active');
    });
    
    // Убрать активный класс со всех кнопок
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Показать выбранную вкладку
    const targetTab = document.getElementById(`${tabName}-tab`);
    if (targetTab) {
        targetTab.classList.add('active');
    }
    
    // Активировать кнопку
    const targetBtn = document.querySelector(`.tab-btn[data-tab="${tabName}"]`);
    if (targetBtn) {
        targetBtn.classList.add('active');
    }
    
    // Загрузить данные для вкладки если нужно
    if (tabName === 'tournaments') {
        safeLoadTournaments();
    } else if (tabName === 'tournament-results') {
        loadTournamentResultsTab();
        
        // Переинициализация фильтров при переключении на вкладку
        setTimeout(() => {
            const manager = window.tournamentResultsManager;
            if (manager && manager.setupFilters) {
                manager.setupFilters();
                
                // Применяем активный фильтр
                const activeFilter = document.querySelector('.filter-btn-compact.active')?.dataset.filter || 'all';
                if (manager.applyFilter) {
                    setTimeout(() => {
                        manager.applyFilter(activeFilter);
                    }, 100);
                }
            }
        }, 500);
    }
}

async function joinTournament(tournamentId) {
    console.log('🎯 Attempting to join tournament:', tournamentId);
    
    // Проверяем авторизацию несколькими способами
    const isAuth = await checkAuthentication();
    
    if (!isAuth) {
        showNotification('Для участия в турнирах необходимо войти в систему', 'warning');
        return;
    }
    
    console.log('✅ User is authenticated, proceeding with registration...');
    
    // Добавляем индикатор загрузки на кнопку
    const joinButton = document.querySelector(`.join-tournament-btn[data-id="${tournamentId}"]`);
    const originalButtonHTML = joinButton ? joinButton.innerHTML : null;
    
    if (joinButton) {
        joinButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Регистрация...';
        joinButton.disabled = true;
    }
    
    // Используем tournamentManager для регистрации
    if (window.tournamentManager && typeof window.tournamentManager.joinTournament === 'function') {
        const success = await window.tournamentManager.joinTournament(tournamentId);
        
        if (success) {
            console.log('🔄 Обновление баланса после регистрации в турнире...');
            
            // ★★★ ОБНОВЛЯЕМ ИНТЕРФЕЙС ТУРНИРА ★★★
            updateTournamentButtonStatus(tournamentId, true);
            
            // ★★★ УБИРАЕМ НЕМЕДЛЕННУЮ ПЕРЕЗАГРУЗКУ ТУРНИРОВ ★★★
            // Вместо немедленной перезагрузки просто обновляем локальное состояние
            if (window.tournamentManager) {
                window.tournamentManager.registeredTournaments.add(tournamentId.toString());
                window.tournamentManager.saveRegisteredTournaments();
            }
            
            // Обновляем баланс
            setTimeout(() => {
                refreshStats().then(() => {
                    console.log('✅ Баланс обновлен после регистрации:', stats.totalPoints);
                    showNotification('Взнос за турнир принят!', 'success');
                }).catch(error => {
                    console.error('❌ Ошибка обновления баланса:', error);
                });
            }, 1000);
            
            return true;
        } else {
            // Восстанавливаем кнопку при ошибке
            if (joinButton && originalButtonHTML) {
                joinButton.innerHTML = originalButtonHTML;
                joinButton.disabled = false;
            }
            return false;
        }
    } else {
        // Fallback: прямая регистрация через fetch
        try {
            console.log('🔄 Using direct fetch for tournament registration');
            const response = await fetch(`websocket_proxy.php?action=join_tournament&tournament_id=${tournamentId}&_=${Date.now()}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('📦 Registration response:', data);
            
            if (data.success) {
                showNotification(data.message || 'Регистрация прошла успешно!', 'success');
                
                // ★★★ ОБНОВЛЯЕМ ИНТЕРФЕЙС ТУРНИРА ★★★
                updateTournamentButtonStatus(tournamentId, true);
                
                // ★★★ УБИРАЕМ НЕМЕДЛЕННУЮ ПЕРЕЗАГРУЗКУ ТУРНИРОВ ★★★
                // Вместо немедленной перезагрузки просто обновляем локальное состояние
                if (window.tournamentManager) {
                    window.tournamentManager.registeredTournaments.add(tournamentId.toString());
                    window.tournamentManager.saveRegisteredTournaments();
                }
                
                // Обновляем баланс
                setTimeout(() => {
                    refreshStats().then(() => {
                        console.log('✅ Баланс обновлен после регистрации:', stats.totalPoints);
                    });
                }, 1000);
                
                return true;
            } else {
                showNotification(data.message || 'Ошибка регистрации', 'error');
                // Восстанавливаем кнопку при ошибке
                if (joinButton && originalButtonHTML) {
                    joinButton.innerHTML = originalButtonHTML;
                    joinButton.disabled = false;
                }
                return false;
            }
        } catch (error) {
            console.error('Ошибка регистрации в турнире:', error);
            showNotification('Ошибка соединения: ' + error.message, 'error');
            // Восстанавливаем кнопку при ошибке
            if (joinButton && originalButtonHTML) {
                joinButton.innerHTML = originalButtonHTML;
                joinButton.disabled = false;
            }
            return false;
        }
    }
}

async function startTournamentGame(tournamentId) {
    try {
        console.log('🎯 Starting tournament game:', tournamentId);
        const response = await fetch(`websocket_proxy.php?action=start_tournament_game&tournament_id=${tournamentId}`);
        const data = await response.json();
        
        console.log('📦 Tournament game response:', data);
        
        if (data.success) {
            showNotification(data.message, 'success');
            // TODO: Загрузить турнирную игру в основной интерфейс
            // loadTournamentGame(data.game_id, data.board);
            
            // Обновляем список турниров
            setTimeout(safeLoadTournaments, 1000);
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        console.error('Ошибка начала турнирной игры:', error);
        showNotification('Ошибка начала игры: ' + error.message, 'error');
    }
}

// Функция проверки авторизации
async function checkAuthentication() {
    // Способ 1: Проверяем глобальную переменную
    if (window.isLoggedIn) {
        return true;
    }
    
    // Способ 2: Проверяем наличие элементов пользователя на странице
    const userElements = document.querySelectorAll('.user-info-container, .user-nick-btn, .user-avatar');
    if (userElements.length > 0) {
        window.isLoggedIn = true;
        return true;
    }
    
    // Способ 3: Делаем AJAX запрос для проверки сессии
    try {
        const response = await fetch('api/check_auth.php');
        if (response.ok) {
            const data = await response.json();
            window.isLoggedIn = data.loggedIn || false;
            return window.isLoggedIn;
        }
    } catch (error) {
        console.log('Auth check failed, assuming guest:', error);
    }
    
    // Способ 4: Проверяем наличие гостевого режима
    if (typeof isGuest !== 'undefined' && !isGuest) {
        window.isLoggedIn = true;
        return true;
    }
    
    return false;
}

function initiatePayment(method) {
    switch (method) {
        case 'donationalerts':
            initiateDonationAlertsPayment();
            break;
        default:
            showNotification('Платежная система временно недоступна', 'warning');
    }
}

function initiateDonationAlertsPayment() {
    const amount = prompt('Введите сумму пополнения в рублях (1 рубль = 1 чатл):');
    if (!amount || isNaN(amount) || amount < 1) {
        showNotification('Неверная сумма', 'error');
        return;
    }
    
    showNotification('Имитация платежа через DonationAlerts...', 'info');
    
    // Имитация успешного платежа для демонстрации
    setTimeout(() => {
        updateUserBalance(parseInt(amount));
        showNotification(`Оплата успешно завершена! Зачислено ${amount} чатлов.`, 'success');
    }, 2000);
}

function updateUserBalance(amount) {
    // В демо-режиме просто обновляем отображение
    const balanceElement = document.querySelector('.balance-amount');
    if (balanceElement) {
        const currentBalance = parseInt(balanceElement.textContent) || window.userStats?.totalPoints || 0;
        const newBalance = currentBalance + amount;
        balanceElement.textContent = `${newBalance} <i class="fa-solid fa-money-bill-1-wave"></i>`;
        
        // Обновляем глобальную переменную
        if (window.userStats) {
            window.userStats.totalPoints = newBalance;
        }
        
        // Обновляем отображение в header
        const headerBalance = document.getElementById('user-rating');
        if (headerBalance) {
            headerBalance.textContent = newBalance;
        }
    }
}

// ==================== СИСТЕМА УВЕДОМЛЕНИЙ О ТУРНИРАХ ====================

class TournamentNotificationManager {
    constructor() {
        this.checkInterval = null;
        this.lastCheckTime = null;
        this.notificationCooldown = 5 * 60 * 1000; // 5 минут между проверками
        this.currentlyShowing = new Set();
    }

// Добавьте этот вспомогательный метод
getStoppedTournaments() {
    try {
        return JSON.parse(localStorage.getItem('stoppedTournamentNotifications') || '[]');
    } catch (e) {
        return [];
    }
}

// Добавьте метод для фильтрации просмотренных турниров
async filterUnseenTournaments(tournaments) {
    try {
        const response = await fetch('api/get_seen_tournaments.php', {
            method: 'GET',
            credentials: 'same-origin'
        });
        
        if (response.ok) {
            const data = await response.json();
            const seenTournamentIds = data.seen_tournaments || [];
            
            // Возвращаем только турниры, которые пользователь еще не видел
            return tournaments.filter(tournament => 
                !seenTournamentIds.includes(tournament.id)
            );
        }
    } catch (error) {
        console.error('Ошибка фильтрации турниров:', error);
    }
    
    return tournaments; // Если ошибка - показываем все
}

}

// Функция для отметки турнира как просмотренного
async function markTournamentAsSeen(tournamentId) {
    try {
        console.log('🔖 Помечаем турнир как просмотренный:', tournamentId);
        
        const response = await fetch('api/mark_tournament_seen.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ tournament_id: tournamentId }),
            credentials: 'same-origin'
        });
        
        if (response.ok) {
            const result = await response.json();
            if (result.success) {
                console.log('✅ Турнир помечен как просмотренный');
                
                // Удаляем турнир из списка отслеживаемых
                if (window.tournamentManager) {
                    window.tournamentManager.registeredTournaments.delete(parseInt(tournamentId));
                    window.tournamentManager.saveRegisteredTournaments();
                }
            }
        }
    } catch (error) {
        console.error('❌ Ошибка отметки турнира:', error);
    }
}

// Инициализация менеджера уведомлений
function initializeTournamentNotifications() {
    // ★★★ ЗАКОММЕНТИРОВАТЬ ВЕСЬ БЛОК ★★★
    /*
    if (typeof window.tournamentNotificationManager === 'undefined') {
        window.tournamentNotificationManager = new TournamentNotificationManager();
        
        if (window.isLoggedIn) {
            setTimeout(() => {
                window.tournamentNotificationManager.startPolling();
            }, 10000);
        }
    }
    */
}

// ==================== ИНИЦИАЛИЗАЦИЯ СИСТЕМЫ ТУРНИРОВ ====================

// Обновляем функцию initializeTournamentSystem
function initializeTournamentSystem() {
    if (typeof tournamentManager === 'undefined') {
        window.tournamentManager = new TournamentManager();
        tournamentManager.connect();
        console.log('🎯 Турнирная система инициализирована');
    }
    
    // ★★★ ЗАКОММЕНТИРОВАТЬ эту строку ★★★
    // initializeTournamentNotifications();
    
    setupCabinetHandlers();
}

// Безопасная загрузка турниров
async function safeLoadTournaments() {
    try {
        console.log('🔄 Безопасная загрузка турниров...');
        
        // Добавляем таймаут
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 15000);
        
        const response = await fetch('api/get_tournaments.php?_=' + Date.now(), {
            method: 'GET',
            signal: controller.signal,
            credentials: 'same-origin'
        });
        
        clearTimeout(timeoutId);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.tournaments) {
            console.log('✅ Загружено реальных турниров:', data.tournaments.length);
            
            // После загрузки турниров проверяем реальный статус регистрации
            await checkActualRegistrationStatus(data.tournaments);
            
            // Отображаем турниры
            displayTournaments(data.tournaments);
            
            if (data.tournaments.length === 0) {
                console.log('ℹ️ В базе нет активных турниров');
            }
        } else {
            console.error('❌ Ошибка сервера:', data.message);
            displayTournaments([]);
        }
    } catch (error) {
        console.error('❌ Критическая ошибка загрузки турниров:', error);
        
        if (error.name === 'AbortError') {
            console.log('⏰ Таймаут загрузки турниров');
        }
        
        displayTournaments([]);
    }
}

// Инициализация страницы
function initializePage() {
    console.log('🔄 Инициализация страницы...');
    
    // Инициализация турнирной системы
    if (typeof initializeTournamentSystem === 'function') {
        initializeTournamentSystem();
    }
    
    // Показываем контент
    document.body.classList.remove('loading');
    document.body.classList.add('loaded');
}

// ==================== ФУНКЦИИ ТУРНИРОВ ====================

// ★★★ ФУНКЦИЯ ДЛЯ ВОСПРОИЗВЕДЕНИЯ ЗВУКА УВЕДОМЛЕНИЯ ТУРНИРА ★★★
function playTournamentNotificationSound() {
    if (soundEffects && soundEffects.win) {
        soundEffects.win.cloneNode().play().catch(() => {
            // Игнорируем ошибки воспроизведения
        });
    }
}

// ★★★ ФУНКЦИЯ ДЛЯ ОБНОВЛЕНИЯ СТАТУСА КНОПКИ ТУРНИРА ★★★
function updateTournamentButtonStatus(tournamentId, isRegistered) {
    console.log('🔄 Обновление статуса кнопки турнира:', tournamentId, isRegistered);
    
    // Находим все кнопки с данным tournamentId
    const joinButtons = document.querySelectorAll(`.join-tournament-btn[data-id="${tournamentId}"]`);
    
    if (joinButtons.length === 0) {
        console.log('❌ Кнопки турнира не найдены:', tournamentId);
        return;
    }
    
    joinButtons.forEach(joinButton => {
        const tournamentCard = joinButton.closest('.tournament-card');
        let tournamentStatus = 'registration';
        
        // Определяем статус турнира
        const activeSection = document.querySelector('#active-tournaments-list');
        const completedSection = document.querySelector('#completed-tournaments-list');
        
        if (completedSection && completedSection.contains(tournamentCard)) {
            tournamentStatus = 'completed';
        } else if (activeSection && activeSection.contains(tournamentCard)) {
            tournamentStatus = 'active';
        }
        
        console.log('📊 Статус турнира для кнопки:', tournamentId, tournamentStatus);
        
        // Используем плавное обновление
        updateTournamentButtonSmoothly(tournamentId, isRegistered, tournamentStatus, joinButton, tournamentCard);
    });
}

// ★★★ ФУНКЦИЯ ДЛЯ ОБНОВЛЕНИЯ ВСЕХ КНОПОК ТУРНИРОВ ПОСЛЕ ЗАГРУЗКИ ★★★
function updateAllTournamentButtons() {
    console.log('🔄 Обновление всех кнопок турниров...');
    
    if (!window.tournamentManager) {
        console.log('❌ Tournament manager не доступен');
        return;
    }
    
    // Сначала собираем все данные, потом обновляем за один проход
    const allJoinButtons = document.querySelectorAll('.join-tournament-btn');
    const updateData = [];
    
    // Собираем данные для обновления
    allJoinButtons.forEach(button => {
        const tournamentId = button.getAttribute('data-id');
        if (tournamentId) {
            const isRegistered = window.tournamentManager.isRegistered(tournamentId);
            const tournamentCard = button.closest('.tournament-card');
            
            // Определяем статус турнира
            let tournamentStatus = 'registration';
            const activeSection = document.querySelector('#active-tournaments-list');
            const completedSection = document.querySelector('#completed-tournaments-list');
            
            if (completedSection && completedSection.contains(tournamentCard)) {
                tournamentStatus = 'completed';
            } else if (activeSection && activeSection.contains(tournamentCard)) {
                tournamentStatus = 'active';
            }
            
            updateData.push({
                button: button,
                tournamentId: tournamentId,
                isRegistered: isRegistered,
                tournamentStatus: tournamentStatus,
                tournamentCard: tournamentCard
            });
        }
    });
    
    // Теперь обновляем все кнопки за один проход
    updateData.forEach(data => {
        updateTournamentButtonSmoothly(
            data.tournamentId, 
            data.isRegistered, 
            data.tournamentStatus, 
            data.button, 
            data.tournamentCard
        );
    });
    
    console.log('✅ Все кнопки турниров обновлены без моргания');
}

// ★★★ ПЛАВНОЕ ОБНОВЛЕНИЕ СТАТУСА КНОПКИ ТУРНИРА ★★★
function updateTournamentButtonSmoothly(tournamentId, isRegistered, tournamentStatus, joinButton, tournamentCard) {
    // Сохраняем текущее состояние кнопки
    const currentButtonState = {
        html: joinButton.innerHTML,
        className: joinButton.className,
        disabled: joinButton.disabled
    };
    
    // Определяем новое состояние
    let newButtonClass = '';
    let newButtonText = '';
    let newButtonIcon = '';
    let newDisabled = false;
    
    if (tournamentStatus === 'completed') {
        newButtonClass = 'btn btn-secondary join-tournament-btn';
        newButtonText = '<i class="fas fa-flag-checkered"></i> Завершен';
        newDisabled = true;
    }
    else if (tournamentStatus === 'active') {
        newButtonClass = 'btn btn-secondary join-tournament-btn';
        newButtonText = '<i class="fas fa-lock"></i> Регистрация закрыта';
        newDisabled = true;
    }
    else if (isRegistered && tournamentStatus === 'registration') {
        newButtonClass = 'btn btn-success join-tournament-btn';
        newButtonText = '<i class="fas fa-check-circle"></i> Вы в деле! ✓';
        newDisabled = true;
    }
    else if (tournamentStatus === 'registration') {
        newButtonClass = 'btn btn-primary join-tournament-btn';
        newButtonText = '<i class="fas fa-sign-in-alt"></i> Зарегистрироваться';
        newDisabled = false;
    }
    else {
        newButtonClass = 'btn btn-secondary join-tournament-btn';
        newButtonText = '<i class="fas fa-times"></i> Недоступно';
        newDisabled = true;
    }
    
    // ★★★ ПРОВЕРЯЕМ, ИЗМЕНИЛОСЬ ЛИ СОСТОЯНИЕ ★★★
    const hasChanged = 
        currentButtonState.html !== newButtonText ||
        currentButtonState.className !== newButtonClass ||
        currentButtonState.disabled !== newDisabled;
    
    if (!hasChanged) {
        // Если состояние не изменилось - ничего не делаем
        return;
    }
    
    // ★★★ ОБНОВЛЯЕМ ТОЛЬКО ЕСЛИ СОСТОЯНИЕ ИЗМЕНИЛОСЬ ★★★
    joinButton.innerHTML = newButtonText;
    joinButton.className = newButtonClass;
    joinButton.disabled = newDisabled;
    
    // Обновляем бейдж "Зарегистрирован"
    if (tournamentCard) {
        let registeredBadge = tournamentCard.querySelector('.registered-badge');
        
        if (isRegistered && tournamentStatus !== 'completed') {
            if (!registeredBadge) {
                const header = tournamentCard.querySelector('.tournament-header');
                if (header) {
                    registeredBadge = document.createElement('span');
                    registeredBadge.className = 'registered-badge';
                    registeredBadge.innerHTML = '✓ Зарегистрирован';
                    header.appendChild(registeredBadge);
                }
            }
        } else {
            if (registeredBadge) {
                registeredBadge.remove();
            }
        }
    }
    
    console.log('✅ Статус кнопки плавно обновлен для турнира:', tournamentId);
}

// ЕДИНСТВЕННЫЙ обработчик загрузки DOM для турнирной системы
document.addEventListener('DOMContentLoaded', function() {
    // Инициализируем турнирную систему сразу
    initializeTournamentSystem();
    
    // Настройка обработчиков личного кабинета
    const cabinetBtn = document.getElementById('user-cabinet-btn');
    const cabinetModal = document.getElementById('user-cabinet-modal');
    const closeCabinetBtn = document.getElementById('close-cabinet-btn');
    const closeCabinetModal = document.getElementById('close-cabinet-modal');
    
    if (cabinetBtn && cabinetModal) {
        cabinetBtn.addEventListener('click', function() {
            // ★★★ ОБНОВЛЯЕМ СТАТИСТИКУ ПРИ ОТКРЫТИИ ЛИЧНОГО КАБИНЕТА ★★★
            refreshStats().then(() => {
                console.log('✅ Статистика обновлена для личного кабинета:', stats.totalPoints);
            });
            
            cabinetModal.style.display = 'flex';
            safeLoadTournaments(); // Загружаем актуальные турниры
        });
    }
    
    if (closeCabinetBtn) {
        closeCabinetBtn.addEventListener('click', function() {
            cabinetModal.style.display = 'none';
        });
    }
    
    if (closeCabinetModal) {
        closeCabinetModal.addEventListener('click', function() {
            cabinetModal.style.display = 'none';
        });
    }
    
    // Закрытие по клику вне модального окна
    if (cabinetModal) {
        cabinetModal.addEventListener('click', function(e) {
            if (e.target === cabinetModal) {
                cabinetModal.style.display = 'none';
            }
        });
    }
    
    // Инициализация менеджера результатов турниров
    initializeTournamentResults();
    
    // Инициализация обработчиков для личного кабинета
    setupCabinetHandlers();
    
    console.log('✅ Турнирная система полностью инициализирована');
    
    // Дополнительная инициализация через 2 секунды для надежности
    setTimeout(() => {
        if (typeof tournamentManager === 'undefined') {
            initializeTournamentSystem();
        }
    }, 2000);
});