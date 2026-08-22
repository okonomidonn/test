<?php
$registration_errors = zaito_get_form_errors_from_token();
get_header();
?>

<main class="auth-main">
  <div class="auth-container">
    <div class="auth-card">
      <h1>企業登録</h1>

      <?php if ( ! empty( $registration_errors ) ) : ?>
        <div class="auth-error">
          <?php foreach ( $registration_errors as $error ) : ?>
            <p><?php echo esc_html( $error ); ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="auth-form">
        <input type="hidden" name="action" value="zaito_register_company" />

        <div class="form-group">
          <label for="company_name">企業名</label>
          <input type="text" id="company_name" name="company_name" required />
        </div>

        <div class="form-group">
          <label for="contact_person">ご担当者名</label>
          <input type="text" id="contact_person" name="contact_person" required />
        </div>

        <div class="form-group">
          <label for="phone">電話番号</label>
          <input type="tel" id="phone" name="phone" required />
        </div>

        <div class="form-group">
          <label for="email">メールアドレス</label>
          <input type="email" id="email" name="email" required />
        </div>

        <div class="form-group">
          <label for="password">パスワード</label>
          <input type="password" id="password" name="password" required />
          <small>8文字以上で設定してください</small>
        </div>

        <div class="form-group">
          <label for="password_confirm">パスワード（確認）</label>
          <input type="password" id="password_confirm" name="password_confirm" required />
        </div>

        <button type="submit" class="btn btn-accent btn-block">登録</button>
      </form>

      <p class="auth-link">
        企業アカウントをお持ちの方は <a href="<?php echo esc_url( home_url( '/company-login/' ) ); ?>">こちらからログイン</a>
      </p>
    </div>
  </div>
</main>

<?php get_footer(); ?>
