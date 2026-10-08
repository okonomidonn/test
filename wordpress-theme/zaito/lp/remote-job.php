<?php
/**
 * まとめ求人の詳細（/remote/{slug}/）。inc/remote-jobs.php から $zaito_remote_job をセットして読み込む。
 *
 * 「応募ページへ進む」は /remote/{slug}/go/ へのリンク。登録済みのブラウザ（Cookie zaito_m）なら
 * そのまま進み、未登録ならページ内の登録フォームを開く。JavaScriptが動かない場合も、
 * /go/ が未登録をこのページの #apply へ戻し、フォームの通常送信で登録できる。
 *
 * @var array $zaito_remote_job
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
require_once __DIR__ . '/remote-parts.php';

$zaito_j       = $zaito_remote_job;
$zaito_closed  = ! empty( $zaito_j['closed'] );
$zaito_go      = zaito_remote_url( $zaito_j['slug'] ) . 'go/';
$zaito_initial = mb_substr( preg_replace( '/^(株式会社|合同会社|有限会社|学校法人)|(株式会社|合同会社|有限会社)$/u', '', $zaito_j['company'] ), 0, 1 );
$zaito_error   = isset( $_GET['signup'] ) && 'error' === $_GET['signup'];
$zaito_profile_opts = zaito_remote_profile_options();

$zaito_title = $zaito_j['title'] . '（' . $zaito_j['company'] . '）｜完全在宅の求人 | zaito';
$zaito_desc  = $zaito_j['company'] . 'の「' . $zaito_j['title'] . '」。' . $zaito_j['summary'] . '在宅の条件はzaito運営が確認済みです。';

$zaito_facts = array(
    array( 'home', '在宅の条件', $zaito_j['remote'], '' ),
    array( 'payments', '報酬', $zaito_j['pay'] ? $zaito_j['pay'] : '元のページで確認してください', '' ),
    array( 'schedule', '稼働条件', $zaito_j['hours'] ? $zaito_j['hours'] : '元のページで確認してください', '' ),
    array( 'school', '対象', $zaito_j['target'], '' ),
    array( 'fact_check', 'zaitoの確認日', date_i18n( 'Y年n月j日', strtotime( $zaito_j['checked'] ) ), '募集内容はその後変わっている場合があります' ),
);

zaito_remote_head( $zaito_title, $zaito_desc, zaito_remote_url( $zaito_j['slug'] ), array(
    '@context'        => 'https://schema.org',
    '@type'           => 'BreadcrumbList',
    'itemListElement' => array(
        array( '@type' => 'ListItem', 'position' => 1, 'name' => 'zaito', 'item' => home_url( '/' ) ),
        array( '@type' => 'ListItem', 'position' => 2, 'name' => '完全在宅の求人', 'item' => zaito_remote_url() ),
        array( '@type' => 'ListItem', 'position' => 3, 'name' => $zaito_j['title'] ),
    ),
) );
?>
<body class="has-spbar">
<?php zaito_remote_header(); ?>

<main class="wrap">
  <p class="crumb"><a href="<?php echo esc_url( zaito_remote_url() ); ?>">完全在宅の求人</a> ／ <?php echo esc_html( $zaito_j['company'] ); ?></p>

  <div class="dg">
    <div>
      <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px">
        <span class="cat"><?php echo esc_html( $zaito_j['category'] ); ?></span>
        <?php if ( $zaito_closed ) : ?>
          <span class="warn">募集終了</span>
        <?php elseif ( ! empty( $zaito_j['caution'] ) ) : ?>
          <span class="warn"><span class="ms" aria-hidden="true">info</span>条件あり</span>
        <?php else : ?>
          <span class="ok"><span class="ms" aria-hidden="true">verified</span>完全在宅を確認</span>
        <?php endif; ?>
      </div>
      <h1 class="t"><?php echo esc_html( $zaito_j['title'] ); ?></h1>
      <div class="coline"><span class="av" aria-hidden="true"><?php echo esc_html( $zaito_initial ); ?></span><?php echo esc_html( $zaito_j['company'] ); ?></div>
      <p class="lead"><?php echo esc_html( $zaito_j['summary'] ); ?></p>

      <dl class="facts">
        <?php foreach ( $zaito_facts as $zaito_f ) : ?>
          <div><dt><span class="ms" aria-hidden="true"><?php echo esc_html( $zaito_f[0] ); ?></span><?php echo esc_html( $zaito_f[1] ); ?></dt><dd><?php echo esc_html( $zaito_f[2] ); ?><?php if ( $zaito_f[3] ) : ?><small><?php echo esc_html( $zaito_f[3] ); ?></small><?php endif; ?></dd></div>
        <?php endforeach; ?>
      </dl>
      <?php if ( ! empty( $zaito_j['caution'] ) ) : ?>
        <p class="caution"><span class="ms" aria-hidden="true">info</span><span><?php echo esc_html( $zaito_j['caution'] ); ?></span></p>
      <?php endif; ?>

      <section class="blk">
        <h2>応募の流れ</h2>
        <ol class="steps">
          <li>「応募ページへ進む」を押す（はじめての方はメールアドレスで無料登録）</li>
          <li><?php echo esc_html( $zaito_j['company'] ); ?>の募集ページで、仕事内容と条件を確認する</li>
          <li>募集ページから応募する（選考は企業と直接やりとりします）</li>
        </ol>
      </section>

      <section class="blk">
        <h2>この求人情報について</h2>
        <p>企業が公開している募集ページをもとに、zaito運営が在宅の条件などを確認してまとめたものです。仕事内容のくわしい説明は、元の募集ページをご覧ください。zaitoは応募を受け付けたり取り次いだりしません。</p>
        <p>掲載内容の修正・削除をご希望の企業の方は、<a href="mailto:info@zaito-work.com?subject=<?php echo rawurlencode( 'まとめ求人の掲載について（' . $zaito_j['company'] . '）' ); ?>">info@zaito-work.com</a> までご連絡ください。</p>
      </section>
    </div>

    <aside class="side">
      <div class="apply" id="apply">
        <div class="pay">報酬<b><?php echo esc_html( $zaito_j['pay'] ? $zaito_j['pay'] : '元のページで確認' ); ?></b></div>
        <?php if ( $zaito_closed ) : ?>
          <span class="btn btn-g btn-lg" aria-disabled="true">募集は終了しました</span>
          <p class="cap"><a href="<?php echo esc_url( zaito_remote_url() ); ?>">ほかの完全在宅の求人を見る</a></p>
        <?php else : ?>
          <a class="btn btn-p btn-lg" id="zgo" href="<?php echo esc_url( $zaito_go ); ?>" rel="nofollow">応募ページへ進む<span class="ms" aria-hidden="true">open_in_new</span></a>
          <p class="cap"><?php echo esc_html( $zaito_j['company'] ); ?>の募集ページに移動します</p>

          <form class="signup<?php echo $zaito_error ? ' on' : ''; ?>" id="zsignup" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" novalidate>
            <b>無料登録して応募ページへ</b>
            <p>メールアドレスだけで登録できます。次回からはすぐに応募ページへ進めます。新しい完全在宅の求人もお知らせします。</p>
            <input type="hidden" name="action" value="zaito_remote_register">
            <input type="hidden" name="job" value="<?php echo esc_attr( $zaito_j['slug'] ); ?>">
            <input type="hidden" name="utm_source" value="">
            <input type="hidden" name="utm_medium" value="">
            <input type="hidden" name="utm_campaign" value="">
            <input type="hidden" name="referrer" value="">
            <div class="hp" aria-hidden="true"><label>Webサイト<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
            <label for="zemail">メールアドレス</label>
            <input type="email" id="zemail" name="email" required autocomplete="email" inputmode="email" placeholder="example@gmail.com">
            <button type="submit" class="btn btn-p btn-lg">登録して応募ページへ進む</button>
            <p class="legal">登録すると、<a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">利用規約</a>と<a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">プライバシーポリシー</a>に同意したものとみなします。</p>
            <p class="err" id="zerr" role="alert"><?php echo $zaito_error ? '登録できませんでした。メールアドレスを確認して、もう一度お試しください。' : ''; ?></p>
          </form>

          <form class="profile" id="zprofile" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" hidden>
            <b>登録できました</b>
            <p>よければ、あなたに合う求人をお知らせするために教えてください（任意・どれも押すだけ）。</p>
            <input type="hidden" name="action" value="zaito_remote_profile">
            <fieldset><legend>いまの立場</legend><div class="chips">
              <?php foreach ( $zaito_profile_opts['role'] as $zaito_o ) : ?>
                <label><input type="radio" name="role" value="<?php echo esc_attr( $zaito_o ); ?>"><span><?php echo esc_html( $zaito_o ); ?></span></label>
              <?php endforeach; ?>
            </div></fieldset>
            <div class="stu" hidden>
              <fieldset><legend>学年</legend><div class="chips">
                <?php foreach ( $zaito_profile_opts['grade'] as $zaito_o ) : ?>
                  <label><input type="radio" name="grade" value="<?php echo esc_attr( $zaito_o ); ?>"><span><?php echo esc_html( $zaito_o ); ?></span></label>
                <?php endforeach; ?>
              </div></fieldset>
              <label class="lb" for="zschool">学校名</label>
              <input type="text" id="zschool" name="school" maxlength="80" autocomplete="organization" placeholder="例：〇〇大学">
            </div>
            <fieldset><legend>やってみたい仕事（いくつでも）</legend><div class="chips">
              <?php foreach ( $zaito_profile_opts['interests'] as $zaito_o ) : ?>
                <label><input type="checkbox" name="interests[]" value="<?php echo esc_attr( $zaito_o ); ?>"><span><?php echo esc_html( $zaito_o ); ?></span></label>
              <?php endforeach; ?>
            </div></fieldset>
            <button type="submit" class="btn btn-p btn-lg">保存して応募ページへ進む</button>
            <a class="skip" href="<?php echo esc_url( $zaito_go ); ?>" rel="nofollow">答えずに応募ページへ進む</a>
          </form>
        <?php endif; ?>
      </div>
    </aside>
  </div>
</main>

<div class="spbar">
  <?php if ( $zaito_closed ) : ?>
    <a class="btn btn-g" href="<?php echo esc_url( zaito_remote_url() ); ?>">ほかの求人を見る</a>
  <?php else : ?>
    <a class="btn btn-p" id="zgo-sp" href="<?php echo esc_url( $zaito_go ); ?>" rel="nofollow">応募ページへ進む<span class="ms" aria-hidden="true">open_in_new</span></a>
  <?php endif; ?>
</div>

<?php zaito_remote_footer(); ?>
<?php if ( ! $zaito_closed ) : ?>
<script>
(function () {
  var slug = <?php echo wp_json_encode( $zaito_j['slug'] ); ?>;
  var form = document.getElementById('zsignup');
  var err = document.getElementById('zerr');
  var profile = document.getElementById('zprofile');
  var stu = profile.querySelector('.stu');
  function member() { return /(?:^|;\s*)zaito_m=1/.test(document.cookie); }
  function track(name, params) { if (typeof window.gtag === 'function') { window.gtag('event', name, params); } }
  function openForm() {
    form.classList.add('on');
    document.body.classList.add('form-open'); // スマホ下部の固定ボタンとフォームが重ならないようにする
    document.getElementById('apply').scrollIntoView({ behavior: 'smooth', block: 'start' });
    setTimeout(function () { document.getElementById('zemail').focus({ preventScroll: true }); }, 350);
  }

  // 流入経路（UTM・参照元）を登録データと一緒に保存する。
  var q = new URLSearchParams(location.search);
  ['utm_source', 'utm_medium', 'utm_campaign'].forEach(function (k) { form.elements[k].value = q.get(k) || ''; });
  form.elements.referrer.value = document.referrer || '';

  ['zgo', 'zgo-sp'].forEach(function (id) {
    var a = document.getElementById(id);
    if (!a) return;
    a.addEventListener('click', function (e) {
      if (member()) { track('remote_apply_click', { job: slug }); return; }
      e.preventDefault();
      openForm();
    });
  });
  if (location.hash === '#apply' && !member()) { openForm(); }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    err.textContent = '';
    var email = form.elements.email.value.trim();
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { err.textContent = '正しいメールアドレスを入力してください'; return; }
    var btn = form.querySelector('button[type=submit]');
    btn.disabled = true;
    // form.action は name="action" の入力欄を指してしまうため、属性から読む。
    fetch(form.getAttribute('action'), { method: 'POST', body: new FormData(form), credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res || !res.success) { throw new Error(res && res.data && res.data.message ? res.data.message : ''); }
        track('remote_apply_click', { job: slug });
        if (!res.data.created) { location.href = res.data.go; return; }
        // はじめて登録した人にだけ、任意のプロフィールを聞いてから応募ページへ進める。
        track('sign_up', { method: 'remote' });
        form.hidden = true;
        profile.hidden = false;
        profile.dataset.go = res.data.go;
        profile.scrollIntoView({ behavior: 'smooth', block: 'start' });
      })
      .catch(function (x) {
        btn.disabled = false;
        err.textContent = x.message || '登録できませんでした。時間をおいてもう一度お試しください';
      });
  });

  // 学生を選んだときだけ、学年と学校名を出す。
  profile.addEventListener('change', function (e) {
    if (e.target.name === 'role') { stu.hidden = !/生$/.test(e.target.value); }
  });
  profile.addEventListener('submit', function (e) {
    e.preventDefault();
    var btn = profile.querySelector('button[type=submit]');
    btn.disabled = true;
    var go = function () { location.href = profile.dataset.go; };
    fetch(profile.getAttribute('action'), { method: 'POST', body: new FormData(profile), credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(function () { track('profile_complete', { job: slug }); }, function () {})
      .then(go, go);
  });
})();
</script>
<?php endif; ?>
</body>
</html>
