<?php
require_once 'includes/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BRIEFING // OPERATIONAL PROTOCOL</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .instructions-box { max-width:680px; width:100%; background:var(--bg-panel); border:1px solid var(--border); border-radius:4px; padding:36px 40px; }
        .step { display:flex; gap:18px; margin-bottom:28px; align-items:flex-start; }
        .step-num { flex-shrink:0; width:38px; height:38px; border:1px solid var(--green-dim); border-radius:3px; display:flex; align-items:center; justify-content:center; font-weight:bold; color:var(--green); font-size:0.9rem; font-family:'Courier New',monospace; text-shadow:0 0 8px var(--green); }
        .step-body h3 { color:var(--green); margin-bottom:5px; font-size:0.9rem; letter-spacing:2px; text-transform:uppercase; }
        .step-body p { color:var(--text-dim); font-size:0.85rem; line-height:1.7; letter-spacing:0.5px; }
        .rule-divider { width:100%; height:1px; background:linear-gradient(90deg,transparent,var(--border),transparent); margin:24px 0; }
        .tip-box { background:rgba(0,255,65,0.04); border-left:2px solid var(--green-dim); padding:14px 18px; border-radius:0 3px 3px 0; margin-top:8px; }
        .tip-box p { color:var(--text); font-size:0.85rem; line-height:1.7; letter-spacing:0.5px; }
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
        <span class="hero-emoji">📋</span>
        <h1 class="hero-title" style="font-size:2rem;">OPERATIONAL BRIEFING</h1>
        <div class="hero-divider"></div>
        <p class="hero-subtitle">// Read before entering the complex</p>
    </section>

    <div class="instructions-box">

        <div class="step">
            <div class="step-num">01</div>
            <div class="step-body">
                <h3>Initialize Session</h3>
                <p>Enter your agent name and age on the home terminal. This registers your identity in the system and begins tracking your clearance progress.</p>
            </div>
        </div>

        <div class="step">
            <div class="step-num">02</div>
            <div class="step-body">
                <h3>Read the Encrypted File</h3>
                <p>Each level presents an encrypted question or riddle. Analyze the data carefully — the decryption key is hidden within.</p>
            </div>
        </div>

        <div class="step">
            <div class="step-num">03</div>
            <div class="step-body">
                <h3>Submit Decryption Key</h3>
                <p>Enter your answer and submit. A correct key unlocks the next terminal. An incorrect key triggers a retry — no lockout, keep trying.</p>
            </div>
        </div>

        <div class="step">
            <div class="step-num">04</div>
            <div class="step-body">
                <h3>Clear All Terminals</h3>
                <p>Decrypt all levels to reach the core and complete the mission. Speed determines your rank on the clearance leaderboard.</p>
            </div>
        </div>

        <div class="rule-divider"></div>

        <div class="tip-box">
            <p>// <strong style="color:var(--green);">NOTE:</strong> Progress is auto-saved after each successful decryption. You may close the terminal and reconnect later — your session will resume from the last cleared level.</p>
        </div>

    </div>

    <br><br>
    <div style="display:flex;gap:14px;flex-wrap:wrap;justify-content:center;">
        <a href="game.php" class="menu-card primary" style="max-width:220px;text-decoration:none;">
            <span class="menu-card-icon">🔓</span>
            <div class="menu-card-title">Start Hunt</div>
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
