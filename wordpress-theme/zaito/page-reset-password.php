<?php
/**
 * 新しいパスワードを決める（/reset-password/?login=&key=）。保存は functions.php の zaito_handle_reset_password。
 */
require_once get_template_directory() . '/lp/biz-parts.php';
$zaito_login = isset( $_GET['login'] ) ? sanitize_text_field( wp_unslash( $_GET['login'] ) ) : '';
$zaito_key   = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';
$zaito_user  = ( $zaito_login && $zaito_key ) ? check_password_reset_key( $zaito_key, $zaito_login ) : new WP_Error( 'invalid_key', '' );
zaito_biz_head( '新しいパスワード' );
?>
<body>
<?php zaito_biz_auth_header( 'login' ); ?>
<div class="biz-auth">
<main class="auth">
  <h1>新しいパスワード</h1>
  <?php if ( is_wp_error( $zaito_user ) ) : ?>
    <div class="note n-err"><span class="ms" aria-hidden="true">warning</span><div><b>リンクが無効か、期限が切れています</b><p>もう一度、再設定のリンクを送ってください。</p></div></div>
    <a class="btn btn-p btn-lg" href="<?php echo esc_url( home_url( '/forgot-password/' ) ); ?>">再設定をやり直す</a>
  <?php else : ?>
    <p class="lead">8文字以上で決めてください。</p>
    <?php zaito_biz_errors(); ?>
    <form class="form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
      <input type="hidden" name="action" value="zaito_reset_password">
      <input type="hidden" name="login" value="<?php echo esc_attr( $zaito_login ); ?>">
      <input type="hidden" name="key" value="<?php echo esc_attr( $zaito_key ); ?>">
      <?php wp_nonce_field( 'zaito_reset_password' ); ?>
      <div class="fld"><label for="password">新しいパスワード</label><input type="password" id="password" name="password" minlength="8" autocomplete="new-password" required></div>
      <div class="fld"><label for="password_confirm">もう一度入力</label><input type="password" id="password_confirm" name="password_confirm" minlength="8" autocomplete="new-password" required></div>
      <button type="submit" class="btn btn-p btn-lg">パスワードを変更する</button>
    </form>
  <?php endif; ?>
</main>
</div>
<?php zaito_biz_auth_footer(); ?>
</body>
</html>
