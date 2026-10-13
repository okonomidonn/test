/* Lucian UX/UI Prompt #073 · MIT License (c) 2026 Seokwoooo · https://lucianlabs.dev/uxui-prompt/#073 */
/*
 * トップページのお知らせの帯（.u073）。お知らせは data-items（[見出し, 補足, リンク先] の配列）から作る。
 * - 3.8秒ごとに下から上へ切り替わり、右の短いバーが残り時間で伸びる。マウスを乗せる・中にフォーカスがある間は止まる。
 * - × で閉じると帯がたたまれ、ヘッダーにベルが出る。ベルで開き直せる。
 * - 閉じたことはこのブラウザに覚えておき、お知らせの中身（data-ver）が変わったらまた開いて出す。
 * - 動きを減らす設定のときは自動では切り替えない（→ のボタンで進める）。
 */
(function () {
  var root = document.querySelector('.u073');
  if (!root) return;
  var D = [];
  try { D = JSON.parse(root.getAttribute('data-items') || '[]'); } catch (e) {}
  if (!D.length) return;
  var ver = root.getAttribute('data-ver') || '';
  var KEY = 'zaito_news_closed';
  var ban = root.firstElementChild;
  var msgs = root.querySelector('.u073-msgs'), segs = root.querySelector('.u073-segs'), bar = root.querySelector('.u073-bar');
  var x = root.querySelector('.u073-x'), nx = root.querySelector('.u073-nx');
  var rm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ベルはヘッダーのナビの先頭に置く（閉じたあとだけ見える）。
  var bell = document.createElement('button');
  bell.type = 'button';
  bell.className = 'u073-bell';
  bell.tabIndex = -1;
  bell.hidden = true;
  bell.setAttribute('aria-label', 'お知らせを表示');
  bell.innerHTML = '<svg viewBox="0 0 17 17" aria-hidden="true"><path d="M4 11.5V8a4.5 4.5 0 0 1 9 0v3.5l1 1H3zM7 14.5a1.6 1.6 0 0 0 3 0"/></svg>';
  var nav = document.querySelector('.hd nav');
  if (nav) nav.insertBefore(bell, nav.firstChild);

  var els = D.map(function (d) {
    var m = document.createElement(d[2] ? 'a' : 'div');
    m.className = 'u073-m';
    if (d[2]) m.href = d[2];
    m.innerHTML = '<b></b><span></span>';
    m.firstChild.textContent = d[0];
    m.lastChild.textContent = d[1];
    msgs.appendChild(m);
    return m;
  });
  var bars = D.map(function () { var i = document.createElement('i'); segs.appendChild(i); return i; });
  var cur = 0, el = 0, last = performance.now(), paused = false, closed = false, DUR = 3800;
  function show(i) {
    var prev = els[cur];
    cur = (i + D.length) % D.length;
    els.forEach(function (m, k) {
      m.classList.toggle('on', k === cur);
      m.classList.toggle('up', m === prev && k !== cur);
      m.setAttribute('aria-hidden', String(k !== cur));
      m.tabIndex = k === cur ? 0 : -1;
    });
    setTimeout(function () { prev.classList.remove('up'); }, 600);
    bars.forEach(function (b, k) { b.style.setProperty('--f', k < cur ? 1 : 0); });
    el = 0;
  }
  (function loop(now) {
    if (!root.isConnected) return;
    var dt = now - last; last = now;
    if (!paused && !closed && !rm && !document.hidden) {
      el += dt;
      bars[cur].style.setProperty('--f', Math.min(1, el / DUR).toFixed(3));
      if (el >= DUR) show(cur + 1);
    }
    requestAnimationFrame(loop);
  })(last);
  bar.addEventListener('pointerenter', function () { paused = true; });
  bar.addEventListener('pointerleave', function () { paused = bar.contains(document.activeElement); });
  bar.addEventListener('focusin', function () { paused = true; });
  bar.addEventListener('focusout', function (e) { if (!bar.contains(e.relatedTarget)) paused = false; });
  nx.addEventListener('click', function () { show(cur + 1); });

  function close(focus) {
    closed = true;
    root.classList.add('is-closed');
    ban.inert = true;
    bell.hidden = false;
    bell.tabIndex = 0;
    void bell.offsetWidth;
    bell.classList.add('on');
    if (focus) setTimeout(function () { if (root.isConnected && closed) bell.focus({ preventScroll: true }); }, 300);
  }
  x.addEventListener('click', function () {
    close(true);
    try { localStorage.setItem(KEY, ver); } catch (e) {}
    if (typeof window.gtag === 'function') window.gtag('event', 'news_close');
  });
  bell.addEventListener('click', function () {
    closed = false;
    root.classList.remove('is-closed');
    ban.inert = false;
    bell.classList.remove('on');
    bell.tabIndex = -1;
    setTimeout(function () { if (!closed) bell.hidden = true; }, rm ? 0 : 450);
    show(0);
    x.focus({ preventScroll: true });
    try { localStorage.removeItem(KEY); } catch (e) {}
  });
  els.forEach(function (m, k) {
    m.addEventListener('click', function () {
      if (typeof window.gtag === 'function') window.gtag('event', 'news_click', { item: k + 1 });
    });
  });

  show(0);
  var was = '';
  try { was = localStorage.getItem(KEY) || ''; } catch (e) {}
  if (was && was === ver) {
    // 前に閉じたお知らせのままなら、閉じた状態で始める（たたむ動きは出さない）。
    root.style.transition = 'none';
    close(false);
    void root.offsetWidth;
    root.style.transition = '';
  }
})();
