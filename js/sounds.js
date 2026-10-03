(function () {
    let _ctx = null;
    let _musicOsc1 = null, _musicOsc2 = null, _musicLFO = null, _musicGain = null;
    let _musicRunning = false;

    var SOUND_KEY = 'th_sound';
    var MUSIC_KEY = 'th_music';

    function isSoundOn() { return localStorage.getItem(SOUND_KEY) !== 'off'; }
    function isMusicOn()  { return localStorage.getItem(MUSIC_KEY) === 'on'; }

    function getCtx() {
        if (!_ctx) _ctx = new (window.AudioContext || window.webkitAudioContext)();
        if (_ctx.state === 'suspended') _ctx.resume();
        return _ctx;
    }

    function playClick() {
        if (!isSoundOn()) return;
        try {
            var c = getCtx(), osc = c.createOscillator(), g = c.createGain();
            osc.connect(g); g.connect(c.destination);
            osc.type = 'square';
            osc.frequency.setValueAtTime(660, c.currentTime);
            osc.frequency.exponentialRampToValueAtTime(440, c.currentTime + 0.06);
            g.gain.setValueAtTime(0.12, c.currentTime);
            g.gain.exponentialRampToValueAtTime(0.001, c.currentTime + 0.07);
            osc.start(c.currentTime); osc.stop(c.currentTime + 0.07);
        } catch(e) {}
    }

    function playCorrect() {
        if (!isSoundOn()) return;
        try {
            var c = getCtx();
            [523, 659, 784, 1047].forEach(function(freq, i) {
                var osc = c.createOscillator(), g = c.createGain();
                osc.connect(g); g.connect(c.destination);
                osc.type = 'sine'; osc.frequency.value = freq;
                var t = c.currentTime + i * 0.13;
                g.gain.setValueAtTime(0.25, t);
                g.gain.exponentialRampToValueAtTime(0.001, t + 0.18);
                osc.start(t); osc.stop(t + 0.18);
            });
        } catch(e) {}
    }

    function playWrong() {
        if (!isSoundOn()) return;
        try {
            var c = getCtx();
            [200, 140].forEach(function(freq, i) {
                var osc = c.createOscillator(), g = c.createGain();
                osc.connect(g); g.connect(c.destination);
                osc.type = 'sawtooth';
                var t = c.currentTime + i * 0.18;
                osc.frequency.setValueAtTime(freq, t);
                osc.frequency.exponentialRampToValueAtTime(freq * 0.5, t + 0.15);
                g.gain.setValueAtTime(0.18, t);
                g.gain.exponentialRampToValueAtTime(0.001, t + 0.16);
                osc.start(t); osc.stop(t + 0.16);
            });
        } catch(e) {}
    }

    function playHint() {
        if (!isSoundOn()) return;
        try {
            var c = getCtx(), osc = c.createOscillator(), g = c.createGain();
            osc.connect(g); g.connect(c.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, c.currentTime);
            osc.frequency.exponentialRampToValueAtTime(660, c.currentTime + 0.25);
            g.gain.setValueAtTime(0.15, c.currentTime);
            g.gain.exponentialRampToValueAtTime(0.001, c.currentTime + 0.28);
            osc.start(c.currentTime); osc.stop(c.currentTime + 0.28);
        } catch(e) {}
    }

    function startMusic() {
        if (_musicRunning) return;
        try {
            var c = getCtx();
            _musicGain = c.createGain();
            _musicGain.gain.setValueAtTime(0, c.currentTime);
            _musicGain.gain.linearRampToValueAtTime(0.028, c.currentTime + 3);
            _musicGain.connect(c.destination);

            // Bass drone at 45 Hz
            _musicOsc1 = c.createOscillator();
            _musicOsc1.type = 'sine';
            _musicOsc1.frequency.value = 45;
            _musicOsc1.connect(_musicGain);
            _musicOsc1.start();

            // Octave up, quieter
            var g2 = c.createGain();
            g2.gain.value = 0.35;
            g2.connect(_musicGain);
            _musicOsc2 = c.createOscillator();
            _musicOsc2.type = 'sine';
            _musicOsc2.frequency.value = 90;
            _musicOsc2.connect(g2);
            _musicOsc2.start();

            // Slow volume LFO — breathing effect at 0.05 Hz
            _musicLFO = c.createOscillator();
            var lfoG = c.createGain();
            _musicLFO.frequency.value = 0.05;
            lfoG.gain.value = 0.007;
            _musicLFO.connect(lfoG);
            lfoG.connect(_musicGain.gain);
            _musicLFO.start();

            _musicRunning = true;
        } catch(e) {}
    }

    function stopMusic() {
        if (!_musicRunning || !_ctx) return;
        try {
            _musicGain.gain.setTargetAtTime(0, _ctx.currentTime, 0.8);
            setTimeout(function() {
                try { _musicOsc1 && _musicOsc1.stop(); } catch(e2) {}
                try { _musicOsc2 && _musicOsc2.stop(); } catch(e2) {}
                try { _musicLFO && _musicLFO.stop(); } catch(e2) {}
                _musicOsc1 = _musicOsc2 = _musicLFO = _musicGain = null;
                _musicRunning = false;
            }, 2500);
        } catch(e) { _musicRunning = false; }
    }

    // Shared click listener: music auto-start + click sound
    document.addEventListener('click', function(e) {
        // Start ambient music on first interaction if enabled
        if (isMusicOn() && !_musicRunning) startMusic();

        // Button click sound (skip settings panel buttons to avoid feedback loop)
        if (e.target.closest && !e.target.closest('.sp-panel')) {
            var el = e.target.closest('button, .btn-primary, .btn-submit, .btn-hint, .btn-logout, .menu-card, .auth-footer-link a, .btn-play-guest, .nav-auth-link');
            if (el && !el.disabled && !el.classList.contains('broke')) {
                playClick();
            }
        }
    }, true);

    window.TH = {
        playClick: playClick,
        playCorrect: playCorrect,
        playWrong: playWrong,
        playHint: playHint,
        startMusic: startMusic,
        stopMusic: stopMusic,
        isSoundOn: isSoundOn,
        isMusicOn: isMusicOn,
        SOUND_KEY: SOUND_KEY,
        MUSIC_KEY: MUSIC_KEY
    };
})();
