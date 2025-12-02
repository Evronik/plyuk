<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

try {
    // Подготавливаем данные - используем board_data и solution_data
    $board_data = json_encode($input['board'] ?? []);
    $solution_data = json_encode($input['solution'] ?? []);
    $fixed_cells = json_encode($input['fixedCells'] ?? []);
    $difficulty = $input['difficulty'] ?? 'easy';
    $seconds = $input['seconds'] ?? 0;
    $mistakes = $input['mistakes'] ?? 0;
    $hints_used = $input['hintsUsed'] ?? 0;
    $hints_left = $input['hintsLeft'] ?? 3;
    $was_solved = $input['wasSolved'] ? 1 : 0;
    $game_lost = $input['gameLost'] ? 1 : 0;

    // Проверяем корректность данных перед сохранением
    if (empty($board_data) || $board_data === 'null') {
        error_log("Invalid board data for user $user_id");
        echo json_encode(['success' => false, 'error' => 'Invalid board data']);
        exit;
    }

    // Используем новые колонки board_data и solution_data
    $stmt = $pdo->prepare("
        INSERT INTO user_games 
        (user_id, board_data, solution_data, fixed_cells, difficulty, seconds, mistakes, hints_used, hints_left, was_solved, game_lost) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            board_data = VALUES(board_data),
            solution_data = VALUES(solution_data),
            fixed_cells = VALUES(fixed_cells),
            difficulty = VALUES(difficulty),
            seconds = VALUES(seconds),
            mistakes = VALUES(mistakes),
            hints_used = VALUES(hints_used),
            hints_left = VALUES(hints_left),
            was_solved = VALUES(was_solved),
            game_lost = VALUES(game_lost),
            updated_at = NOW()
    ");
    
    $success = $stmt->execute([
        $user_id,
        $board_data,
        $solution_data,
        $fixed_cells,
        $difficulty,
        $seconds,
        $mistakes,
        $hints_used,
        $hints_left,
        $was_solved,
        $game_lost
    ]);
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Game saved successfully',
            'debug' => [
                'seconds' => $seconds,
                'difficulty' => $difficulty,
                'wasSolved' => $was_solved,
                'columns_used' => ['user_id', 'board_data', 'solution_data', 'fixed_cells', 'difficulty', 'seconds', 'mistakes', 'hints_used', 'hints_left', 'was_solved', 'game_lost']
            ]
        ]);
    } else {
        $errorInfo = $stmt->errorInfo();
        error_log("Save game failed for user $user_id: " . json_encode($errorInfo));
        echo json_encode([
            'success' => false, 
            'error' => 'Save failed',
            'debug' => $errorInfo
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Save game error for user $user_id: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>