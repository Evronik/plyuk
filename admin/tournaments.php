<?php
require_once '../config.php';

// Восстановить проверки
if (!is_logged_in()) {
    header('Location: ../login.php');
    exit;
}

if (!is_admin()) {
    die('Доступ запрещен. Только для администраторов.');
}

// Обработка действий
if ($_POST['action'] ?? '') {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'create':
            create_tournament($_POST);
            break;
        case 'update':
            update_tournament($_POST);
            break;
        case 'delete':
            delete_tournament($_POST['id']);
            break;
    }
}

function create_tournament($data) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO tournaments (name, description, entry_fee, prize_pool, max_players, difficulty, status, start_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['name'],
            $data['description'],
            $data['entry_fee'],
            $data['prize_pool'],
            $data['max_players'],
            $data['difficulty'],
            $data['status'],
            $data['start_time']
        ]);
        
        $_SESSION['message'] = 'Турнир создан успешно!';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Ошибка создания турнира: ' . $e->getMessage();
    }
}

function update_tournament($data) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE tournaments SET name=?, description=?, entry_fee=?, prize_pool=?, max_players=?, difficulty=?, status=?, start_time=? WHERE id=?");
        $stmt->execute([
            $data['name'],
            $data['description'],
            $data['entry_fee'],
            $data['prize_pool'],
            $data['max_players'],
            $data['difficulty'],
            $data['status'],
            $data['start_time'],
            $data['id']
        ]);
        
        $_SESSION['message'] = 'Турнир обновлен успешно!';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Ошибка обновления турнира: ' . $e->getMessage();
    }
}

function delete_tournament($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM tournaments WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['message'] = 'Турнир удален успешно!';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Ошибка удаления турнира: ' . $e->getMessage();
    }
}

// Получаем список турниров
$stmt = $pdo->query("SELECT * FROM tournaments ORDER BY created_at DESC");
$tournaments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Управление турнирами</title>
    <style>
        .admin-panel { max-width: 1200px; margin: 20px auto; padding: 20px; }
        .form-group { margin: 10px 0; }
        .tournament-list { margin-top: 30px; }
        .tournament-item { border: 1px solid #ddd; padding: 15px; margin: 10px 0; }
        .message { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="admin-panel">
        <h1>Управление турнирами</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <h2>Создать турнир</h2>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            
            <div class="form-group">
                <label>Название:</label>
                <input type="text" name="name" required>
            </div>
            
            <div class="form-group">
                <label>Описание:</label>
                <textarea name="description" required></textarea>
            </div>
            
            <div class="form-group">
                <label>Взнос:</label>
                <input type="number" name="entry_fee" value="0" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label>Призовой фонд:</label>
                <input type="number" name="prize_pool" value="50" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label>Макс. игроков:</label>
                <input type="number" name="max_players" value="10" required>
            </div>
            
            <div class="form-group">
                <label>Сложность:</label>
                <select name="difficulty" required>
                    <option value="easy">Легкий</option>
                    <option value="medium">Средний</option>
                    <option value="hard">Трудный</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Статус:</label>
                <select name="status" required>
                    <option value="registration">Регистрация</option>
                    <option value="active">Активный</option>
                    <option value="completed">Завершен</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Время начала:</label>
                <input type="datetime-local" name="start_time" required>
            </div>
            
            <button type="submit">Создать турнир</button>
        </form>

        <div class="tournament-list">
            <h2>Список турниров</h2>
            
            <?php foreach ($tournaments as $tournament): ?>
            <div class="tournament-item">
                <h3><?= htmlspecialchars($tournament['name']) ?> (ID: <?= $tournament['id'] ?>)</h3>
                <p><?= htmlspecialchars($tournament['description']) ?></p>
                <p>Взнос: <?= $tournament['entry_fee'] ?> | Приз: <?= $tournament['prize_pool'] ?> | Игроков: <?= $tournament['max_players'] ?></p>
                <p>Сложность: <?= $tournament['difficulty'] ?> | Статус: <?= $tournament['status'] ?> | Начало: <?= $tournament['start_time'] ?></p>
                
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $tournament['id'] ?>">
                    <button type="submit" onclick="return confirm('Удалить турнир?')">Удалить</button>
                </form>
                
                <button onclick="editTournament(<?= htmlspecialchars(json_encode($tournament)) ?>)">Редактировать</button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
    function editTournament(tournament) {
        // Заполняем форму данными турнира для редактирования
        document.querySelector('input[name="name"]').value = tournament.name;
        document.querySelector('textarea[name="description"]').value = tournament.description;
        document.querySelector('input[name="entry_fee"]').value = tournament.entry_fee;
        document.querySelector('input[name="prize_pool"]').value = tournament.prize_pool;
        document.querySelector('input[name="max_players"]').value = tournament.max_players;
        document.querySelector('select[name="difficulty"]').value = tournament.difficulty;
        document.querySelector('select[name="status"]').value = tournament.status;
        document.querySelector('input[name="start_time"]').value = tournament.start_time.replace(' ', 'T').substring(0, 16);
        
        // Меняем действие формы на обновление
        document.querySelector('input[name="action"]').value = 'update';
        document.querySelector('button[type="submit"]').textContent = 'Обновить турнир';
        
        // Добавляем hidden input с ID
        if (!document.querySelector('input[name="id"]')) {
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            document.querySelector('form').appendChild(idInput);
        }
        document.querySelector('input[name="id"]').value = tournament.id;
        
        // Прокручиваем к форме
        document.querySelector('form').scrollIntoView();
    }
    </script>
</body>
</html>