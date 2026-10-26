/*
 * トップページの「スワイプで探す」。一覧の求人カード（#zcards .card）を1枚ずつ重ねて出し、
 * 右へ（または ♡）で「気になる」＝保存（fav.js の window.zaitoFav）、左へ（または ×）でスキップする。
 * いま絞り込んでいる条件で表示中の求人だけを出す。見終わった求人はこのブラウザに覚えておき、次は続きから出す。
 * 企業に何かを送る仕組みではない（応募は今までどおり各企業の募集ページで行う）。
 */
(function () {
  var open = document.getElementById('zswipe-open');
  var list = document.getElementById('zcards');
  if (!open || !list) return;
  var SEEN = 'zaito_swiped';
  var rm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function seen() {
    try { var a = JSON.parse(localStorage.getItem(SEEN) || '[]'); return Array.isArray(a) ? a : []; } catch (e) { return []; }
  }
  function markSeen(slug) {
    var a = seen().filter(function (s) { return s !== slug; });
    a.push(slug);
    try { localStorage.setItem(SEEN, JSON.stringify(a.slice(-300))); } catch (e) {}
  }
  function track(name, p) { if (typeof window.gtag === 'function') window.gtag('event', name, p || {}); }
  function like(slug) {
    var F = window.zaitoFav;
    if (!F) return;
    var favs = F.load();
    if (favs.indexOf(slug) !== -1) return;
    favs.unshift(slug);
    F.save(favs);
    F.paint();
    if (F.post) F.post({ action: 'zaito_fav', slug: slug, on: '1', bid: F.bid() }).catch(function () {});
  }

  // 画面
  var dlg = document.createElement('div');
  dlg.className = 'sw';
  dlg.hidden = true;
  dlg.setAttribute('role', 'dialog');
  dlg.setAttribute('aria-modal', 'true');
  dlg.setAttribute('aria-label', 'スワイプで求人を探す');
  dlg.innerHTML =
    '<div class="sw-in">' +
      '<div class="sw-hd"><p class="sw-n" aria-live="polite"></p>' +
      '<button type="button" class="sw-close" aria-label="閉じる"><span class="ms" aria-hidden="true">close</span></button></div>' +
      '<p class="sw-help">右へで「気になる」、左へでスキップ。気になる求人は「保存した求人」に入ります。</p>' +
      '<div class="sw-stack"></div>' +
      '<div class="sw-end" hidden></div>' +
      '<div class="sw-act">' +
        '<button type="button" class="sw-b sw-no" aria-label="スキップ"><span class="ms" aria-hidden="true">close</span></button>' +
        '<a class="sw-more" href="#">詳しく見る</a>' +
        '<button type="button" class="sw-b sw-yes" aria-label="気になる"><span class="ms" aria-hidden="true">favorite</span></button>' +
      '</div>' +
    '</div>';
  document.body.appendChild(dlg);
  var stack = dlg.querySelector('.sw-stack'), end = dlg.querySelector('.sw-end'), act = dlg.querySelector('.sw-act');
  var countEl = dlg.querySelector('.sw-n'), more = dlg.querySelector('.sw-more'), help = dlg.querySelector('.sw-help');

  var queue = [], pos = 0, liked = 0, busy = false;

  // 一覧のカードを写して、スワイプ用のカードにする（リンクは外し、保存ボタンは消す）。
  function build(li) {
    var c = li.cloneNode(true);
    c.classList.add('sw-card');
    c.removeAttribute('id');
    var fav = c.querySelector('.fav');
    if (fav) fav.remove();
    var a = c.querySelector('a');
    if (a) {
      var d = document.createElement('div');
      d.className = 'sw-face';
      while (a.firstChild) d.appendChild(a.firstChild);
      a.replaceWith(d);
    }
    c.insertAdjacentHTML('beforeend', '<span class="sw-stamp sw-stamp-y">気になる</span><span class="sw-stamp sw-stamp-n">スキップ</span>');
    return c;
  }

  function current() { return queue[pos]; }
  function render() {
    stack.innerHTML = '';
    var left = queue.length - pos;
    if (left <= 0) { finish(); return; }
    end.hidden = true;
    act.hidden = false;
    stack.hidden = false;
    help.hidden = false;
    countEl.textContent = (pos + 1) + ' / ' + queue.length + '件';
    // 上の2枚だけ置く（後ろの1枚は少し小さく見せる）。
    for (var i = Math.min(pos + 1, queue.length - 1); i >= pos; i--) {
      var c = build(queue[i]);
      if (i > pos) c.classList.add('sw-next'); else c.classList.add('sw-top');
      stack.appendChild(c);
    }
    more.href = current().querySelector('a').href;
    drag(stack.querySelector('.sw-top'));
  }

  function finish() {
    countEl.textContent = '';
    act.hidden = true;
    stack.hidden = true;
    help.hidden = true;
    var member = /(?:^|;\s*)zaito_m=1/.test(document.cookie);
    end.innerHTML =
      '<span class="ms" aria-hidden="true">task_alt</span>' +
      '<p class="sw-end-t">' + (queue.length ? 'すべて見ました' : 'いまの条件で見られる求人はありません') + '</p>' +
      '<p>' + (liked ? '「気になる」にした' + liked + '件は、保存した求人で見られます。' : '気になる求人がなければ、条件を変えて探してみてください。') + '</p>' +
      (liked ? '<button type="button" class="btn btn-p sw-favs">保存した求人を見る</button>' : '') +
      (member ? '' : '<a class="btn btn-g sw-signup" href="#signup">新着をメールで受け取る</a>') +
      '<button type="button" class="sw-again">最初から見る</button>';
    end.hidden = false;
    track('swipe_end', { liked: liked, total: queue.length });
  }

  function go(dir) {
    if (busy) return;
    var c = stack.querySelector('.sw-top');
    var li = current();
    if (!c || !li) return;
    busy = true;
    var slug = li.getAttribute('data-slug');
    if (dir > 0) { like(slug); liked++; }
    markSeen(slug);
    track(dir > 0 ? 'swipe_like' : 'swipe_skip', { job: slug });
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
      var dy = (e.clientY - y0) * 0.2;
      c.style.transform = 'translate(' + dx + 'px,' + dy + 'px) rotate(' + (dx / 18) + 'deg)';
      c.classList.toggle('is-yes', dx > 40);
      c.classList.toggle('is-no', dx < -40);
    });
    function up() {
      if (!on) return;
      on = false;
      var fast = Math.abs(dx) > 40 && Date.now() - t0 < 250;
      if (Math.abs(dx) > c.offsetWidth * 0.3 || fast) { go(dx > 0 ? 1 : -1); return; }
      c.style.transition = rm ? 'none' : 'transform .25s ease';
      c.style.transform = '';
      c.classList.remove('is-yes', 'is-no');
      // ほとんど動かさずに離したら、タップとして詳しく見る。
      if (Math.abs(dx) < 6 && Date.now() - t0 < 300) { track('swipe_detail', { job: current().getAttribute('data-slug') }); location.href = more.href; }
    }
    c.addEventListener('pointerup', up);
    c.addEventListener('pointercancel', up);
  }

  function start(fresh) {
    // いま一覧に出ている（絞り込みで隠れていない）募集中の求人だけ。
    var all = Array.prototype.filter.call(list.querySelectorAll('.card:not(.closed)'), function (li) { return !li.hidden && li.offsetParent !== null; });
    var s = fresh ? [] : seen();
    var rest = all.filter(function (li) { return s.indexOf(li.getAttribute('data-slug')) === -1; });
    queue = rest.length ? rest : all;
    pos = 0; liked = 0;
    render();
  }

  var back = null;
  function show() {
    back = document.activeElement;
    dlg.hidden = false;
    document.documentElement.classList.add('sw-lock');
    start(false);
    track('swipe_open', { total: queue.length });
    dlg.querySelector('.sw-close').focus();
  }
  function hide() {
    dlg.hidden = true;
    document.documentElement.classList.remove('sw-lock');
    if (back && back.focus) back.focus();
  }

  open.addEventListener('click', function (e) { e.preventDefault(); show(); });
  dlg.querySelector('.sw-close').addEventListener('click', hide);
  dlg.querySelector('.sw-no').addEventListener('click', function () { go(-1); });
  dlg.querySelector('.sw-yes').addEventListener('click', function () { go(1); });
  more.addEventListener('click', function () { if (current()) track('swipe_detail', { job: current().getAttribute('data-slug') }); });
  end.addEventListener('click', function (e) {
    if (e.target.closest('.sw-again')) { start(true); return; }
    if (e.target.closest('.sw-favs')) {
      hide();
      var q = document.querySelector('.qfav');
      if (q && q.getAttribute('aria-pressed') !== 'true') q.click();
      var bar = document.querySelector('.count-bar');
      if (bar) bar.scrollIntoView({ behavior: rm ? 'auto' : 'smooth', block: 'start' });
      return;
    }
    if (e.target.closest('.sw-signup')) { hide(); }
  });
  dlg.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') hide();
    else if (e.key === 'ArrowRight' && !act.hidden) go(1);
    else if (e.key === 'ArrowLeft' && !act.hidden) go(-1);
  });
})();
