<?php
require_once 'includes/session.php';

$isLoggedIn = isUserRegistered();
$isGuest    = isGuest();
$hasSession = $isLoggedIn || $isGuest;
$playerName = $isLoggedIn ? getPlayerName() : ($isGuest ? getGuestName() : '');

// data-user-mode tells main.js which settings panel content to render
$userMode   = $isLoggedIn ? 'loggedin' : ($isGuest ? 'guest' : 'none');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TREASURE HUNT</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
    /* ─── Navbar auth links (landing state) ─────────────────────── */
    #nav-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .nav-auth-link {
        font-size: 11px;
        letter-spacing: .12em;
        text-transform: uppercase;
        text-decoration: none;
        padding: 6px 12px;
        border: 1px solid var(--border);
        color: var(--text-dim);
        font-family: 'Courier New', monospace;
        transition: all .2s;
    }
    .nav-auth-link:hover { border-color: var(--green-dim); color: var(--green); }
    .nav-auth-link.primary { border-color: var(--green-dim); color: var(--green); }
    .nav-auth-link.primary:hover { background: rgba(0,255,65,.08); }

    /* ─── Landing page layout ────────────────────────────────────── */
    .landing {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: calc(100vh - 44px - 46px);
        padding: 40px 20px;
        text-align: center;
    }
    .landing-logo {
        height: 82px;
        width: 82px;
        object-fit: contain;
        mix-blend-mode: screen;
        animation: flicker 4s infinite;
        margin-bottom: 14px;
    }
    .landing-title {
        font-size: clamp(20px, 4.5vw, 34px);
        color: var(--green);
        letter-spacing: .22em;
        font-weight: normal;
        text-shadow: 0 0 22px rgba(0,255,65,.35);
        margin-bottom: 6px;
    }
    .landing-rule {
        width: 260px;
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--green-dim), transparent);
        margin: 0 auto 10px;
    }
    .landing-sub {
        font-size: 10px;
        color: var(--text-dim);
        letter-spacing: .14em;
        text-transform: uppercase;
        margin-bottom: 38px;
    }

    /* ─── Name form ──────────────────────────────────────────────── */
    .landing-form {
        width: 100%;
        max-width: 340px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .landing-input {
        width: 100%;
        padding: 13px 16px;
        background: rgba(0,255,65,.03);
        border: 1px solid var(--border);
        color: var(--green);
        font-family: 'Courier New', monospace;
        font-size: .95rem;
        letter-spacing: 1px;
        outline: none;
        text-align: center;
        transition: border-color .2s, box-shadow .2s;
    }
    .landing-input:focus {
        border-color: var(--green-dim);
        box-shadow: 0 0 0 2px rgba(0,255,65,.07);
    }
    .landing-input::placeholder { color: var(--text-faint); letter-spacing: .06em; }
    .landing-err {
        font-size: 11px;
        color: var(--danger);
        letter-spacing: .04em;
        min-height: 14px;
    }
    .btn-play-guest {
        width: 100%;
        padding: 13px;
        background: transparent;
        border: 1px solid var(--green-dim);
        color: var(--green);
        font-family: 'Courier New', monospace;
        font-size: .85rem;
        font-weight: bold;
        letter-spacing: .18em;
        text-transform: uppercase;
        cursor: pointer;
        transition: all .2s;
    }
    .btn-play-guest:hover {
        background: rgba(0,255,65,.06);
        box-shadow: 0 0 18px rgba(0,255,65,.15);
    }
    .landing-note {
        margin-top: 22px;
        font-size: 11px;
        color: var(--text-dim);
        letter-spacing: .06em;
        line-height: 1.9;
    }
    .landing-note a { color: var(--green-dim); text-decoration: none; }
    .landing-note a:hover { color: var(--green); }

    /* ─── Menu state: guest banner ───────────────────────────────── */
    .guest-bar {
        max-width: 720px; margin: 0 auto 18px;
        padding: 10px 16px;
        border: 1px solid var(--border);
        border-left: 2px solid var(--amber);
        color: var(--amber); font-size: .76rem;
        letter-spacing: .07em;
        display: flex; align-items: center;
        justify-content: space-between; gap: 12px; flex-wrap: wrap;
    }
    .guest-bar a { color: var(--green-dim); text-decoration: none; }
    .guest-bar a:hover { color: var(--green); }

    /* ─── Locked menu card ───────────────────────────────────────── */
    .menu-card-locked {
        opacity: .45;
        cursor: not-allowed;
        pointer-events: none;
        position: relative;
    }
    .lock-badge {
        position: absolute; top: 8px; right: 8px;
        font-size: 9px; letter-spacing: .1em; color: var(--text-dim);
        border: 1px solid var(--border); padding: 2px 6px; text-transform: uppercase;
    }
    </style>
</head>
<body data-user-mode="<?= $userMode ?>">
<div class="bg-wrapper">

<?php if (!$hasSession): ?>
<!-- ═══════════════════════ LANDING PAGE ═══════════════════════ -->

<nav class="navbar">
    <a class="navbar-brand" href="index.php">
        <img src="images/logo.png" class="nav-logo" alt="TH"> TREASURE HUNT
    </a>
    <div id="nav-actions">
        <!-- ⚙ gear injected here by main.js -->
        <a href="login.php"   class="nav-auth-link">SIGN IN</a>
        <a href="signup.php"  class="nav-auth-link primary">SIGN UP</a>
    </div>
</nav>

<main class="main-content">
    <div class="landing">

        <img src="images/logo.png" class="landing-logo" alt="TH">
        <h1 class="landing-title">TREASURE HUNT</h1>
        <div class="landing-rule"></div>
        <p class="landing-sub">// DECRYPT THE FILES &nbsp;&#x25a0;&nbsp; FIND THE CORE &nbsp;&#x25a0;&nbsp; CLAIM ACCESS</p>

        <form class="landing-form" action="start_guest.php" method="POST" id="landing-form">
            <input type="text" name="name" id="landing-name" class="landing-input"
                   placeholder="ENTER YOUR NAME..." maxlength="50" autocomplete="off" autofocus>
            <div class="landing-err" id="landing-err"></div>
            <button type="submit" class="btn-play-guest">&gt; PLAY AS GUEST</button>
        </form>

        <div class="landing-note">
            // Sign in to save progress &amp; appear on the leaderboard.<br>
            // Guest session ends when you close the browser.<br>
            <a href="login.php">&gt; SIGN IN</a> &nbsp;&#x25a0;&nbsp; <a href="signup.php">&gt; CREATE ACCOUNT</a>
        </div>

    </div>
</main>

<?php else: ?>
<!-- ═══════════════════════ MAIN MENU ═══════════════════════════ -->

<nav class="navbar">
    <a class="navbar-brand" href="index.php">
        <img src="images/logo.png" class="nav-logo" alt="TH"> TREASURE HUNT
    </a>
    <?php if ($isLoggedIn): ?>
        <div class="navbar-player">
            LOGGED IN: <span class="js-player-name"><?= htmlspecialchars(strtoupper($playerName)) ?></span>
        </div>
        <button class="btn-logout" id="logout-btn">DISCONNECT</button>
    <?php else: ?>
        <div class="navbar-player" style="color:var(--amber);">
            GUEST: <span class="js-player-name"><?= htmlspecialchars(strtoupper($playerName)) ?></span>
        </div>
        <div id="nav-actions">
            <a href="login.php" class="nav-auth-link primary">SIGN IN</a>
        </div>
    <?php endif; ?>
</nav>

<main class="main-content">

    <section class="hero">
        <img src="images/logo.png" class="hero-logo" alt="TH">
        <h1 class="hero-title">TREASURE HUNT</h1>
        <div class="hero-divider"></div>
        <p class="hero-subtitle">// DECRYPT THE FILES &nbsp;&#x25a0;&nbsp; FIND THE CORE &nbsp;&#x25a0;&nbsp; CLAIM ACCESS</p>
    </section>

    <?php if ($isGuest): ?>
    <div class="guest-bar">
        <span>&gt; GUEST MODE — progress is not saved after you close the browser</span>
        <span>
            <a href="signup.php">&gt; SIGN UP</a> &nbsp;/&nbsp;
            <a href="login.php">&gt; LOG IN</a> to save your score
        </span>
    </div>
    <?php else: ?>
    <div class="welcome-banner">
        &gt; SESSION ACTIVE — AGENT: <strong class="js-player-name"><?= htmlspecialchars(strtoupper($playerName)) ?></strong>
        — SELECT OPERATION BELOW
    </div>
    <?php endif; ?>

    <div class="menu-grid">

        <a href="game.php" class="menu-card primary">
            <span class="menu-card-icon">🔓</span>
            <div class="menu-card-title">Start Hunt</div>
            <div class="menu-card-desc">Access the terminal grid and begin decryption</div>
        </a>

        <?php if ($isGuest): ?>
        <div class="menu-card menu-card-locked" style="position:relative;">
            <span class="lock-badge">// LOGIN REQUIRED</span>
            <span class="menu-card-icon">📊</span>
            <div class="menu-card-title">Leaderboard</div>
            <div class="menu-card-desc">Sign in to view ranked agent clearance logs</div>
        </div>
        <?php else: ?>
        <a href="leaderboard.php" class="menu-card">
            <span class="menu-card-icon">📊</span>
            <div class="menu-card-title">Leaderboard</div>
            <div class="menu-card-desc">View ranked agent clearance logs</div>
        </a>
        <?php endif; ?>

        <a href="instructions.php" class="menu-card">
            <span class="menu-card-icon">📋</span>
            <div class="menu-card-title">How to Play</div>
            <div class="menu-card-desc">Read operational briefing and protocols</div>
        </a>

        <?php if ($isGuest): ?>
        <div class="menu-card menu-card-locked" style="position:relative;">
            <span class="lock-badge">// LOGIN REQUIRED</span>
            <span class="menu-card-icon">📡</span>
            <div class="menu-card-title">My Progress</div>
            <div class="menu-card-desc">Sign in to check your decryption status log</div>
        </div>
        <?php else: ?>
        <a href="progress.php" class="menu-card">
            <span class="menu-card-icon">📡</span>
            <div class="menu-card-title">My Progress</div>
            <div class="menu-card-desc">Check your decryption status log</div>
        </a>
        <?php endif; ?>

    </div>
</main>
<?php endif; ?>

<footer class="site-footer">
    SYSTEM v1.0 &nbsp;&#x25a0;&nbsp; CREATED BY SUCHI PATEL &amp; KRISHNAPRIYA M.
</footer>
</div>

<div class="toast" id="toast"></div>
<script src="js/sounds.js?v=2"></script>
<script src="js/main.js?v=2"></script>
<?php if (!$hasSession): ?>
<script>
document.getElementById('landing-form').addEventListener('submit', function(e) {
    var name = document.getElementById('landing-name').value.trim();
    var err  = document.getElementById('landing-err');
    if (name.length < 2) {
        e.preventDefault();
        err.textContent = 'ERROR: Name must be at least 2 characters.';
        document.getElementById('landing-name').focus();
    } else {
        err.textContent = '';
    }
});
</script>
<?php endif; ?>
</body>
</html>
