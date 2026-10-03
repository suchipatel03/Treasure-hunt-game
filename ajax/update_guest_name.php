<?php
require_once '../includes/session.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isGuest()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

$name = trim($_POST['name'] ?? '');
if (strlen($name) < 2 || strlen($name) > 50) {
    echo json_encode(['success' => false, 'message' => 'ERROR: Name must be 2–50 characters.']);
    exit;
}

$_SESSION['guest_name'] = $name;
echo json_encode(['success' => true, 'name' => $name]);
