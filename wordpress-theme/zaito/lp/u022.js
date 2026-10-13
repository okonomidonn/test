/* Lucian UX/UI Prompt #022 · MIT License (c) 2026 Seokwoooo · https://lucianlabs.dev/uxui-prompt/#022 */
/*
 * 保存（しおり）ボタン（[data-fav].u022）の見た目。保存そのものは lp/fav.js が行い、aria-pressed を切り替える。
 * ここでは aria-pressed に合わせてしおりを塗り、押して保存したときだけはじける動きを出す。
 * 求人ページの「◯人が保存しています」（.u022-meta）は、押すたびに数字を1つ回して増減させる。
 */
(function () {
  var rm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var btns = document.querySelectorAll('[data-fav].u022');
  function sync(b) { b.classList.toggle('is-on', b.getAttribute('aria-pressed') === 'true'); }
  btns.forEach(function (b) {
    sync(b);
    // 戻るボタン・別タブでの保存など、fav.js が表示を直したときも塗りをそろえる。
    new MutationObserver(function () { sync(b); }).observe(b, { attributes: true, attributeFilter: ['aria-pressed'] });
  });

  function rollTo(meta, v, up) {
    var roll = meta.querySelector('.u022-roll');
    var ci = +(meta.getAttribute('data-ci') || 0);
    var cur = roll.children[ci], nxt = roll.children[1 - ci];
    nxt.style.transition = 'none';
    nxt.textContent = v;
    nxt.style.transform = 'translateY(' + (up ? 100 : -100) + '%)'; nxt.style.opacity = '0';
    void nxt.offsetWidth;
    nxt.style.transition = '';
    nxt.style.transform = 'none'; nxt.style.opacity = '1';
    cur.style.transform = 'translateY(' + (up ? -100 : 100) + '%)'; cur.style.opacity = '0';
    meta.setAttribute('data-ci', String(1 - ci));
  }

  document.addEventListener('click', function (e) {
    var b = e.target.closest && e.target.closest('[data-fav].u022');
    if (!b) return;
    // fav.js の切り替えが終わってから読む。
    setTimeout(function () {
      var on = b.getAttribute('aria-pressed') === 'true';
      sync(b);
      b.classList.remove('is-pop');
      clearTimeout(b._u022);
      if (on && !rm) {
        void b.offsetWidth;
        b.classList.add('is-pop');
        b._u022 = setTimeout(function () { b.classList.remove('is-pop'); }, 900);
      }
      var meta = document.querySelector('.u022-meta[data-fav-count="' + b.getAttribute('data-fav') + '"]');
      if (meta) {
        var n = Math.max(0, +meta.getAttribute('data-n') + (on ? 1 : -1));
        meta.setAttribute('data-n', String(n));
        meta.hidden = n === 0;
        rollTo(meta, n, on);
      }
    }, 0);
  });
})();
