<?php
require_once 'config.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'Итоги турниров - ' . APP_NAME;
include 'header.php';

// Получаем завершенные турниры с результатами пользователя
$completedTournaments = getCompletedTournamentsWithResults($_SESSION['user_id']);
?>

<div class="container">
    <div class="tournament-results-header">
        <h1><i class="fas fa-trophy"></i> Итоги турниров</h1>
        <p>История вашего участия в завершенных турнирах</p>
    </div>

    <div class="tournament-results-container">
        <?php if (empty($completedTournaments)): ?>
            <div class="empty-results">
                <i class="fas fa-inbox"></i>
                <h3>Пока нет завершенных турниров</h3>
                <p>Участвуйте в турнирах, чтобы видеть здесь свои результаты!</p>
            </div>
        <?php else: ?>
            <div class="results-filters">
                <button class="filter-btn active" data-filter="all">Все турниры</button>
                <button class="filter-btn" data-filter="prize">С выигрышем</button>
                <button class="filter-btn" data-filter="top3">Топ-3</button>
            </div>

            <div class="tournament-results-list">
                <?php foreach ($completedTournaments as $tournament): ?>
                    <div class="tournament-result-card" data-prize="<?= $tournament['prize'] > 0 ? 'prize' : '' ?>" 
                         data-position="<?= $tournament['position'] <= 3 ? 'top3' : '' ?>">
                        <div class="result-header">
                            <h3><?= htmlspecialchars($tournament['name']) ?></h3>
                            <span class="tournament-date">
                                <?= date('d.m.Y', strtotime($tournament['completed_at'])) ?>
                            </span>
                        </div>
                        
                        <div class="result-stats">
                            <div class="stat">
                                <span class="label">Место:</span>
                                <span class="value position-<?= $tournament['position'] ?>">
                                    <?= $tournament['position'] ?>
                                    <?php if ($tournament['position'] <= 3): ?>
                                        <i class="fas fa-trophy"></i>
                                    <?php endif; ?>
                                </span>
                            </div>
                            
                            <div class="stat">
                                <span class="label">Очки:</span>
                                <span class="value"><?= $tournament['score'] ?></span>
                            </div>
                            
                            <div class="stat">
                                <span class="label">Награда:</span>
                                <span class="value prize-amount">
                                    <?= $tournament['prize'] > 0 ? '+' . $tournament['prize'] . ' чатлов' : '—' ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="result-actions">
                            <button class="btn btn-outline view-details" 
                                    data-tournament-id="<?= $tournament['id'] ?>">
                                Подробнее
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Модальное окно деталей турнира -->
<div id="tournament-details-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Детали турнира</h2>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body" id="tournament-details-content">
            <!-- Контент будет загружаться через AJAX -->
        </div>
    </div>
</div>

<script src="js/tournament_results.js"></script>

<?php include 'footer.php'; ?>