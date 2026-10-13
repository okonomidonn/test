/* Lucian UX/UI Prompt #064 · MIT License (c) 2026 Seokwoooo · https://lucianlabs.dev/uxui-prompt/#064 */
/*
 * マイページのログイン: 送るボタンを押すと手紙が封筒に入って飛んでいき、「メールを確認してください」に切り替わる。
 * 「もう一度送る」は WAIT 秒たつまで押せない（サーバー側も同じアドレスへは60秒に1回まで）。
 * 使い方: window.zaitoU064(要素, function (email) { return Promise }) — Promise が失敗したら、
 * 入力の画面に戻してエラーを表示する（rejectの値がそのままメッセージになる）。
 */
(function () {
  var WAIT = 60;
  // よく使われるメールのWeb版。ほかのアドレスでは「メールを開く」ボタンを出さない。
  var MAIL = {
    'gmail.com': ['Gmailを開く', 'https://mail.google.com/mail/u/0/#inbox'],
    'googlemail.com': ['Gmailを開く', 'https://mail.google.com/mail/u/0/#inbox'],
    'icloud.com': ['iCloudメールを開く', 'https://www.icloud.com/mail'],
    'me.com': ['iCloudメールを開く', 'https://www.icloud.com/mail'],
    'yahoo.co.jp': ['Yahoo!メールを開く', 'https://mail.yahoo.co.jp/'],
    'outlook.com': ['Outlookを開く', 'https://outlook.live.com/mail/'],
    'outlook.jp': ['Outlookを開く', 'https://outlook.live.com/mail/'],
    'hotmail.com': ['Outlookを開く', 'https://outlook.live.com/mail/'],
    'docomo.ne.jp': ['ドコモメールを開く', 'https://mail.smt.docomo.ne.jp/']
  };

  window.zaitoU064 = function (root, sendMail) {
    var ask = root.querySelector('.u064-ask'), done = root.querySelector('.u064-done'), inp = ask.querySelector('input[type=email]'), box = ask.querySelector('.u064-box');
    var re = root.querySelector('.u064-re'), reLab = re.querySelector('span'), to = root.querySelector('.u064-to'), other = root.querySelector('.u064-link');
    var open = root.querySelector('.u064-mail'), err = root.querySelector('.u064-err');
    var rm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var raf = 0, ts = [];
    function later(fn, ms) { ts.push(setTimeout(function () { if (root.isConnected) fn(); }, rm ? 0 : ms)); }
    function clear() { ts.forEach(clearTimeout); ts = []; cancelAnimationFrame(raf); }
    function countdown() {
      var t0 = performance.now();
      re.setAttribute('aria-disabled', 'true');
      (function step(now) {
        if (!root.isConnected) return;
        var el = (now - t0) / 1000, left = Math.max(0, Math.ceil(WAIT - el));
        re.style.setProperty('--p', Math.min(1, el / WAIT).toFixed(3));
        reLab.textContent = left ? 'もう一度送る · ' + left + '秒' : 'もう一度送る';
        if (left) raf = requestAnimationFrame(step); else re.setAttribute('aria-disabled', 'false');
      })(t0);
    }
    function setPane(sent) {
      ask.inert = sent; done.inert = !sent;
      done.setAttribute('aria-hidden', String(!sent));
      done.querySelectorAll('button,a').forEach(function (b) { b.tabIndex = sent ? 0 : -1; });
    }
    function reset(message) {
      clear();
      root.classList.remove('sent', 'sealed', 'fly');
      setPane(false);
      err.textContent = message || '';
      inp.focus({ preventScroll: true });
    }
    function send() {
      clear();
      var email = inp.value.trim();
      to.textContent = email;
      var m = MAIL[email.split('@').pop().toLowerCase()];
      open.hidden = !m;
      if (m) { open.textContent = m[0]; open.href = m[1]; }
      err.textContent = '';
      root.classList.remove('sent');
      root.classList.add('sealed');
      // 封筒の動きと送信を同時に進め、両方が終わってから「確認してください」を出す。
      var shown = new Promise(function (ok) {
        later(function () { root.classList.add('fly'); }, 950);
        later(ok, 1650);
      });
      Promise.all([sendMail(email), shown]).then(function () {
        if (!root.isConnected) return;
        root.classList.remove('fly');
        root.classList.add('sent');
        setPane(true);
        re.focus({ preventScroll: true });
        countdown();
      }, function (x) {
        reset(typeof x === 'string' ? x : '送信できませんでした。通信状況を確認してください');
      });
    }
    ask.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!/^[^@\s]+@[^@\s]+\.[^@\s]{2,}$/.test(inp.value.trim())) {
        box.classList.add('bad'); inp.setAttribute('aria-invalid', 'true');
        err.textContent = '正しいメールアドレスを入力してください';
        if (box.animate && !rm) box.animate([{ transform: 'translateX(-6px)' }, { transform: 'translateX(5px)' }, { transform: 'none' }], { duration: 300 });
        return;
      }
      box.classList.remove('bad'); inp.removeAttribute('aria-invalid');
      send();
    });
    inp.addEventListener('input', function () { box.classList.remove('bad'); inp.removeAttribute('aria-invalid'); err.textContent = ''; });
    re.addEventListener('click', function () {
      if (re.getAttribute('aria-disabled') === 'true') return;
      root.classList.remove('sent'); setPane(false);
      send();
    });
    other.addEventListener('click', function () {
      reset('');
      inp.select();
    });
    setPane(false);
  };
})();
