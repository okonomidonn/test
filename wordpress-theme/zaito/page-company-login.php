<?php get_header(); ?>

<main class="auth-main">
  <div class="auth-container">
    <div class="auth-card">
      <h1>企業ログイン</h1>

      <?php if ( isset( $_GET['login'] ) && $_GET['login'] === 'failed' ) : ?>
        <div class="auth-error">
          メールアドレスまたはパスワードが正しくありません
        </div>
      <?php endif; ?>

      <form method="post" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" class="auth-form">
        <div class="form-group">
          <label for="user_login">メールアドレス</label>
          <input
            type="email"
            id="user_login"
            name="log"
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
        企業アカウントをお持ちでない方は <a href="<?php echo esc_url( home_url( '/company-register/' ) ); ?>">こちらから登録</a>
      </p>

      <div class="auth-divider">
        <span>ワーカーの方は</span>
      </div>

      <a href="<?php echo esc_url( home_url( '/login/' ) ); ?>" class="btn btn-outline btn-block">
        ワーカーログイン
      </a>
    </div>
  </div>
</main>

<?php get_footer(); ?>
