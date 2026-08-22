<?php
$zaito_login_redirect_to = ! empty( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : home_url( '/mypage/' );
get_header();
?>

<main class="auth-main">
  <div class="auth-container">
    <div class="auth-card">
      <h1>ログイン</h1>

      <?php if ( isset( $_GET['login'] ) && $_GET['login'] === 'failed' ) : ?>
        <div class="auth-error">
          メールアドレスまたはパスワードが正しくありません
        </div>
      <?php endif; ?>

      <form method="post" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" class="auth-form">
        <input type="hidden" name="redirect_to" value="<?php echo esc_url( $zaito_login_redirect_to ); ?>" />
        <div class="form-group">
          <label for="user_login">メールアドレス（またはユーザー名）</label>
          <input
            type="text"
            id="user_login"
            name="log"
            autocomplete="username"
            value="<?php echo isset( $_POST['log'] ) ? esc_attr( $_POST['log'] ) : ''; ?>"
            required
          />
        </div>

        <div class="form-group">
          <label for="user_pass">パスワード</label>
          <input
            type="password"
            id="user_pass"
            name="pwd"
            required
          />
        </div>

        <button type="submit" class="btn btn-accent btn-block">ログイン</button>
      </form>

      <p class="auth-link">
        アカウントをお持ちでない方は <a href="<?php echo esc_url( home_url( '/register/' ) ); ?>">こちらから登録</a>
      </p>

      <div class="auth-divider">
        <span>企業の方は</span>
      </div>

      <a href="<?php echo esc_url( home_url( '/company-login/' ) ); ?>" class="btn btn-outline btn-block">
        企業ログイン
      </a>
    </div>
  </div>
</main>

<?php get_footer(); ?>
