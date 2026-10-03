<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isUserRegistered() {
    return isset($_SESSION['player_id']) && !empty($_SESSION['player_id']);
}

function isGuest() {
    return !isUserRegistered() && !empty($_SESSION['guest_mode']);
}

function isPlaying() {
    return isUserRegistered() || isGuest();
}

function getGuestName() {
    return $_SESSION['guest_name'] ?? 'GUEST';
}

function startGuestSession($name = 'GUEST') {
    if (!isUserRegistered() && !isset($_SESSION['guest_mode'])) {
        $_SESSION['guest_mode'] = true;
        $_SESSION['guest_name'] = $name;
        $_SESSION['guest_progress'] = [
            'current_level'       => 1,
            'coins'               => 0,
            'free_hint_available' => 1,
            'hints_bought'        => 0,
            'level_hints'         => [],    // level_number => hint_text
            'level_attempts'      => [],    // level_number => attempt count
            'levels_done'         => [],    // level_number => true
        ];
    }
}

function getGuestProgress() {
    return $_SESSION['guest_progress'] ?? [
        'current_level'       => 1,
        'coins'               => 0,
        'free_hint_available' => 1,
        'hints_bought'        => 0,
        'level_hints'         => [],
        'level_attempts'      => [],
        'levels_done'         => [],
    ];
}

function getPlayerName()  { return $_SESSION['player_name']  ?? ''; }
function getPlayerId()    { return $_SESSION['player_id']    ?? null; }
function getPlayerEmail() { return $_SESSION['player_email'] ?? ''; }

function setPlayer($id, $name, $email = '') {
    $_SESSION['player_id']    = $id;
    $_SESSION['player_name']  = $name;
    $_SESSION['player_email'] = $email;
    // Clear guest session on login
    unset($_SESSION['guest_mode'], $_SESSION['guest_name'], $_SESSION['guest_progress']);
}

function clearPlayer() {
    unset(
        $_SESSION['player_id'],
        $_SESSION['player_name'],
        $_SESSION['player_email'],
        $_SESSION['guest_mode'],
        $_SESSION['guest_name'],
        $_SESSION['guest_progress']
    );
}

function requireLogin($redirect = 'login.php') {
    if (!isUserRegistered()) {
        header("Location: $redirect");
        exit;
    }
}
