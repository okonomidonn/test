/*
 * トップページの「今日のおすすめ」（#zreco）。inc/reco.php（admin-ajax の zaito_reco）から5件を受け取り、
 * 一覧の求人カード（#zcards .card）を写して1枚ずつ重ねて出す。
 * - 右へ（または ♡）で「気になる」＝保存（fav.js の window.zaitoFav）、左へ（または ×）でスキップ。
 * - 登録していなくても5件すべてスワイプできる（保存はこのブラウザに残り、登録するとマイページに引き継がれる）。
 *   5件見終わったところで登録を案内する。次の5件は登録すると見られる。
 * - 見終わった求人はこのブラウザに覚えておき、次は別の5件を出す。
 * - その日初めてトップページを開いたときだけ、おすすめを画面の正面に大きく出す（後ろに一覧がうっすら見える）。
 *   閉じると、見出しの横（スマホでは下）の元の場所に戻る。2回目以降はその場所にだけ出す。
 * - 企業には何も送らない（応募は今までどおり各企業の募集ページで行う）。
 */
(function () {
  var root = document.getElementById('zreco');
  var list = document.getElementById('zcards');
  if (!root || !list) return;
  var ajax = root.getAttribute('data-ajax');
  var SEEN = 'zaito_swiped';
  var rm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var stack = root.querySelector('.sw-stack'), act = root.querySelector('.sw-act'), more = root.querySelector('.sw-more');
  var gate = root.querySelector('.reco-gate'), end = root.querySelector('.reco-end');
  var sub = root.querySelector('.reco-sub'), num = root.querySelector('.reco-n');
  var top = document.getElementById('ztop'), profile = document.getElementById('zprofile');

  // その日初めての訪問かどうか（このブラウザの日付で数える）。
  var DAY = 'zaito_reco_day';
  var d = new Date();
  var today = d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate();
  var firstToday = false;
  try { firstToday = localStorage.getItem(DAY) !== today; } catch (e) {}
  var home = document.createComment('reco');
  root.parentNode.insertBefore(home, root);
  var modal = document.createElement('div');
  modal.className = 'reco-modal';
  modal.hidden = true;
  modal.setAttribute('role', 'dialog');
  modal.setAttribute('aria-modal', 'true');
  modal.setAttribute('aria-labelledby', 'zreco-t');
  modal.innerHTML = '<div class="reco-modal-in" tabindex="-1"><button type="button" class="reco-modal-x" aria-label="閉じる"><span class="ms" aria-hidden="true">close</span></button></div>';
  document.body.appendChild(modal);
  var modalIn = modal.firstChild;
  var backFocus = null;
  function openModal() {
    backFocus = document.activeElement;
    modalIn.appendChild(root);
    root.hidden = false;
    modal.hidden = false;
    document.documentElement.classList.add('reco-lock');
    try { localStorage.setItem(DAY, today); } catch (e) {}
    modalIn.focus({ preventScroll: true });
    track('reco_modal_open', {});
  }
  function closeModal() {
    if (modal.hidden) return;
    modal.hidden = true;
    document.documentElement.classList.remove('reco-lock');
    home.parentNode.insertBefore(root, home.nextSibling);
    root.hidden = !items.length;
    if (backFocus && backFocus.focus) backFocus.focus({ preventScroll: true });
    track('reco_modal_close', { seen: pos });
  }
  modal.querySelector('.reco-modal-x').addEventListener('click', closeModal);
  modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden && gate.hidden) closeModal(); });

  function member() { return /(?:^|;\s*)zaito_m=1/.test(document.cookie); }
  function track(name, p) { if (typeof window.gtag === 'function') window.gtag('event', name, p || {}); }
  function seen() {
    try { var a = JSON.parse(localStorage.getItem(SEEN) || '[]'); return Array.isArray(a) ? a : []; } catch (e) { return []; }
  }
  function markSeen(slug) {
    var a = seen().filter(function (s) { return s !== slug; });
    a.push(slug);
    try { localStorage.setItem(SEEN, JSON.stringify(a.slice(-300))); } catch (e) {}
  }
  function like(slug) {
    var F = window.zaitoFav;
    if (!F) return;
    var favs = F.load();
    if (favs.indexOf(slug) !== -1) return;
    favs.unshift(slug);
    F.save(favs);
    F.paint();
    if (F.post) F.post({ action: 'zaito_fav', slug: slug, on: '1', bid: F.bid() }).catch(function () {});
    // 一覧の「保存した求人」の件数をそろえる。
    document.dispatchEvent(new CustomEvent('zaito:favs'));
  }

  var items = [], pos = 0, liked = 0, busy = false;

  function cardFor(slug) {
    var li = list.querySelector('.card[data-slug="' + slug + '"]');
    return li && !li.classList.contains('closed') ? li : null;
  }
  // 一覧のカードを写して、スワイプ用のカードにする（リンクは外し、保存ボタンは消す）。
  function build(it) {
    var c = cardFor(it.slug).cloneNode(true);
    c.classList.add('sw-card');
    var fav = c.querySelector('.fav');
    if (fav) fav.remove();
    var a = c.querySelector('a');
    var d = document.createElement('div');
    d.className = 'sw-face';
    while (a.firstChild) d.appendChild(a.firstChild);
    a.replaceWith(d);
    if (it.why && it.why.length) {
      var w = document.createElement('div');
      w.className = 'reco-why';
      it.why.forEach(function (t) { var s = document.createElement('span'); s.textContent = t; w.appendChild(s); });
      var body = d.querySelector('.body');
      body.insertBefore(w, body.firstChild);
    }
    c.insertAdjacentHTML('beforeend', '<span class="sw-stamp sw-stamp-y">気になる</span><span class="sw-stamp sw-stamp-n">スキップ</span>');
    return c;
  }
  function current() { return items[pos]; }

  function render() {
    stack.innerHTML = '';
    if (pos >= items.length) { finish(); return; }
    end.hidden = true;
    stack.hidden = false;
    act.hidden = false;
    num.textContent = (pos + 1) + ' / ' + items.length;
    for (var i = Math.min(pos + 1, items.length - 1); i >= pos; i--) {
      var c = build(items[i]);
      c.classList.add(i > pos ? 'sw-next' : 'sw-top');
      stack.appendChild(c);
    }
    more.href = cardFor(current().slug).querySelector('a').href;
    var topCard = stack.querySelector('.sw-top');
    if (!hinted && pos === 0) {
      topCard.classList.add('sw-hint');
      topCard.insertAdjacentHTML('beforeend', '<span class="reco-hint" aria-hidden="true"><span class="ms">swipe</span>右へで「気になる」・左へでスキップ</span>');
    }
    drag(topCard);
  }
  var hinted = false;
  function unhint() {
    if (hinted) return;
    hinted = true;
    stack.querySelectorAll('.sw-hint').forEach(function (c) { c.classList.remove('sw-hint'); });
    stack.querySelectorAll('.reco-hint').forEach(function (h) { h.remove(); });
  }
  root.addEventListener('pointerdown', unhint, true);
  root.addEventListener('keydown', unhint, true);

  function finish() {
    stack.hidden = true;
    act.hidden = true;
    num.textContent = '';
    var guest = !member();
    end.innerHTML =
      '<span class="ms" aria-hidden="true">task_alt</span>' +
      '<p class="reco-end-t">今日のおすすめは以上です</p>' +
      '<p>' + (liked ? '「気になる」にした' + liked + '件は、保存した求人で見られます。' : '気になる求人がなければ、次の5件も見てみてください。') + '</p>' +
      (guest ? '<button type="button" class="btn btn-p reco-join">次の5件を見る（無料登録）</button>' : '<button type="button" class="btn btn-p reco-next">次の5件を見る</button>') +
      (liked ? '<button type="button" class="btn btn-g reco-favs">保存した求人を見る</button>' : '') +
      '<button type="button" class="reco-all">すべての求人を見る</button>';
    end.hidden = false;
    track('reco_end', { liked: liked, guest: guest ? 1 : 0 });
    // 登録していない人には、見終わったところで一度だけ登録を案内する。
    if (guest && !gateShown) {
      gateShown = true;
      gate.querySelector('.reco-gate-t').textContent = liked ? '「気になる」の' + liked + '件を保存しました' : '今日の5件を見終わりました';
      openGate();
    }
  }
  var gateShown = false;

  function go(dir) {
    if (busy || !current()) return;
    var c = stack.querySelector('.sw-top');
    busy = true;
    var slug = current().slug;
    if (dir > 0) { like(slug); liked++; }
    markSeen(slug);
    track(dir > 0 ? 'reco_like' : 'reco_skip', { job: slug });
    c.classList.add(dir > 0 ? 'is-yes' : 'is-no');
    c.style.transition = rm ? 'none' : 'transform .32s ease, opacity .32s ease';
    c.style.transform = 'translate(' + (dir * 130) + '%, 4%) rotate(' + (dir * 14) + 'deg)';
    c.style.opacity = '0';
    setTimeout(function () { pos++; busy = false; render(); }, rm ? 0 : 300);
  }

  function drag(c) {
    if (!c) return;
    var x0 = 0, y0 = 0, dx = 0, on = false, t0 = 0;
    c.addEventListener('pointerdown', function (e) {
      if (e.button !== 0) return;
      on = true; x0 = e.clientX; y0 = e.clientY; dx = 0; t0 = Date.now();
      c.setPointerCapture(e.pointerId);
      c.style.transition = 'none';
    });
    c.addEventListener('pointermove', function (e) {
      if (!on) return;
      dx = e.clientX - x0;
      c.style.transform = 'translate(' + dx + 'px,' + ((e.clientY - y0) * 0.2) + 'px) rotate(' + (dx / 18) + 'deg)';
      c.classList.toggle('is-yes', dx > 40);
      c.classList.toggle('is-no', dx < -40);
    });
    function back() {
      c.style.transition = rm ? 'none' : 'transform .25s ease';
      c.style.transform = '';
      c.classList.remove('is-yes', 'is-no');
    }
    function up() {
      if (!on) return;
      on = false;
      var fast = Math.abs(dx) > 40 && Date.now() - t0 < 250;
      if (Math.abs(dx) > c.offsetWidth * 0.3 || fast) { go(dx > 0 ? 1 : -1); return; }
      back();
      // ほとんど動かさずに離したら、タップとして詳しく見る（登録していなくても見られる）。
      if (Math.abs(dx) < 6 && Date.now() - t0 < 300) { track('reco_detail', { job: current().slug }); location.href = more.href; }
    }
    c.addEventListener('pointerup', up);
    c.addEventListener('pointercancel', up);
  }

  // 登録の案内（未登録の人が5件見終わったとき）
  function openGate() {
    gate.hidden = false;
    track('reco_gate', {});
    gate.scrollIntoView({ behavior: rm ? 'auto' : 'smooth', block: 'center' });
    var inp = gate.querySelector('input');
    if (inp) setTimeout(function () { inp.focus({ preventScroll: true }); }, rm ? 0 : 400);
  }
  function closeGate() { gate.hidden = true; track('reco_gate_skip', {}); }
  gate.querySelector('.reco-gate-x').addEventListener('click', function () {
    closeGate();
    closeModal();
    var bar = document.querySelector('.count-bar');
    if (bar) bar.scrollIntoView({ behavior: rm ? 'auto' : 'smooth', block: 'start' });
  });
  gate.querySelector('form').addEventListener('submit', function (e) {
    e.preventDefault();
    var err = gate.querySelector('.err');
    var email = gate.querySelector('input').value.trim();
    err.textContent = '';
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { err.textContent = '正しいメールアドレスを入力してください'; return; }
    if (!top) return;
    var btn = gate.querySelector('button[type=submit]');
    btn.disabled = true;
    top.elements.email.value = email;
    fetch(top.getAttribute('action'), { method: 'POST', body: new FormData(top), credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res || !res.success) throw new Error(res && res.data && res.data.message ? res.data.message : '');
        if (res.data.created) {
          track('sign_up', { method: 'reco' });
          // ページ下の登録欄は、希望条件の質問に進めておく。
          top.hidden = true;
          if (profile) profile.hidden = false;
        }
        gate.hidden = true;
        showJoined(res.data.created);
        finish();
        // 新しく登録した人は、そのままページ下の質問へ移り、お名前（必須）から答えてもらう。
        if (res.data.created) openProfile();
      })
      .catch(function (x) {
        btn.disabled = false;
        err.textContent = x.message || '登録できませんでした。時間をおいてもう一度お試しください';
      });
  });
  function openProfile() {
    closeModal();
    document.getElementById('signup').scrollIntoView({ behavior: rm ? 'auto' : 'smooth' });
    var n = document.getElementById('zpname');
    if (n) setTimeout(function () { n.focus({ preventScroll: true }); }, rm ? 0 : 500);
    track('profile_open', { from: 'reco' });
  }
  function showJoined(created) {
    sub.innerHTML = '';
    sub.appendChild(document.createTextNode(created ? '登録できました。続けて、お名前を教えてください。' : '登録済みのメールアドレスでした。スワイプで保存できます。'));
    if (created) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'reco-prefs';
      b.textContent = 'お名前を入力する';
      b.addEventListener('click', openProfile);
      sub.appendChild(b);
    }
  }

  act.querySelector('.sw-no').addEventListener('click', function () { go(-1); });
  act.querySelector('.sw-yes').addEventListener('click', function () { go(1); });
  more.addEventListener('click', function () { if (current()) track('reco_detail', { job: current().slug }); });
  end.addEventListener('click', function (e) {
    if (e.target.closest('.reco-next')) { load(); return; }
    if (e.target.closest('.reco-join')) { openGate(); return; }
    var bar = document.querySelector('.count-bar');
    if (e.target.closest('.reco-favs') || e.target.closest('.reco-all')) closeModal();
    if (e.target.closest('.reco-favs')) {
      var q = document.querySelector('.qfav');
      if (q && q.getAttribute('aria-pressed') !== 'true') q.click();
    }
    if (e.target.closest('.reco-favs') || e.target.closest('.reco-all')) {
      if (bar) bar.scrollIntoView({ behavior: rm ? 'auto' : 'smooth', block: 'start' });
    }
  });
  root.addEventListener('keydown', function (e) {
    if (e.target.closest('input,textarea') || act.hidden) return;
    if (e.key === 'ArrowRight') { e.preventDefault(); go(1); }
    else if (e.key === 'ArrowLeft') { e.preventDefault(); go(-1); }
  });

  var TEXT_AI = {
    member: '登録した希望条件をもとに、AIがあなたに合いそうな求人を選びました。',
    member_noprefs: 'AIが選んだおすすめです。希望の条件を教えると、もっとあなたに合う求人を選べます。',
    guest: 'AIが、初めての人にまず見てほしい求人を選びました。右へスワイプすると「気になる」に保存できます。'
  };
  var TEXT = {
    member: '登録した希望条件から、あなたに合いそうな求人を選びました。',
    member_noprefs: '希望の条件を教えると、もっとあなたに合う求人を選べます。',
    guest: '未経験OK・高時給・新着から選びました。右へスワイプすると「気になる」に保存できます。'
  };
  function loading(on) {
    root.classList.toggle('is-loading', on);
    if (on) {
      sub.textContent = 'あなたに合う求人を選んでいます…';
      stack.innerHTML = '<div class="reco-skel" aria-hidden="true"><span></span><span></span><span></span></div>';
      stack.hidden = false;
      act.hidden = true;
      end.hidden = true;
    }
  }
  function load() {
    loading(true);
    var fd = new FormData();
    fd.append('action', 'zaito_reco');
    seen().forEach(function (s) { fd.append('seen[]', s); });
    return fetch(ajax, { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        loading(false);
        if (!res || !res.success) { closeModal(); return; }
        items = res.data.items.filter(function (it) { return cardFor(it.slug); });
        if (!items.length) { closeModal(); return; }
        pos = 0; liked = 0;
        var T = res.data.ai ? TEXT_AI : TEXT;
        sub.textContent = T[res.data.mode] || T.guest;
        if (res.data.mode === 'member_noprefs') showJoined(true);
        root.hidden = false;
        render();
        track('reco_show', { mode: res.data.mode, ai: res.data.ai ? 1 : 0 });
      })
      .catch(function () { loading(false); closeModal(); });
  }
  // 希望条件を答え終わったら、おすすめを選び直す。
  if (profile) profile.addEventListener('submit', function () { setTimeout(load, 1200); });
  // 条件を付けたURL（共有されたリンクなど）や、ページの途中への移動で来た人には、正面には出さない。
  if (firstToday && !location.hash && !/[?&](g|who|pay|days|newbie|fav)=/.test(location.search)) openModal();
  load();
})();
