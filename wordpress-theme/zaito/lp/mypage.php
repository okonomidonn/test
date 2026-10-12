<?php
/**
 * マイページ（/mypage/）。中身（保存した求人・希望条件）は inc/mypage.php の admin-ajax から読み込む。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
require_once __DIR__ . '/remote-parts.php';

zaito_remote_head( 'マイページ | zaito', '保存した求人と、新着メールの希望条件を確認・変更できます。', home_url( '/mypage/' ) );
$zaito_ajax = admin_url( 'admin-ajax.php' );
?>
<body>
<?php zaito_remote_header(); ?>
<main class="wrap doc">
  <a class="back" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span class="ms" aria-hidden="true">arrow_back</span>求人一覧へ</a>
  <h1>マイページ</h1>
  <p class="lead" id="zmy-who">読み込んでいます…</p>

  <section class="my-sec" aria-labelledby="zmy-fav-t">
    <h2 id="zmy-fav-t">保存した求人</h2>
    <p>求人の <span class="ms" aria-hidden="true" style="font-size:16px;vertical-align:-3px">favorite</span> を押すと、ここに保存されます。</p>
    <ul class="my-favs" id="zmy-favs"></ul>
    <p class="my-empty" id="zmy-empty" hidden>保存した求人はまだありません。<a href="<?php echo esc_url( home_url( '/' ) ); ?>">求人を探す</a></p>
  </section>

  <section class="my-sec" id="zmy-guest" hidden aria-labelledby="zmy-login-t">
    <h2 id="zmy-login-t">登録したメールアドレスでログイン</h2>
    <p id="zmy-guest-text">ログインすると、希望条件の変更や、別のスマホ・パソコンでの保存した求人の確認ができます。登録したメールアドレスに、ログイン用のリンクを送ります。</p>
    <form class="my-login" id="zmy-login" novalidate>
      <label class="sr" for="zmy-email">メールアドレス</label>
      <input type="email" id="zmy-email" required autocomplete="email" inputmode="email" placeholder="example@gmail.com">
      <div class="hp" aria-hidden="true"><label>Webサイト<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
      <button type="submit" class="btn btn-p">ログインリンクを送る</button>
    </form>
    <p class="err" id="zmy-login-err" role="alert"></p>
    <p class="my-ok" id="zmy-sent" hidden>登録されているメールアドレスなら、ログイン用のリンクを送りました。メールを確認してください（届かないときは迷惑メールのフォルダも確認してください）。</p>
    <p style="margin-top:14px;font-size:14px;color:var(--z-text-3)">まだ登録していない方は、<a href="<?php echo esc_url( home_url( '/#signup' ) ); ?>">トップページから無料で登録</a>できます。</p>
  </section>

  <section class="my-sec" id="zmy-member" hidden aria-labelledby="zmy-pref-t">
    <h2 id="zmy-pref-t">新着メールの希望条件</h2>
    <p>ここで選んだ条件に合う新しい求人だけを、メールでお知らせします。</p>
    <form class="profile" id="zmy-prefs" style="margin-top:0;padding-top:0;border-top:0">
      <?php zaito_remote_profile_fields(); ?>
      <label class="my-mail"><input type="checkbox" name="mail" id="zmy-mail" value="on">新着求人のメールを受け取る</label>
      <div class="my-acts">
        <button type="submit" class="btn btn-p btn-lg">保存する</button>
        <span class="my-ok" id="zmy-saved" role="status" hidden>保存しました</span>
      </div>
      <p class="err" id="zmy-err" role="alert"></p>
    </form>
    <button type="button" class="my-out" id="zmy-logout">このブラウザでログアウト</button>
  </section>
</main>
<?php zaito_remote_footer(); ?>
<script>
(function () {
  var AJAX = <?php echo wp_json_encode( $zaito_ajax ); ?>;
  var F = window.zaitoFav;
  var who = document.getElementById('zmy-who');
  var favs = document.getElementById('zmy-favs');
  var empty = document.getElementById('zmy-empty');
  var guest = document.getElementById('zmy-guest');
  var member = document.getElementById('zmy-member');
  var prefs = document.getElementById('zmy-prefs');
  var stu = prefs.querySelector('.stu');
  var state = 'guest';

  function post(data) { return F.post(data); }

  function renderFavs(list) {
    favs.innerHTML = '';
    list.forEach(function (j) {
      var li = document.createElement('li');
      var a = document.createElement('a');
      a.className = 't';
      a.href = j.url;
      var b = document.createElement('b');
      b.textContent = j.title;
      var s = document.createElement('span');
      s.textContent = j.company + '・' + j.pay;
      a.appendChild(b);
      a.appendChild(s);
      if (j.closed) {
        var c = document.createElement('span');
        c.className = 'cl';
        c.textContent = '募集終了';
        a.appendChild(c);
      }
      var x = document.createElement('button');
      x.type = 'button';
      x.className = 'x';
      x.textContent = '外す';
      x.addEventListener('click', function () {
        F.save(F.load().filter(function (v) { return v !== j.slug; }));
        post({ action: 'zaito_fav', slug: j.slug, on: '', bid: F.bid() }).catch(function () {});
        li.remove();
        empty.hidden = favs.children.length > 0;
      });
      li.appendChild(a);
      li.appendChild(x);
      favs.appendChild(li);
    });
    empty.hidden = list.length > 0;
  }

  function fillPrefs(p) {
    ['role', 'grade', 'days', 'min_pay'].forEach(function (k) {
      prefs.querySelectorAll('input[name="' + k + '"]').forEach(function (el) { el.checked = el.value === p[k]; });
    });
    prefs.querySelectorAll('input[name="interests[]"]').forEach(function (el) { el.checked = p.interests.indexOf(el.value) !== -1; });
    prefs.elements.school.value = p.school || '';
    showStu();
  }
  function showStu() {
    var r = prefs.querySelector('input[name="role"]:checked');
    stu.hidden = !(r && /学生|高校生/.test(r.value));
  }
  prefs.addEventListener('change', showStu);

  // 本人確認が済んだブラウザで初めて開いたときだけ、このブラウザの保存を登録に移す。
  var local = F.load();
  var merged = false;
  try { merged = localStorage.getItem('zaito_favs_merged') === '1'; } catch (e) {}
  post({ action: 'zaito_my_data', local: local, merge: merged ? '' : '1' }).then(function (r) {
    var d = r && r.data;
    if (!r || !r.success || !d) throw new Error();
    state = d.state;
    renderFavs(d.favorites);
    if (d.state === 'member') {
      try { localStorage.setItem('zaito_favs_merged', '1'); } catch (e) {}
      F.save(d.favorites.map(function (j) { return j.slug; }));
      F.paint();
      who.textContent = d.email + ' でログインしています。';
      fillPrefs(d.prefs);
      document.getElementById('zmy-mail').checked = !d.unsubscribed;
      member.hidden = false;
    } else {
      who.textContent = 'このブラウザで保存した求人を表示しています。';
      if (/[?&]login=expired/.test(location.search)) {
        document.getElementById('zmy-guest-text').textContent = 'ログイン用のリンクの期限が切れているか、正しくありません。もう一度メールアドレスを入れてください。';
      }
      guest.hidden = false;
    }
  }).catch(function () {
    who.textContent = '読み込めませんでした。時間をおいて、もう一度開いてください。';
  });

  document.getElementById('zmy-login').addEventListener('submit', function (e) {
    e.preventDefault();
    var err = document.getElementById('zmy-login-err');
    var v = document.getElementById('zmy-email').value.trim();
    err.textContent = '';
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) { err.textContent = '正しいメールアドレスを入力してください'; return; }
    var btn = this.querySelector('button');
    btn.disabled = true;
    post({ action: 'zaito_my_login', email: v, website: this.querySelector('[name=website]').value }).then(function (r) {
      btn.disabled = false;
      if (r && r.success) { document.getElementById('zmy-sent').hidden = false; }
      else { err.textContent = (r && r.data && r.data.message) || '送信できませんでした'; }
    }).catch(function () { btn.disabled = false; err.textContent = '送信できませんでした。通信状況を確認してください'; });
  });

  prefs.addEventListener('submit', function (e) {
    e.preventDefault();
    var fd = new FormData(prefs);
    fd.set('action', 'zaito_my_save');
    fd.set('mail', document.getElementById('zmy-mail').checked ? 'on' : 'off');
    var ok = document.getElementById('zmy-saved'), err = document.getElementById('zmy-err');
    ok.hidden = true; err.textContent = '';
    fetch(AJAX, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (r) {
      if (r && r.success) { ok.hidden = false; if (typeof window.gtag === 'function') window.gtag('event', 'mypage_save'); }
      else { err.textContent = (r && r.data && r.data.message) || '保存できませんでした'; }
    }).catch(function () { err.textContent = '保存できませんでした。通信状況を確認してください'; });
  });

  document.getElementById('zmy-logout').addEventListener('click', function () {
    post({ action: 'zaito_my_logout' }).then(function () {
      try { localStorage.removeItem('zaito_favs'); localStorage.removeItem('zaito_favs_merged'); } catch (e) {}
      location.reload();
    });
  });
})();
</script>
</body>
</html>
