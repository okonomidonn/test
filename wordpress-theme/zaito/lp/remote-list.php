<?php
/**
 * まとめ求人の一覧。トップページ（/）として inc/prelaunch.php から、
 * 条件別の一覧（/zaitaku/{slug}/）として inc/remote-jobs.php から（$zaito_list_cond をセットして）読み込む。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
require_once __DIR__ . '/remote-parts.php';

$zaito_cond    = isset( $zaito_list_cond ) ? zaito_remote_conditions()[ $zaito_list_cond ] : null;
$zaito_jobs    = $zaito_cond ? zaito_remote_condition_jobs( $zaito_list_cond ) : zaito_remote_jobs();
$zaito_actives = zaito_remote_active_conditions();
$zaito_open   = array_values( array_filter( $zaito_jobs, function ( $j ) {
    return empty( $j['closed'] );
} ) );
$zaito_closed = array_values( array_filter( $zaito_jobs, function ( $j ) {
    return ! empty( $j['closed'] );
} ) );

// 条件の書き添えがない求人、報酬が書かれている求人を先に並べる（同じ条件の中では元の順番）。
$zaito_rank = function ( $j ) {
    return ( empty( $j['caution'] ) ? 0 : 2 ) + ( '' !== $j['pay'] ? 0 : 1 );
};
foreach ( $zaito_open as $zaito_i => $zaito_j ) {
    $zaito_open[ $zaito_i ]['_order'] = $zaito_i;
}
usort( $zaito_open, function ( $a, $b ) use ( $zaito_rank ) {
    $d = $zaito_rank( $a ) - $zaito_rank( $b );
    return $d ? $d : $a['_order'] - $b['_order'];
} );

// 分類ごとの件数（絞り込みボタンに表示）。
$zaito_groups = array();
foreach ( $zaito_open as $zaito_j ) {
    $zaito_g = zaito_remote_group( $zaito_j['category'] );
    if ( ! isset( $zaito_groups[ $zaito_g[0] ] ) ) {
        $zaito_groups[ $zaito_g[0] ] = array( 'icon' => $zaito_g[1], 'tone' => $zaito_g[2], 'n' => 0 );
    }
    $zaito_groups[ $zaito_g[0] ]['n']++;
}
$zaito_checked  = max( array_map( function ( $j ) {
    return $j['checked'];
}, $zaito_jobs ) );
$zaito_verified = count( array_filter( $zaito_open, function ( $j ) {
    return empty( $j['caution'] );
} ) );

if ( $zaito_cond ) {
    $zaito_title = $zaito_cond['title'] . ' | zaito';
    $zaito_desc  = mb_substr( $zaito_cond['body'][0], 0, 110 ) . '（' . count( $zaito_jobs ) . '件・運営が在宅の条件を確認済み）';
    $zaito_canon = zaito_remote_condition_url( $zaito_list_cond );
} else {
    $zaito_title = 'zaito | 出社なしの仕事だけを集めた、完全在宅の求人サイト';
    $zaito_desc  = '「在宅」で探しても出社ありが混ざる。zaitoは、自宅だけで働ける完全在宅の求人を、運営が1件ずつ確認してまとめています。SNS運用、Webマーケ、教育、ライティング、エンジニアなど。';
    $zaito_canon = zaito_remote_url();
}

$zaito_items = array();
foreach ( $zaito_open as $zaito_i => $zaito_j ) {
    $zaito_items[] = array(
        '@type'    => 'ListItem',
        'position' => $zaito_i + 1,
        'url'      => zaito_remote_url( $zaito_j['slug'] ),
        'name'     => $zaito_j['title'] . '（' . $zaito_j['company'] . '）',
    );
}
zaito_remote_head( $zaito_title, $zaito_desc, $zaito_canon, array(
    '@context'        => 'https://schema.org',
    '@type'           => 'ItemList',
    'name'            => $zaito_cond ? $zaito_cond['h1'] : '完全在宅の求人',
    'itemListElement' => $zaito_items,
) );

$zaito_card = function ( $j ) {
    $g       = zaito_remote_group( $j['category'] );
    $pay     = zaito_remote_pay_parts( $j['pay'] );
    $initial = mb_substr( preg_replace( '/^(株式会社|合同会社|有限会社|学校法人)|(株式会社|合同会社|有限会社)$/u', '', $j['company'] ), 0, 1 );
    ?>
    <li class="card t-<?php echo esc_attr( $g[2] ); ?><?php echo ! empty( $j['closed'] ) ? ' closed' : ''; ?>" data-group="<?php echo esc_attr( $g[0] ); ?>">
      <a href="<?php echo esc_url( zaito_remote_url( $j['slug'] ) ); ?>">
        <div class="band"><span class="ms" aria-hidden="true"><?php echo esc_html( $g[1] ); ?></span><span class="bcat"><?php echo esc_html( $j['category'] ); ?></span></div>
        <div class="body">
          <h2><?php echo esc_html( $j['title'] ); ?></h2>
          <span class="co"><span class="av" aria-hidden="true"><?php echo esc_html( $initial ); ?></span><?php echo esc_html( $j['company'] ); ?></span>
          <div class="pay">
            <?php if ( $pay ) : ?>
              <span class="u"><?php echo esc_html( $pay[0] ); ?></span><b><?php echo esc_html( $pay[1] ); ?></b><span class="u"><?php echo esc_html( $pay[2] ); ?></span>
            <?php elseif ( '' !== $j['pay'] ) : ?>
              <span class="tx"><?php echo esc_html( $j['pay'] ); ?></span>
            <?php else : ?>
              <span class="none">報酬は募集ページで確認</span>
            <?php endif; ?>
          </div>
          <?php if ( $j['hours'] ) : ?>
            <p class="hrs"><span class="ms" aria-hidden="true">schedule</span><span><?php echo esc_html( $j['hours'] ); ?></span></p>
          <?php endif; ?>
          <div class="tags">
            <?php if ( ! empty( $j['closed'] ) ) : ?>
              <span class="warn">募集終了</span>
            <?php elseif ( ! empty( $j['caution'] ) ) : ?>
              <span class="warn"><span class="ms" aria-hidden="true">info</span>条件あり</span>
            <?php else : ?>
              <span class="ok"><span class="ms" aria-hidden="true">verified</span>完全在宅</span>
            <?php endif; ?>
            <span class="tg"><?php echo esc_html( $j['target'] ); ?></span>
          </div>
        </div>
      </a>
    </li>
    <?php
};
?>
<body>
<?php zaito_remote_header(); ?>

<section class="hero">
  <div class="wrap">
    <div class="hero-in">
      <div class="hero-copy">
        <?php if ( $zaito_cond ) : ?>
          <p class="crumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">完全在宅の求人</a> ／ <?php echo esc_html( $zaito_cond['label'] ); ?></p>
          <h1><?php echo esc_html( $zaito_cond['h1'] ); ?></h1>
          <?php foreach ( $zaito_cond['body'] as $zaito_para ) : ?>
            <p><?php echo esc_html( $zaito_para ); ?></p>
          <?php endforeach; ?>
        <?php else : ?>
        <span class="eb">REMOTE JOBS</span>
        <h1>出社なしの仕事だけを、<br>集めました。</h1>
        <p>「在宅」で探しても、出社ありの求人が混ざる。zaitoは、自宅だけで働ける求人を運営が1件ずつ確認して載せています。</p>
        <?php endif; ?>
        <a class="alert-link" href="#signup"><span class="ms" aria-hidden="true">notifications</span>新しい在宅求人をメールで受け取る</a>
      </div>
      <dl class="stats">
        <div><dt>掲載中の求人</dt><dd><b><?php echo esc_html( count( $zaito_open ) ); ?></b>件</dd></div>
        <div><dt>完全在宅を確認</dt><dd><b><?php echo esc_html( $zaito_verified ); ?></b>件</dd></div>
        <div><dt>登録・利用料</dt><dd><b>0</b>円</dd></div>
      </dl>
    </div>

    <?php if ( ! $zaito_cond && count( $zaito_groups ) > 1 ) : ?>
      <div class="filters" role="group" aria-label="職種で絞り込む">
        <button type="button" data-filter="" aria-pressed="true"><span class="ms" aria-hidden="true">apps</span>すべて<small><?php echo esc_html( count( $zaito_open ) ); ?></small></button>
        <?php foreach ( $zaito_groups as $zaito_name => $zaito_g ) : ?>
          <button type="button" class="t-<?php echo esc_attr( $zaito_g['tone'] ); ?>" data-filter="<?php echo esc_attr( $zaito_name ); ?>" aria-pressed="false"><span class="ms" aria-hidden="true"><?php echo esc_html( $zaito_g['icon'] ); ?></span><?php echo esc_html( $zaito_name ); ?><small><?php echo esc_html( $zaito_g['n'] ); ?></small></button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<main class="wrap">
  <p class="count" aria-live="polite"><b id="zcount"><?php echo esc_html( count( $zaito_open ) ); ?></b>件の求人<span>・<?php echo esc_html( date_i18n( 'n月j日', strtotime( $zaito_checked ) ) ); ?>に運営が確認</span></p>
  <ul class="cards" id="zcards">
    <?php foreach ( $zaito_open as $zaito_j ) { $zaito_card( $zaito_j ); } ?>
  </ul>

  <?php if ( $zaito_closed ) : ?>
    <h2 class="sub">募集が終了した求人</h2>
    <ul class="cards">
      <?php foreach ( $zaito_closed as $zaito_j ) { $zaito_card( $zaito_j ); } ?>
    </ul>
  <?php endif; ?>

  <?php if ( $zaito_actives ) : ?>
    <nav class="conds" aria-labelledby="conds-title">
      <h2 id="conds-title">条件で探す</h2>
      <div class="conds-list">
      <?php foreach ( $zaito_actives as $zaito_cs => $zaito_cn ) : $zaito_cd = zaito_remote_conditions()[ $zaito_cs ]; ?>
        <a href="<?php echo esc_url( zaito_remote_condition_url( $zaito_cs ) ); ?>"<?php echo isset( $zaito_list_cond ) && $zaito_list_cond === $zaito_cs ? ' aria-current="page"' : ''; ?>><span class="ms" aria-hidden="true"><?php echo esc_html( $zaito_cd['icon'] ); ?></span><?php echo esc_html( $zaito_cd['label'] ); ?><small><?php echo esc_html( $zaito_cn ); ?></small></a>
      <?php endforeach; ?>
      </div>
    </nav>
  <?php endif; ?>

  <section class="signup-band" id="signup" aria-labelledby="signup-title">
    <div class="sb-copy">
      <span class="ms" aria-hidden="true">mark_email_unread</span>
      <h2 id="signup-title">新しい在宅求人を、<br>メールで受け取る</h2>
      <p>求人は随時追加しています。メールアドレスを登録すると、新しい完全在宅の求人が入ったときにお知らせします。登録は無料です。配信をやめたいときは、届いたメールに返信するだけで止められます。</p>
    </div>
    <div class="sb-form">
      <form id="ztop" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" novalidate>
        <input type="hidden" name="action" value="zaito_remote_register">
        <input type="hidden" name="job" value="">
        <input type="hidden" name="utm_source" value=""><input type="hidden" name="utm_medium" value=""><input type="hidden" name="utm_campaign" value=""><input type="hidden" name="referrer" value="">
        <div class="hp" aria-hidden="true"><label>Webサイト<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
        <label for="ztopemail">メールアドレス</label>
        <div class="sb-row">
          <input type="email" id="ztopemail" name="email" required autocomplete="email" inputmode="email" placeholder="example@gmail.com">
          <button type="submit" class="btn btn-p btn-lg">無料で登録する</button>
        </div>
        <p class="legal">登録すると、<a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">利用規約</a>と<a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">プライバシーポリシー</a>に同意したものとみなします。</p>
        <p class="err" id="ztoperr" role="alert"></p>
      </form>
      <form class="profile" id="zprofile" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" hidden>
        <b>登録できました</b>
        <p>よければ、あなたに合う求人をお知らせするために教えてください（任意・どれも押すだけ）。</p>
        <input type="hidden" name="action" value="zaito_remote_profile">
        <?php zaito_remote_profile_fields(); ?>
        <button type="submit" class="btn btn-p btn-lg">保存する</button>
        <button type="button" class="skip" id="zskip">答えずに閉じる</button>
      </form>
      <div class="sb-done" id="zdone" hidden>
        <span class="ms" aria-hidden="true">check_circle</span>
        <b id="zdonetitle">登録が完了しました</b>
        <p id="zdonetext">新しい在宅求人が入ったら、メールでお知らせします。気になる求人は「応募ページへ進む」からそのまま応募できます。</p>
      </div>
    </div>
  </section>

  <section class="how" aria-labelledby="how-title">
    <h2 id="how-title">zaitoの使い方</h2>
    <ol>
      <li><span class="ms" aria-hidden="true">search</span><b>求人を探す</b><span>載っているのは、運営が出社の有無を確かめた求人だけです。</span></li>
      <li><span class="ms" aria-hidden="true">mail</span><b>無料で登録</b><span>メールアドレスだけで登録でき、新しい求人もお知らせします。</span></li>
      <li><span class="ms" aria-hidden="true">open_in_new</span><b>募集ページから応募</b><span>応募と選考は、各企業の募集ページ（Wantedlyなど）で行います。</span></li>
    </ol>
  </section>

  <a class="biz" href="<?php echo esc_url( home_url( '/for-companies/' ) ); ?>">
    <span><b>在宅で働く人を募集している企業の方へ</b>zaitoへの求人掲載は無料です。求人原稿の作成もお手伝いします。</span>
    <span class="ms" aria-hidden="true">arrow_forward</span>
  </a>

  <div class="disc">
    <b>この一覧について</b><br>
    企業が公開している募集ページをもとに、zaito運営が会社名・職種・報酬・稼働条件・在宅の条件をまとめたものです。応募や選考は各企業の募集ページで行われ、zaitoは応募を受け付けたり取り次いだりしません。募集内容は変わることがあるため、応募前に必ず元のページをご確認ください。掲載内容の修正・削除をご希望の企業の方は、<a href="mailto:info@zaito-work.com">info@zaito-work.com</a> までご連絡ください。
  </div>
</main>

<?php zaito_remote_footer(); ?>
<script>
(function () {
  var buttons = document.querySelectorAll('.filters button');
  var cards = document.querySelectorAll('#zcards .card');
  var count = document.getElementById('zcount');
  buttons.forEach(function (b) {
    b.addEventListener('click', function () {
      var g = b.getAttribute('data-filter');
      var n = 0;
      buttons.forEach(function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
      cards.forEach(function (c) {
        var show = !g || c.getAttribute('data-group') === g;
        c.style.display = show ? '' : 'none';
        if (show) n++;
      });
      count.textContent = n;
    });
  });
  // 新着求人のメール登録（トップページ）
  var top = document.getElementById('ztop');
  var profile = document.getElementById('zprofile');
  var stu = profile.querySelector('.stu');
  var done = document.getElementById('zdone');
  var terr = document.getElementById('ztoperr');
  function track(name, params) { if (typeof window.gtag === 'function') { window.gtag('event', name, params); } }
  function showDone(title, text) {
    top.hidden = true; profile.hidden = true; done.hidden = false;
    if (title) document.getElementById('zdonetitle').textContent = title;
    if (text) document.getElementById('zdonetext').textContent = text;
  }
  if (/(?:^|;\s*)zaito_m=1/.test(document.cookie)) {
    showDone('登録済みです', '新しい在宅求人が入ったら、登録したメールアドレスにお知らせします。');
  }
  var q = new URLSearchParams(location.search);
  ['utm_source', 'utm_medium', 'utm_campaign'].forEach(function (k) { top.elements[k].value = q.get(k) || ''; });
  top.elements.referrer.value = document.referrer || '';
  top.addEventListener('submit', function (e) {
    e.preventDefault();
    terr.textContent = '';
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(top.elements.email.value.trim())) { terr.textContent = '正しいメールアドレスを入力してください'; return; }
    var btn = top.querySelector('button[type=submit]');
    btn.disabled = true;
    // form.action は name="action" の入力欄を指してしまうため、属性から読む。
    fetch(top.getAttribute('action'), { method: 'POST', body: new FormData(top), credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res || !res.success) { throw new Error(res && res.data && res.data.message ? res.data.message : ''); }
        if (!res.data.created) { showDone('登録済みです', 'このメールアドレスはすでに登録されています。新しい在宅求人が入ったらお知らせします。'); return; }
        track('sign_up', { method: 'top' });
        top.hidden = true; profile.hidden = false;
      })
      .catch(function (x) {
        btn.disabled = false;
        terr.textContent = x.message || '登録できませんでした。時間をおいてもう一度お試しください';
      });
  });
  profile.addEventListener('change', function (e) {
    if (e.target.name === 'role') { stu.hidden = !/生$/.test(e.target.value); }
  });
  profile.addEventListener('submit', function (e) {
    e.preventDefault();
    profile.querySelector('button[type=submit]').disabled = true;
    fetch(profile.getAttribute('action'), { method: 'POST', body: new FormData(profile), credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(function () { track('profile_complete', { from: 'top' }); }, function () {})
      .then(function () { showDone(); });
  });
  document.getElementById('zskip').addEventListener('click', function () { showDone(); });
})();
</script>
</body>
</html>
