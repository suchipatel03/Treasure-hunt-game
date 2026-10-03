<?php
require_once '../includes/session.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$email    = strtolower(trim($_POST['email']    ?? ''));
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'ERROR: Email and password are required.']);
    exit;
}

$stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'ERROR: No account found with that email.']);
    $stmt->close();
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

if (!password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'ERROR: Incorrect password.']);
    exit;
}

// Ensure game_progress exists (safety check for older accounts)
$gpCheck = $conn->query("SELECT id FROM game_progress WHERE user_id = {$user['id']} LIMIT 1");
if ($gpCheck && $gpCheck->num_rows === 0) {
    $conn->query("INSERT INTO game_progress (user_id, current_level, coins, free_hint_available, hints_bought) VALUES ({$user['id']}, 1, 0, 1, 0)");
}

setPlayer($user['id'], $user['name'], $user['email']);
echo json_encode(['success' => true]);

$conn->close();
