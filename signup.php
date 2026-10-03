<?php
require_once 'includes/session.php';
if (isUserRegistered()) { header('Location: index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CREATE ACCOUNT // TREASURE HUNT</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="bg-wrapper">
<div class="auth-page">

    <div class="auth-card">

        <div class="auth-logo">
            <img src="images/logo.png" class="auth-logo-img" alt="TH">
            <span class="auth-logo-title">&#x25a0; TREASURE HUNT</span>
        </div>

        <h2 class="auth-heading">CREATE ACCOUNT</h2>
        <p class="auth-sub">// Register your agent profile to begin</p>
        <div class="auth-divider"></div>

        <div class="auth-error" id="auth-error"></div>

        <form id="signup-form" autocomplete="off" novalidate>

            <div class="form-group">
                <label for="s-name">AGENT NAME</label>
                <input type="text" id="s-name" name="name"
                       placeholder="e.g. Riya Sharma" maxlength="80">
                <div class="form-error" id="err-name"></div>
            </div>

            <div class="form-group">
                <label for="s-email">EMAIL ID</label>
                <input type="email" id="s-email" name="email"
                       placeholder="e.g. riya@email.com" maxlength="100">
                <div class="form-error" id="err-email"></div>
            </div>

            <div class="form-group">
                <label for="s-password">CREATE PASSWORD</label>
                <input type="password" id="s-password" name="password"
                       placeholder="Min. 6 characters" maxlength="100">
                <div class="form-error" id="err-password"></div>
            </div>

            <div class="form-group">
                <label for="s-confirm">CONFIRM PASSWORD</label>
                <input type="password" id="s-confirm" name="confirm"
                       placeholder="Re-enter password" maxlength="100">
                <div class="form-error" id="err-confirm"></div>
            </div>

            <button type="submit" class="btn-primary" id="signup-btn">
                &gt; CREATE ACCOUNT
            </button>
        </form>

        <div class="auth-footer-link">
            Already have an account? <a href="login.php">&gt; LOGIN HERE</a>
        </div>

    </div>

</div>
<footer class="site-footer">SYSTEM v1.0 &nbsp;&#x25a0;&nbsp; CREATED BY SUCHI PATEL &amp; KRISHNAPRIYA M.</footer>
</div>

<div class="toast" id="toast"></div>
<script src="js/sounds.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form     = document.getElementById('signup-form');
    const btn      = document.getElementById('signup-btn');
    const authErr  = document.getElementById('auth-error');

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

        const name     = document.getElementById('s-name').value.trim();
        const email    = document.getElementById('s-email').value.trim();
        const password = document.getElementById('s-password').value;
        const confirm  = document.getElementById('s-confirm').value;
        let valid = true;

        if (!name || name.length < 2) {
            fieldErr('err-name', 'ERROR: Name must be at least 2 characters.'); valid = false;
        }
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            fieldErr('err-email', 'ERROR: Enter a valid email address.'); valid = false;
        }
        if (!password || password.length < 6) {
            fieldErr('err-password', 'ERROR: Password must be at least 6 characters.'); valid = false;
        }
        if (password !== confirm) {
            fieldErr('err-confirm', 'ERROR: Passwords do not match.'); valid = false;
        }
        if (!valid) return;

        btn.disabled = true;
        btn.textContent = '> REGISTERING...';

        fetch('ajax/register_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: `name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'index.php';
            } else {
                authErr.textContent = data.message || 'Registration failed.';
                authErr.classList.add('visible');
                btn.disabled = false;
                btn.textContent = '> CREATE ACCOUNT';
            }
        })
        .catch(() => {
            authErr.textContent = 'ERROR: Connection lost. Try again.';
            authErr.classList.add('visible');
            btn.disabled = false;
            btn.textContent = '> CREATE ACCOUNT';
        });
    });
});
</script>
</body>
</html>
