<?php
/**
 * 企業の応募者一覧（/company-applicants/）。状態ごとに絞り込み、行を押すとメッセージ（/chat/?c=）を開く。
 * 応募の状態はメッセージの画面の右側で変える。
 */
require_once get_template_directory() . '/lp/biz-parts.php';
$zaito_user = zaito_require_company();
$zaito_lbl  = zaito_direct_app_statuses();
$zaito_all  = zaito_direct_applications( 'company', $zaito_user->ID );
$zaito_view = isset( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : 'active';

$zaito_views = array(
    'active'    => array( '対応中', function ( $a ) {
        return in_array( zaito_direct_app_status( $a->ID ), array( 'new', 'talking', 'interview' ), true );
    } ),
    'unread'    => array( '未読', function ( $a ) {
        return (bool) get_post_meta( $a->ID, 'unread_company', true );
    } ),
    'new'       => array( '新着', function ( $a ) {
        return 'new' === zaito_direct_app_status( $a->ID );
    } ),
    'interview' => array( '面接', function ( $a ) {
        return 'interview' === zaito_direct_app_status( $a->ID );
    } ),
    'offer'     => array( '内定', function ( $a ) {
        return 'offer' === zaito_direct_app_status( $a->ID );
    } ),
    'done'      => array( '終了', function ( $a ) {
        return in_array( zaito_direct_app_status( $a->ID ), array( 'rejected', 'declined' ), true );
    } ),
    'all'       => array( 'すべて', '__return_true' ),
);
if ( ! isset( $zaito_views[ $zaito_view ] ) ) {
    $zaito_view = 'active';
}
$zaito_list = array_filter( $zaito_all, $zaito_views[ $zaito_view ][1] );
// 未読を先に。
usort( $zaito_list, function ( $a, $b ) {
    return (int) get_post_meta( $b->ID, 'unread_company', true ) - (int) get_post_meta( $a->ID, 'unread_company', true );
} );
zaito_biz_head( '応募者' );
?>
<body>
<?php zaito_biz_header( 'applicants' ); ?>
<main class="wrap bz">
  <div class="biz-title"><div><h1>応募者</h1><p>名前を押すと、応募の内容とメッセージが開きます。</p></div></div>

  <nav class="seg" aria-label="応募者の絞り込み">
    <?php foreach ( $zaito_views as $zaito_k => $zaito_vw ) : ?>
      <a href="<?php echo esc_url( add_query_arg( 'view', $zaito_k, home_url( '/company-applicants/' ) ) ); ?>"<?php echo $zaito_k === $zaito_view ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $zaito_vw[0] ); ?><small><?php echo count( array_filter( $zaito_all, $zaito_vw[1] ) ); ?></small></a>
    <?php endforeach; ?>
  </nav>

  <?php if ( $zaito_list ) : ?>
    <ul class="rows">
      <?php foreach ( $zaito_list as $zaito_a ) : ?>
        <?php
        $zaito_st   = zaito_direct_app_status( $zaito_a->ID );
        $zaito_job  = get_post( (int) get_post_meta( $zaito_a->ID, 'job_id', true ) );
        $zaito_new  = (bool) get_post_meta( $zaito_a->ID, 'unread_company', true );
        $zaito_last = (string) get_post_meta( $zaito_a->ID, 'last_message_text', true );
        ?>
        <li>
          <div class="main"><b><a href="<?php echo esc_url( zaito_direct_chat_url( $zaito_a->ID ) ); ?>"><?php if ( $zaito_new ) : ?><span class="dot-new" aria-label="未読"></span><?php endif; ?><?php echo esc_html( get_post_meta( $zaito_a->ID, 'applicant_name', true ) ); ?></a>
            <span style="font-weight:400;font-size:13px;color:var(--z-text-3);margin-left:6px"><?php echo esc_html( zaito_direct_applicant_line( $zaito_a->ID ) ); ?></span></b>
            <span class="rsub"><?php echo esc_html( $zaito_job ? $zaito_job->post_title : '' ); ?>　／　<?php echo esc_html( mb_strimwidth( preg_replace( '/\s+/u', ' ', $zaito_last ), 0, 80, '…' ) ); ?></span></div>
          <span class="meta">応募 <?php echo esc_html( get_the_date( 'n/j', $zaito_a ) ); ?></span>
          <?php echo zaito_biz_status_pill( $zaito_st, $zaito_lbl[ $zaito_st ] ); // エスケープ済み ?>
          <div class="acts"><a class="btn <?php echo $zaito_new ? 'btn-p' : 'btn-g'; ?>" href="<?php echo esc_url( zaito_direct_chat_url( $zaito_a->ID ) ); ?>">開く</a></div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else : ?>
    <div class="rows"><div class="empty" style="flex-direction:column"><?php echo $zaito_all ? 'この条件の応募はありません。' : 'まだ応募はありません。求人が公開されると、ここに応募が表示されます。'; ?></div></div>
  <?php endif; ?>
</main>
<?php zaito_biz_footer(); ?>
</body>
</html>
