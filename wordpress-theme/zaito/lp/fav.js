/*
 * 求人の保存（♡）。保存した求人はこのブラウザ（localStorage）に置き、
 * ♡を押すたびにサーバーへ送って数を記録し、マイページを使えるブラウザでは登録にも保存する（inc/mypage.php）。
 * 読み込み元の <script data-ajax="admin-ajax.phpのURL" data-my="マイページのURL">。
 */
(function () {
  var script = document.currentScript;
  var ajax = script && script.getAttribute('data-ajax');
  var myUrl = script && script.getAttribute('data-my');
  var KEY = 'zaito_favs';

  function load() {
    try {
      var a = JSON.parse(localStorage.getItem(KEY) || '[]');
      return Array.isArray(a) ? a.filter(function (s) { return typeof s === 'string'; }) : [];
    } catch (e) { return []; }
  }
  function save(list) {
    try { localStorage.setItem(KEY, JSON.stringify(list.slice(0, 100))); } catch (e) {}
  }
  // 匿名のブラウザID（♡の数を「同じブラウザは1回」と数えるため）。
  function bid() {
    try {
      var v = localStorage.getItem('zaito_bid');
      if (!v) {
        v = Array.prototype.map.call(crypto.getRandomValues(new Uint8Array(12)), function (n) { return ('0' + n.toString(16)).slice(-2); }).join('');
        localStorage.setItem('zaito_bid', v);
      }
      return v;
    } catch (e) { return ''; }
  }
  function post(data) {
    var fd = new FormData();
    Object.keys(data).forEach(function (k) {
      [].concat(data[k]).forEach(function (v) { fd.append(Array.isArray(data[k]) ? k + '[]' : k, v); });
    });
    return fetch(ajax, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); });
  }

  function paint() {
    var list = load();
    document.querySelectorAll('[data-fav]').forEach(function (b) {
      var on = list.indexOf(b.getAttribute('data-fav')) !== -1;
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
      var lb = b.querySelector('.lb');
      if (lb) lb.textContent = on ? '保存済み' : '保存する';
    });
  }

  var toast;
  function notice(on) {
    if (!toast) {
      toast = document.createElement('div');
      toast.className = 'fav-toast';
      toast.setAttribute('role', 'status');
      document.body.appendChild(toast);
    }
    toast.innerHTML = '';
    toast.appendChild(document.createTextNode(on ? '保存しました。' : '保存をやめました。'));
    if (on && myUrl) {
      var a = document.createElement('a');
      a.href = myUrl;
      a.textContent = 'マイページで見る';
      toast.appendChild(a);
    }
    toast.classList.add('on');
    clearTimeout(toast._t);
    toast._t = setTimeout(function () { toast.classList.remove('on'); }, 3200);
  }

  function toggle(slug) {
    var list = load();
    var on = list.indexOf(slug) === -1;
    list = list.filter(function (s) { return s !== slug; });
    if (on) list.unshift(slug);
    save(list);
    paint();
    notice(on);
    if (typeof window.gtag === 'function') window.gtag('event', on ? 'fav_add' : 'fav_remove', { job: slug });
    if (ajax) post({ action: 'zaito_fav', slug: slug, on: on ? '1' : '', bid: bid() }).catch(function () {});
  }

  document.addEventListener('click', function (e) {
    var b = e.target.closest && e.target.closest('[data-fav]');
    if (!b) return;
    e.preventDefault();
    e.stopPropagation();
    toggle(b.getAttribute('data-fav'));
  });
  // 戻るボタンで表示し直したときや、別のタブで保存したときも表示をそろえる。
  window.addEventListener('pageshow', paint);
  window.addEventListener('storage', function (e) { if (e.key === KEY) paint(); });
  paint();

  // 登録済みのブラウザでは、ヘッダーの「無料登録」を出さない。
  if (/(?:^|;\s*)zaito_m=1/.test(document.cookie)) {
    document.querySelectorAll('.hd .js-signup').forEach(function (a) { a.remove(); });
  }

  window.zaitoFav = { load: load, save: save, paint: paint, post: post, bid: bid };
})();
