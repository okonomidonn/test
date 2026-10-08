<?php
/**
 * まとめ求人の一覧（/remote/）。inc/remote-jobs.php から読み込む。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
require_once __DIR__ . '/remote-parts.php';

/**
 * 職種を絞り込みの大きな分類にまとめる。
 */
function zaito_remote_group( $category ) {
    $groups = array(
        'SNS・マーケ'        => array( 'SNS', 'マーケ' ),
        '教育・学習サポート' => array( '教育', '学習' ),
        'ライティング・編集' => array( 'ライティング', '編集' ),
        'エンジニア・デザイン' => array( 'エンジニア', 'デザイン' ),
        '事務・アシスタント' => array( '事務', 'アシスタント' ),
    );
    foreach ( $groups as $name => $keys ) {
        foreach ( $keys as $key ) {
            if ( false !== strpos( $category, $key ) ) {
                return $name;
            }
        }
    }
    return 'その他';
}

$zaito_jobs   = zaito_remote_jobs();
$zaito_open   = array_values( array_filter( $zaito_jobs, function ( $j ) {
    return empty( $j['closed'] );
} ) );
$zaito_closed = array_values( array_filter( $zaito_jobs, function ( $j ) {
    return ! empty( $j['closed'] );
} ) );
$zaito_groups = array();
foreach ( $zaito_open as $zaito_j ) {
    $zaito_groups[ zaito_remote_group( $zaito_j['category'] ) ] = true;
}
$zaito_checked = max( array_map( function ( $j ) {
    return $j['checked'];
}, $zaito_jobs ) );

$zaito_title = '大学生OKの完全在宅求人・インターン一覧 | zaito';
$zaito_desc  = '出社なしで働ける、大学生OKの在宅求人・長期インターンを、zaito運営が1件ずつ確認してまとめています。SNS運用、Webマーケ、教育、ライティング、エンジニアなど。';

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
    'name'            => '大学生OKの完全在宅求人',
    'itemListElement' => $zaito_items,
) );

$zaito_card = function ( $j ) {
    $group = zaito_remote_group( $j['category'] );
    ?>
    <li class="card<?php echo ! empty( $j['closed'] ) ? ' closed' : ''; ?>" data-group="<?php echo esc_attr( $group ); ?>">
      <a href="<?php echo esc_url( zaito_remote_url( $j['slug'] ) ); ?>">
        <div class="top">
          <span class="cat"><?php echo esc_html( $j['category'] ); ?></span>
          <?php if ( ! empty( $j['closed'] ) ) : ?>
            <span class="warn">募集終了</span>
          <?php elseif ( ! empty( $j['caution'] ) ) : ?>
            <span class="warn"><span class="ms" aria-hidden="true">info</span>条件あり</span>
          <?php else : ?>
            <span class="ok"><span class="ms" aria-hidden="true">verified</span>完全在宅を確認</span>
          <?php endif; ?>
        </div>
        <h2><?php echo esc_html( $j['title'] ); ?></h2>
        <span class="co"><?php echo esc_html( $j['company'] ); ?></span>
        <dl>
          <dt><span class="ms" aria-hidden="true">payments</span>報酬</dt><dd><?php echo esc_html( $j['pay'] ? $j['pay'] : '元のページで確認' ); ?></dd>
          <dt><span class="ms" aria-hidden="true">schedule</span>稼働</dt><dd><?php echo esc_html( $j['hours'] ? $j['hours'] : '元のページで確認' ); ?></dd>
          <dt><span class="ms" aria-hidden="true">school</span>対象</dt><dd><?php echo esc_html( $j['target'] ); ?></dd>
        </dl>
      </a>
    </li>
    <?php
};
?>
<body>
<?php zaito_remote_header(); ?>

<main class="wrap">
  <div class="lh">
    <span class="eb">REMOTE JOBS</span>
    <h1>大学生OKの、完全在宅の求人</h1>
    <p>「在宅」で探しても出社ありの求人が混ざる。そんな手間をなくすため、出社なしで働ける学生向けの求人を、zaito運営が1件ずつ確認してまとめています。</p>
  </div>

  <ol class="how" aria-label="使い方">
    <li><span class="ms" aria-hidden="true">verified</span><span><b>在宅の条件を確認済み</b>求人ページを運営が読み、出社の有無を確かめています。</span></li>
    <li><span class="ms" aria-hidden="true">mail</span><span><b>無料登録で応募ページへ</b>メールアドレスだけで登録でき、新着求人もお知らせします。</span></li>
    <li><span class="ms" aria-hidden="true">open_in_new</span><span><b>応募は企業の募集ページから</b>各企業の募集ページ（Wantedlyなど）から応募します。</span></li>
  </ol>

  <?php if ( count( $zaito_groups ) > 1 ) : ?>
    <div class="filters" role="group" aria-label="職種で絞り込む">
      <button type="button" data-filter="" aria-pressed="true">すべて</button>
      <?php foreach ( array_keys( $zaito_groups ) as $zaito_g ) : ?>
        <button type="button" data-filter="<?php echo esc_attr( $zaito_g ); ?>" aria-pressed="false"><?php echo esc_html( $zaito_g ); ?></button>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <p class="count" aria-live="polite"><span id="zcount"><?php echo esc_html( count( $zaito_open ) ); ?></span>件の求人（<?php echo esc_html( date_i18n( 'Y年n月j日', strtotime( $zaito_checked ) ) ); ?>に確認）</p>
  <ul class="cards" id="zcards">
    <?php foreach ( $zaito_open as $zaito_j ) { $zaito_card( $zaito_j ); } ?>
  </ul>

  <?php if ( $zaito_closed ) : ?>
    <h2 style="margin:56px 0 0;font-size:18px">募集が終了した求人</h2>
    <ul class="cards">
      <?php foreach ( $zaito_closed as $zaito_j ) { $zaito_card( $zaito_j ); } ?>
    </ul>
  <?php endif; ?>

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
