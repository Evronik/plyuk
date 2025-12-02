<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Полный список достижений по умолчанию (как в main.js)
    $defaultAchievements = [
        [
            'id' => 'first_win',
            'name' => 'Привет, Плюк!', 
            'description' => 'Решите Ваше первое судоку',
            'unlocked' => false,
            'icon' => 'fa-meteor',
            'color' => 'linear-gradient(135deg, #FFB800, #254BCC)',
            'progress' => 0,
            'progressMax' => 1,
            'points' => 5
        ],
        [ 
            'id' => 'no_mistakes', 
            'name' => 'Без ошибок', 
            'description' => 'Решите судоку без единой ошибки', 
            'unlocked' => false, 
            'icon' => 'fa-check-circle', 
            'color' => 'linear-gradient(135deg, #fd4d00, #b3832a)',
            'progress' => 0,
            'progressMax' => 1,
            'points' => 2
        ],
        [ 
            'id' => 'no_hints', 
            'name' => 'Без подсказок', 
            'description' => 'Решите судоку без использования подсказок', 
            'unlocked' => false, 
            'icon' => 'fa-lightbulb', 
            'color' => 'linear-gradient(135deg, #c9a5df, #254BCC)',
            'progress' => 0,
            'progressMax' => 1,
            'points' => 2
        ],
        [ 
            'id' => 'perfectionist', 
            'name' => 'Последний выдох', 
            'description' => 'Решите судоку без ошибок и подсказок', 
            'unlocked' => false, 
            'icon' => 'fa-cloud-meatball', 
            'color' => 'linear-gradient(135deg, #375d5d, #43d2fd)',
            'progress' => 0,
            'progressMax' => 1,
            'points' => 5
        ],
        [ 
            'id' => 'speedster_easy', 
            'name' => 'Зелёные штаны', 
            'description' => 'Решите легкое судоку менее чем за 5 минут', 
            'unlocked' => false, 
            'icon' => 'fa-universal-access', 
            'color' => 'linear-gradient(135deg, #52ff30, #00ff51)',
            'progress' => 0,
            'progressMax' => 300,
            'points' => 5
        ],
        [ 
            'id' => 'speedster_medium', 
            'name' => 'Сиреневые штаны', 
            'description' => 'Решите среднее судоку менее чем за 10 минут', 
            'unlocked' => false, 
            'icon' => 'fa-universal-access', 
            'color' => 'linear-gradient(135deg, #af52de, #8E3DBD)',
            'progress' => 0,
            'progressMax' => 600,
            'points' => 10
        ],
        [ 
            'id' => 'speedster_hard', 
            'name' => 'Жёлтые штаны', 
            'description' => 'Решите сложное судоку менее чем за 15 минут', 
            'unlocked' => false, 
            'icon' => 'fa-universal-access', 
            'color' => 'linear-gradient(135deg, #FFD700, #FFB800)',
            'progress' => 0,
            'progressMax' => 900,
            'points' => 15
        ],
        [ 
            'id' => 'veteran', 
            'name' => 'Чатланин', 
            'description' => 'Решите 100 судоку за любое время', 
            'unlocked' => false, 
            'icon' => 'fa-user-tie', 
            'color' => 'linear-gradient(135deg, #835003, #d5a582)',
            'progress' => 0,
            'progressMax' => 100,
            'points' => 100
        ],
        [ 
            'id' => 'master', 
            'name' => 'Эцилопп', 
            'description' => 'Решите 500 судоку за любое время', 
            'unlocked' => false, 
            'icon' => 'fa-user-ninja', 
            'color' => 'linear-gradient(135deg, #af52de, #CC7700)',
            'progress' => 0,
            'progressMax' => 500,
            'points' => 500
        ],
        [ 
            'id' => 'professional', 
            'name' => 'Господин ПЖ', 
            'description' => 'Решите 1000 судоку за любое время', 
            'unlocked' => false, 
            'icon' => 'fa-crown', 
            'color' => 'linear-gradient(135deg, #30dbff, #CC2444)',
            'progress' => 0,
            'progressMax' => 1000,
            'points' => 1000
        ]
    ];

    // Получаем сохраненные достижения пользователя
    $stmt = $pdo->prepare("SELECT * FROM user_achievements WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $userAchievements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Создаем мапу для быстрого доступа к сохраненным достижениям
    $userAchievementsMap = [];
    foreach ($userAchievements as $ua) {
        $userAchievementsMap[$ua['achievement_id']] = $ua;
    }
    
    // Объединяем с достижениями по умолчанию
    $mergedAchievements = [];
    foreach ($defaultAchievements as $achievement) {
        $achievementId = $achievement['id'];
        
        if (isset($userAchievementsMap[$achievementId])) {
            $userAchievement = $userAchievementsMap[$achievementId];
            
            // Обновляем данные из базы
            $achievement['unlocked'] = (bool)$userAchievement['unlocked'];
            $achievement['progress'] = (int)$userAchievement['progress'];
            
            // Сохраняем unlocked_at если есть
            if ($userAchievement['unlocked_at']) {
                $achievement['unlockedAt'] = $userAchievement['unlocked_at'];
            }
        }
        
        $mergedAchievements[] = $achievement;
    }
    
    echo json_encode([
        'success' => true,
        'achievements' => $mergedAchievements
    ]);

} catch (PDOException $e) {
    error_log("Error getting achievements: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'Database error',
        'achievements' => []
    ]);
}
?>