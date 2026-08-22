<?php
$login = isset( $_GET['login'] ) ? sanitize_text_field( wp_unslash( $_GET['login'] ) ) : '';
$key   = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';

$reset_user = ( $login && $key ) ? check_password_reset_key( $key, $login ) : new WP_Error( 'invalid_key', '' );
$reset_errors = zaito_get_form_errors_from_token();

get_header();
?>

<main class="auth-main">
  <div class="auth-container">
    <div class="auth-card">
      <h1>新しいパスワードの設定</h1>

      <?php if ( is_wp_error( $reset_user ) ) : ?>
        <div class="auth-error">
          <p>リンクが無効か、有効期限が切れています。もう一度パスワード再設定をお試しください。</p>
        </div>
        <p class="auth-link">
          <a href="<?php echo esc_url( home_url( '/forgot-password/' ) ); ?>">パスワード再設定をやり直す</a>
        </p>
      <?php else : ?>

        <?php if ( ! empty( $reset_errors ) ) : ?>
          <div class="auth-error">
            <?php foreach ( $reset_errors as $error ) : ?>
              <p><?php echo esc_html( $error ); ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="auth-form">
          <input type="hidden" name="action" value="zaito_reset_password" />
          <input type="hidden" name="login" value="<?php echo esc_attr( $login ); ?>" />
          <input type="hidden" name="key" value="<?php echo esc_attr( $key ); ?>" />
          <?php wp_nonce_field( 'zaito_reset_password' ); ?>

          <div class="form-group">
            <label for="password">新しいパスワード</label>
            <input type="password" id="password" name="password" required />
            <small>8文字以上で設定してください</small>
          </div>

          <div class="form-group">
            <label for="password_confirm">新しいパスワード（確認）</label>
            <input type="password" id="password_confirm" name="password_confirm" required />
          </div>

          <button type="submit" class="btn btn-accent btn-block">パスワードを再設定</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php get_footer(); ?>
