<?php
require_once '../includes/session.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$age  = intval($_POST['age'] ?? 0);

if (strlen($name) < 2 || strlen($name) > 80) {
    echo json_encode(['success' => false, 'message' => 'Name must be 2–80 characters.']);
    exit;
}

if ($age < 5 || $age > 120) {
    echo json_encode(['success' => false, 'message' => 'Age must be between 5 and 120.']);
    exit;
}

$name = $conn->real_escape_string($name);

$check = $conn->query("SELECT id, name, age FROM users WHERE LOWER(name) = LOWER('$name') LIMIT 1");

if ($check && $check->num_rows > 0) {
    $user = $check->fetch_assoc();
    setPlayer($user['id'], $user['name'], $user['age']);
    ensureGameProgress($conn, $user['id']);
    echo json_encode(['success' => true, 'returning' => true, 'message' => 'Welcome back!']);
} else {
    $stmt = $conn->prepare("INSERT INTO users (name, age, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param('si', $name, $age);

    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        setPlayer($newId, $name, $age);
        ensureGameProgress($conn, $newId);
        echo json_encode(['success' => true, 'returning' => false, 'message' => 'Player registered!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Could not save player. Please try again.']);
    }

    $stmt->close();
}

$conn->close();

function ensureGameProgress($conn, $userId) {
    $res = $conn->query("SELECT id FROM game_progress WHERE user_id = $userId LIMIT 1");
    if ($res && $res->num_rows === 0) {
        $conn->query("INSERT INTO game_progress (user_id, current_level, coins, free_hint_available) VALUES ($userId, 1, 0, 1)");
    }
}
