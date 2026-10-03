<?php
require_once 'includes/session.php';

// If no session at all, send to name form
if (!isPlaying()) {
    header('Location: start_guest.php');
    exit;
}

$isLoggedIn = isUserRegistered();
$isGuest    = isGuest();

require_once 'config/db.php';

$totalRes    = $conn->query("SELECT COUNT(*) AS total FROM questions");
$totalLevels = intval($totalRes->fetch_assoc()['total']);

if ($isLoggedIn) {
    $userId = intval(getPlayerId());
    $playerName = getPlayerName();

    $gpRes = $conn->query("SELECT id, current_level, coins, free_hint_available, hints_bought FROM game_progress WHERE user_id = $userId LIMIT 1");
    if (!$gpRes || $gpRes->num_rows === 0) {
        $conn->query("INSERT INTO game_progress (user_id, current_level, coins, free_hint_available, hints_bought) VALUES ($userId, 1, 0, 1, 0)");
        $gpRes = $conn->query("SELECT id, current_level, coins, free_hint_available, hints_bought FROM game_progress WHERE user_id = $userId LIMIT 1");
    }
    $gp           = $gpRes->fetch_assoc();
    $gpId         = intval($gp['id']);
    $currentLevel = intval($gp['current_level']);
    $coins        = intval($gp['coins']);
    $freeHint     = (bool)$gp['free_hint_available'];
    $hintsBought  = intval($gp['hints_bought']);

    $finished    = $currentLevel > $totalLevels;
    $isLastLevel = !$finished && ($currentLevel === $totalLevels);

    $question = null;
    $attempts = 0;
    $hintUsed = false;
    if (!$finished) {
        $qRes     = $conn->query("SELECT * FROM questions WHERE level_number = $currentLevel LIMIT 1");
        $question = $qRes ? $qRes->fetch_assoc() : null;

        $lpRes = $conn->query("SELECT attempts, hint_used FROM level_progress WHERE progress_id = $gpId AND level_number = $currentLevel LIMIT 1");
        if ($lpRes && $lpRes->num_rows > 0) {
            $lp       = $lpRes->fetch_assoc();
            $attempts = intval($lp['attempts']);
            $hintUsed = (bool)$lp['hint_used'];
        }
    }
} else {
    // Guest mode — state lives in PHP session
    $playerName  = getGuestName();
    $guestData   = getGuestProgress();
    $currentLevel = intval($guestData['current_level']);
    $coins        = intval($guestData['coins']);
    $freeHint     = (bool)$guestData['free_hint_available'];
    $hintsBought  = intval($guestData['hints_bought']);

    $finished    = $currentLevel > $totalLevels;
    $isLastLevel = !$finished && ($currentLevel === $totalLevels);

    $question = null;
    $attempts = 0;
    $hintUsed = false;
    if (!$finished) {
        $qRes     = $conn->query("SELECT * FROM questions WHERE level_number = $currentLevel LIMIT 1");
        $question = $qRes ? $qRes->fetch_assoc() : null;
        $attempts = intval($guestData['level_attempts'][$currentLevel] ?? 0);
        $hintUsed = isset($guestData['level_hints'][$currentLevel]);
        if ($hintUsed) {
            // Pre-load stored hint text so it stays visible on reload
            $question['_stored_hint'] = $guestData['level_hints'][$currentLevel];
        }
    }
}

$nextHintCost  = $freeHint ? 0 : ($hintsBought + 1) * 25;
$canAffordHint = $freeHint || ($coins >= $nextHintCost);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TERMINAL <?= $currentLevel ?> // TREASURE HUNT</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .game-wrap { width:100%; max-width:720px; display:flex; flex-direction:column; gap:20px; }

        .guest-bar {
            padding:9px 16px; border:1px solid var(--border); border-left:2px solid var(--amber);
            color:var(--amber); font-size:0.75rem; letter-spacing:.08em;
            display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;
        }
        .guest-bar a { color:var(--green); text-decoration:none; }
        .guest-bar a:hover { text-shadow:0 0 8px var(--green); }

        /* Coin bar */
        .coin-bar {
            display:flex; align-items:center; justify-content:space-between;
            background:var(--bg-panel); border:1px solid var(--border);
            border-radius:3px; padding:10px 18px;
        }
        .coin-bar-left  { font-size:0.72rem; color:var(--text-dim); letter-spacing:2px; text-transform:uppercase; }
        .coin-display   { font-size:1rem; font-weight:bold; color:var(--amber); text-shadow:0 0 8px var(--amber-dim); letter-spacing:2px; }
        .coin-label     { font-size:0.7rem; color:var(--amber-dim); letter-spacing:1px; text-transform:uppercase; margin-left:6px; }

        /* Progress pips */
        .level-bar { display:flex; gap:8px; align-items:center; justify-content:center; }
        .level-pip { width:36px; height:6px; border-radius:2px; background:var(--border); transition:background 0.4s; }
        .level-pip.done   { background:var(--green-dim); box-shadow:0 0 6px rgba(0,255,65,0.3); }
        .level-pip.active { background:var(--green);     box-shadow:0 0 10px rgba(0,255,65,0.5); }

        /* Terminal screen */
        .terminal-screen {
            background:var(--bg-panel); border:1px solid var(--border-lit);
            border-radius:4px; padding:28px 32px; position:relative;
        }
        .terminal-screen::before {
            content:''; position:absolute; top:0; left:0; right:0; height:2px;
            background:linear-gradient(90deg,transparent,var(--green),transparent);
        }
        .terminal-header {
            display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;
        }
        .terminal-label   { font-size:0.72rem; color:var(--green-dim); letter-spacing:3px; text-transform:uppercase; }
        .terminal-counter { font-size:0.72rem; color:var(--text-dim);  letter-spacing:2px; }
        .terminal-divider { height:1px; background:linear-gradient(90deg,var(--border),transparent); margin-bottom:20px; }
        .question-text    { font-size:1.05rem; color:var(--text); line-height:1.85; letter-spacing:0.5px; }
        .question-text::before { content:'> '; color:var(--green-dim); }

        /* Answer form */
        .answer-form  { display:flex; flex-direction:column; gap:12px; margin-top:24px; }
        .answer-row   { display:flex; gap:10px; }
        .answer-input {
            flex:1; padding:11px 16px; background:rgba(0,255,65,0.03);
            border:1px solid var(--border); border-radius:3px;
            color:var(--green); font-size:0.95rem; font-family:'Courier New',monospace;
            letter-spacing:1px; outline:none; transition:border-color 0.2s, box-shadow 0.2s;
        }
        .answer-input:focus { border-color:var(--green-dim); box-shadow:0 0 0 2px rgba(0,255,65,0.08); }
        .answer-input::placeholder { color:var(--text-faint); }

        .btn-submit {
            padding:11px 22px; background:transparent; border:1px solid var(--green-dim);
            border-radius:3px; color:var(--green); font-family:'Courier New',monospace;
            font-size:0.82rem; font-weight:bold; letter-spacing:2px; text-transform:uppercase;
            cursor:pointer; white-space:nowrap; transition:all 0.2s;
        }
        .btn-submit:hover    { background:rgba(0,255,65,0.06); box-shadow:0 0 12px rgba(0,255,65,0.15); }
        .btn-submit:disabled { opacity:0.4; cursor:not-allowed; }

        /* Hint row */
        .hint-row { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }

        .btn-hint {
            padding:8px 16px; background:transparent; border:1px solid var(--border);
            border-radius:3px; font-family:'Courier New',monospace;
            font-size:0.75rem; letter-spacing:2px; text-transform:uppercase;
            cursor:pointer; transition:all 0.2s;
        }
        .btn-hint.free     { color:var(--green-dim); border-color:var(--border); }
        .btn-hint.free:hover { border-color:var(--green-dim); color:var(--green); }
        .btn-hint.paid     { color:var(--amber); border-color:var(--amber-dim); }
        .btn-hint.paid:hover { box-shadow:0 0 10px rgba(255,176,0,0.15); }
        .btn-hint.broke    { color:var(--text-faint); border-color:var(--border); cursor:not-allowed; opacity:0.5; }
        .btn-hint:disabled { opacity:0.35; cursor:not-allowed; }

        .attempt-counter { font-size:0.75rem; color:var(--text-faint); letter-spacing:1px; }
        .attempt-counter span { color:var(--danger); }

        .hint-box {
            margin-top:10px; padding:12px 16px;
            background:rgba(255,176,0,0.05); border:1px solid var(--amber-dim);
            border-radius:3px; color:var(--amber); font-size:0.85rem;
            letter-spacing:0.5px; display:none;
        }
        .hint-box.visible { display:block; }
        .hint-box::before { content:'// HINT: '; color:var(--amber-dim); }

        .no-hint-notice {
            font-size:0.75rem; color:var(--danger); letter-spacing:1px;
            text-transform:uppercase; opacity:0.7;
        }

        .feedback {
            padding:12px 18px; border-radius:3px; font-size:0.82rem;
            letter-spacing:1px; text-transform:uppercase; display:none; margin-top:4px;
        }
        .feedback.correct { display:block; border:1px solid var(--green-dim); color:var(--green); background:rgba(0,255,65,0.05); }
        .feedback.wrong   { display:block; border:1px solid var(--danger);    color:var(--danger); background:rgba(255,51,51,0.05); }

        /* Win screen */
        .win-screen {
            background:var(--bg-panel); border:1px solid var(--green); border-radius:4px;
            padding:48px 36px; text-align:center; box-shadow:0 0 40px rgba(0,255,65,0.12);
            position:relative; overflow:hidden;
        }
        .win-screen::before {
            content:''; position:absolute; top:0; left:0; right:0; height:2px;
            background:linear-gradient(90deg,transparent,var(--green),transparent);
        }
        .win-icon  { font-size:3.5rem; display:block; margin-bottom:16px; filter:drop-shadow(0 0 16px var(--green)); animation:flicker 3s infinite; }
        .win-title { font-size:1.8rem; color:var(--green); letter-spacing:6px; text-transform:uppercase; text-shadow:0 0 20px var(--green); margin-bottom:10px; }
        .win-agent { font-size:1.1rem; color:var(--green); letter-spacing:3px; text-transform:uppercase; text-shadow:0 0 12px rgba(0,255,65,.5); margin-bottom:10px; font-weight:bold; }
        .win-sub   { font-size:0.85rem; color:var(--text-dim); letter-spacing:2px; text-transform:uppercase; margin-bottom:8px; line-height:1.8; }
        .win-coins { font-size:1.1rem; color:var(--amber); text-shadow:0 0 10px var(--amber-dim); margin-bottom:28px; letter-spacing:2px; }
        .win-actions { display:flex; gap:14px; justify-content:center; flex-wrap:wrap; }
        .win-guest-prompt {
            margin-top:24px; padding:14px 20px;
            border:1px solid var(--amber-dim); color:var(--amber);
            font-size:0.82rem; letter-spacing:.08em; line-height:1.8;
        }
        .win-guest-prompt a { color:var(--green); text-decoration:none; }
        .win-guest-prompt a:hover { text-shadow:0 0 8px var(--green); }
    </style>
</head>
<body>
<div class="bg-wrapper">
<nav class="navbar">
    <a class="navbar-brand" href="index.php"><img src="images/logo.png" class="nav-logo" alt="TH"> TREASURE HUNT</a>
    <?php if ($isLoggedIn): ?>
        <div class="navbar-player">LOGGED IN: <span><?= htmlspecialchars(strtoupper($playerName)) ?></span></div>
        <button class="btn-logout" id="logout-btn">DISCONNECT</button>
    <?php else: ?>
        <div class="navbar-player" style="color:var(--amber);">GUEST MODE</div>
        <a href="login.php" class="btn-logout" style="text-decoration:none;">&gt; SIGN IN</a>
    <?php endif; ?>
</nav>
<main class="main-content">

<?php if ($finished): ?>
<!-- ═══════════ WIN SCREEN ═══════════ -->
<div class="game-wrap">
    <div class="win-screen">
        <img src="images/logo.png" class="win-icon" style="height:120px;width:120px;object-fit:contain;mix-blend-mode:screen;" alt="TH">
        <div class="win-title">MISSION COMPLETE</div>
        <div class="win-agent">AGENT <?= htmlspecialchars(strtoupper($playerName)) ?>, YOU HAVE COMPLETED THE MISSION.</div>
        <div class="win-sub">
            All terminals decrypted. You have reached the core of the abandoned complex.
        </div>
        <div class="win-coins">FINAL SCORE: <?= $coins ?> PTS</div>

        <?php if ($isGuest): ?>
        <div class="win-guest-prompt">
            // GUEST SESSION — your score is not saved.<br>
            <a href="signup.php">&gt; SIGN UP</a> or <a href="login.php">&gt; LOG IN</a> to save progress and appear on the leaderboard.
        </div>
        <?php endif; ?>

        <div class="win-actions" style="margin-top:20px;">
            <?php if ($isLoggedIn): ?>
            <a href="leaderboard.php" class="menu-card" style="max-width:200px;text-decoration:none;">
                <span class="menu-card-icon">📊</span>
                <div class="menu-card-title">Leaderboard</div>
            </a>
            <?php endif; ?>
            <a href="index.php" class="menu-card" style="max-width:200px;text-decoration:none;">
                <span class="menu-card-icon">⬅️</span>
                <div class="menu-card-title">Back to Base</div>
            </a>
        </div>
    </div>
</div>

<?php elseif (!$question): ?>
<p style="color:var(--danger);letter-spacing:1px;">// ERROR: Terminal data not found. Contact admin.</p>

<?php else: ?>
<!-- ═══════════ ACTIVE GAME ═══════════ -->
<div class="game-wrap">

    <?php if ($isGuest): ?>
    <div class="guest-bar">
        <span>&gt; GUEST MODE — progress is not saved</span>
        <span><a href="signup.php">&gt; SIGN UP</a> &nbsp;/&nbsp; <a href="login.php">&gt; LOG IN</a> to save your score</span>
    </div>
    <?php endif; ?>

    <!-- Coin balance bar -->
    <div class="coin-bar">
        <span class="coin-bar-left">
            <img src="images/logo.png" style="height:22px;width:22px;object-fit:contain;vertical-align:middle;mix-blend-mode:screen;margin-right:6px;" alt="TH">
            Agent: <?= htmlspecialchars(strtoupper($playerName)) ?>
        </span>
        <div>
            <span class="coin-display" id="coin-display"><?= $coins ?></span>
            <span class="coin-label">PTS</span>
        </div>
    </div>

    <!-- Level pips -->
    <div class="level-bar">
        <?php for ($i = 1; $i <= $totalLevels; $i++):
            $pip = $i < $currentLevel ? 'done' : ($i === $currentLevel ? 'active' : '');
        ?>
        <div class="level-pip <?= $pip ?>" title="Terminal <?= $i ?>"></div>
        <?php endfor; ?>
    </div>

    <!-- Terminal screen -->
    <div class="terminal-screen">
        <div class="terminal-header">
            <span class="terminal-label">
                TERMINAL <?= $currentLevel ?> / <?= $totalLevels ?>
                <?= $isLastLevel ? ' &nbsp;[FINAL]' : '' ?>
            </span>
            <span class="terminal-counter">DECRYPTION SEQUENCE ACTIVE</span>
        </div>
        <div class="terminal-divider"></div>
        <div class="question-text"><?= htmlspecialchars($question['question_text']) ?></div>

        <div class="answer-form">
            <div class="answer-row">
                <input type="text" id="answer-input" class="answer-input"
                       placeholder="ENTER DECRYPTION KEY..." autocomplete="off" autofocus>
                <button class="btn-submit" id="submit-btn">&gt; SUBMIT</button>
            </div>

            <div id="feedback" class="feedback"></div>

            <div class="hint-row">
                <?php if ($isLastLevel): ?>
                    <span class="no-hint-notice">// NO HINTS AVAILABLE ON FINAL TERMINAL</span>

                <?php elseif ($hintUsed): ?>
                    <button class="btn-hint" disabled>// HINT USED</button>

                <?php elseif ($freeHint): ?>
                    <button class="btn-hint free" id="hint-btn">&gt; REQUEST HINT [FREE]</button>

                <?php elseif ($canAffordHint): ?>
                    <button class="btn-hint paid" id="hint-btn"
                            data-cost="<?= $nextHintCost ?>">
                        &gt; BUY HINT [&minus;<?= $nextHintCost ?> PTS]
                    </button>

                <?php else: ?>
                    <button class="btn-hint broke" disabled>
                        &gt; HINT [&minus;<?= $nextHintCost ?> PTS] — INSUFFICIENT PTS
                    </button>
                <?php endif; ?>

                <?php if ($attempts > 0): ?>
                <div class="attempt-counter">
                    FAILED ATTEMPTS: <span id="attempt-count"><?= $attempts ?></span>
                </div>
                <?php else: ?>
                <div class="attempt-counter" id="attempt-display" style="display:none;">
                    FAILED ATTEMPTS: <span id="attempt-count">0</span>
                </div>
                <?php endif; ?>
            </div>

            <?php
            $storedHint = $question['_stored_hint'] ?? ($hintUsed ? $question['hint_text'] : '');
            ?>
            <div class="hint-box <?= $hintUsed ? 'visible' : '' ?>" id="hint-box">
                <?= $hintUsed ? htmlspecialchars($storedHint) : '' ?>
            </div>
        </div>
    </div>

    <a href="index.php" class="menu-card" style="max-width:200px;text-decoration:none;align-self:flex-start;">
        <span class="menu-card-icon">⬅️</span>
        <div class="menu-card-title">Back to Base</div>
    </a>

</div><!-- /game-wrap -->
<?php endif; ?>

</main>
<footer class="site-footer">SYSTEM v1.0 &nbsp;&#x25a0;&nbsp; CREATED BY SUCHI PATEL &amp; KRISHNAPRIYA M.</footer>
</div>
<div class="toast" id="toast"></div>
<script src="js/sounds.js"></script>
<script src="js/main.js"></script>

<?php if (!$finished && $question): ?>
<script>
(function () {
    const currentLevel   = <?= $currentLevel ?>;
    const isGuest        = <?= $isGuest ? 'true' : 'false' ?>;
    const submitBtn      = document.getElementById('submit-btn');
    const answerInput    = document.getElementById('answer-input');
    const feedback       = document.getElementById('feedback');
    const hintBtn        = document.getElementById('hint-btn');
    const hintBox        = document.getElementById('hint-box');
    const attemptCount   = document.getElementById('attempt-count');
    const attemptDisplay = document.getElementById('attempt-display');
    const coinDisplay    = document.getElementById('coin-display');

    function doSubmit() {
        const answer = answerInput.value.trim();
        if (!answer) { showFeedback('// ERROR: Enter a decryption key first.', 'wrong'); return; }

        submitBtn.disabled = true;
        submitBtn.textContent = '> PROCESSING...';

        fetch('ajax/check_answer.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: `answer=${encodeURIComponent(answer)}&level_number=${currentLevel}`
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                showFeedback('// SYSTEM ERROR: ' + data.message, 'wrong');
                submitBtn.disabled = false;
                submitBtn.textContent = '> SUBMIT';
                return;
            }
            if (data.correct) {
                window.TH && window.TH.playCorrect();
                if (coinDisplay && data.new_coins !== undefined) {
                    coinDisplay.textContent = data.new_coins;
                }
                showFeedback('// ' + data.message + ' +100 PTS — LOADING NEXT TERMINAL...', 'correct');
                submitBtn.disabled = true;
                answerInput.disabled = true;
                setTimeout(() => window.location.reload(), 1800);
            } else {
                window.TH && window.TH.playWrong();
                const count = data.attempts ?? 1;
                if (attemptCount)   attemptCount.textContent = count;
                if (attemptDisplay) attemptDisplay.style.display = '';
                showFeedback('// ' + data.message + ' — TRY AGAIN.', 'wrong');
                answerInput.value = '';
                answerInput.focus();
                submitBtn.disabled = false;
                submitBtn.textContent = '> SUBMIT';
            }
        })
        .catch(() => {
            showFeedback('// CONNECTION ERROR — Retry.', 'wrong');
            submitBtn.disabled = false;
            submitBtn.textContent = '> SUBMIT';
        });
    }

    submitBtn.addEventListener('click', doSubmit);
    answerInput.addEventListener('keydown', e => { if (e.key === 'Enter') doSubmit(); });

    if (hintBtn) {
        hintBtn.addEventListener('click', function () {
            const cost = parseInt(hintBtn.dataset.cost || '0', 10);

            if (cost > 0) {
                const currentPts = parseInt(coinDisplay ? coinDisplay.textContent : '0', 10);
                if (currentPts < cost) {
                    showToast(`INSUFFICIENT POINTS — need ${cost} pts.`, 'error');
                    return;
                }
                if (!confirm(`Spend ${cost} points to reveal this hint?`)) return;
            }

            hintBtn.disabled = true;
            hintBtn.textContent = '// RETRIEVING...';

            fetch('ajax/get_hint.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: `level_number=${currentLevel}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    window.TH && window.TH.playHint();
                    hintBox.textContent = data.hint;
                    hintBox.classList.add('visible');
                    hintBtn.textContent = '// HINT USED';

                    if (coinDisplay && data.new_coins !== undefined) {
                        coinDisplay.textContent = data.new_coins;
                    }

                    if (!data.was_free) {
                        showToast(`-${data.cost} PTS — Hint unlocked.`, 'error');
                    }
                } else if (data.insufficient) {
                    showToast(`INSUFFICIENT POINTS — need ${data.cost} pts, have ${data.coins}.`, 'error');
                    hintBtn.disabled = false;
                    hintBtn.textContent = hintBtn.classList.contains('paid')
                        ? `> BUY HINT [-${data.cost} PTS]`
                        : '> REQUEST HINT [FREE]';
                } else {
                    showToast('// NO HINT AVAILABLE.', 'error');
                    hintBtn.textContent = '// NO HINT';
                }
            })
            .catch(() => {
                hintBtn.disabled = false;
                hintBtn.textContent = '> REQUEST HINT';
            });
        });
    }

    function showFeedback(msg, type) {
        feedback.textContent = msg;
        feedback.className = 'feedback ' + type;
    }
})();
</script>
<?php endif; ?>
</body>
</html>
