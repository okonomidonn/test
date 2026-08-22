<?php
$uid   = isset( $_GET['uid'] ) ? intval( $_GET['uid'] ) : 0;
$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';

$status = 'invalid';

if ( $uid && $token ) {
    $stored_token   = get_user_meta( $uid, 'zaito_email_verify_token', true );
    $expires        = (int) get_user_meta( $uid, 'zaito_email_verify_expires', true );

    if ( zaito_is_email_verified( $uid ) ) {
        $status = 'already';
    } elseif ( $stored_token && hash_equals( $stored_token, $token ) ) {
        if ( $expires && $expires < time() ) {
            $status = 'expired';
        } else {
            update_user_meta( $uid, 'zaito_email_verified', '1' );
            delete_user_meta( $uid, 'zaito_email_verify_token' );
            delete_user_meta( $uid, 'zaito_email_verify_expires' );
            $status = 'success';
        }
    }
}

get_header();
?>

<main class="auth-main">
  <div class="auth-container">
    <div class="auth-card">
      <h1>メールアドレスの確認</h1>

      <?php if ( $status === 'success' ) : ?>
        <p class="auth-success-message">メールアドレスの確認が完了しました。ご登録ありがとうございます。</p>
      <?php elseif ( $status === 'already' ) : ?>
        <p class="auth-success-message">このメールアドレスは既に確認済みです。</p>
      <?php elseif ( $status === 'expired' ) : ?>
        <div class="auth-error">確認リンクの有効期限が切れています。ログイン後、確認メールを再送信してください。</div>
      <?php else : ?>
        <div class="auth-error">確認リンクが無効です。URLをご確認いただくか、ログイン後に確認メールを再送信してください。</div>
      <?php endif; ?>

      <?php if ( is_user_logged_in() ) : ?>
        <a href="<?php echo esc_url( home_url( '/mypage/' ) ); ?>" class="btn btn-accent btn-block">マイページへ</a>
      <?php else : ?>
        <a href="<?php echo esc_url( home_url( '/login/' ) ); ?>" class="btn btn-accent btn-block">ログインへ</a>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php get_footer(); ?>
