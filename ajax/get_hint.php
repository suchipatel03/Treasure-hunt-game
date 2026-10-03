<?php
require_once '../includes/session.php';
require_once '../config/db.php';

header('Content-Type: application/json');

$isGuest = isGuest();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || (!isUserRegistered() && !$isGuest)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

$levelNumber = intval($_POST['level_number'] ?? 0);

if ($levelNumber < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid level.']);
    exit;
}

// Get hint text from DB (needed for both paths)
$qRes = $conn->query("SELECT hint_text FROM questions WHERE level_number = $levelNumber LIMIT 1");
if (!$qRes || $qRes->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'No hint available for this terminal.']);
    exit;
}
$hint = $qRes->fetch_assoc()['hint_text'];

// ── Guest path ────────────────────────────────────────────────────────────────
if ($isGuest) {
    $gp          = getGuestProgress();
    $coins       = intval($gp['coins']);
    $freeAvail   = (bool)$gp['free_hint_available'];
    $hintsBought = intval($gp['hints_bought']);

    $cost = $freeAvail ? 0 : ($hintsBought + 1) * 25;

    if (!$freeAvail && $coins < $cost) {
        echo json_encode(['success' => false, 'insufficient' => true, 'cost' => $cost, 'coins' => $coins, 'message' => "INSUFFICIENT POINTS — need $cost, have $coins."]);
        $conn->close();
        exit;
    }

    // Store hint text in session so it survives page reloads
    $gp['level_hints'][$levelNumber] = $hint;

    if ($freeAvail) {
        $gp['free_hint_available'] = 0;
        $newCoins = $coins;
    } else {
        $newCoins    = $coins - $cost;
        $hintsBought = $hintsBought + 1;
        $gp['coins']        = $newCoins;
        $gp['hints_bought'] = $hintsBought;
    }

    $_SESSION['guest_progress'] = $gp;
    $nextCost = ($hintsBought + 1) * 25;

    echo json_encode([
        'success'   => true,
        'hint'      => $hint,
        'was_free'  => $freeAvail,
        'cost'      => $cost,
        'new_coins' => $newCoins,
        'next_cost' => $nextCost,
    ]);

    $conn->close();
    exit;
}

// ── Logged-in path ────────────────────────────────────────────────────────────
$userId = intval(getPlayerId());

$gpRes = $conn->query("SELECT id, coins, free_hint_available, hints_bought FROM game_progress WHERE user_id = $userId LIMIT 1");
if (!$gpRes || $gpRes->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'No game session.']);
    exit;
}
$gp          = $gpRes->fetch_assoc();
$gpId        = intval($gp['id']);
$coins       = intval($gp['coins']);
$freeAvail   = (bool)$gp['free_hint_available'];
$hintsBought = intval($gp['hints_bought']);

$cost = $freeAvail ? 0 : ($hintsBought + 1) * 25;

if (!$freeAvail && $coins < $cost) {
    echo json_encode(['success' => false, 'insufficient' => true, 'cost' => $cost, 'coins' => $coins, 'message' => "INSUFFICIENT POINTS — need $cost, have $coins."]);
    exit;
}

$lpRes = $conn->query("SELECT id FROM level_progress WHERE progress_id = $gpId AND level_number = $levelNumber LIMIT 1");
if ($lpRes && $lpRes->num_rows > 0) {
    $lpId = intval($lpRes->fetch_assoc()['id']);
    $conn->query("UPDATE level_progress SET hint_used = 1 WHERE id = $lpId");
} else {
    $conn->query("INSERT INTO level_progress (progress_id, level_number, completed, attempts, hint_used) VALUES ($gpId, $levelNumber, 0, 0, 1)");
}

if ($freeAvail) {
    $conn->query("UPDATE game_progress SET free_hint_available = 0 WHERE id = $gpId");
    $newCoins = $coins;
} else {
    $newCoins    = $coins - $cost;
    $hintsBought = $hintsBought + 1;
    $conn->query("UPDATE game_progress SET coins = $newCoins, hints_bought = $hintsBought WHERE id = $gpId");
}

$nextCost = ($hintsBought + 1) * 25;

echo json_encode([
    'success'   => true,
    'hint'      => $hint,
    'was_free'  => $freeAvail,
    'cost'      => $cost,
    'new_coins' => $newCoins,
    'next_cost' => $nextCost,
]);

$conn->close();
