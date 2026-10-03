<?php
require_once 'includes/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isUserRegistered()) {
    $name = trim($_POST['name'] ?? '');
    if (strlen($name) < 2 || strlen($name) > 50) {
        $name = 'AGENT';
    }
    startGuestSession($name);
}

header('Location: index.php');
exit;
