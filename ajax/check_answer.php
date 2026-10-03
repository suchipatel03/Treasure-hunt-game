<?php
require_once '../includes/session.php';
require_once '../config/db.php';

header('Content-Type: application/json');

$isGuest = isGuest();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || (!isUserRegistered() && !$isGuest)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

$submitted   = strtolower(trim($_POST['answer'] ?? ''));
$levelNumber = intval($_POST['level_number'] ?? 0);

if (!$submitted || $levelNumber < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

// ── Guest path — state lives in session ──────────────────────────────────────
if ($isGuest) {
    $gp           = getGuestProgress();
    $currentLevel = intval($gp['current_level']);

    if ($levelNumber !== $currentLevel) {
        echo json_encode(['success' => false, 'message' => 'Answer the current terminal first.']);
        exit;
    }

    $qRes = $conn->query("SELECT answer FROM questions WHERE level_number = $levelNumber LIMIT 1");
    if (!$qRes || $qRes->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Terminal not found.']);
        exit;
    }
    $correctAnswer = strtolower(trim($qRes->fetch_assoc()['answer']));

    $gp['level_attempts'][$levelNumber] = ($gp['level_attempts'][$levelNumber] ?? 0) + 1;

    if ($submitted === $correctAnswer) {
        $newCoins  = intval($gp['coins']) + 100;
        $nextLevel = $levelNumber + 1;
        $gp['coins']          = $newCoins;
        $gp['current_level']  = $nextLevel;
        $gp['levels_done'][$levelNumber] = true;
        $_SESSION['guest_progress'] = $gp;

        $totalRes = $conn->query("SELECT COUNT(*) AS total FROM questions");
        $total    = intval($totalRes->fetch_assoc()['total']);

        if ($nextLevel > $total) {
            echo json_encode(['success' => true, 'correct' => true, 'finished' => true, 'new_coins' => $newCoins, 'message' => 'MISSION COMPLETE']);
        } else {
            echo json_encode(['success' => true, 'correct' => true, 'finished' => false, 'next_level' => $nextLevel, 'new_coins' => $newCoins, 'message' => 'DECRYPTION SUCCESSFUL']);
        }
    } else {
        $_SESSION['guest_progress'] = $gp;
        echo json_encode(['success' => true, 'correct' => false, 'attempts' => $gp['level_attempts'][$levelNumber], 'message' => 'DECRYPTION FAILED — KEY REJECTED']);
    }

    $conn->close();
    exit;
}

// ── Logged-in path — state lives in DB ───────────────────────────────────────
$userId = intval(getPlayerId());

$gpRes = $conn->query("SELECT id, current_level, coins FROM game_progress WHERE user_id = $userId LIMIT 1");
if (!$gpRes || $gpRes->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'No game session found. Please restart.']);
    exit;
}
$gp   = $gpRes->fetch_assoc();
$gpId = intval($gp['id']);

if ($levelNumber !== intval($gp['current_level'])) {
    echo json_encode(['success' => false, 'message' => 'Answer the current terminal first.']);
    exit;
}

$qRes = $conn->query("SELECT answer FROM questions WHERE level_number = $levelNumber LIMIT 1");
if (!$qRes || $qRes->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Terminal not found.']);
    exit;
}
$correctAnswer = strtolower(trim($qRes->fetch_assoc()['answer']));

$lpRes = $conn->query("SELECT id, attempts FROM level_progress WHERE progress_id = $gpId AND level_number = $levelNumber LIMIT 1");
$lp    = $lpRes ? $lpRes->fetch_assoc() : null;

if ($submitted === $correctAnswer) {
    $newCoins = intval($gp['coins']) + 100;

    if ($lp) {
        $attempts = intval($lp['attempts']) + 1;
        $conn->query("UPDATE level_progress SET completed = 1, attempts = $attempts, completed_at = NOW() WHERE id = {$lp['id']}");
    } else {
        $conn->query("INSERT INTO level_progress (progress_id, level_number, completed, attempts, completed_at) VALUES ($gpId, $levelNumber, 1, 1, NOW())");
    }

    $totalRes  = $conn->query("SELECT COUNT(*) AS total FROM questions");
    $total     = intval($totalRes->fetch_assoc()['total']);
    $nextLevel = $levelNumber + 1;

    $conn->query("UPDATE game_progress SET current_level = $nextLevel, coins = $newCoins WHERE id = $gpId");

    if ($nextLevel > $total) {
        echo json_encode(['success' => true, 'correct' => true, 'finished' => true, 'new_coins' => $newCoins, 'message' => 'MISSION COMPLETE']);
    } else {
        echo json_encode(['success' => true, 'correct' => true, 'finished' => false, 'next_level' => $nextLevel, 'new_coins' => $newCoins, 'message' => 'DECRYPTION SUCCESSFUL']);
    }
} else {
    if ($lp) {
        $attempts = intval($lp['attempts']) + 1;
        $conn->query("UPDATE level_progress SET attempts = $attempts WHERE id = {$lp['id']}");
    } else {
        $conn->query("INSERT INTO level_progress (progress_id, level_number, completed, attempts) VALUES ($gpId, $levelNumber, 0, 1)");
        $attempts = 1;
    }

    echo json_encode(['success' => true, 'correct' => false, 'attempts' => $attempts, 'message' => 'DECRYPTION FAILED — KEY REJECTED']);
}

$conn->close();
