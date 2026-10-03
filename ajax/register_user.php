<?php
require_once '../includes/session.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$name     = trim($_POST['name']     ?? '');
$email    = strtolower(trim($_POST['email']    ?? ''));
$password = $_POST['password'] ?? '';

if (strlen($name) < 2 || strlen($name) > 80) {
    echo json_encode(['success' => false, 'message' => 'ERROR: Name must be 2–80 characters.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'ERROR: Invalid email address.']);
    exit;
}
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'ERROR: Password must be at least 6 characters.']);
    exit;
}

$checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$checkStmt->bind_param('s', $email);
$checkStmt->execute();
$checkStmt->store_result();
if ($checkStmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'ERROR: This email is already registered. Please login.']);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (name, age, email, password, created_at) VALUES (?, 0, ?, ?, NOW())");
$stmt->bind_param('sss', $name, $email, $hash);

if ($stmt->execute()) {
    $newId = $conn->insert_id;
    $conn->query("INSERT INTO game_progress (user_id, current_level, coins, free_hint_available, hints_bought) VALUES ($newId, 1, 0, 1, 0)");
    setPlayer($newId, $name, $email);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'ERROR: Could not create account. Try again.']);
}

$stmt->close();
$conn->close();
