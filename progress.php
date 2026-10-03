<?php
require_once 'includes/session.php';
if (!isUserRegistered()) {
    header('Location: login.php');
    exit;
}

require_once 'config/db.php';
$playerId = intval(getPlayerId());

// Get game_progress id for this user
$gpRes = $conn->query("SELECT id FROM game_progress WHERE user_id = $playerId LIMIT 1");
$gpRow = $gpRes ? $gpRes->fetch_assoc() : null;
$gpId  = $gpRow ? intval($gpRow['id']) : 0;

// Join questions with level_progress through game_progress
$result = $conn->query("
    SELECT q.level_number, q.question_text,
           lp.completed, lp.completed_at, lp.attempts
    FROM questions q
    LEFT JOIN level_progress lp
        ON lp.level_number = q.level_number
        AND lp.progress_id = $gpId
    ORDER BY q.level_number ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STATUS // DECRYPTION LOG</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .progress-list { width:100%; max-width:680px; display:flex; flex-direction:column; gap:10px; }
        .progress-item { display:flex; align-items:center; gap:16px; background:var(--bg-panel); border:1px solid var(--border); border-radius:3px; padding:14px 20px; transition:border-color 0.2s; }
        .progress-item.done { border-color:var(--green-dark); }
        .level-icon { font-size:1.3rem; flex-shrink:0; width:30px; text-align:center; }
        .level-info { flex:1; }
        .level-num { font-size:0.7rem; color:var(--green-dim); letter-spacing:2px; text-transform:uppercase; margin-bottom:3px; }
        .level-text { font-size:0.87rem; color:var(--text); letter-spacing:0.5px; }
        .level-meta { font-size:0.72rem; color:var(--text-faint); margin-top:3px; letter-spacing:0.5px; }
        .level-status { flex-shrink:0; font-size:0.75rem; letter-spacing:1px; text-transform:uppercase; }
        .badge-done { color:var(--success); text-shadow:0 0 8px rgba(0,255,65,0.4); }
        .badge-pending { color:var(--text-faint); }
        .summary-bar { max-width:680px; width:100%; background:var(--bg-panel); border:1px solid var(--border); border-left:2px solid var(--green-dim); border-radius:3px; padding:14px 22px; display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; gap:16px; flex-wrap:wrap; }
        .summary-bar span { color:var(--text-dim); font-size:0.82rem; letter-spacing:1px; text-transform:uppercase; }
        .summary-bar strong { color:var(--green); font-size:1.05rem; text-shadow:0 0 8px rgba(0,255,65,0.3); }
    </style>
</head>
<body>
<div class="bg-wrapper">
<nav class="navbar">
    <a class="navbar-brand" href="index.php"><img src="images/logo.png" class="nav-logo" alt="TH"> TREASURE HUNT</a>
    <div class="navbar-player">LOGGED IN: <span><?= htmlspecialchars(getPlayerName()) ?></span></div>
    <button class="btn-logout" id="logout-btn">DISCONNECT</button>
</nav>
<main class="main-content">

    <section class="hero" style="margin-bottom:32px;">
        <span class="hero-emoji">📡</span>
        <h1 class="hero-title" style="font-size:2rem;">DECRYPTION LOG</h1>
        <div class="hero-divider"></div>
        <p class="hero-subtitle">// Agent clearance status — <?= htmlspecialchars(strtoupper(getPlayerName())) ?></p>
    </section>

    <?php
    $rows = [];
    $done = 0;
    if ($result) {
        while ($r = $result->fetch_assoc()) {
            $rows[] = $r;
            if ($r['completed']) $done++;
        }
    }
    $total = count($rows);
    $pct   = $total > 0 ? round(($done / $total) * 100) : 0;
    ?>

    <div class="summary-bar">
        <span>Terminals cleared:</span>
        <strong><?= $done ?> / <?= $total ?></strong>
        <span>Completion: <?= $pct ?>%</span>
    </div>

    <div class="progress-list">
    <?php foreach ($rows as $row):
        $isDone   = (bool)$row['completed'];
        $icon     = $isDone ? '✅' : '🔒';
        $cls      = $isDone ? 'done' : '';
        $attempts = intval($row['attempts']);
    ?>
        <div class="progress-item <?= $cls ?>">
            <span class="level-icon"><?= $icon ?></span>
            <div class="level-info">
                <div class="level-num">Terminal <?= intval($row['level_number']) ?></div>
                <div class="level-text"><?= $isDone ? htmlspecialchars($row['question_text']) : '[ENCRYPTED — not yet accessed]' ?></div>
                <?php if ($attempts > 0): ?>
                <div class="level-meta">Attempts: <?= $attempts ?></div>
                <?php endif; ?>
            </div>
            <div class="level-status">
                <?php if ($isDone): ?>
                    <span class="badge-done">// CLEARED</span>
                <?php else: ?>
                    <span class="badge-pending">// LOCKED</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($rows)): ?>
        <p style="color:var(--text-dim);font-size:0.85rem;letter-spacing:1px;text-align:center;padding:24px 0;">// NO DATA — Terminal list is empty.</p>
    <?php endif; ?>
    </div>

    <br><br>
    <div style="display:flex;gap:14px;flex-wrap:wrap;justify-content:center;">
        <a href="game.php" class="menu-card primary" style="max-width:220px;text-decoration:none;">
            <span class="menu-card-icon">🔓</span>
            <div class="menu-card-title">Continue Hunt</div>
        </a>
        <a href="index.php" class="menu-card" style="max-width:220px;text-decoration:none;">
            <span class="menu-card-icon">⬅️</span>
            <div class="menu-card-title">Back to Base</div>
        </a>
    </div>

</main>
<footer class="site-footer">SYSTEM v1.0 &nbsp;&#x25a0;&nbsp; CREATED BY SUCHI PATEL &amp; KRISHNAPRIYA M.</footer>
</div>
<div class="toast" id="toast"></div>
<script src="js/sounds.js"></script>
<script src="js/main.js"></script>
</body>
</html>
