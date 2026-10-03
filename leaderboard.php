<?php
require_once 'includes/session.php';
if (!isUserRegistered()) {
    header('Location: login.php');
    exit;
}
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LEADERBOARD // CLEARANCE LOG</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .leaderboard-table { width:100%; max-width:700px; border-collapse:collapse; margin-top:24px; }
        .leaderboard-table th,
        .leaderboard-table td { padding:11px 18px; text-align:left; border-bottom:1px solid var(--border); font-family:'Courier New',monospace; }
        .leaderboard-table th { color:var(--green-dim); font-size:0.72rem; letter-spacing:2px; text-transform:uppercase; }
        .leaderboard-table td { color:var(--text); font-size:0.9rem; }
        .leaderboard-table tr:hover td { background:rgba(0,255,65,0.03); }
        .rank-1 td { color:var(--amber); text-shadow:0 0 8px var(--amber-dim); }
        .rank-2 td { color:#c0c0c0; }
        .rank-3 td { color:#cd7f32; }
        .empty-state { color:var(--text-dim); font-style:italic; text-align:center; padding:32px 0; letter-spacing:1px; font-size:0.85rem; }
    </style>
</head>
<body>
<div class="bg-wrapper">
<nav class="navbar">
    <a class="navbar-brand" href="index.php"><img src="images/logo.png" class="nav-logo" alt="TH"> TREASURE HUNT</a>
    <?php if (isUserRegistered()): ?>
    <div class="navbar-player">LOGGED IN: <span><?= htmlspecialchars(getPlayerName()) ?></span></div>
    <button class="btn-logout" id="logout-btn">DISCONNECT</button>
    <?php endif; ?>
</nav>
<main class="main-content">

    <section class="hero" style="margin-bottom:32px;">
        <span class="hero-emoji">📊</span>
        <h1 class="hero-title" style="font-size:2rem;">LEADERBOARD</h1>
        <div class="hero-divider"></div>
        <p class="hero-subtitle">// Top agents ranked by terminals decrypted</p>
    </section>

    <?php
    // Join through game_progress since level_progress has no direct user_id
    $result = $conn->query("
        SELECT u.name,
               COUNT(lp.id) AS levels_done,
               MIN(lp.completed_at) AS last_clear
        FROM users u
        LEFT JOIN game_progress gp ON gp.user_id = u.id
        LEFT JOIN level_progress lp ON lp.progress_id = gp.id AND lp.completed = 1
        GROUP BY u.id, u.name
        ORDER BY levels_done DESC, last_clear ASC
        LIMIT 20
    ");
    ?>

    <table class="leaderboard-table">
        <thead>
            <tr>
                <th>RANK</th>
                <th>AGENT</th>
                <th>TERMINALS CLEARED</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0):
            $rank = 1;
            while ($row = $result->fetch_assoc()):
                $cls   = $rank <= 3 ? "rank-$rank" : '';
                $label = ['1' => '[01]', '2' => '[02]', '3' => '[03]'][$rank] ?? sprintf('[%02d]', $rank);
        ?>
            <tr class="<?= $cls ?>">
                <td><?= $label ?></td>
                <td><?= htmlspecialchars(strtoupper($row['name'])) ?></td>
                <td><?= intval($row['levels_done']) ?> / 5</td>
            </tr>
        <?php $rank++; endwhile; else: ?>
            <tr><td colspan="3" class="empty-state">// NO DATA — Be the first agent to complete the hunt.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <br><br>
    <a href="index.php" class="menu-card" style="max-width:220px;text-decoration:none;">
        <span class="menu-card-icon">⬅️</span>
        <div class="menu-card-title">Back to Base</div>
    </a>

</main>
<footer class="site-footer">SYSTEM v1.0 &nbsp;&#x25a0;&nbsp; CREATED BY SUCHI PATEL &amp; KRISHNAPRIYA M.</footer>
</div>
<div class="toast" id="toast"></div>
<script src="js/sounds.js"></script>
<script src="js/main.js"></script>
</body>
</html>
