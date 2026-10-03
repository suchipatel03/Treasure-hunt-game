<?php
require_once 'includes/session.php';
if (isUserRegistered()) { header('Location: index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN // TREASURE HUNT</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .btn-guest {
            display:inline-block; padding:9px 22px; border:1px solid var(--border);
            color:var(--text-dim); font-family:'Courier New',monospace; font-size:0.8rem;
            letter-spacing:.12em; text-transform:uppercase; text-decoration:none;
            transition:all .2s;
        }
        .btn-guest:hover { border-color:var(--green-dim); color:var(--green); }
    </style>
</head>
<body>
<div class="bg-wrapper">
<div class="auth-page">

    <div class="auth-card">

        <div class="auth-logo">
            <img src="images/logo.png" class="auth-logo-img" alt="TH">
            <span class="auth-logo-title">&#x25a0; TREASURE HUNT</span>
        </div>

        <h2 class="auth-heading">SYSTEM LOGIN</h2>
        <p class="auth-sub">// Enter your credentials to access your session</p>
        <div class="auth-divider"></div>

        <div class="auth-error" id="auth-error"></div>

        <form id="login-form" autocomplete="off" novalidate>

            <div class="form-group">
                <label for="l-email">EMAIL ID</label>
                <input type="email" id="l-email" name="email"
                       placeholder="e.g. riya@email.com" maxlength="100" autofocus>
                <div class="form-error" id="err-email"></div>
            </div>

            <div class="form-group">
                <label for="l-password">PASSWORD</label>
                <input type="password" id="l-password" name="password"
                       placeholder="Your password" maxlength="100">
                <div class="form-error" id="err-password"></div>
            </div>

            <button type="submit" class="btn-primary" id="login-btn">
                &gt; LOGIN
            </button>
        </form>

        <div class="auth-footer-link">
            New agent? <a href="signup.php">&gt; CREATE ACCOUNT</a>
        </div>

        <div class="auth-divider" style="margin-top:18px;"></div>
        <div style="text-align:center;margin-top:16px;">
            <a href="start_guest.php" class="btn-guest">&gt; PLAY AS GUEST</a>
            <p style="font-size:11px;color:var(--text-dim);margin-top:8px;letter-spacing:.05em;">
                // Guest progress is not saved. Login to appear on leaderboard.
            </p>
        </div>

    </div>

</div>
<footer class="site-footer">SYSTEM v1.0 &nbsp;&#x25a0;&nbsp; CREATED BY SUCHI PATEL &amp; KRISHNAPRIYA M.</footer>
</div>

<div class="toast" id="toast"></div>
<script src="js/sounds.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form    = document.getElementById('login-form');
    const btn     = document.getElementById('login-btn');
    const authErr = document.getElementById('auth-error');

    function fieldErr(id, msg) {
        const el = document.getElementById(id);
        if (el) { el.textContent = msg; el.classList.add('visible'); }
    }
    function clearErrors() {
        document.querySelectorAll('.form-error').forEach(e => { e.textContent = ''; e.classList.remove('visible'); });
        authErr.textContent = ''; authErr.classList.remove('visible');
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();

        const email    = document.getElementById('l-email').value.trim();
        const password = document.getElementById('l-password').value;
        let valid = true;

        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            fieldErr('err-email', 'ERROR: Enter a valid email address.'); valid = false;
        }
        if (!password) {
            fieldErr('err-password', 'ERROR: Enter your password.'); valid = false;
        }
        if (!valid) return;

        btn.disabled = true;
        btn.textContent = '> AUTHENTICATING...';

        fetch('ajax/login_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'index.php';
            } else {
                authErr.textContent = data.message || 'Login failed.';
                authErr.classList.add('visible');
                btn.disabled = false;
                btn.textContent = '> LOGIN';
            }
        })
        .catch(() => {
            authErr.textContent = 'ERROR: Connection lost. Try again.';
            authErr.classList.add('visible');
            btn.disabled = false;
            btn.textContent = '> LOGIN';
        });
    });
});
</script>
</body>
</html>
