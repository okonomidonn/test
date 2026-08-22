<?php get_header(); ?>

<main class="auth-main">
  <div class="auth-container">
    <div class="auth-card">
      <h1>パスワードをお忘れの方</h1>

      <?php if ( isset( $_GET['sent'] ) && $_GET['sent'] === '1' ) : ?>
        <p class="auth-success-message">
          ご入力いただいたメールアドレス宛にパスワード再設定用のメールを送信しました（該当するアカウントが存在する場合）。メールをご確認ください。
        </p>
      <?php else : ?>
        <p class="auth-description">ご登録のメールアドレスを入力してください。パスワード再設定用のリンクをお送りします。</p>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="auth-form">
          <input type="hidden" name="action" value="zaito_forgot_password" />
          <?php wp_nonce_field( 'zaito_forgot_password' ); ?>

          <div class="form-group">
            <label for="email">メールアドレス</label>
            <input type="email" id="email" name="email" required />
          </div>

          <button type="submit" class="btn btn-accent btn-block">再設定メールを送信</button>
        </form>
      <?php endif; ?>

      <p class="auth-link">
        <a href="<?php echo esc_url( home_url( '/login/' ) ); ?>">ログイン画面に戻る</a>
      </p>
    </div>
  </div>
</main>

<?php get_footer(); ?>
