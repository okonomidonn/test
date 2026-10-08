<?php
/**
 * まとめ求人の一覧。トップページ（/）として inc/prelaunch.php から読み込む。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
require_once __DIR__ . '/remote-parts.php';

$zaito_jobs   = zaito_remote_jobs();
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

$zaito_title = 'zaito | 出社なしの仕事だけを集めた、完全在宅の求人サイト';
$zaito_desc  = '「在宅」で探しても出社ありが混ざる。zaitoは、自宅だけで働ける完全在宅の求人を、運営が1件ずつ確認してまとめています。SNS運用、Webマーケ、教育、ライティング、エンジニアなど。';

$zaito_items = array();
foreach ( $zaito_open as $zaito_i => $zaito_j ) {
    $zaito_items[] = array(
        '@type'    => 'ListItem',
        'position' => $zaito_i + 1,
        'url'      => zaito_remote_url( $zaito_j['slug'] ),
        'name'     => $zaito_j['title'] . '（' . $zaito_j['company'] . '）',
    );
}
zaito_remote_head( $zaito_title, $zaito_desc, zaito_remote_url(), array(
    '@context'        => 'https://schema.org',
    '@type'           => 'ItemList',
    'name'            => '完全在宅の求人',
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
        <span class="eb">REMOTE JOBS</span>
        <h1>出社なしの仕事だけを、<br>集めました。</h1>
        <p>「在宅」で探しても、出社ありの求人が混ざる。zaitoは、自宅だけで働ける求人を運営が1件ずつ確認して載せています。</p>
      </div>
      <dl class="stats">
        <div><dt>掲載中の求人</dt><dd><b><?php echo esc_html( count( $zaito_open ) ); ?></b>件</dd></div>
        <div><dt>完全在宅を確認</dt><dd><b><?php echo esc_html( $zaito_verified ); ?></b>件</dd></div>
        <div><dt>登録・利用料</dt><dd><b>0</b>円</dd></div>
      </dl>
    </div>

    <?php if ( count( $zaito_groups ) > 1 ) : ?>
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
})();
</script>
</body>
</html>
