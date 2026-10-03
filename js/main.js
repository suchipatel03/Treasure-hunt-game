(function () {
'use strict';

// ── Inject settings panel CSS ──────────────────────────────────────────────
var _style = document.createElement('style');
_style.textContent = [
    '#sp-gear-btn{background:transparent;border:1px solid var(--border);color:var(--text-dim);',
    'font-family:"Courier New",monospace;font-size:16px;padding:5px 10px;cursor:pointer;',
    'transition:all .2s;line-height:1;margin-right:6px;}',
    '#sp-gear-btn:hover{border-color:var(--green-dim);color:var(--green);}',

    '.sp-overlay{position:fixed;inset:0;z-index:800;background:rgba(0,0,0,.65);',
    'display:none;justify-content:flex-end;backdrop-filter:blur(1px);}',
    '.sp-overlay.open{display:flex;}',

    '.sp-panel{width:276px;height:100%;background:#060e06;border-left:1px solid var(--border);',
    'display:flex;flex-direction:column;overflow-y:auto;transform:translateX(100%);',
    'transition:transform .25s ease;scrollbar-width:thin;scrollbar-color:var(--border) transparent;}',
    '.sp-overlay.open .sp-panel{transform:translateX(0);}',

    '.sp-hdr{display:flex;align-items:center;justify-content:space-between;',
    'padding:14px 16px;border-bottom:1px solid var(--border);',
    'position:sticky;top:0;background:#060e06;z-index:1;}',
    '.sp-hdr-title{color:var(--green);font-size:11px;letter-spacing:.18em;text-transform:uppercase;}',
    '.sp-x{background:transparent;border:1px solid var(--border);color:var(--text-dim);',
    'font-family:"Courier New",monospace;font-size:11px;padding:3px 7px;cursor:pointer;transition:all .15s;}',
    '.sp-x:hover{border-color:var(--danger);color:var(--danger);}',

    '.sp-sec{padding:14px 16px;border-bottom:1px solid var(--border);}',
    '.sp-sec-lbl{font-size:9px;letter-spacing:.2em;color:var(--text-dim);',
    'text-transform:uppercase;margin-bottom:10px;}',
    '.sp-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;}',
    '.sp-row:last-child{margin-bottom:0;}',
    '.sp-lbl{font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:var(--text);}',

    '.sp-tg-grp{display:flex;border:1px solid var(--border);}',
    '.sp-tg{background:transparent;border:none;border-right:1px solid var(--border);',
    'color:var(--text-dim);font-family:"Courier New",monospace;font-size:10px;',
    'letter-spacing:.1em;padding:4px 10px;cursor:pointer;transition:all .15s;}',
    '.sp-tg:last-child{border-right:none;}',
    '.sp-tg.on{background:rgba(0,255,65,.08);color:var(--green);}',

    '.sp-col{flex-direction:column;align-items:flex-start;gap:7px;}',
    '.sp-rename{display:flex;gap:5px;width:100%;}',
    '.sp-inp{flex:1;background:rgba(0,255,65,.03);border:1px solid var(--border);',
    'color:var(--green);font-family:"Courier New",monospace;font-size:12px;',
    'padding:5px 8px;outline:none;}',
    '.sp-inp:focus{border-color:var(--green-dim);}',
    '.sp-save{background:transparent;border:1px solid var(--green-dim);color:var(--green);',
    'font-family:"Courier New",monospace;font-size:10px;letter-spacing:.1em;',
    'padding:5px 9px;cursor:pointer;white-space:nowrap;transition:all .15s;}',
    '.sp-save:hover{background:rgba(0,255,65,.08);}',
    '.sp-save:disabled{opacity:.4;cursor:not-allowed;}',
    '.sp-msg{font-size:11px;letter-spacing:.04em;min-height:13px;}',

    '.sp-info-btn{display:block;width:100%;background:transparent;border:none;',
    'border-bottom:1px solid var(--border);color:var(--text-dim);',
    'font-family:"Courier New",monospace;font-size:11px;letter-spacing:.1em;',
    'text-transform:uppercase;padding:10px 0;cursor:pointer;text-align:left;transition:color .15s;}',
    '.sp-info-btn:last-of-type{border-bottom:none;}',
    '.sp-info-btn:hover{color:var(--green);}',
    '.sp-sub{font-size:12px;color:var(--text-dim);letter-spacing:.04em;line-height:1.9;',
    'padding:8px 0 10px;border-bottom:1px solid var(--border);}',
    '.sp-sub a{color:var(--green-dim);text-decoration:none;}',
    '.sp-sub a:hover{color:var(--green);}'
].join('');
document.head.appendChild(_style);

// ── State helpers (mirror sounds.js keys) ────────────────────────────────
var SK = 'th_sound', MK = 'th_music';
function soundOn() { return localStorage.getItem(SK) !== 'off'; }
function musicOn() { return localStorage.getItem(MK) === 'on'; }

// ── Open / close ─────────────────────────────────────────────────────────
function openPanel() {
    var o = document.getElementById('sp-overlay');
    if (o) o.classList.add('open');
}
function closePanel() {
    var o = document.getElementById('sp-overlay');
    if (o) o.classList.remove('open');
}

// ── Toggle button state ───────────────────────────────────────────────────
function setTg(id, isOn) {
    var el = document.getElementById(id);
    if (el) el.classList.toggle('on', isOn);
}

// ── Build and inject panel ────────────────────────────────────────────────
function buildPanel() {
    if (document.getElementById('sp-overlay')) return;
    var mode = (document.body.dataset.userMode) || 'none';

    // Account section depends on mode
    var accHtml = '';
    if (mode === 'guest') {
        accHtml = '<div class="sp-sec">'
            + '<div class="sp-sec-lbl">ACCOUNT</div>'
            + '<div class="sp-row sp-col">'
            + '<span class="sp-lbl">CHANGE USERNAME</span>'
            + '<div class="sp-rename">'
            + '<input type="text" class="sp-inp" id="sp-nm-inp" placeholder="New name..." maxlength="50">'
            + '<button class="sp-save" id="sp-nm-save">SAVE</button>'
            + '</div>'
            + '<div class="sp-msg" id="sp-nm-msg"></div>'
            + '</div></div>';
    } else if (mode === 'none') {
        accHtml = '<div class="sp-sec">'
            + '<div class="sp-sec-lbl">ACCOUNT</div>'
            + '<div class="sp-row"><span class="sp-lbl" style="color:var(--text-dim);font-size:11px;">'
            + '// Enter your name in<br>the field below.</span></div>'
            + '</div>';
    }

    var el = document.createElement('div');
    el.id = 'sp-overlay';
    el.className = 'sp-overlay';
    el.innerHTML = '<div class="sp-panel" id="sp-panel">'
        + '<div class="sp-hdr">'
        +   '<span class="sp-hdr-title">&#x2699; SYSTEM SETTINGS</span>'
        +   '<button class="sp-x" id="sp-x">&#x2715;</button>'
        + '</div>'

        + '<div class="sp-sec">'
        +   '<div class="sp-sec-lbl">AUDIO</div>'
        +   '<div class="sp-row">'
        +     '<span class="sp-lbl">SOUND EFFECTS</span>'
        +     '<div class="sp-tg-grp">'
        +       '<button class="sp-tg ' + (soundOn() ? 'on' : '') + '" id="sp-s-on">ON</button>'
        +       '<button class="sp-tg ' + (!soundOn() ? 'on' : '') + '" id="sp-s-off">OFF</button>'
        +     '</div>'
        +   '</div>'
        +   '<div class="sp-row">'
        +     '<span class="sp-lbl">MUSIC</span>'
        +     '<div class="sp-tg-grp">'
        +       '<button class="sp-tg ' + (musicOn() ? 'on' : '') + '" id="sp-m-on">ON</button>'
        +       '<button class="sp-tg ' + (!musicOn() ? 'on' : '') + '" id="sp-m-off">OFF</button>'
        +     '</div>'
        +   '</div>'
        + '</div>'

        + accHtml

        + '<div class="sp-sec">'
        +   '<div class="sp-sec-lbl">INFO</div>'
        +   '<button class="sp-info-btn" id="sp-cred-btn">&#x25a0; CREDITS</button>'
        +   '<div class="sp-sub" id="sp-cred-sub" style="display:none">'
        +     'TREASURE HUNT v1.0<br>'
        +     '&#x2500;&#x2500;&#x2500;&#x2500;&#x2500;&#x2500;&#x2500;&#x2500;&#x2500;&#x2500;&#x2500;<br>'
        +     'CREATED BY:<br>'
        +     '<strong style="color:var(--green)">SUCHI PATEL</strong><br>'
        +     '<strong style="color:var(--green)">KRISHNAPRIYA M.</strong>'
        +   '</div>'
        +   '<button class="sp-info-btn" id="sp-help-btn">&#x25a0; SUPPORT &amp; HELP</button>'
        +   '<div class="sp-sub" id="sp-help-sub" style="display:none">'
        +     '<a href="instructions.php">&#x25a0; VIEW INSTRUCTIONS</a><br><br>'
        +     'HAVING ISSUES?<br>'
        +     'Restart your browser<br>'
        +     'or contact your admin.'
        +   '</div>'
        + '</div>'

        + '</div>'; // /sp-panel

    document.body.appendChild(el);

    // Close
    el.addEventListener('click', function(e) { if (e.target === el) closePanel(); });
    document.getElementById('sp-x').addEventListener('click', closePanel);

    // Sound toggles
    document.getElementById('sp-s-on').addEventListener('click', function() {
        localStorage.setItem(SK, 'on');
        setTg('sp-s-on', true); setTg('sp-s-off', false);
    });
    document.getElementById('sp-s-off').addEventListener('click', function() {
        localStorage.setItem(SK, 'off');
        setTg('sp-s-on', false); setTg('sp-s-off', true);
    });

    // Music toggles
    document.getElementById('sp-m-on').addEventListener('click', function() {
        localStorage.setItem(MK, 'on');
        setTg('sp-m-on', true); setTg('sp-m-off', false);
        if (window.TH && window.TH.startMusic) window.TH.startMusic();
    });
    document.getElementById('sp-m-off').addEventListener('click', function() {
        localStorage.setItem(MK, 'off');
        setTg('sp-m-on', false); setTg('sp-m-off', true);
        if (window.TH && window.TH.stopMusic) window.TH.stopMusic();
    });

    // Credits expand
    document.getElementById('sp-cred-btn').addEventListener('click', function() {
        var s = document.getElementById('sp-cred-sub');
        s.style.display = s.style.display === 'none' ? 'block' : 'none';
    });

    // Help expand
    document.getElementById('sp-help-btn').addEventListener('click', function() {
        var s = document.getElementById('sp-help-sub');
        s.style.display = s.style.display === 'none' ? 'block' : 'none';
    });

    // Change username (guest only)
    var saveBtn = document.getElementById('sp-nm-save');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            var inp = document.getElementById('sp-nm-inp');
            var msg = document.getElementById('sp-nm-msg');
            var name = inp.value.trim();
            if (name.length < 2) {
                msg.style.color = 'var(--danger)';
                msg.textContent = 'ERROR: Min 2 characters.';
                return;
            }
            saveBtn.disabled = true;
            fetch('ajax/update_guest_name.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: 'name=' + encodeURIComponent(name)
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    msg.style.color = 'var(--green-dim)';
                    msg.textContent = '// Name updated.';
                    // Update name displays on page without reload
                    document.querySelectorAll('.js-player-name').forEach(function(el) {
                        el.textContent = name.toUpperCase();
                    });
                    setTimeout(function() { msg.textContent = ''; }, 2000);
                } else {
                    msg.style.color = 'var(--danger)';
                    msg.textContent = data.message || 'ERROR.';
                }
                saveBtn.disabled = false;
            })
            .catch(function() {
                msg.style.color = 'var(--danger)';
                msg.textContent = 'CONNECTION ERROR.';
                saveBtn.disabled = false;
            });
        });
    }
}

// ── Gear button ───────────────────────────────────────────────────────────
function addGearBtn() {
    if (document.getElementById('sp-gear-btn')) return;
    var btn = document.createElement('button');
    btn.id = 'sp-gear-btn';
    btn.title = 'Settings';
    btn.innerHTML = '&#x2699;';
    btn.addEventListener('click', function(e) { e.stopPropagation(); openPanel(); });

    var nav = document.querySelector('.navbar');
    if (!nav) return;
    var ref = nav.querySelector('#nav-actions, .btn-logout');
    if (ref) nav.insertBefore(btn, ref);
    else nav.appendChild(btn);
}

// ── Logout ────────────────────────────────────────────────────────────────
function setupLogout() {
    var btn = document.getElementById('logout-btn');
    if (btn) {
        btn.addEventListener('click', function() {
            if (confirm('TERMINATE SESSION? Your progress is saved.')) {
                window.location.href = 'logout.php';
            }
        });
    }
}

// ── Toast ─────────────────────────────────────────────────────────────────
function showToast(msg, type) {
    var toast = document.getElementById('toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast'; toast.className = 'toast';
        document.body.appendChild(toast);
    }
    toast.textContent = msg;
    toast.className = 'toast ' + (type || 'info');
    toast.classList.add('show');
    clearTimeout(toast._t);
    toast._t = setTimeout(function() { toast.classList.remove('show'); }, 3500);
}
window.showToast = showToast;

// ── Init ──────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    addGearBtn();
    buildPanel();
    setupLogout();
});

})();
