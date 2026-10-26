<?php
/**
 * 「応募はできましたか？」の回答ページ（/apply-check/）。inc/apply-track.php から $zaito_ac を受け取る。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
require_once __DIR__ . '/remote-parts.php';

zaito_remote_head( '応募の状況を教えてください | zaito', '応募の状況を教えてください。', home_url( '/apply-check/' ) );
?>
<body>
<?php zaito_remote_header(); ?>
<main class="wrap doc ac">
  <a class="back" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span class="ms" aria-hidden="true">arrow_back</span>求人一覧へ</a>
  <?php if ( ! $zaito_ac['ok'] ) : ?>
    <h1>リンクの期限が切れています</h1>
    <p class="lead">お手数ですが、<a href="<?php echo esc_url( home_url( '/mypage/' ) ); ?>">マイページ</a>から、もう一度お試しください。</p>
  <?php elseif ( $zaito_ac['saved'] ) : ?>
    <div class="my-done" role="status">
      <span class="ms" aria-hidden="true">check_circle</span>
      <div><b>ありがとうございました</b><p>教えていただいた内容は、掲載する求人を選ぶのに使います。採用された方、おめでとうございます。</p>
      <a class="btn btn-p" href="<?php echo esc_url( home_url( '/' ) ); ?>">ほかの求人を見る</a></div>
    </div>
  <?php elseif ( ! $zaito_ac['rows'] ) : ?>
    <h1>いま教えていただくことはありません</h1>
    <p class="lead"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">完全在宅の求人を見る</a></p>
  <?php else : ?>
    <h1>応募の状況を教えてください</h1>
    <p class="lead">zaitoから募集ページを見た求人です。当てはまるものを選んで、最後に「送信する」を押してください。</p>
    <form method="post" action="<?php echo esc_url( home_url( '/apply-check/' ) ); ?>">
      <input type="hidden" name="k" value="<?php echo esc_attr( $zaito_ac['k'] ); ?>">
      <?php foreach ( $zaito_ac['rows'] as $zaito_r ) : $zaito_j = $zaito_r['job']; ?>
        <fieldset class="ac-job">
          <legend><span><?php echo esc_html( $zaito_j['company'] ); ?></span><?php echo esc_html( $zaito_j['title'] ); ?></legend>
          <p class="ac-q"><?php echo 2 === $zaito_r['stage'] ? '選考の結果は？' : '応募しましたか？'; ?></p>
          <div class="chips">
            <?php foreach ( zaito_ac_choices( $zaito_r['stage'] ) as $zaito_v => $zaito_l ) : ?>
              <label><input type="radio" name="a[<?php echo esc_attr( $zaito_j['slug'] ); ?>]" value="<?php echo esc_attr( $zaito_v ); ?>"<?php checked( $zaito_r['status'], $zaito_v ); ?>><span><?php echo esc_html( $zaito_l ); ?></span></label>
            <?php endforeach; ?>
          </div>
        </fieldset>
      <?php endforeach; ?>
      <button type="submit" class="btn btn-p btn-lg">送信する</button>
    </form>
  <?php endif; ?>
</main>
<?php zaito_remote_footer(); ?>
</body>
</html>
