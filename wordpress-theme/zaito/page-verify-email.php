<?php
/**
 * 企業アカウントのメールアドレス確認（/verify-email/?uid=&token=）。リンクは zaito_send_verification_email() が送る。
 */
require_once get_template_directory() . '/lp/biz-parts.php';
$zaito_uid    = isset( $_GET['uid'] ) ? (int) $_GET['uid'] : 0;
$zaito_token  = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
$zaito_status = 'invalid';

if ( $zaito_uid && $zaito_token ) {
    $zaito_stored  = get_user_meta( $zaito_uid, 'zaito_email_verify_token', true );
    $zaito_expires = (int) get_user_meta( $zaito_uid, 'zaito_email_verify_expires', true );
    if ( zaito_is_email_verified( $zaito_uid ) ) {
        $zaito_status = 'already';
    } elseif ( $zaito_stored && hash_equals( $zaito_stored, $zaito_token ) ) {
        if ( $zaito_expires && $zaito_expires < time() ) {
            $zaito_status = 'expired';
        } else {
            update_user_meta( $zaito_uid, 'zaito_email_verified', '1' );
            delete_user_meta( $zaito_uid, 'zaito_email_verify_token' );
            delete_user_meta( $zaito_uid, 'zaito_email_verify_expires' );
            $zaito_status = 'success';
        }
    }
}
$zaito_next = is_user_logged_in() && zaito_is_company() ? home_url( '/company/' ) : home_url( '/company-login/' );
zaito_biz_head( 'メールアドレスの確認' );
?>
<body>
<?php zaito_remote_header( 'company' ); ?>
<main class="auth">
  <h1>メールアドレスの確認</h1>
  <?php if ( 'success' === $zaito_status || 'already' === $zaito_status ) : ?>
    <div class="note n-ok"><span class="ms" aria-hidden="true">check_circle</span><div><b><?php echo 'success' === $zaito_status ? '確認が終わりました' : 'このアドレスは確認済みです'; ?></b><p>応募のお知らせは、このアドレスに届きます。</p></div></div>
  <?php elseif ( 'expired' === $zaito_status ) : ?>
    <div class="note n-err"><span class="ms" aria-hidden="true">warning</span><div><b>リンクの期限が切れています</b><p>ログインして、ホームの「確認メールを送り直す」を押してください。</p></div></div>
  <?php else : ?>
    <div class="note n-err"><span class="ms" aria-hidden="true">warning</span><div><b>リンクが無効です</b><p>メールのリンクをもう一度開くか、ログインして確認メールを送り直してください。</p></div></div>
  <?php endif; ?>
  <a class="btn btn-p btn-lg" href="<?php echo esc_url( $zaito_next ); ?>"><?php echo is_user_logged_in() && zaito_is_company() ? 'ホームへ' : 'ログインへ'; ?></a>
</main>
<?php zaito_remote_footer(); ?>
</body>
</html>
